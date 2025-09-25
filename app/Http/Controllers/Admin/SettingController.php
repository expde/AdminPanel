<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = DB::table('settings')->get()->keyBy('key_name');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'contact_address' => 'nullable|string',
            'currency' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:5',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'low_stock_threshold' => 'required|integer|min:1',
            'enable_registration' => 'boolean',
            'enable_guest_checkout' => 'boolean',
            'maintenance_mode' => 'boolean',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'site_favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,ico|max:1024',
        ]);

        $settings = [
            'site_name' => $request->site_name,
            'site_description' => $request->site_description,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'contact_address' => $request->contact_address,
            'currency' => $request->currency,
            'currency_symbol' => $request->currency_symbol,
            'tax_rate' => $request->tax_rate,
            'low_stock_threshold' => $request->low_stock_threshold,
            'enable_registration' => $request->has('enable_registration') ? '1' : '0',
            'enable_guest_checkout' => $request->has('enable_guest_checkout') ? '1' : '0',
            'maintenance_mode' => $request->has('maintenance_mode') ? '1' : '0',
        ];

        // Handle logo upload
        if ($request->hasFile('site_logo')) {
            $logo = $request->file('site_logo');
            $logoName = 'logo.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads'), $logoName);
            $settings['site_logo'] = 'uploads/' . $logoName;
        }

        // Handle favicon upload
        if ($request->hasFile('site_favicon')) {
            $favicon = $request->file('site_favicon');
            $faviconName = 'favicon.' . $favicon->getClientOriginalExtension();
            $favicon->move(public_path('uploads'), $faviconName);
            $settings['site_favicon'] = 'uploads/' . $faviconName;
        }

        // Update settings
        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key_name' => $key],
                [
                    'key_name' => $key,
                    'value' => $value,
                    'updated_at' => now()
                ]
            );
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    public function profile()
    {
        $user = auth()->user();
        return view('admin.settings.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password',
            'password' => 'nullable|min:8|confirmed',
        ]);

        // Verify current password if changing password
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        DB::table('users')->where('id', $user->id)->update($updateData);

        return redirect()->route('admin.settings.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
