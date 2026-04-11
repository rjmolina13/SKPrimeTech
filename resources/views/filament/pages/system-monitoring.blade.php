<x-filament-panels::page>
    @livewire(\App\Filament\Widgets\SystemStatsOverview::class)

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">
                System Information
            </x-slot>

            <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <li class="flex justify-between">
                    <span>PHP Version:</span>
                    <span class="font-semibold text-gray-950 dark:text-white">{{ phpversion() }}</span>
                </li>
                <li class="flex justify-between">
                    <span>Laravel Version:</span>
                    <span class="font-semibold text-gray-950 dark:text-white">{{ app()->version() }}</span>
                </li>
                <li class="flex justify-between">
                    <span>Server OS:</span>
                    <span class="font-semibold text-gray-950 dark:text-white">{{ php_uname('s') }} {{ php_uname('r') }}</span>
                </li>
                <li class="flex justify-between">
                    <span>Timezone:</span>
                    <span class="font-semibold text-gray-950 dark:text-white">{{ config('app.timezone') }}</span>
                </li>
            </ul>
        </x-filament::section>
        
        <x-filament::section>
            <x-slot name="heading">
                Database Information
            </x-slot>

            <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <li class="flex justify-between">
                    <span>Database Driver:</span>
                    <span class="font-semibold text-gray-950 dark:text-white">{{ \Illuminate\Support\Facades\DB::connection()->getDriverName() }}</span>
                </li>
                <li class="flex justify-between">
                    <span>Database Name:</span>
                    <span class="font-semibold text-gray-950 dark:text-white">{{ \Illuminate\Support\Facades\DB::connection()->getDatabaseName() }}</span>
                </li>
            </ul>
        </x-filament::section>
    </div>
</x-filament-panels::page>
