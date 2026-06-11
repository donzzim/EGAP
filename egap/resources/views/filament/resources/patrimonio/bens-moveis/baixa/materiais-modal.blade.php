<div class="space-y-4">
    @if ($itens->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 px-4 py-6 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400">
            Nenhum material está vinculado a esta baixa.
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Patrimônio</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Descrição</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Marca</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Modelo</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Situação atual</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Situação de destino</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-950">
                        @foreach ($itens as $item)
                            @php($bem = $item->bem)
                            @php($situacaoDestino = $situacoesDestino->get($item->id_situacao))
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-950 dark:text-white">
                                    {{ $bem?->NumPatrimonio ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $bem?->descricaoResumidaBemRef?->Descricao ?: $bem?->Descricao ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $bem?->marcaRef?->descricao ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $bem?->modeloRef?->descricao ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $bem?->situacaoBemRef?->descricao_completa ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                    {{ $situacaoDestino?->descricao_completa ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
