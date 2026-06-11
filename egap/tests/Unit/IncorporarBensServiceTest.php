<?php

namespace Tests\Unit;

use App\Services\Patrimonio\IncorporarBensService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class IncorporarBensServiceTest extends TestCase
{
    public function test_expande_faixas_e_numeros_individuais(): void
    {
        $service = new IncorporarBensService;

        $numeros = $service->numerosDasFaixas([
            ['inicio' => 10, 'fim' => 12],
            ['inicio' => 20, 'fim' => null],
        ]);

        $this->assertSame([10, 11, 12, 20], $numeros);
    }

    public function test_permite_incorporar_somente_o_bem_de_referencia(): void
    {
        $service = new IncorporarBensService;

        $this->assertSame([], $service->numerosDasFaixas([]));
    }

    public function test_rejeita_faixas_sobrepostas(): void
    {
        $service = new IncorporarBensService;

        $this->expectException(ValidationException::class);

        $service->numerosDasFaixas([
            ['inicio' => 10, 'fim' => 12],
            ['inicio' => 12, 'fim' => 14],
        ]);
    }

    public function test_rejeita_faixa_decrescente(): void
    {
        $service = new IncorporarBensService;

        $this->expectException(ValidationException::class);

        $service->numerosDasFaixas([
            ['inicio' => 20, 'fim' => 10],
        ]);
    }
}
