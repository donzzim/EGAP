<div class="rounded-2xl border border-gray-200 bg-white shadow dark:border-gray-800 dark:bg-gray-900">
    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
            {{ $sectionTitle }}
        </h2>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $sectionDescription }}
        </p>
    </div>

    <div class="px-6 pt-4" id="graphic-params">
        <div class="flex flex-col gap-3">
            <label
                for="patrimonio-indicator"
                class="text-sm font-medium text-gray-700 dark:text-gray-200"
            >
                Gráficos
            </label>

            <div class="flex flex-col gap-3">
                <div class="flex flex-col lg:flex-row">
                    <select
                        id="patrimonio-indicator"
                        wire:model.defer="selectedIndicator"
                        class="w-full rounded-t-2xl border border-gray-300 bg-white py-3 pl-6 pr-10 text-sm text-gray-700 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:ring-primary-800 lg:rounded-l-2xl lg:rounded-r-none"
                    >
                        @foreach ($indicators as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select
                        wire:model.defer="chartType"
                        class="w-full rounded-b-2xl border-x border-b border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:ring-primary-800 lg:w-56 lg:rounded-r-2xl lg:rounded-l-none lg:border lg:border-l-0"
                    >
                        <option value="bar">Barras</option>
                        <option value="line">Linha</option>
                        <option value="bubble">Bolhas</option>
                        <option value="doughnut">Rosca</option>
                        <option value="pie">Pizza</option>
                        <option value="polarArea">Polar</option>
                    </select>
                </div>

                <div class="flex justify-end">
                    <button
                        type="button"
                        wire:click="generateChart"
                        class="inline-flex items-center justify-center rounded-2xl border border-primary-600 bg-primary-600 px-6 py-3 text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-300 dark:border-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400"
                    >
                        <x-heroicon-s-presentation-chart-bar class="h-5 w-5" />
                        <span class="ml-2 hidden sm:inline">Gerar Gráfico</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 pb-6 pt-4">
        <div class="min-h-[420px] rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-700 dark:bg-gray-950/40">
            @if ($hasGenerated && $currentWidget)
                @livewire(
                    $currentWidget,
                    ['chartType' => $chartType],
                    key('patrimonio-widget-' . $selectedIndicator . '-' . $chartType . '-' . $widgetRenderKey)
                )
            @else
                <div class="flex h-64 items-center justify-center text-gray-400 dark:text-gray-500">
                    Selecione os filtros e clique em "Gerar Gráfico"
                </div>
            @endif
        </div>
    </div>
</div>
