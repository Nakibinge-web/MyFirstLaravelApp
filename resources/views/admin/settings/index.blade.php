@extends('layouts.admin')

@section('page-title', 'System Settings')

@section('content')
<div class="max-w-3xl">
    <p class="text-sm text-gray-500 mb-6">Configure system-wide application settings. Changes take effect immediately.</p>

    @if($settings->isEmpty())
    <div class="bg-white rounded-lg shadow px-6 py-12 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.549-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
        </svg>
        <p class="text-sm">No system settings configured yet.</p>
    </div>
    @else
    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-800">Settings</h2>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($settings as $setting)
                <div class="px-6 py-5">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        {{-- Label + Description --}}
                        <div class="flex-1">
                            <label for="setting_{{ $setting->key }}" class="block text-sm font-medium text-gray-800">
                                {{ $setting->key }}
                            </label>
                            @if($setting->description)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $setting->description }}</p>
                            @endif
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 mt-1">
                                {{ $setting->type }}
                            </span>
                        </div>

                        {{-- Input --}}
                        <div class="sm:w-72">
                            @if($setting->type === 'boolean')
                                <select id="setting_{{ $setting->key }}"
                                        name="settings[{{ $setting->key }}]"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="1" {{ $setting->getCastValue() ? 'selected' : '' }}>True</option>
                                    <option value="0" {{ !$setting->getCastValue() ? 'selected' : '' }}>False</option>
                                </select>

                            @elseif($setting->type === 'integer')
                                <input type="number"
                                       id="setting_{{ $setting->key }}"
                                       name="settings[{{ $setting->key }}]"
                                       value="{{ $setting->value }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                            @elseif($setting->type === 'json')
                                <textarea id="setting_{{ $setting->key }}"
                                          name="settings[{{ $setting->key }}]"
                                          rows="4"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $setting->value }}</textarea>

                            @else
                                {{-- string --}}
                                <input type="text"
                                       id="setting_{{ $setting->key }}"
                                       name="settings[{{ $setting->key }}]"
                                       value="{{ $setting->value }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <p class="text-xs text-gray-500">All changes are logged for audit purposes.</p>
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Save Settings
                </button>
            </div>
        </div>
    </form>
    @endif
</div>
@endsection
