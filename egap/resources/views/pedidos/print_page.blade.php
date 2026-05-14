<x-filament-panels::page>
    @include('egap.pedidos.pedidos_impressao', ['pedido' => $pedido])

    <script>
        document.addEventListener('livewire:load', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</x-filament-panels::page>
