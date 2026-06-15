<?php

namespace Tests\Unit;

use App\Models\Patrimonio\BensMoveis\ArquivoDigital;
use PHPUnit\Framework\TestCase;

class ArquivoDigitalTest extends TestCase
{
    public function test_situacoes_usam_os_codigos_da_tabela_mat_arquivodigital(): void
    {
        $this->assertSame([
            0 => 'Pendente',
            1 => 'Validado',
            2 => 'Invalidado',
            3 => 'Cancelado',
        ], ArquivoDigital::situacaoOptions());

        $this->assertSame('Cancelado', ArquivoDigital::situacaoLabel(3));
        $this->assertSame('Indefinido', ArquivoDigital::situacaoLabel(null));
    }

    public function test_caminho_do_arquivo_e_salvo_no_padrao_legado(): void
    {
        $arquivoDigital = new ArquivoDigital;
        $arquivoDigital->arquivo_digital = 'images/termos/termo_11_2015.pdf';

        $this->assertSame(
            '/images/termos/termo_11_2015.pdf',
            $arquivoDigital->getAttributes()['arquivo_digital'],
        );

        $arquivoDigital->arquivo_digital = '/images/termos/termo_11_2015.pdf';

        $this->assertSame(
            '/images/termos/termo_11_2015.pdf',
            $arquivoDigital->getAttributes()['arquivo_digital'],
        );
    }
}
