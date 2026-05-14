<x-filament-panels::page>
    @include('pedidos.pedidos_impressao', ['pedido' => $pedido])

    <script>
        document.addEventListener('livewire:load', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</x-filament-panels::page>
