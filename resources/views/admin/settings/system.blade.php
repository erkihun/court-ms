<x-admin-layout title="System Settings">
    @section('page_header','System Settings')

    <div class="enterprise-page mx-auto max-w-6xl">
        <div class="enterprise-header">
            <h1 class="enterprise-title">System Settings</h1>
            <p class="enterprise-subtitle">Manage platform identity, branding assets, and public contact information.</p>
        </div>

        @if(session('ok'))
        <div class="ui-alert ui-alert-success">
            <x-heroicon-o-check-circle class="h-5 w-5 mt-0.5 shrink-0" />
            <div>{{ session('ok') }}</div>
        </div>
        @endif

        @if($errors->any())
        <div class="ui-alert ui-alert-error">
            <x-heroicon-o-exclamation-circle class="h-5 w-5 mt-0.5 shrink-0" />
            <div>
                <div class="font-semibold mb-1">Please correct the following:</div>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('settings.system.update') }}" enctype="multipart/form-data" class="enterprise-panel">
            @csrf

            <div class="enterprise-panel-body space-y-8">
                <section class="space-y-4">
                    <h2 class="text-lg font-semibold text-slate-900">General</h2>
                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">System Name <span class="text-rose-500">*</span></label>
                            <input name="app_name" value="{{ old('app_name', $settings->app_name) }}" required class="ui-input mt-1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Short Name</label>
                            <input name="short_name" value="{{ old('short_name', $settings->short_name) }}" class="ui-input mt-1">
                        </div>
                    </div>

                    <label class="admin-checkbox-card">
                        <input type="checkbox" name="maintenance_mode" value="1" class="ui-checkbox"
                            @checked(old('maintenance_mode', $settings->maintenance_mode))>
                        <span>
                            <span class="font-medium text-slate-800">Enable maintenance mode</span>
                            <span class="block text-xs text-slate-500">Shows a maintenance message to public users.</span>
                        </span>
                    </label>
                </section>

                <section class="space-y-4 border-t border-slate-200 pt-7">
                    <h2 class="text-lg font-semibold text-slate-900">Branding</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="admin-panel-muted space-y-2">
                            <label class="block text-sm font-medium text-slate-700">Banner</label>
                            <input type="file" name="banner" class="enterprise-file-input">
                            <p class="text-xs text-slate-500">Wide header image (max 3MB)</p>
                            @if($settings->banner_path)
                            <div class="rounded-xl border border-slate-200 bg-white p-2">
                                <img src="{{ asset('storage/'.$settings->banner_path) }}" class="w-full max-h-40 object-cover rounded-lg">
                            </div>
                            @endif
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="admin-panel-muted">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Logo</label>
                                <input type="file" name="logo" class="enterprise-file-input">
                                @if($settings->logo_path)
                                <div class="mt-3 border border-slate-200 bg-white rounded-xl p-3"><img src="{{ asset('storage/'.$settings->logo_path) }}" class="h-12 object-contain"></div>
                                @endif
                            </div>

                            <div class="admin-panel-muted">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Favicon</label>
                                <input type="file" name="favicon" class="enterprise-file-input">
                                @if($settings->favicon_path)
                                <div class="mt-3 border border-slate-200 bg-white rounded-xl p-3"><img src="{{ asset('storage/'.$settings->favicon_path) }}" class="h-8 w-8 object-contain"></div>
                                @endif
                            </div>

                            <div class="admin-panel-muted">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Official Seal</label>
                                <input type="file" name="seal" class="enterprise-file-input">
                                @if($settings->seal_path)
                                <div class="mt-3 border border-slate-200 bg-white rounded-xl p-3"><img src="{{ asset('storage/'.$settings->seal_path) }}" class="h-14 w-14 object-contain"></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-slate-200 pt-7">
                    <h2 class="text-lg font-semibold text-slate-900">About</h2>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">About the System / Court</label>
                        <textarea name="about" rows="5" class="ui-textarea mt-1">{{ old('about', $settings->about) }}</textarea>
                    </div>
                </section>

                <section class="space-y-4 border-t border-slate-200 pt-7">
                    <h2 class="text-lg font-semibold text-slate-900">Contact Information</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Contact Email</label>
                            <input type="email" name="contact_email" value="{{ old('contact_email', $settings->contact_email) }}" class="ui-input mt-1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Contact Phone</label>
                            <input name="contact_phone" value="{{ old('contact_phone', $settings->contact_phone) }}" class="ui-input mt-1">
                        </div>
                    </div>
                </section>
            </div>

            <div class="enterprise-panel-header">
                <span class="text-xs uppercase tracking-[0.16em] text-slate-500">Changes apply after save</span>
                <button class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
</x-admin-layout>
