<div>
    <!-- Page Header -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
        <div class="absolute inset-0 -z-10">
            <div class="absolute -top-40 left-1/2 h-[520px] w-[520px] -translate-x-1/2 rounded-full bg-primary-500/10 blur-3xl"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 relative z-10">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-primary-100 bg-primary-50 dark:border-primary-900/50 dark:bg-primary-900/20 px-3 py-1 text-xs font-semibold text-primary-700 dark:text-primary-300 mb-6">
                    <x-heroicon-s-globe-alt class="h-4 w-4" />
                    Shared Access Portal
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">
                    Public Dashboard: {{ $link->name }}
                </h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    Welcome to the public dashboard. Here you can track compliance, view submitted reports, and monitor key metrics shared for public transparency.
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
        @php
            $visibleSections = $link->getVisibleSections();
            $showDashboard = in_array('dashboard', $visibleSections);
            $showRecords = in_array('records', $visibleSections);
        @endphp

        @if($showDashboard)
            <div class="space-y-6">
                <div class="flex items-center gap-3 border-b border-gray-200 dark:border-gray-800 pb-4">
                    <div class="p-2 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                        <x-heroicon-o-chart-pie class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard Overview</h2>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                    @livewire(\App\Livewire\SharedAccess\PublicApprovedSubmissionsChart::class)
                    @livewire(\App\Livewire\SharedAccess\PublicComplianceChart::class)
                </div>
            </div>
        @endif

        @if($showRecords)
            <div class="space-y-6">
                <div class="flex items-center gap-3 border-b border-gray-200 dark:border-gray-800 pb-4">
                    <div class="p-2 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                        <x-heroicon-o-document-text class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">All Records</h2>
                </div>
                <div class="rounded-xl shadow-sm ring-1 ring-gray-900/5 dark:ring-white/10 overflow-hidden">
                    {{ $this->table }}
                </div>
            </div>
        @endif

        @if(!$showDashboard && !$showRecords)
            <div class="text-center py-20 px-6 sm:px-12 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                    <x-heroicon-o-eye-slash class="h-6 w-6 text-gray-500 dark:text-gray-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">No Content Available</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    There are currently no sections visible for this link. Please check back later or contact the administrator.
                </p>
            </div>
        @endif
    </div>
</div>
