<?php

namespace App\Services;

use App\Support\Settings;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class EmailTemplateTranslator
{
    public static function configured(): bool
    {
        return filled(Settings::get('deepl_api_key', ''));
    }

    public function translate(array $texts, string $source, string $target): array
    {
        try {
            $key = Crypt::decryptString(Settings::get('deepl_api_key', ''));
        } catch (\Throwable) {
            throw new \RuntimeException('Configure the DeepL API key in Localized Content first.');
        }
        $host = str_ends_with($key, ':fx') ? 'https://api-free.deepl.com' : 'https://api.deepl.com';
        $translated = [];
        foreach (array_chunk($texts, 20, true) as $batch) {
            $xml = [];
            $protected = [];
            foreach ($batch as $field => $text) {
                $markers = [];
                $index = 0;
                $escaped = htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $escaped = preg_replace_callback('/:[a-z_]+/', function ($match) use (&$markers, &$index, $text) {
                    do {
                        $marker = '__FC_VAR_'.$index++.'__';
                    } while (str_contains($text, $marker));
                    $markers[$marker] = $match[0];

                    return '<keep>'.$marker.'</keep>';
                }, $escaped);

                $xml[] = '<text>'.$escaped.'</text>';
                $protected[$field] = $markers;
            }
            try {
                $response = Http::withHeaders(['Authorization' => 'DeepL-Auth-Key '.$key])
                    ->connectTimeout(5)->timeout(15)->post($host.'/v2/translate', [
                        'text' => $xml, 'source_lang' => strtoupper($source),
                        'target_lang' => ['en' => 'EN-US', 'pt' => 'PT-PT'][$target] ?? strtoupper($target),
                        'tag_handling' => 'xml', 'tag_handling_version' => 'v2', 'ignore_tags' => ['keep'],
                    ]);
            } catch (\Throwable) {
                throw new \RuntimeException('DeepL could not be reached. Existing templates have been preserved.');
            }
            if (! $response->successful()) {
                throw new \RuntimeException($response->status() === 456
                    ? 'DeepL quota exhausted. Existing templates have been preserved; no paid upgrade was requested.'
                    : 'DeepL translation failed (HTTP '.$response->status().'). Existing templates have been preserved.');
            }
            $rows = $response->json('translations');
            if (! is_array($rows) || count($rows) !== count($batch)) {
                throw new \RuntimeException('DeepL returned an incomplete translation. Existing templates have been preserved.');
            }
            foreach (array_keys($batch) as $index => $field) {
                $value = $rows[$index]['text'] ?? '';
                $document = new \DOMDocument;
                if (! is_string($value) || preg_match('/<!DOCTYPE|<!ENTITY/i', $value) || ! @$document->loadXML($value, LIBXML_NONET)) {
                    throw new \RuntimeException('DeepL returned invalid template markup.');
                }
                $kept = [];
                foreach ($document->getElementsByTagName('keep') as $node) {
                    $kept[] = $node->textContent;
                }
                $expectedMarkers = array_keys($protected[$field]);
                sort($kept);
                sort($expectedMarkers);
                if ($kept !== $expectedMarkers) {
                    throw new \RuntimeException('A translated template changed a protected placeholder. Existing templates have been preserved.');
                }
                $value = strtr($document->documentElement->textContent, $protected[$field]);
                preg_match_all('/:[a-z_]+/', $batch[$field], $expected);
                preg_match_all('/:[a-z_]+/', $value, $actual);
                $a = $expected[0];
                $b = $actual[0];
                sort($a);
                sort($b);
                if ($a !== $b || trim($value) === '' || (str_ends_with($field, '_subject') && preg_match('/[\r\n]/', $value))) {
                    throw new \RuntimeException('A translated template changed a placeholder or subject format. Existing templates have been preserved.');
                }
                $translated[$field] = $value;
            }
        }

        return $translated;
    }
}
