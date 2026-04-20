<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    public function index()
    {
        $backups = $this->getBackups();

        return view('admin.backup.index', compact('backups'));
    }

    public function create(Request $request)
    {
        try {
            $filename = 'backup-'.date('Y-m-d-His').'.sql';

            $dbName = config('database.connections.'.config('database.default').'.database');

            if (config('database.default') === 'sqlite') {
                $path = database_path('database.sqlite');
                $filename = 'backup-'.date('Y-m-d-His').'.sqlite';
                $destination = storage_path('app/backups/'.$filename);

                if (! File::exists(storage_path('app/backups'))) {
                    File::makeDirectory(storage_path('app/backups'), 0755, true);
                }

                File::copy($path, $destination);
            } else {
                $username = config('database.connections.'.config('database.default').'.username');
                $password = config('database.connections.'.config('database.default').'.password');
                $host = config('database.connections.'.config('database.default').'.host');
                $port = config('database.connections.'.config('database.default').'.port');

                $cmd = sprintf(
                    'mysqldump -u%s %s %s > %s 2>/dev/null',
                    $username,
                    $password ? '-p'.escapeshellarg($password) : '',
                    $dbName,
                    storage_path('app/backups/'.$filename)
                );

                if (! File::exists(storage_path('app/backups'))) {
                    File::makeDirectory(storage_path('app/backups'), 0755, true);
                }

                exec($cmd);
            }

            return back()->with('success', 'Backup created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: '.$e->getMessage());
        }
    }

    public function download(Request $request, string $filename)
    {
        $path = storage_path('app/backups/'.$filename);

        if (! File::exists($path)) {
            return back()->with('error', 'Backup file not found.');
        }

        return Response::download($path);
    }

    public function delete(Request $request, string $filename)
    {
        $path = storage_path('app/backups/'.$filename);

        if (File::exists($path)) {
            File::delete($path);

            return back()->with('success', 'Backup deleted.');
        }

        return back()->with('error', 'Backup file not found.');
    }

    private function getBackups(): array
    {
        $dir = storage_path('app/backups');

        if (! File::exists($dir)) {
            return [];
        }

        $files = File::files($dir);

        return collect($files)
            ->filter(fn ($f) => in_array($f->getExtension(), ['sql', 'sqlite', 'gz']))
            ->map(fn ($f) => [
                'name' => $f->getFilename(),
                'size' => $f->getSize(),
                'modified' => $f->getMTime(),
            ])
            ->sortByDesc('modified')
            ->values()
            ->toArray();
    }
}
