<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageTranslation;
use Illuminate\Http\Request;

class AdminPagesController extends Controller
{
    public function index(Request $request)
    {
        $pages = PageTranslation::query()
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.pages.index', compact('pages'));
    }
}
