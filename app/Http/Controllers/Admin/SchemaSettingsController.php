<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SchemaSettingsController extends Controller
{
    public function edit()
    {
        $schema = SiteSetting::getValue('schema', [
            'organization_enabled' => true,
            'organization_name' => 'CodeBazaar',
            'organization_url' => '',
            'organization_logo' => '',
            'organization_same_as' => [],
            'website_enabled' => true,
            'product_enabled' => true,
            'breadcrumb_enabled' => true,
            'custom_json_ld' => '',
        ]);

        return view('admin.settings.schema', compact('schema'));
    }

    public function update(Request $request)
    {
        $sameAs = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $request->input('organization_same_as', '')))));

        SiteSetting::setValue('schema', [
            'organization_enabled' => $request->boolean('organization_enabled'),
            'organization_name' => $request->input('organization_name'),
            'organization_url' => $request->input('organization_url'),
            'organization_logo' => $request->input('organization_logo'),
            'organization_same_as' => $sameAs,
            'website_enabled' => $request->boolean('website_enabled'),
            'product_enabled' => $request->boolean('product_enabled'),
            'breadcrumb_enabled' => $request->boolean('breadcrumb_enabled'),
            'custom_json_ld' => $request->input('custom_json_ld'),
        ], 'seo');

        return back()->with('success', 'Schema / JSON-LD settings saved.');
    }
}
