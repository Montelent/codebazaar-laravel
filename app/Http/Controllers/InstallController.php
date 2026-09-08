<?php

namespace App\Http\Controllers;

use App\Support\Installer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class InstallController extends Controller
{
    public function index()
    {
        if (Installer::isInstalled()) {
            abort(404);
        }

        return redirect()->route('install.requirements');
    }

    public function requirements()
    {
        $checks = Installer::requirements();
        $passed = Installer::allRequirementsPassed($checks);

        return view('install.requirements', compact('checks', 'passed'));
    }

    public function databaseForm()
    {
        if (! Installer::allRequirementsPassed(Installer::requirements())) {
            return redirect()->route('install.requirements');
        }

        return view('install.database');
    }

    public function databaseStore(Request $request)
    {
        $data = $request->validate([
            'app_name' => 'required|string|max:80',
            'app_url' => 'required|url',
            'db_connection' => 'required|in:mysql,pgsql,sqlite',
            'db_host' => 'nullable|string',
            'db_port' => 'nullable|string',
            'db_database' => 'required|string',
            'db_username' => 'nullable|string',
            'db_password' => 'nullable|string',
        ]);

        $test = Installer::testDatabase([
            'connection' => $data['db_connection'],
            'host' => $data['db_host'] ?? '127.0.0.1',
            'port' => $data['db_port'] ?? '3306',
            'database' => $data['db_database'],
            'username' => $data['db_username'] ?? '',
            'password' => $data['db_password'] ?? '',
        ]);

        if (! $test['ok']) {
            return back()->withInput()->with('error', 'Database connection failed: '.$test['message']);
        }

        $request->session()->put('install.db', $data);

        return redirect()->route('install.admin');
    }

    public function adminForm()
    {
        if (! session('install.db')) {
            return redirect()->route('install.database');
        }

        return view('install.admin');
    }

    public function finish(Request $request)
    {
        $admin = $request->validate([
            'admin_name' => 'required|string|max:120',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $db = session('install.db');
        if (! $db) {
            return redirect()->route('install.database');
        }

        try {
            Installer::writeEnv([
                'app_name' => $db['app_name'],
                'app_url' => $db['app_url'],
                'db_connection' => $db['db_connection'],
                'db_host' => $db['db_host'] ?? '127.0.0.1',
                'db_port' => $db['db_port'] ?? ($db['db_connection'] === 'pgsql' ? '5432' : '3306'),
                'db_database' => $db['db_database'],
                'db_username' => $db['db_username'] ?? '',
                'db_password' => $db['db_password'] ?? '',
                'admin_email' => $admin['admin_email'],
                'admin_password' => $admin['admin_password'],
                'admin_name' => $admin['admin_name'],
            ]);

            Artisan::call('config:clear');

            config([
                'database.default' => $db['db_connection'],
                'database.connections.mysql.host' => $db['db_host'] ?? '127.0.0.1',
                'database.connections.mysql.port' => $db['db_port'] ?? '3306',
                'database.connections.mysql.database' => $db['db_database'],
                'database.connections.mysql.username' => $db['db_username'] ?? '',
                'database.connections.mysql.password' => $db['db_password'] ?? '',
                'database.connections.pgsql.host' => $db['db_host'] ?? '127.0.0.1',
                'database.connections.pgsql.port' => $db['db_port'] ?? '5432',
                'database.connections.pgsql.database' => $db['db_database'],
                'database.connections.pgsql.username' => $db['db_username'] ?? '',
                'database.connections.pgsql.password' => $db['db_password'] ?? '',
            ]);

            if ($db['db_connection'] === 'sqlite') {
                $path = $db['db_database'];
                if (! str_starts_with($path, '/')) {
                    $path = database_path(basename($path));
                }
                if (! is_file($path)) {
                    touch($path);
                }
                config(['database.connections.sqlite.database' => $path]);
            }

            Installer::runMigrations();
            Installer::seedAdmin($admin['admin_name'], $admin['admin_email'], $admin['admin_password']);
            Installer::markInstalled();
            $request->session()->forget('install');

            return view('install.complete', [
                'email' => $admin['admin_email'],
                'url' => $db['app_url'],
            ]);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Install failed: '.$e->getMessage());
        }
    }
}
