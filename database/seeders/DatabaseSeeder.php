<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@codebazaar.com');
        $adminPassword = env('ADMIN_PASSWORD', 'ChangeMeNow123!');

        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => env('ADMIN_NAME', 'CodeBazaar Admin'),
                'username' => 'codebazaar',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
            ]
        );

        $cats = [
            ['name' => 'JavaScript', 'slug' => 'javascript'],
            ['name' => 'PHP Scripts', 'slug' => 'php-scripts'],
            ['name' => 'WordPress', 'slug' => 'wordpress'],
            ['name' => 'HTML', 'slug' => 'html'],
            ['name' => 'Mobile', 'slug' => 'mobile'],
        ];
        foreach ($cats as $c) {
            Category::updateOrCreate(['slug' => $c['slug']], $c);
        }

        SiteSetting::set('homepage.hero', [
            'title' => 'The marketplace for high-quality code',
            'subtitle' => 'Scripts, plugins, themes, and digital assets.',
        ], 'homepage');

        // Also write the shorter key used by admin settings UI so both stay in sync
        SiteSetting::set('hero', [
            'title' => 'The marketplace for high-quality code',
            'subtitle' => 'Scripts, plugins, themes, and digital assets.',
            'cta' => 'Browse items',
            'image' => '',
        ], 'homepage');

        SiteSetting::set('site', [
            'name' => 'CodeBazaar',
            'tagline' => 'Code, scripts & digital assets',
        ], 'site');

        if (Item::count() === 0) {
            $js = Category::where('slug', 'javascript')->first();
            Item::create([
                'title' => 'React Dashboard Pro',
                'slug' => 'react-dashboard-pro',
                'description' => '<p>A modern React admin dashboard with charts, tables, and auth-ready layouts.</p>',
                'features' => ['Responsive layout', 'Dark mode', 'Chart kits'],
                'regular_price' => 29,
                'extended_price' => 149,
                'thumbnail_url' => 'https://picsum.photos/seed/reactdash/800/500',
                'status' => 'approved',
                'is_featured' => true,
                'author_id' => $admin->id,
                'category_id' => $js?->id,
            ]);
            Item::create([
                'title' => 'WordPress SaaS Theme',
                'slug' => 'wordpress-saas-theme',
                'description' => '<p>Clean WordPress theme tailored for SaaS and startup landing pages.</p>',
                'features' => ['Gutenberg ready', 'One-click demo'],
                'regular_price' => 39,
                'extended_price' => 199,
                'thumbnail_url' => 'https://picsum.photos/seed/wpsaas/800/500',
                'status' => 'approved',
                'is_featured' => true,
                'author_id' => $admin->id,
                'category_id' => Category::where('slug', 'wordpress')->value('id'),
            ]);
        }
    }
}
