<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageTranslation;

class StaticPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'terms-of-service' => [
                'title' => 'Terms of Service',
                'view'  => 'pages.terms-of-service',
            ],
            'ethical-code' => [
                'title' => 'Ethical Code',
                'view'  => 'pages.ethical-code',
            ],
            'privacy-policy' => [
                'title' => 'Privacy Policy',
                'view'  => 'pages.privacy-policy',
            ],
        ];

        foreach ($pages as $slug => $data) {

            $page = Page::create();

            $content = view($data['view'])->render();

            PageTranslation::create([
                'page_id'  => $page->id,
                'locale'   => 'en',
                'title'    => $data['title'],
                'slug'     => $slug,
                'content'  => $content,
                'published'=> true,
            ]);
        }
    }
}
