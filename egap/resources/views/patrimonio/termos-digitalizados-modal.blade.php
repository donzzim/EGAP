<div class="space-y-4 p-4">
    @forelse($termos as $termo)
        <div class="border rounded-lg p-4 bg-white shadow-sm flex flex-col gap-1">
            <div class="flex items-center justify-between">
                <span class="font-bold text-gray-800">
                    Termo Nº: {{ $termo->num_termo }}/{{ $termo->ano_termo }}
                </span>

                @if($termo->StatusArquivo == 1)
                <a href="{{ route('termo.imprimir', ['id' => $termo->TermoID]) }}"
                target="_blank"
                class="text-sm text-primary-600 font-bold underline">
                    Visualizar Termo
                </a>
                @else
                    <span class="inline-flex items-center text-xs font-semibold text-amber-600 gap-1 bg-amber-50 px-2 py-1 rounded">
                        ⚠️ — ainda não validado/assinado
                    </span>
                @endif
            </div>
            <span class="text-xs text-gray-400">Data: {{ \Carbon\Carbon::parse($termo->date_time)->format('d/m/Y') }}</span>
        </div>
    @empty
        <p class="text-sm text-gray-500 text-center">Nenhum termo associado a este patrimônio.</p>
    @endforelse
</div>
