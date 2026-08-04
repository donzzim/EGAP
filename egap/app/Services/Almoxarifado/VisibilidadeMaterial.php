<?php

namespace App\Services\Almoxarifado;

class VisibilidadeMaterial
{
    /**
     * Replica a regra do sistema legado (pedidos_consumo.php): unidades 766 e
     * 866 enxergam os materiais com visibilidade 2 ou 3; as demais unidades
     * enxergam 1 ou 3.
     *
     * @return array<int>
     */
    public static function permitidas(?int $unidadeJudiciaria): array
    {
        return in_array($unidadeJudiciaria, [766, 866], true)
            ? [2, 3]
            : [1, 3];
    }
}
