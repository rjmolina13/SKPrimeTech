@php
    $scope = $record->scope;
    
    $submittedRecords = $submissions->map(function ($sub) {
        return $sub->record_type . '-' . $sub->record_id;
    })->toArray();

    $barangays = \Illuminate\Support\Facades\Cache::rememberForever('all_barangays_list', function () {
        return \App\Models\Barangay::select('id', 'name', 'municipality_id')->with('municipality:id,name')->get();
    });
    
    $municipalities = \Illuminate\Support\Facades\Cache::rememberForever('all_municipalities_list', function () {
        return \App\Models\Municipality::select('id', 'name')->get();
    });

    $barangaysByMunicipality = [];
    $allSubmittedBarangays = [];
    $allPendingBarangays = [];
    $totalBarangays = 0;
    $totalSubmittedBarangays = 0;
    
    $submittedMunicipalities = [];
    $pendingMunicipalities = [];

    if (in_array($scope, ['barangay', 'both'])) {
        foreach ($municipalities as $mun) {
            $barangaysByMunicipality[$mun->name] = [
                'total' => 0,
                'submitted' => [],
                'pending' => []
            ];
        }
        
        foreach ($barangays as $barangay) {
            $munName = $barangay->municipality->name ?? 'Unknown';
            if (!isset($barangaysByMunicipality[$munName])) {
                $barangaysByMunicipality[$munName] = ['total' => 0, 'submitted' => [], 'pending' => []];
            }
            
            $barangaysByMunicipality[$munName]['total']++;
            $totalBarangays++;
            
            $key = \App\Models\Barangay::class . '-' . $barangay->id;
            if (in_array($key, $submittedRecords)) {
                $barangaysByMunicipality[$munName]['submitted'][] = $barangay;
                $allSubmittedBarangays[] = $barangay;
                $totalSubmittedBarangays++;
            } else {
                $barangaysByMunicipality[$munName]['pending'][] = $barangay;
                $allPendingBarangays[] = $barangay;
            }
        }
    }

    if (in_array($scope, ['municipality', 'both'])) {
        foreach ($municipalities as $municipality) {
            $key = \App\Models\Municipality::class . '-' . $municipality->id;
            if (in_array($key, $submittedRecords)) {
                $submittedMunicipalities[] = $municipality;
            } else {
                $pendingMunicipalities[] = $municipality;
            }
        }
    }
@endphp

