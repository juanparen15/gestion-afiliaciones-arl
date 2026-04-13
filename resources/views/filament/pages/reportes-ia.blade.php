<x-filament-panels::page>
    <div class="space-y-5 max-w-4xl mx-auto">

        {{-- Banner de bienvenida --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-600 via-blue-600 to-indigo-700 p-6 shadow-lg">
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0 shadow-inner">
                    <x-heroicon-o-sparkles class="w-6 h-6 text-white"/>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-white">Asistente de Análisis con IA</h2>
                    <p class="text-sm text-blue-100 mt-0.5 leading-relaxed">
                        Consulta en lenguaje natural sobre contratos SECOP y afiliaciones ARL. Los datos son en tiempo real.
                    </p>
                </div>
            </div>
            <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full bg-white/5 pointer-events-none"></div>
            <div class="absolute -bottom-6 -right-20 w-56 h-56 rounded-full bg-white/5 pointer-events-none"></div>
        </div>

        {{-- Preguntas sugeridas --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="px-5 py-4">
                <div class="flex items-center gap-2 mb-3">
                    <x-heroicon-m-light-bulb class="w-4 h-4 text-amber-500"/>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Preguntas sugeridas
                    </p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @php
                        $chips = [
                            ['icon' => 'heroicon-o-document-text',    'color' => 'text-blue-500',   'bg' => 'bg-blue-50 dark:bg-blue-900/20'],
                            ['icon' => 'heroicon-o-building-office-2', 'color' => 'text-violet-500', 'bg' => 'bg-violet-50 dark:bg-violet-900/20'],
                            ['icon' => 'heroicon-o-clock',             'color' => 'text-amber-500',  'bg' => 'bg-amber-50 dark:bg-amber-900/20'],
                            ['icon' => 'heroicon-o-banknotes',         'color' => 'text-emerald-500','bg' => 'bg-emerald-50 dark:bg-emerald-900/20'],
                            ['icon' => 'heroicon-o-shield-check',      'color' => 'text-rose-500',   'bg' => 'bg-rose-50 dark:bg-rose-900/20'],
                            ['icon' => 'heroicon-o-user-group',        'color' => 'text-sky-500',    'bg' => 'bg-sky-50 dark:bg-sky-900/20'],
                        ];
                    @endphp
                    @foreach($this->ejemplos() as $i => $ejemplo)
                        @php $chip = $chips[$i] ?? $chips[0]; @endphp
                        <button
                            wire:click="usarEjemplo('{{ $ejemplo }}')"
                            type="button"
                            class="group flex items-start gap-2.5 w-full text-left rounded-lg px-3 py-2.5
                                   border border-gray-100 dark:border-white/10
                                   hover:border-sky-200 dark:hover:border-sky-700/50
                                   hover:bg-sky-50/60 dark:hover:bg-sky-900/10
                                   transition-all duration-150 cursor-pointer">
                            <div class="w-6 h-6 rounded-md {{ $chip['bg'] }} flex items-center justify-center flex-shrink-0 mt-0.5">
                                <x-dynamic-component :component="$chip['icon']"
                                    class="w-3.5 h-3.5 {{ $chip['color'] }}"/>
                            </div>
                            <span class="text-xs text-gray-600 dark:text-gray-400
                                         group-hover:text-sky-700 dark:group-hover:text-sky-300
                                         transition-colors leading-relaxed">
                                {{ $ejemplo }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Formulario --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="p-5">
                <form wire:submit="consultar" class="space-y-3">
                    <div>
                        <textarea
                            wire:model="pregunta"
                            rows="3"
                            placeholder="Ej: ¿Cuántos contratos están activos este año y cuál es su valor total?"
                            class="block w-full rounded-lg border border-gray-200 dark:border-white/10
                                   bg-gray-50 dark:bg-white/5 px-4 py-3
                                   text-sm text-gray-900 dark:text-white
                                   placeholder:text-gray-400 dark:placeholder:text-gray-500
                                   focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500
                                   resize-none transition-colors">
                        </textarea>
                        @error('pregunta')
                            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                                <x-heroicon-m-exclamation-circle class="w-3.5 h-3.5 flex-shrink-0"/>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-75"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold
                                       bg-sky-600 text-white hover:bg-sky-500 active:bg-sky-700
                                       disabled:opacity-60 disabled:cursor-not-allowed
                                       transition-all duration-150 shadow-sm cursor-pointer
                                       focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-600">
                                <span wire:loading.remove wire:target="consultar" class="flex items-center gap-2">
                                    <x-heroicon-m-sparkles class="w-4 h-4"/>
                                    Consultar con IA
                                </span>
                                <span wire:loading wire:target="consultar" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                    </svg>
                                    Analizando datos...
                                </span>
                            </button>

                            @if($respuesta || $error)
                                <button
                                    type="button"
                                    wire:click="$set('respuesta', null); $set('error', null); $set('pregunta', '')"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium
                                           text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200
                                           hover:bg-gray-100 dark:hover:bg-white/10
                                           transition-colors cursor-pointer">
                                    <x-heroicon-m-arrow-path class="w-4 h-4"/>
                                    Nueva consulta
                                </button>
                            @endif
                        </div>

                        <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block select-none">
                            Powered by Gemini AI
                        </span>
                    </div>
                </form>
            </div>
        </div>

        {{-- Skeleton de carga --}}
        <div wire:loading wire:target="consultar"
             class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-5">
            <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100 dark:border-white/10">
                <div class="w-8 h-8 rounded-lg bg-gray-200 dark:bg-white/10 animate-pulse"></div>
                <div class="space-y-1.5 flex-1">
                    <div class="h-3.5 w-36 bg-gray-200 dark:bg-white/10 rounded animate-pulse"></div>
                    <div class="h-3 w-48 bg-gray-100 dark:bg-white/5 rounded animate-pulse"></div>
                </div>
            </div>
            <div class="space-y-2.5">
                <div class="h-3 w-full bg-gray-100 dark:bg-white/5 rounded animate-pulse"></div>
                <div class="h-3 w-4/5 bg-gray-100 dark:bg-white/5 rounded animate-pulse"></div>
                <div class="h-3 w-full bg-gray-100 dark:bg-white/5 rounded animate-pulse"></div>
                <div class="h-3 w-3/4 bg-gray-100 dark:bg-white/5 rounded animate-pulse"></div>
                <div class="h-3 w-full bg-gray-100 dark:bg-white/5 rounded animate-pulse"></div>
                <div class="h-3 w-2/3 bg-gray-100 dark:bg-white/5 rounded animate-pulse"></div>
            </div>
        </div>

        {{-- Error --}}
        @if($error)
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 ring-1 ring-red-200 dark:ring-red-800 p-5">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/40 flex items-center justify-center flex-shrink-0">
                        <x-heroicon-m-exclamation-triangle class="w-4 h-4 text-red-600 dark:text-red-400"/>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-red-800 dark:text-red-300">Error al procesar la consulta</p>
                        <pre class="mt-2 text-xs text-red-700 dark:text-red-400 bg-red-100/60 dark:bg-red-900/30 rounded-lg p-3 overflow-x-auto whitespace-pre-wrap break-words font-mono leading-relaxed">{{ $error }}</pre>
                    </div>
                </div>
            </div>
        @endif

        {{-- Respuesta --}}
        @if($respuesta)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                 x-data x-init="setTimeout(() => $el.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 100)">
                <div class="p-5">

                    {{-- Header --}}
                    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100 dark:border-white/10">
                        <div class="w-8 h-8 rounded-lg bg-sky-50 dark:bg-sky-900/40 flex items-center justify-center flex-shrink-0">
                            <x-heroicon-m-sparkles class="w-4 h-4 text-sky-600 dark:text-sky-400"/>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Respuesta del Asistente IA</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Datos en tiempo real del sistema</p>
                        </div>
                    </div>

                    {{-- Pregunta --}}
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-7 h-7 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center flex-shrink-0 mt-0.5 ring-1 ring-gray-200 dark:ring-white/10">
                            <x-heroicon-m-user class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400"/>
                        </div>
                        <div class="flex-1 bg-gray-50 dark:bg-white/5 rounded-xl rounded-tl-none px-4 py-3 text-sm text-gray-700 dark:text-gray-300 leading-relaxed ring-1 ring-gray-100 dark:ring-white/10">
                            {{ $pregunta }}
                        </div>
                    </div>

                    {{-- Respuesta IA --}}
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full bg-sky-100 dark:bg-sky-900/50 flex items-center justify-center flex-shrink-0 mt-0.5 ring-1 ring-sky-200 dark:ring-sky-700/50">
                            <x-heroicon-m-sparkles class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400"/>
                        </div>
                        <div class="flex-1 bg-sky-50/50 dark:bg-sky-900/10 rounded-xl rounded-tl-none px-4 py-3 ring-1 ring-sky-100 dark:ring-sky-700/30">
                            <div class="prose prose-sm max-w-none dark:prose-invert
                                        prose-headings:font-semibold prose-headings:text-gray-900 dark:prose-headings:text-white
                                        prose-p:text-gray-700 dark:prose-p:text-gray-300
                                        prose-li:text-gray-700 dark:prose-li:text-gray-300
                                        prose-strong:text-gray-900 dark:prose-strong:text-white
                                        prose-li:my-0.5">
                                {!! nl2br(e($respuesta)) !!}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif

        {{-- API Key warning --}}
        @if(empty(config('services.gemini.key')))
            <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 ring-1 ring-amber-200 dark:ring-amber-800 p-4">
                <div class="flex items-start gap-3">
                    <x-heroicon-m-key class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5"/>
                    <div>
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">API Key no configurada</p>
                        <p class="text-xs text-amber-700 dark:text-amber-400 mt-1 leading-relaxed">
                            Agrega <code class="bg-amber-100 dark:bg-amber-900/50 px-1.5 py-0.5 rounded font-mono text-amber-800 dark:text-amber-300">GEMINI_API_KEY=tu_clave</code>
                            en el archivo <code class="bg-amber-100 dark:bg-amber-900/50 px-1.5 py-0.5 rounded font-mono text-amber-800 dark:text-amber-300">.env</code> del servidor.
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
