<div class="space-y-4">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-medium text-gray-950 dark:text-white">
                Termo {{ $record->num_termo }}/{{ $record->ano_termo }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Pedido {{ $pedidoNo ?: '-' }} | Fluxo {{ $fluxo }}
            </p>
        </div>

        <a
            href="{{ route('termo.imprimir', $record->id) }}"
            target="_blank"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
        >
            Imprimir termo
        </a>
    </div>

    @if ($materiais->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-6 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400">
            Nenhum patrimonio foi encontrado para este termo.
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Patrimonio</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Descricao</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Marca</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Modelo</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Atualizado em</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-950">
                        @foreach ($materiais as $material)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-950 dark:text-white">
                                    {{ $material->numero_patrimonio ?: $material->patrimonio_id }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $material->descricao ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $material->marca ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $material->modelo ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ filled($material->atualizado_em) ? \Illuminate\Support\Carbon::parse($material->atualizado_em)->format('d/m/Y H:i') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