<div class="space-y-6">
    @if(in_array($scope, ['municipality', 'both']))
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Municipalities Progress</h3>
                @php
                    $totalMuns = count($municipalities);
                    $submittedMunsCount = count($submittedMunicipalities);
                    $munOverallPct = $totalMuns > 0 ? round(($submittedMunsCount / $totalMuns) * 100) : 0;
                @endphp
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ $submittedMunsCount }} / {{ $totalMuns }}
                    </span>
                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold rounded-full {{ $munOverallPct >= 100 ? 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400' : ($munOverallPct >= 50 ? 'bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400' : 'bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-400') }}">
                        {{ $munOverallPct }}% Complete
                    </span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Submitted Municipalities -->
                <div class="p-4 rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 bg-white dark:bg-gray-900 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-3 h-3 rounded-full bg-success-500"></div>
                        <h4 class="font-medium text-sm text-gray-700 dark:text-gray-200">Submitted ({{ count($submittedMunicipalities) }})</h4>
                    </div>
                    <ul class="text-sm space-y-1 text-gray-600 dark:text-gray-400 max-h-48 overflow-y-auto pr-2">
                        @forelse($submittedMunicipalities as $mun)
                            <li>{{ $mun->name }}</li>
                        @empty
                            <li class="text-gray-400 italic">None</li>
                        @endforelse
                    </ul>
                </div>

                <!-- Pending Municipalities -->
                <div class="p-4 rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 bg-white dark:bg-gray-900 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-3 h-3 rounded-full bg-danger-500"></div>
                        <h4 class="font-medium text-sm text-gray-700 dark:text-gray-200">Pending ({{ count($pendingMunicipalities) }})</h4>
                    </div>
                    <ul class="text-sm space-y-1 text-gray-600 dark:text-gray-400 max-h-48 overflow-y-auto pr-2">
                        @forelse($pendingMunicipalities as $mun)
                            <li>{{ $mun->name }}</li>
                        @empty
                            <li class="text-gray-400 italic">None</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if(in_array($scope, ['barangay', 'both']))
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Barangays Progress</h3>
                @php
                    $overallPct = $totalBarangays > 0 ? round(($totalSubmittedBarangays / $totalBarangays) * 100) : 0;
                @endphp
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ $totalSubmittedBarangays }} / {{ $totalBarangays }}
                    </span>
                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold rounded-full {{ $overallPct >= 100 ? 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400' : ($overallPct >= 50 ? 'bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400' : 'bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-400') }}">
                        {{ $overallPct }}% Complete
                    </span>
                </div>
            </div>
            
            <!-- All Barangays Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <!-- Submitted All Barangays -->
                <div class="p-4 rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 bg-white dark:bg-gray-900 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-3 h-3 rounded-full bg-success-500"></div>
                        <h4 class="font-medium text-sm text-gray-700 dark:text-gray-200">Submitted ({{ count($allSubmittedBarangays) }})</h4>
                    </div>
                    <ul class="text-sm space-y-1 text-gray-600 dark:text-gray-400 max-h-48 overflow-y-auto pr-2">
                        @forelse($allSubmittedBarangays as $brgy)
                            <li>{{ $brgy->name }} <span class="text-xs text-gray-400">({{ $brgy->municipality->name ?? 'Unknown' }})</span></li>
                        @empty
                            <li class="text-gray-400 italic">None</li>
                        @endforelse
                    </ul>
                </div>

                <!-- Pending All Barangays -->
                <div class="p-4 rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 bg-white dark:bg-gray-900 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-3 h-3 rounded-full bg-danger-500"></div>
                        <h4 class="font-medium text-sm text-gray-700 dark:text-gray-200">Pending ({{ count($allPendingBarangays) }})</h4>
                    </div>
                    <ul class="text-sm space-y-1 text-gray-600 dark:text-gray-400 max-h-48 overflow-y-auto pr-2">
                        @forelse($allPendingBarangays as $brgy)
                            <li>{{ $brgy->name }} <span class="text-xs text-gray-400">({{ $brgy->municipality->name ?? 'Unknown' }})</span></li>
                        @empty
                            <li class="text-gray-400 italic">None</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- By Municipality -->
            <h3 class="text-md font-medium text-gray-900 dark:text-white mb-3">By Municipality</h3>
            <div class="space-y-4">
                @foreach($barangaysByMunicipality as $munName => $munData)
                    @if($munData['total'] > 0)
                        @php
                            $munPct = $munData['total'] > 0 ? round((count($munData['submitted']) / $munData['total']) * 100) : 0;
                        @endphp
                        <div class="rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10 bg-white dark:bg-gray-900 overflow-hidden" x-data="{ expanded: false }">
                            <!-- Header (Clickable) -->
                            <button @click="expanded = !expanded" type="button" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors cursor-pointer focus:outline-none">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-gray-500 transition-transform duration-200" :class="{'rotate-90': expanded}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                    </svg>
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200">{{ $munName }}</h4>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ count($munData['submitted']) }}/{{ $munData['total'] }}</span>
                                    <span class="text-xs font-bold {{ $munPct >= 100 ? 'text-success-600 dark:text-success-400' : ($munPct >= 50 ? 'text-warning-600 dark:text-warning-400' : 'text-danger-600 dark:text-danger-400') }}">
                                        {{ $munPct }}%
                                    </span>
                                </div>
                            </button>
                            
                            <!-- Content -->
                            <div x-show="expanded" x-collapse x-cloak class="border-t border-gray-100 dark:border-gray-800 p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Submitted -->
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="w-2.5 h-2.5 rounded-full bg-success-500"></div>
                                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Submitted ({{ count($munData['submitted']) }})</span>
                                        </div>
                                        <ul class="text-sm space-y-1 text-gray-700 dark:text-gray-300 max-h-40 overflow-y-auto pr-2">
                                            @forelse($munData['submitted'] as $brgy)
                                                <li>{{ $brgy->name }}</li>
                                            @empty
                                                <li class="text-gray-400 italic">None</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                    
                                    <!-- Pending -->
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="w-2.5 h-2.5 rounded-full bg-danger-500"></div>
                                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending ({{ count($munData['pending']) }})</span>
                                        </div>
                                        <ul class="text-sm space-y-1 text-gray-700 dark:text-gray-300 max-h-40 overflow-y-auto pr-2">
                                            @forelse($munData['pending'] as $brgy)
                                                <li>{{ $brgy->name }}</li>
                                            @empty
                                                <li class="text-gray-400 italic">None</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
