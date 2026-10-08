<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SanitizerAdversarialTest extends TestCase
{
    public function test_removes_script_tag_and_contents(): void
    {
        foreach ($this->both('<script>alert(1)</script>') as $out) {
            $this->assertStringNotContainsString('script', $out);
            $this->assertStringNotContainsString('alert(1)', $out);
        }
    }

    public function test_removes_event_handler_attributes(): void
    {
        $payloads = [
            '<img src="/a.png" onerror="alert(1)">',
            '<p onclick="x()">click</p>',
        ];

        foreach ($payloads as $payload) {
            foreach ($this->both($payload) as $out) {
                $this->assertStringNotContainsString('onerror', $out);
                $this->assertStringNotContainsString('onclick', $out);
                $this->assertStringNotContainsString('alert(1)', $out);
            }
        }
    }

    public function test_removes_javascript_urls_in_all_encodings(): void
    {
        $payloads = [
            '<a href="javascript:alert(1)">L</a>',
            '<a href="jav&#x61;script:alert(1)">L</a>',
            "<a href=\"java\tscript:alert(1)\">L</a>",
        ];

        foreach ($payloads as $payload) {
            foreach ($this->both($payload) as $out) {
                $this->assertStringNotContainsString('javascript', $out);
                $this->assertStringNotContainsString('script', $out);
            }
        }
    }

    public function test_removes_data_uris_from_src_and_srcset(): void
    {
        $payloads = [
            '<img src="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==">',
            '<img src="/ok.png" srcset="data:image/png;base64,iVBORw0KGgo= 1x">',
        ];

        foreach ($payloads as $payload) {
            foreach ($this->both($payload) as $out) {
                $this->assertStringNotContainsString('data:', $out);
            }
        }
    }

    public function test_removes_protocol_relative_urls(): void
    {
        $payloads = [
            '<a href="//evil.test/x">L</a>',
            '<img src="//evil.test/x">',
        ];

        foreach ($payloads as $payload) {
            foreach ($this->both($payload) as $out) {
                $this->assertStringNotContainsString('evil.test', $out);
            }
        }
    }

    public function test_removes_svg_and_math_payloads(): void
    {
        foreach ($this->both('<svg onload="alert(1)"><circle r="1"></circle></svg>') as $out) {
            $this->assertStringNotContainsString('svg', $out);
            $this->assertStringNotContainsString('onload', $out);
            $this->assertStringNotContainsString('alert(1)', $out);
        }

        $math = '<math><mtext><mglyph xlink:href="javascript:alert(1)"></mglyph></mtext></math>';
        foreach ($this->both($math) as $out) {
            $this->assertStringNotContainsString('math', $out);
            $this->assertStringNotContainsString('javascript', $out);
            $this->assertStringNotContainsString('alert(1)', $out);
        }
    }

    public function test_removes_embedded_frames_forms_and_objects(): void
    {
        foreach ($this->both('<iframe src="https://evil.test/f">IFRAME_CONTENTS</iframe>') as $out) {
            $this->assertStringNotContainsString('iframe', $out);
            $this->assertStringNotContainsString('IFRAME_CONTENTS', $out);
        }

        $form = '<form action="https://evil.test/s"><input name="q"><button>BUTTON_CONTENTS</button></form>';
        foreach ($this->both($form) as $out) {
            $this->assertStringNotContainsString('<form', $out);
            $this->assertStringNotContainsString('<input', $out);
            $this->assertStringNotContainsString('<button', $out);
            $this->assertStringNotContainsString('BUTTON_CONTENTS', $out);
        }

        foreach ($this->both('<object data="https://evil.test/o">OBJECT_CONTENTS</object>') as $out) {
            $this->assertStringNotContainsString('object', $out);
            $this->assertStringNotContainsString('OBJECT_CONTENTS', $out);
        }

        foreach ($this->both('<embed src="https://evil.test/e">') as $out) {
            $this->assertStringNotContainsString('embed', $out);
        }

        foreach ($this->both('<base href="//evil.test/b">') as $out) {
            $this->assertStringNotContainsString('base', $out);
            $this->assertStringNotContainsString('evil.test', $out);
        }
    }

    public function test_neutralises_mxss_payloads(): void
    {
        $payloads = [
            '<noscript><p title="</noscript><img src=x onerror=alert(1)>"></noscript>',
            '<template><img src=x onerror=alert(1)></template>',
            '<xmp><img src=x onerror=alert(1)></xmp>',
            '<plaintext><img src=x onerror=alert(1)>',
        ];

        foreach ($payloads as $payload) {
            foreach ($this->both($payload) as $out) {
                $this->assertStringNotContainsString('onerror', $out);
                $this->assertStringNotContainsString('alert(1)', $out);
            }
        }
    }

    public function test_removes_style_element_and_contents(): void
    {
        foreach ($this->both('<style>body{background:url(//evil.test/a)}</style>') as $out) {
            $this->assertStringNotContainsString('style', $out);
            $this->assertStringNotContainsString('background', $out);
        }
    }

    public function test_removes_comments_containing_script(): void
    {
        foreach ($this->both('<!-- <script>alert(1)</script> -->') as $out) {
            $this->assertStringNotContainsString('script', $out);
            $this->assertStringNotContainsString('alert(1)', $out);
        }
    }

    public function test_removes_urls_with_control_characters(): void
    {
        $payloads = [
            "<a href=\"java\0script:alert(1)\">L</a>",
            "<img src=\"java\nscript:alert(1)\">",
        ];

        foreach ($payloads as $payload) {
            foreach ($this->both($payload) as $out) {
                $this->assertStringNotContainsString('javascript', $out);
            }
        }
    }

    public function test_keeps_safe_admin_formatting(): void
    {
        $out = sanitizeAdminHtml('<p>a <strong>b</strong> <a href="https://x.test/y">l</a></p>');

        $this->assertStringContainsString('<strong>b</strong>', $out);
        $this->assertStringContainsString('href="https://x.test/y"', $out);
    }

    public function test_keeps_safe_petition_markup(): void
    {
        $this->assertSame('<p>a <strong>b</strong></p>', sanitizePetitionHtml('<p>a <strong>b</strong></p>'));
    }

    public function test_keeps_relative_mailto_and_tel_links_in_admin_output(): void
    {
        $html = '<p><a href="/about">A</a> <a href="mailto:x@y.test">M</a> <a href="tel:+4915112345678">T</a></p>';
        $out = html_entity_decode(sanitizeAdminHtml($html), ENT_QUOTES | ENT_HTML5);

        $this->assertStringContainsString('href="/about"', $out);
        $this->assertStringContainsString('href="mailto:x@y.test"', $out);
        $this->assertStringContainsString('href="tel:+4915112345678"', $out);
    }

    private function both(string $html): array
    {
        return [sanitizeAdminHtml($html), sanitizePetitionHtml($html)];
    }
}
