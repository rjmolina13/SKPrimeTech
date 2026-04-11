<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ $currentMonthLabel }} Stats
        </x-slot>

        <x-slot name="description">
            Monthly snapshot view with expandable historical records.
        </x-slot>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($statDefinitions as $key => $definition)
                <article class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm transition-colors duration-200 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $definition['label'] }}</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format((int) ($currentMonthStats[$key] ?? 0)) }}
                            </p>
                        </div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg {{ $definition['bg'] }}">
                            <x-filament::icon :icon="$definition['icon']" class="h-5 w-5 {{ $definition['tone'] }}" />
                        </span>
                    </div>
                </article>
            @endforeach
        </div>

        <details class="group mt-5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-900/50 p-4">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-lg px-1 py-1 text-sm font-semibold text-gray-700 dark:text-gray-300 transition-colors duration-200 hover:text-primary-600 dark:hover:text-primary-400">
                <span>Previous Months</span>
                <x-filament::icon icon="heroicon-m-chevron-down" class="h-5 w-5 transition-transform duration-200 group-open:rotate-180" />
            </summary>

            <div class="mt-4 space-y-3">
                @forelse ($previousMonths as $monthlyStat)
                    <article class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $monthlyStat->month_start->format('F Y') }}
                            </h4>
                            <span class="rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-gray-400">
                                Captured {{ $monthlyStat->captured_at?->format('M d, Y') }}
                            </span>
                        </div>

                        <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                            @foreach ($statDefinitions as $key => $definition)
                                <div class="rounded-md border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 px-3 py-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $definition['label'] }}</p>
                                    <p class="mt-1 text-base font-semibold text-gray-800 dark:text-gray-200">
                                        {{ number_format((int) (($monthlyStat->stats[$key] ?? 0))) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 text-sm text-gray-600 dark:text-gray-400">
                        No monthly history yet. A snapshot is automatically stored every first day of the month.
                    </div>
                @endforelse
            </div>
        </details>
    </x-filament::section>
</x-filament-widgets::widget>
