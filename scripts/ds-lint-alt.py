import pathlib
import re
import sys


def tags(text):
    for match in re.finditer(r'<img\b', text):
        i = match.end()
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
                    break
            i += 1
        yield text[match.start():i]


def main():
    bad = []
    for root in [pathlib.Path(p) for p in sys.argv[1:]] or [pathlib.Path('resources/views')]:
        for path in root.rglob('*.blade.php'):
            parts = set(path.parts)
            if 'blatui' in parts or 'emails' in parts:
                continue
            for tag in tags(path.read_text(errors='ignore')):
                if not re.search(r'\balt[\s=]', tag):
                    bad.append(f'{path}: ' + re.sub(r'\s+', ' ', tag)[:90])
    if bad:
        print('\n'.join(bad))
        return 1
    return 0


if __name__ == '__main__':
    sys.exit(main())
