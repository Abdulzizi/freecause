<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WordPressUserDeletionService;
use Illuminate\Support\Facades\File;
use RuntimeException;
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

    private function failureMessage(string $wpLoadSource, int $timeout = 20): string
    {
        File::put($this->dir.'/broken-wp-load.php', $wpLoadSource);

        try {
            (new WordPressUserDeletionService($this->dir.'/broken-wp-load.php', $timeout))->delete(new User(['email' => 'jo@example.com']));
        } catch (RuntimeException $e) {
            $this->assertStringNotContainsString('jo@example.com', $e->getMessage());

            return $e->getMessage();
        }

        $this->fail('Expected the WordPress cleanup to throw.');
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

    public function test_an_email_without_a_wordpress_user_is_a_successful_no_op(): void
    {
        $this->assertSame('', $this->deleteWith(['user_email' => 'someone-else@example.com', 'roles' => ['subscriber'], 'meta' => ['fc_sso_account' => 1]]));
    }

    public function test_throws_a_plain_message_when_wordpress_cannot_reach_its_database(): void
    {
        // What WordPress does without a database: print its error page, then die() with exit code 0.
        $message = $this->failureMessage('<?php echo "<h1>Error establishing a database connection</h1>"; die();');

        $this->assertSame('WordPress cleanup did not complete (exit code 0).', $message);
    }

    public function test_throws_when_the_user_lookup_query_fails(): void
    {
        $message = $this->failureMessage(<<<'PHP'
<?php
define('ABSPATH', __DIR__.'/');
$wpdb = (object) ['last_error' => "Table 'wp.abi_users' doesn't exist"];
function get_user_by($field, $value) { return false; }
PHP);

        $this->assertSame('WordPress cleanup did not complete (exit code 3).', $message);
    }

    public function test_throws_a_plain_message_when_wordpress_times_out(): void
    {
        $message = $this->failureMessage('<?php sleep(10);', 1);

        $this->assertSame('WordPress cleanup did not complete (timed out after 1s).', $message);
    }
}
