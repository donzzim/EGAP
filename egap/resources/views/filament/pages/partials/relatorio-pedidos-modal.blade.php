<div class="space-y-6">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Pedido</div>
            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">#{{ $pedido->id }}</div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Data do pedido</div>
            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ optional($pedido->date_time)->format('d/m/Y H:i') ?? '-' }}
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Solicitante</div>
            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $pedido->solicitante_get?->name ?? '-' }}
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Responsavel</div>
            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $pedido->responsavel_atendimento?->name ?? '-' }}
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Situacao do pedido</div>
            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $pedido->situacao?->Descricao ?? '-' }}
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Setor responsavel</div>
            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ $pedido->setorResponsavel?->Setor ?? '-' }}
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Destino</div>
            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ collect([$pedido->setor_get?->UnidadeOrganizacional, $pedido->setor_get?->Setor, $pedido->complementoSetor?->descricao])->filter()->implode(' / ') ?: '-' }}
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Previsao / termino</div>
            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                {{ optional($pedido->DataTermino)->format('d/m/Y') ?? '-' }}
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 dark:border-blue-900/50 dark:bg-blue-950/30">
            <div class="text-xs font-medium uppercase text-blue-700 dark:text-blue-300">Solicitada</div>
            <div class="mt-1 text-lg font-semibold text-blue-900 dark:text-blue-100">
                {{ (int) $itens->sum('QuantidadeMaterial') }}
            </div>
        </div>

        <div class="rounded-lg border border-cyan-200 bg-cyan-50 px-4 py-3 dark:border-cyan-900/50 dark:bg-cyan-950/30">
            <div class="text-xs font-medium uppercase text-cyan-700 dark:text-cyan-300">Validada</div>
            <div class="mt-1 text-lg font-semibold text-cyan-900 dark:text-cyan-100">
                {{ (int) $itens->sum(fn ($item) => (int) ($item->quantidade_validada ?? 0)) }}
            </div>
        </div>

        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-900/50 dark:bg-emerald-950/30">
            <div class="text-xs font-medium uppercase text-emerald-700 dark:text-emerald-300">Atendida</div>
            <div class="mt-1 text-lg font-semibold text-emerald-900 dark:text-emerald-100">
                {{ (int) $itens->sum('QuantidadeMaterialAtendida') }}
            </div>
        </div>

        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900/50 dark:bg-amber-950/30">
            <div class="text-xs font-medium uppercase text-amber-700 dark:text-amber-300">Pendente</div>
            <div class="mt-1 text-lg font-semibold text-amber-900 dark:text-amber-100">
                {{ (int) $itens->sum(fn ($item) => $item->quantidade_pendente) }}
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Itens do pedido</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr class="text-left">
                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Item</th>
                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Material</th>
                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Situacao</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">Solicitada</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">Validada</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">Atendida</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">Pendente</th>
                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Validacao</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($itens as $item)
                    <tr class="align-top">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">#{{ $item->id }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $item->material_nome }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $item->situacaoRef?->Descricao ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-200">{{ (int) ($item->QuantidadeMaterial ?? 0) }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-200">{{ (int) ($item->quantidade_validada ?? 0) }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-200">{{ (int) ($item->QuantidadeMaterialAtendida ?? 0) }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-200">{{ (int) $item->quantidade_pendente }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                            {{ optional($item->data_validacao)->format('d/m/Y') ?? '-' }}
                            @if ($item->validadoPor?->name)
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->validadoPor->name }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            Nenhum item vinculado a este pedido.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Historico do fluxo</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr class="text-left">
                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Data</th>
                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Situacao</th>
                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Descricao</th>
                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Usuario</th>
                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Item</th>
                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Material</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">Quantidade</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($fases as $fase)
                    <tr class="align-top">
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                            {{ optional($fase->date_time)->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                            {{ $fase->situacaoRef?->Descricao ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                            {{ $fase->Descricao ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                            {{ $fase->usuarioRef?->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                            {{ $fase->id_itempedido ?: '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                            {{ $fase->descricaoDetalhadaRef?->descricao_detalhada ?? $fase->descricaoResumidaRef?->Descricao ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-200">
                            {{ filled($fase->quantidade) ? (int) $fase->quantidade : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            Nenhum evento de fluxo foi registrado para este pedido.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
