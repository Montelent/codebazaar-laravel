<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

/** Run pending migrations from the browser (shared hosting without SSH). */
class MigrateController extends Controller
{
    public function run()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();

            return back()->with('success', 'Migrations ran.\n'.$output);
        } catch (\Throwable $e) {
            return back()->with('error', 'Migration failed: '.$e->getMessage());
        }
    }
}
