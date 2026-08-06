<?php

namespace App\Filament\Support;

use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;

class TableColumns
{
    public static function updatedBy(
        string $name,
        ?string $label = 'Atualizado por',
        string $dateColumn = 'date_time',
        string $format = 'd/m/Y H:i',
    ): TextColumn
    {
        return self::text($name, $label)
            ->description(function ($record) use ($dateColumn, $format) {
                $value = $record->{$dateColumn} ?? null;

                return filled($value) ? Carbon::parse($value)->format($format) : null;
            });
    }

    public static function text(string $name, ?string $label = null, bool $isFirstColumn = false): TextColumn
    {
        return self::base($name, $label, $isFirstColumn)
            ->default('-');
    }

    public static function date(
        string $name,
        ?string $label = null,
        string $format = 'd/m/Y',
        bool $isFirstColumn = false,
    ): TextColumn
    {
        return self::base($name, $label, $isFirstColumn)
            ->placeholder('-')
            ->date($format);
    }

    public static function dateTime(
        string $name,
        ?string $label = null,
        ?string $format = 'd/m/Y H:i',
        bool $isFirstColumn = false,
    ): TextColumn
    {
        $column = self::base($name, $label, $isFirstColumn)
            ->placeholder('-');

        return $format === null
            ? $column->dateTime()
            : $column->dateTime($format);
    }

    public static function money(
        string $name,
        ?string $label = null,
        string $currency = 'BRL',
        bool $divideBy = false,
        ?string $locale = null,
        bool $isFirstColumn = false,
    ): TextColumn
    {
        return self::base($name, $label, $isFirstColumn)
            ->placeholder('-')
            ->money($currency, $divideBy, $locale);
    }

    private static function base(string $name, ?string $label = null, bool $isFirstColumn = false): TextColumn
    {
        $column = TextColumn::make($name)
            ->sortable()
            ->searchable();

        if (! $isFirstColumn) {
            $column->alignCenter();
        }

        if ($label !== null) {
            $column->label($label);
        }

        return $column;
    }
}
