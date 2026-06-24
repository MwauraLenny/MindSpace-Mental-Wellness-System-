<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Name') }}</p>
                        <p class="text-lg font-medium">{{ $user->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">{{ __('Email') }}</p>
                        <p class="text-lg font-medium">{{ $user->email }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">{{ __('Role') }}</p>
                        <p class="text-lg font-medium capitalize">{{ $user->role }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">{{ __('Anonymous Sharing') }}</p>
                        <p class="text-lg font-medium">{{ $user->anonymous_sharing ? __('Enabled') : __('Disabled') }}</p>
                    </div>

                    <div class="pt-4">
                        <a
                            href="{{ route('profile.edit') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                        >
                            {{ __('Edit Profile') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
