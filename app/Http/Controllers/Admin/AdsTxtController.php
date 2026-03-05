<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings;
use Illuminate\Http\Request;

class AdsTxtController extends Controller
{
    public function edit()
    {
        return view('admin.options.ads', [
            'ads_txt' => Settings::get('ads_txt', '', 'global'),
        ]);
    }

    public function update(Request $request)
    {
        // Settings::set(
        //     'ads_txt',
        //     $request->input('ads_txt', ''),
        //     'text',
        //     'global'
        // );
        Settings::set('ads_txt', $request->input('ads_txt', ''), 'global');

        return back()->with('success', 'saved');
    }
}
