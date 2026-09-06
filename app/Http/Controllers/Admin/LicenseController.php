<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    protected function defaults(): array
    {
        return [
            [
                'id' => 'regular',
                'name' => 'Regular License',
                'description' => '<p>Use in a single end product sold to one client.</p>',
                'price_label' => 'Included with item',
            ],
            [
                'id' => 'extended',
                'name' => 'Extended License',
                'description' => '<p>Use in an end product charged to end users (SaaS, etc.).</p>',
                'price_label' => 'Item extended price',
            ],
        ];
    }

    public function index()
    {
        $licenses = SiteSetting::getValue('licenses', $this->defaults());
        return view('admin.licenses.index', compact('licenses'));
    }

    public function create()
    {
        return view('admin.licenses.form', ['license' => ['id' => '', 'name' => '', 'description' => '', 'price_label' => ''], 'isNew' => true]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|string|max:60',
            'name' => 'required|string|max:120',
            'description' => 'nullable|string',
            'price_label' => 'nullable|string|max:120',
        ]);
        $licenses = SiteSetting::getValue('licenses', $this->defaults());
        $licenses[] = $data;
        SiteSetting::setValue('licenses', $licenses, 'commerce');
        return redirect()->route('admin.licenses.index')->with('success', 'License added.');
    }

    public function edit(string $license)
    {
        $licenses = SiteSetting::getValue('licenses', $this->defaults());
        $row = collect($licenses)->firstWhere('id', $license);
        abort_unless($row, 404);
        return view('admin.licenses.form', ['license' => $row, 'isNew' => false]);
    }

    public function update(Request $request, string $license)
    {
        $data = $request->validate([
            'id' => 'required|string|max:60',
            'name' => 'required|string|max:120',
            'description' => 'nullable|string',
            'price_label' => 'nullable|string|max:120',
        ]);
        $licenses = SiteSetting::getValue('licenses', $this->defaults());
        $licenses = array_map(function ($row) use ($license, $data) {
            return ($row['id'] ?? '') === $license ? $data : $row;
        }, $licenses);
        SiteSetting::setValue('licenses', array_values($licenses), 'commerce');
        return redirect()->route('admin.licenses.index')->with('success', 'License updated.');
    }

    public function destroy(string $license)
    {
        $licenses = SiteSetting::getValue('licenses', $this->defaults());
        $licenses = array_values(array_filter($licenses, fn ($r) => ($r['id'] ?? '') !== $license));
        SiteSetting::setValue('licenses', $licenses, 'commerce');
        return back()->with('success', 'License removed.');
    }

    public function publicIndex()
    {
        $licenses = SiteSetting::getValue('licenses', $this->defaults());
        return view('licenses.public', compact('licenses'));
    }
}
