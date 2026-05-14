<?php

namespace App\Filament\Egap\Widgets\PortalTransparencia;

use Filament\Widgets\ChartWidget;

abstract class BaseChart extends ChartWidget
{
    public ?string $chartType = 'bar';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        $allowedTypes = [
            'bar',
            'line',
            'bubble',
            'doughnut',
            'pie',
            'polarArea',
        ];

        return in_array($this->chartType, $allowedTypes, true)
            ? $this->chartType
            : 'bar';
    }

    protected function getColors(int $count): array
    {
        $colors = [
            'rgba(59, 130, 246, 0.5)',   // azul
            'rgba(16, 185, 129, 0.5)',   // verde
            'rgba(245, 158, 11, 0.5)',   // amarelo
            'rgba(239, 68, 68, 0.5)',    // vermelho
            'rgba(139, 92, 246, 0.5)',   // roxo
            'rgba(236, 72, 153, 0.5)',   // rosa
            'rgba(6, 182, 212, 0.5)',    // ciano
            'rgba(132, 204, 22, 0.5)',   // lime
        ];

        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $result[] = $colors[$i % count($colors)];
        }

        return $result;
    }

    protected function getBorderColors(int $count): array
    {
        $border_colors = [
            'rgba(59, 130, 246, 1)',   // azul
            'rgba(16, 185, 129, 1)',   // verde
            'rgba(245, 158, 11, 1)',   // amarelo
            'rgba(239, 68, 68, 1)',    // vermelho
            'rgba(139, 92, 246, 1)',   // roxo
            'rgba(236, 72, 153, 1)',   // rosa
            'rgba(6, 182, 212, 1)',    // ciano
            'rgba(132, 204, 22, 1)',   // lime
        ];

        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $result[] = $border_colors[$i % count($border_colors)];
        }

        return $result;
    }
}
