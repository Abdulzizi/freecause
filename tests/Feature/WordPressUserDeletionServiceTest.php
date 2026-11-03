<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WordPressUserDeletionService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class WordPressUserDeletionServiceTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake WordPress: users.json is the user table, deletions are appended to deleted.log.
        $this->dir = sys_get_temp_dir().'/fc-wp-stub-'.uniqid();
        File::ensureDirectoryExists($this->dir.'/wp-admin/includes');
        File::put($this->dir.'/wp-load.php', <<<'PHP'
<?php
define('ABSPATH', __DIR__.'/');
function fc_users() { return json_decode(file_get_contents(__DIR__.'/users.json'), true); }
function get_user_by($field, $value) {
    foreach (fc_users() as $u) {
        if ($u['user_email'] === $value) { return (object) $u; }
    }
    return false;
}
function get_user_meta($id, $key, $single) {
    foreach (fc_users() as $u) {
        if ($u['ID'] === $id) { return $u['meta'][$key] ?? ''; }
    }
    return '';
}
function sanitize_user($name, $strict) { return $name; }
PHP);
        File::put($this->dir.'/wp-admin/includes/user.php', <<<'PHP'
<?php
function wp_delete_user($id) { file_put_contents(ABSPATH.'deleted.log', $id."\n", FILE_APPEND); return true; }
PHP);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    private function deleteWith(array $wpUser): string
    {
        File::put($this->dir.'/users.json', json_encode([$wpUser + ['ID' => 7, 'user_email' => 'jo@example.com', 'user_login' => 'jo', 'meta' => []]]));

        (new WordPressUserDeletionService($this->dir.'/wp-load.php'))->delete(new User(['email' => 'jo@example.com']));

        return (string) @file_get_contents($this->dir.'/deleted.log');
    }

    public function test_deletes_flagged_sso_subscriber(): void
    {
        $this->assertSame("7\n", $this->deleteWith(['roles' => ['subscriber'], 'meta' => ['fc_sso_account' => 1]]));
    }

    public function test_deletes_legacy_sso_subscriber_by_generated_login(): void
    {
        $login = 'jo_'.substr(md5('jo@example.com'), 0, 6);

        $this->assertSame("7\n", $this->deleteWith(['roles' => ['subscriber'], 'user_login' => $login]));
    }

    public function test_never_deletes_editorial_account_even_if_flagged(): void
    {
        $this->assertSame('', $this->deleteWith(['roles' => ['editor'], 'meta' => ['fc_sso_account' => 1]]));
        $this->assertSame('', $this->deleteWith(['roles' => ['subscriber', 'author'], 'meta' => ['fc_sso_account' => 1]]));
    }

    public function test_never_deletes_unflagged_subscriber(): void
    {
        $this->assertSame('', $this->deleteWith(['roles' => ['subscriber']]));
    }
}
