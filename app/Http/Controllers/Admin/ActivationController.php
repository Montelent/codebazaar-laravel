<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ProductActivation;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    public function edit()
    {
        $status = ProductActivation::status();

        return view('admin.activation.edit', compact('status'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'purchase_code' => 'required|string|max:120',
        ]);

        $result = ProductActivation::activate($data['purchase_code']);

        if (! $result['ok']) {
            return back()->withInput()->with('error', $result['message']);
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', $result['message']);
    }

    public function destroy(Request $request)
    {
        // Only allow deactivation when master was used, or for support.
        ProductActivation::deactivate();

        return redirect()
            ->route('admin.activation.edit')
            ->with('success', 'Activation cleared. Enter a license code to unlock again.');
    }
}
