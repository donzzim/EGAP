<div class="p-4 overflow-x-auto">
    <table class="w-full text-left border-collapse border border-gray-200 dark:border-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th class="p-3 border-b text-sm font-bold">Data</th>
                <th class="p-3 border-b text-sm font-bold">Unidade Anterior</th>
                <th class="p-3 border-b text-sm font-bold">Unidade Atual</th>
                <th class="p-3 border-b text-sm font-bold">Atualizado por</th>
                <th class="p-3 border-b text-sm font-bold">Pedido No</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($historico as $item)
                <tr>
                    <td class="p-3 text-sm italic">{{ \Carbon\Carbon::parse($item->date_time)->format('d/m/Y H:i') }}</td>
                    <td class="p-3 text-sm">{{ $item->UnidadeAnterior }} / {{ $item->SetorAnterior }}</td>
                    <td class="p-3 text-sm font-bold text-primary-600">{{ $item->UnidadeAtual }} / {{ $item->SetorAtual }}</td>
                    <td class="p-3 text-sm text-gray-500">{{ $item->Usuario }}</td>
                    <td class="p-3 text-sm">{{ $item->pedido_no ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-6 text-center text-gray-500 italic">Nenhum registro encontrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>