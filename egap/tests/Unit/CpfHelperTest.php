<?php

namespace Tests\Unit;

use App\Helper\CpfHelper;
use PHPUnit\Framework\TestCase;

class CpfHelperTest extends TestCase
{
    public function test_formata_cpf_com_a_mascara_padrao(): void
    {
        $this->assertSame('123.456.789-01', CpfHelper::format('12345678901'));
        $this->assertSame('123.456.789-01', CpfHelper::format('123.456.789-01'));
    }

    public function test_mantem_o_preenchimento_a_esquerda_do_comportamento_legado(): void
    {
        $this->assertSame('000.000.001-23', CpfHelper::format('123'));
    }

    public function test_retorna_string_vazia_para_cpf_vazio(): void
    {
        $this->assertSame('', CpfHelper::format(null));
        $this->assertSame('', CpfHelper::format(''));
    }
}
