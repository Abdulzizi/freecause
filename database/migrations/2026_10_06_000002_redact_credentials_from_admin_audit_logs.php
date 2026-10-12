<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('logs') || ! Schema::hasColumn('logs', 'context') || ! Schema::hasColumn('logs', 'content')) {
            return;
        }

        DB::table('logs')
            ->where('context', 'admin.audit')
            ->where(function ($query) {
                foreach (['current_password', 'new_password', 'new_password_confirmation'] as $field) {
                    $query->orWhere('content', 'like', '%'.$field.'%');
                }
            })
            ->select(['id', 'content'])
            ->orderBy('id')
            ->chunkById(250, function ($rows) {
                foreach ($rows as $row) {
                    $separator = ' | Input: ';
                    $offset = strpos($row->content, $separator);
                    if ($offset === false) {
                        continue;
                    }

                    $json = substr($row->content, $offset + strlen($separator));
                    $input = json_decode($json, true);
                    if (! is_array($input)) {
                        $input = '[REDACTED]';
                    } else {
                        $input = $this->redact($input);
                    }

                    DB::table('logs')->where('id', $row->id)->update([
                        'content' => substr($row->content, 0, $offset).$separator.json_encode(
                            $input,
                            JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR
                        ),
                    ]);
                }
            });
    }

    public function down(): void {}

    private function redact(array $input): array
    {
        foreach ($input as $key => $value) {
            $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $key));
            if (preg_match('/password|passwd|passphrase|secret|token|authorization|credential|apikey|privatekey|otp|onetimecode|codeverifier|authcode|clientassertion/', $normalized) === 1
                || $normalized === 'pass') {
                $input[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $input[$key] = $this->redact($value);
            }
        }

        return $input;
    }
};
