<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin.utils.import');
    }

    public function store(Request $request)
    {
        //* TEMPORARILY DISABLED
        if (true) {
            return back()->withErrors([
                'import' => 'Import feature is temporarily disabled. Will be activated in Phase 3 (legacy migration).'
            ]);
        }

        // EXISTING CODE BELOW (DO NOT TOUCH)

        $request->validate([
            'type' => ['required', 'in:users,petitions,categories,signatures'],
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            return back()->withErrors(['file' => 'Unable to read file']);
        }

        $header = fgetcsv($handle);

        $inserted = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {

            $data = array_combine($header, $row);

            try {
                $this->processRow($request->type, $data);
                $inserted++;
            } catch (\Throwable $e) {
                $errors++;
            }
        }

        fclose($handle);

        return back()->with('success', "Imported: {$inserted} rows. Errors: {$errors}");
    }

    private function processRow(string $type, array $data)
    {
        switch ($type) {

            case 'users':
                DB::table('users')->insert([
                    'name' => $data['name'] ?? '',
                    'email' => $data['email'],
                    'password' => bcrypt($data['password'] ?? 'password123'),
                    'level_id' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                break;

            case 'categories':
                DB::table('categories')->insert([
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                break;

            case 'petitions':
                DB::table('petitions')->insert([
                    'category_id' => $data['category_id'],
                    'user_id' => $data['user_id'],
                    'status' => 'draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                break;

            case 'signatures':
                DB::table('signatures')->insert([
                    'petition_id' => $data['petition_id'],
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                break;
        }
    }
}
