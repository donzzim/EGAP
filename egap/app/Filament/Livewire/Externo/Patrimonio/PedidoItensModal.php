<?php

namespace App\Filament\Livewire\Externo\Patrimonio;

use App\Filament\Livewire\Externo\PedidoItensModal as PedidoItensModalBase;
use App\Filament\Support\TableColumns;
use App\Models\Almoxarifado\ItemPedido;
use App\Services\Mobile\PedidosMobileService;

/**
 * Itens de um pedido de materiais permanentes, exibidos no modal aberto a
 * partir de {@see PedidosPatrimonioTable} (legado: modal_pedidos.api.php,
 * ramo do setor responsável = Patrimônio).
 *
 * Diferente do Almoxarifado, a justificativa é registrada por item (não no
 * pedido) — {@see ItemPedido::$justificativa} guarda
 * o tipo de atendimento (Adição/Substituição) junto com o motivo, formatado
 * em {@see PedidosMobileService::formatarJustificativaPermanente()}.
 */
class PedidoItensModal extends PedidoItensModalBase
{
    protected function colunasExtras(): array
    {
        return [
            TableColumns::text('justificativa', 'Justificativa')
                ->wrap()
                ->formatStateUsing(fn (?string $state): string => $this->formatarJustificativa($state)),
        ];
    }

    protected function statusColor(int $status): string
    {
        return match ($status) {
            3, 7 => 'success',
            4, 10 => 'gray',
            5 => 'danger',
            6, 9 => 'warning',
            8 => 'info',
            default => 'gray',
        };
    }

    private function formatarJustificativa(?string $raw): string
    {
        if (blank($raw)) {
            return '-';
        }

        return html_entity_decode(trim($raw, '{}'));
    }
}
