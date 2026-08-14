<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                        Olá, {{ $this->primeiroNomeUsuario() }}!
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Bem-vindo(a) ao Ambiente Externo do EGAP. Por aqui você solicita e acompanha os
                        serviços de Almoxarifado, Patrimônio e Agendamento de Veículos.
                    </p>
                </div>

                @if ($lotacao = $this->lotacaoAtual())
                    <div class="flex items-center gap-3 rounded-lg bg-gray-50 px-4 py-2 dark:bg-white/5">
                        <x-filament::icon icon="heroicon-o-map-pin" class="h-5 w-5 shrink-0 text-gray-400" />
                        <div class="text-sm">
                            <p class="font-medium text-gray-950 dark:text-white">
                                {{ $lotacao->setorRef?->Setor ?? 'Setor não identificado' }}
                            </p>
                            <p class="text-gray-500 dark:text-gray-400">
                                {{ $lotacao->unidadeJudiciaria?->UnidadeOrganizacional }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @livewire(\App\Filament\Clusters\ExternoCluster\Widgets\MeusPedidosStats::class)

        @foreach ($this->gruposDeAcesso() as $grupo => $links)
            <div>
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    {{ $grupo }}
                </h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($links as $link)
                        <a
                            href="{{ $link['url'] }}"
                            class="group flex items-start gap-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 transition-shadow duration-200 hover:shadow-md dark:bg-gray-900 dark:ring-white/10"
                        >
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                                <x-filament::icon :icon="$link['icon']" class="h-5 w-5" />
                            </span>

                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-gray-950 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                                    {{ $link['label'] }}
                                </span>
                                <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">
                                    {{ $link['description'] }}
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
