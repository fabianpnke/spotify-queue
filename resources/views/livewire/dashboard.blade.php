<div wire:poll>
    <div class="">
        <!-- Sticky search header -->
        <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-6 border-b border-white/5 bg-gray-900 px-4 shadow-sm sm:px-6 lg:px-8">
            <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                <label for="search-field" class="sr-only">Suche</label>
                <div class="relative w-full">
                    <svg class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                    <input wire:model.live="search" id="search-field" class="block h-full w-full border-0 bg-transparent py-0 pl-8 pr-0 text-white focus:ring-0 sm:text-sm" placeholder="Suche..." type="search" name="search">
                </div>
            </div>
        </div>

        <main class="lg:pr-96">
            <header class="flex items-center justify-between border-b border-white/5 px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
                <h1 class="text-base font-semibold leading-7 text-white">Suchergebnisse</h1>
            </header>

            <!-- Search Result list -->
            <ul role="list" class="divide-y divide-white/5">
                @foreach($results as $song)
                <li class="relative flex items-center space-x-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div class="min-w-0 flex-auto">
                        <div class="flex items-center gap-x-3">
                            <div @class(['flex-none rounded-full p-1', 'text-red-500 bg-red-100/10' => !data_get($song, 'playable'), 'text-green-500 bg-green-100/10' => data_get($song, 'playable')])>
                                <div class="h-2 w-2 rounded-full bg-current"></div>
                            </div>
                            <h2 class="min-w-0 text-sm font-semibold leading-6 text-white">
                                @unless(data_get($song, 'playable'))
                                    <div class="flex gap-x-2">
                                        <span class="truncate">{{ data_get($song, 'name') }}</span>
                                        <span class="absolute inset-0"></span>
                                    </div>
                                @else
                                    <button
                                        wire:click="addToQueue('{{ data_get($song, 'id') }}')"
                                        wire:confirm="Soll der Titel {{ data_get($song, 'name') }} von {{ collect(data_get($song, 'artists'))->join(', ') }} zur Warteschlange hinzugefügt werden?"
                                        class="flex gap-x-2"
                                    >
                                        <span class="truncate">{{ data_get($song, 'name') }}</span>
                                        <span class="absolute inset-0"></span>
                                    </button>
                                @endunless
                            </h2>
                        </div>
                        <div class="mt-3 flex items-center gap-x-2.5 text-xs leading-5 text-gray-400">
                            <p class="truncate">{{ collect(data_get($song, 'artists'))->join(', ') }}</p>
                        </div>
                    </div>
                    @unless(data_get($song, 'playable'))
                    <div class="rounded-full flex-none py-1 px-2 text-xs font-medium ring-1 ring-inset text-gray-400 bg-gray-400/10 ring-gray-400/20">
                        @if(data_get($song, 'playing'))
                            {{ 'Läuft gerade' }}
                        @elseif(data_get($song, 'in_queue'))
                            {{ 'In der Warteschlange' }}
                        @elseif(data_get($song, 'recently_played'))
                            {{ 'Vor kurzem gespielt' }}
                        @endif
                    </div>
                    @else
                    <svg class="h-5 w-5 flex-none text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                    </svg>
                    @endunless
                </li>
                @endforeach
            </ul>
        </main>

        <!-- Queue -->
        <aside class="bg-black/10 lg:fixed lg:bottom-0 lg:right-0 lg:top-16 lg:w-96 lg:overflow-y-auto lg:border-l lg:border-white/5">
            <header class="flex items-center justify-between border-b border-white/5 px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
                <h2 class="text-base font-semibold leading-7 text-white">{{ data_get($currently_playing, 'is_playing') ? 'Läuft gerade' : 'Pausiert' }}</h2>
            </header>
            <ul role="list" class="divide-y divide-white/5">
                <li style="--progress-percent: {{ data_get($currently_playing, 'progress_percent', 0) }}%" class="px-4 py-4 sm:px-6 lg:px-8 bg-gradient-to-r from-purple-600/10 from-[percentage:var(--progress-percent)] to-black/10">
                    <div class="flex items-center gap-x-3">
                        <img src="{{ data_get($currently_playing, 'cover') }}" alt="{{ data_get($currently_playing, 'name') }}" class="h-6 w-6 flex-none rounded-full bg-gray-800">
                        <h3 class="flex-auto truncate text-sm font-semibold leading-6 text-white">{{ data_get($currently_playing, 'name') }}</h3>
                        <span class="flex-none text-xs text-gray-600">noch {{ \App\Helpers::formatMilliseconds(data_get($currently_playing, 'remaining_ms')) }}</span>
                    </div>
                    <p class="mt-3 truncate text-sm text-gray-500">{{ collect(data_get($currently_playing, 'artists'))->join(', ') }}</p>
                </li>
            </ul>
            <header class="flex items-center justify-between border-b border-white/5 px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
                <h2 class="text-base font-semibold leading-7 text-white">Warteschlange</h2>
            </header>
            <ul role="list" class="divide-y divide-white/5">
                @foreach($queue as $song)
                    <li class="px-4 py-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-x-3">
                            <img src="{{ data_get($song, 'cover') }}" alt="{{ data_get($song, 'name') }}" class="h-6 w-6 flex-none rounded-full bg-gray-800">
                            <h3 class="flex-auto truncate text-sm font-semibold leading-6 text-white">{{ data_get($song, 'name') }}</h3>
                            <span class="flex-none text-xs text-gray-600">{{ \App\Helpers::formatMillisecondsQueue(data_get($song, 'playing_in_ms')) }}</span>
                        </div>
                        <p class="mt-3 truncate text-sm text-gray-500">{{ collect(data_get($song, 'artists'))->join(', ') }}</p>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
</div>
