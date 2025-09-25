@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>General Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="site_name" class="form-label">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" 
                                   value="{{ $settings['site_name']->value ?? '' }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_email" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                   value="{{ $settings['contact_email']->value ?? '' }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="site_description" class="form-label">Site Description</label>
                        <textarea class="form-control" id="site_description" name="site_description" rows="3">{{ $settings['site_description']->value ?? '' }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_phone" class="form-label">Contact Phone</label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                   value="{{ $settings['contact_phone']->value ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="currency" class="form-label">Currency</label>
                            <input type="text" class="form-control" id="currency" name="currency" 
                                   value="{{ $settings['currency']->value ?? 'USD' }}" maxlength="3" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="currency_symbol" class="form-label">Currency Symbol</label>
                            <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" 
                                   value="{{ $settings['currency_symbol']->value ?? '$' }}" maxlength="5" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" id="tax_rate" name="tax_rate" 
                                   value="{{ $settings['tax_rate']->value ?? '10' }}" min="0" max="100" step="0.01" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="contact_address" class="form-label">Contact Address</label>
                        <textarea class="form-control" id="contact_address" name="contact_address" rows="2">{{ $settings['contact_address']->value ?? '' }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="low_stock_threshold" class="form-label">Low Stock Threshold</label>
                            <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" 
                                   value="{{ $settings['low_stock_threshold']->value ?? '5' }}" min="1" required>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">File Uploads</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="site_logo" class="form-label">Site Logo</label>
                            <input type="file" class="form-control" id="site_logo" name="site_logo" accept="image/*">
                            @if($settings['site_logo']->value ?? false)
                                <div class="mt-2">
                                    <img src="{{ asset($settings['site_logo']->value) }}" alt="Current Logo" 
                                         style="max-height: 50px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="site_favicon" class="form-label">Site Favicon</label>
                            <input type="file" class="form-control" id="site_favicon" name="site_favicon" accept="image/*">
                            @if($settings['site_favicon']->value ?? false)
                                <div class="mt-2">
                                    <img src="{{ asset($settings['site_favicon']->value) }}" alt="Current Favicon" 
                                         style="max-height: 32px;">
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Feature Toggles</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enable_registration" name="enable_registration" 
                                       {{ ($settings['enable_registration']->value ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_registration">
                                    Enable Registration
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="enable_guest_checkout" name="enable_guest_checkout" 
                                       {{ ($settings['enable_guest_checkout']->value ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_guest_checkout">
                                    Enable Guest Checkout
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                       {{ ($settings['maintenance_mode']->value ?? '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="maintenance_mode">
                                    Maintenance Mode
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>PHP Version:</strong>
                    <span class="float-end">{{ PHP_VERSION }}</span>
                </div>
                <div class="mb-3">
                    <strong>Laravel Version:</strong>
                    <span class="float-end">{{ app()->version() }}</span>
                </div>
                <div class="mb-3">
                    <strong>Server:</strong>
                    <span class="float-end">{{ $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' }}</span>
                </div>
                <div class="mb-3">
                    <strong>Database:</strong>
                    <span class="float-end">MySQL</span>
                </div>
                <hr>
                <div class="mb-3">
                    <strong>Total Products:</strong>
                    <span class="float-end">{{ DB::table('products')->count() }}</span>
                </div>
                <div class="mb-3">
                    <strong>Total Orders:</strong>
                    <span class="float-end">{{ DB::table('orders')->count() }}</span>
                </div>
                <div class="mb-3">
                    <strong>Total Customers:</strong>
                    <span class="float-end">{{ DB::table('customers')->count() }}</span>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profile Settings</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Manage your admin profile and password.</p>
                <a href="{{ route('admin.settings.profile') }}" class="btn btn-outline-primary">
                    <i class="fas fa-user-edit me-2"></i>Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
