<div class="space-y-6">
    {{-- Cabeçalho / resumo do pedido --}}
    <div class="rounded-2xl border border-gray-200 bg-gradient-to-r from-white to-gray-50 p-5 shadow-sm dark:border-gray-800 dark:from-gray-900 dark:to-gray-950">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Detalhes do Pedido
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Informações gerais e itens vinculados ao pedido.
                </p>
            </div>

            <div class="rounded-full bg-primary-50 px-4 py-2 text-sm font-semibold text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                Pedido #{{ $pedido->id }}
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Pedido
                </div>
                <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                    {{ $pedido->id }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Data do Pedido
                </div>
                <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                    {{ optional($pedido->date_time)->format('d/m/Y H:i') ?? '-' }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Solicitante
                </div>
                <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                    {{ $pedido->solicitante_get?->name ?? '-' }}
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Setor
                </div>
                <div class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                    {{ $pedido->setor_get?->Setor ?? '-' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela de itens --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                Itens do Pedido
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Relação completa dos materiais solicitados.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr class="text-left">
                    <th class="whitespace-nowrap px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Item</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Material</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Justificativa</th>
                    <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">Qtde Solicitada</th>
                    <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">Qtde Atendida</th>
                    <th class="whitespace-nowrap px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">Qtde Validada</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Situação Material</th>
                    <th class="whitespace-nowrap px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Observação</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($itens as $item)
                    <tr class="align-top transition hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                            #{{ $item->id }}
                        </td>

                        <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                            <div class="font-medium">
                                {{ $item->material_nome }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            <div class="max-w-xs whitespace-normal">
                                {{ $item->justificativa ?: ($pedido->justificativa ?: '-') }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center">
                                <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full bg-blue-50 px-3 py-1 font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                                    {{ (int) ($item->QuantidadeMaterial ?? 0) }}
                                </span>
                        </td>

                        <td class="px-4 py-3 text-center">
                                <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                    {{ (int) ($item->QuantidadeMaterialAtendida ?? 0) }}
                                </span>
                        </td>

                        <td class="px-4 py-3 text-center">
                                <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full bg-amber-50 px-3 py-1 font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                    {{ (int) ($item->quantidade_validada ?? 0) }}
                                </span>
                        </td>

                        <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $item->situacaoRef?->Descricao ?? ($pedido->situacao?->Descricao ?? '-') }}
                                </span>
                        </td>

                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            <div class="max-w-xs whitespace-normal">
                                {{ $item->ObservacaoItem ?: ($pedido->Observacao ?: '-') }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            Nenhum item encontrado para este pedido.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
