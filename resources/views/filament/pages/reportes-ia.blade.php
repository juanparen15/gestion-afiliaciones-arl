<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Ejemplos rápidos --}}
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Preguntas sugeridas:</p>
            <div class="flex flex-wrap gap-2">
                @foreach($this->ejemplos() as $ejemplo)
                    <button
                        wire:click="usarEjemplo('{{ $ejemplo }}')"
                        type="button"
                        class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium
                               bg-primary-50 text-primary-700 border border-primary-200
                               hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-300
                               dark:border-primary-700 dark:hover:bg-primary-900/50
                               transition-colors cursor-pointer">
                        {{ $ejemplo }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Formulario --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="p-6">
                <form wire:submit="consultar" class="space-y-4">
                    <div>
                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 mb-2">
                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                ¿Qué quieres saber sobre los contratos o afiliaciones?
                            </span>
                        </label>
                        <textarea
                            wire:model="pregunta"
                            rows="3"
                            placeholder="Ej: ¿Cuántos contratos están activos este año y cuál es su valor total?"
                            class="fi-input block w-full rounded-lg border border-gray-300 bg-white px-3 py-2
                                   text-sm text-gray-950 shadow-sm placeholder:text-gray-400
                                   focus:ring-2 focus:ring-primary-600 focus:border-primary-600
                                   dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-gray-500
                                   resize-none transition-colors">
                        </textarea>
                        @error('pregunta')
                            <p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="fi-btn fi-btn-size-md inline-flex items-center justify-center gap-1.5
                                   rounded-lg px-4 py-2 text-sm font-semibold shadow-sm
                                   bg-primary-600 text-white hover:bg-primary-500
                                   disabled:opacity-60 disabled:cursor-not-allowed
                                   transition-colors focus-visible:outline focus-visible:outline-2">
                            <span wire:loading.remove wire:target="consultar">
                                <x-heroicon-m-sparkles class="w-4 h-4 inline -mt-0.5 mr-1"/>
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
                                class="fi-btn fi-btn-size-md inline-flex items-center gap-1.5
                                       rounded-lg px-4 py-2 text-sm font-semibold
                                       bg-gray-100 text-gray-700 hover:bg-gray-200
                                       dark:bg-white/10 dark:text-gray-300 dark:hover:bg-white/15
                                       transition-colors">
                                <x-heroicon-m-arrow-path class="w-4 h-4"/>
                                Nueva consulta
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Error --}}
        @if($error)
            <div class="fi-section rounded-xl bg-danger-50 ring-1 ring-danger-200 dark:bg-danger-900/20 dark:ring-danger-800 p-6">
                <div class="flex items-start gap-3">
                    <x-heroicon-m-exclamation-triangle class="w-5 h-5 text-danger-600 dark:text-danger-400 flex-shrink-0 mt-0.5"/>
                    <div>
                        <p class="text-sm font-semibold text-danger-800 dark:text-danger-300">Error</p>
                        <p class="text-sm text-danger-700 dark:text-danger-400 mt-1">{{ $error }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Respuesta --}}
        @if($respuesta)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">

                    {{-- Cabecera --}}
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-gray-100 dark:border-white/10">
                        <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center flex-shrink-0">
                            <x-heroicon-m-sparkles class="w-4 h-4 text-primary-600 dark:text-primary-400"/>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Respuesta del Asistente IA</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Basada en datos reales del sistema</p>
                        </div>
                    </div>

                    {{-- Pregunta --}}
                    <div class="mb-4 p-3 rounded-lg bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Tu consulta</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $pregunta }}</p>
                    </div>

                    {{-- Respuesta --}}
                    <div class="prose prose-sm max-w-none dark:prose-invert
                                prose-headings:font-semibold prose-headings:text-gray-900
                                prose-p:text-gray-700 prose-li:text-gray-700
                                dark:prose-headings:text-white dark:prose-p:text-gray-300 dark:prose-li:text-gray-300">
                        {!! nl2br(e($respuesta)) !!}
                    </div>

                </div>
            </div>
        @endif

        {{-- Info configuración --}}
        @if(empty(config('services.anthropic.key')))
            <div class="fi-section rounded-xl bg-warning-50 ring-1 ring-warning-200 dark:bg-warning-900/20 dark:ring-warning-800 p-5">
                <div class="flex items-start gap-3">
                    <x-heroicon-m-key class="w-5 h-5 text-warning-600 dark:text-warning-400 flex-shrink-0 mt-0.5"/>
                    <div>
                        <p class="text-sm font-semibold text-warning-800 dark:text-warning-300">API Key no configurada</p>
                        <p class="text-sm text-warning-700 dark:text-warning-400 mt-1">
                            Agrega <code class="bg-warning-100 dark:bg-warning-900/50 px-1 rounded">ANTHROPIC_API_KEY=tu_clave</code>
                            en el archivo <code class="bg-warning-100 dark:bg-warning-900/50 px-1 rounded">.env</code> del servidor.
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
