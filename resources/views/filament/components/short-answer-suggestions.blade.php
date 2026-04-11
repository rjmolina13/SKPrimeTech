@if (filled($suggestions))
    <div class="mt-2 flex flex-wrap gap-2">
        @foreach ($suggestions as $suggestion)
            <button
                type="button"
                wire:click="$set('{{ $statePath }}', @js($suggestion))"
                @class([
                    'inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium transition',
                    'border-primary-200 bg-primary-50 text-primary-700 dark:border-primary-500/40 dark:bg-primary-500/10 dark:text-primary-200' => $get($fieldName) === $suggestion,
                    'border-gray-200 bg-white text-gray-700 hover:border-primary-200 hover:text-primary-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-primary-500/40 dark:hover:text-primary-200' => $get($fieldName) !== $suggestion,
                ])
            >
                {{ $suggestion }}
            </button>
        @endforeach
    </div>
@endif
