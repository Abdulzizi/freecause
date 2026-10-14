import pathlib
import re
import sys

ALLOW = pathlib.Path(__file__).with_name('ds-lint-a11y-allow.txt')
VOID = {'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'}
# Public-site UX gate. Admin/BlatUI is a non-goal of the UX plan and owns a
# sibling-label convention, tracked separately; add 'admin' here to re-enable.
SKIP = {'blatui', 'emails', 'admin'}
NAME_ATTRS = ('aria-label', 'aria-labelledby', 'title')
INPUT_SKIP = {'hidden', 'submit', 'button', 'reset', 'image'}


def tag_end(text, i):
    depth = 0
    quote = None
    while i < len(text):
        ch = text[i]
        if text.startswith('{{', i):
            depth += 1
            i += 2
            continue
        if text.startswith('}}', i) and depth:
            depth -= 1
            i += 2
            continue
        if depth == 0:
            if quote:
                if ch == quote:
                    quote = None
            elif ch in '"\'':
                quote = ch
            elif ch == '>':
                return i
        i += 1
    return None


def attr_value(tag, attr):
    m = re.search(r'(?<![\w-])' + re.escape(attr) + r'\s*=\s*(["\'])(.*?)\1', tag, re.S)
    if m:
        return m.group(2)
    if re.search(r'(?<![\w-])' + re.escape(attr) + r'\s*=', tag):
        return 'dynamic'
    return None


def has_name(tag):
    if '{{' in tag or '{!!' in tag:
        return True
    for attr in NAME_ATTRS:
        val = attr_value(tag, attr)
        if val is not None and val.strip() != '':
            return True
    return False


def inner_named(inner):
    if not inner:
        return False
    if '{{' in inner or '{!!' in inner or '<x-' in inner or '<x-' in inner.lower():
        return True
    if re.search(r'<img\b[^>]*\balt\s*=\s*(["\']).+?\1', inner, re.S | re.I):
        return True
    text = re.sub(r'<[^>]*>', ' ', inner)
    text = re.sub(r'&[a-z#0-9]+;', ' ', text, flags=re.I)
    return text.strip() != ''


def in_label(text, idx):
    opens = [m.start() for m in re.finditer(r'<label\b', text, re.I)]
    closes = [m.start() for m in re.finditer(r'</label\s*>', text, re.I)]
    last_close = max([c for c in closes if c < idx], default=-1)
    last_open = max([o for o in opens if o < idx], default=-1)
    return last_open > last_close


def labelled(text, tag, start):
    if not tag:
        return True
    if '{{' in tag or '{!!' in tag:
        return True
    for attr in NAME_ATTRS:
        val = attr_value(tag, attr)
        if val is not None and val.strip() != '':
            return True
    mid = attr_value(tag, 'id')
    if mid and mid != 'dynamic' and mid.strip():
        if re.search(r'(?<![\w-])for\s*=\s*["\']' + re.escape(mid.strip()) + r'["\']', text):
            return True
    if in_label(text, start):
        return True
    return False


def element(text, start, name):
    end = tag_end(text, start)
    if end is None:
        return None
    tag = text[start:end + 1]
    if name in VOID or tag.rstrip().endswith('/>'):
        return tag, '', end + 1
    close = re.search(r'</' + re.escape(name) + r'\s*>', text[end:], re.I)
    if not close:
        return tag, '', end + 1
    return tag, text[end + 1:end + close.start()], end + 1


def all_tags(text):
    for m in re.finditer(r'<([a-zA-Z][\w:-]*)', text):
        end = tag_end(text, m.start())
        if end is None:
            continue
        yield m.group(1).lower(), m.start(), text[m.start():end + 1]


def container_inner(text, start, name):
    end = tag_end(text, start)
    if end is None:
        return None
    pat = re.compile(r'</?' + re.escape(name) + r'(?=[\s>/])', re.I)
    depth = 1
    for m in pat.finditer(text, end + 1):
        if text[m.start():m.start() + 2] == '</':
            depth -= 1
            if depth == 0:
                return end + 1, m.start()
        else:
            te = tag_end(text, m.start())
            if te is not None and text[te - 1] != '/':
                depth += 1
    return None


def mask_comments(text):
    return re.sub(r'<!--.*?-->', lambda m: re.sub(r'[^\n]', ' ', m.group(0)), text, flags=re.S)


def focusable_in(inner):
    m = re.search(r'tabindex\s*=\s*["\']0["\']', inner, re.I)
    if m:
        return m
    for m in re.finditer(r'<(a|button|select|textarea|input)\b', inner, re.I):
        tag = inner[m.start():m.end() + 200]
        end = tag_end(inner, m.start())
        tag = inner[m.start():end + 1] if end is not None else tag
        if m.group(1).lower() == 'input':
            t = attr_value(tag, 'type')
            if t and t.strip().lower().strip('"\'') in INPUT_SKIP:
                continue
        return m
    return None


def add(findings, path, text, rule, idx, snippet):
    line = text.count('\n', 0, idx) + 1
    findings.append((path, line, rule, re.sub(r'\s+', ' ', snippet).strip()[:88]))


def scan(path, text, findings):
    for name, start, tag in all_tags(text):
        if name in ('a', 'button'):
            _, inner, _ = element(text, start, name) or (tag, '', 0)
            if not has_name(tag) and not inner_named(inner):
                add(findings, path, text, 'empty-link' if name == 'a' else 'empty-button', start, tag)
        elif name in ('input', 'select', 'textarea'):
            if name == 'input':
                t = attr_value(tag, 'type')
                if t and t.strip().lower().strip('"\'') in INPUT_SKIP:
                    continue
            if not labelled(text, tag, start):
                add(findings, path, text, 'unlabelled-control', start, tag)
        if re.search(r'\baria-hidden\s*=\s*["\']true["\']', tag, re.I):
            span = container_inner(text, start, name)
            if span:
                inner = text[span[0]:span[1]]
                m = focusable_in(inner)
                if m:
                    add(findings, path, text, 'focus-in-aria-hidden', span[0] + m.start(), inner[m.start():m.start() + 88])

    for m in re.finditer(r'<img\b', text):
        end = tag_end(text, m.start())
        tag = text[m.start():end + 1] if end is not None else text[m.start():m.start() + 90]
        if not re.search(r'\balt[\s=]', tag):
            add(findings, path, text, 'img-alt', m.start(), tag)


def load_allow():
    allowed = set()
    if ALLOW.exists():
        for line in ALLOW.read_text().splitlines():
            line = line.strip()
            if line and not line.startswith('#'):
                allowed.add(line)
    return allowed


def main():
    roots = [pathlib.Path(p) for p in sys.argv[1:]] or [pathlib.Path('resources/views')]
    allowed = load_allow()
    findings = []
    for root in roots:
        for path in sorted(root.rglob('*.blade.php')):
            parts = set(path.parts)
            if parts & SKIP:
                continue
            scan(str(path), mask_comments(path.read_text(errors='ignore')), findings)

    blocked = []
    for path, line, rule, snippet in findings:
        status = 'skip' if f'{path}:{rule}' in allowed else 'fail'
        if status == 'fail':
            blocked.append((path, line, rule, snippet))

    for path, line, rule, snippet in blocked:
        print(f'{path}:{line}: {rule}: {snippet}')
    return 1 if blocked else 0


if __name__ == '__main__':
    sys.exit(main())
