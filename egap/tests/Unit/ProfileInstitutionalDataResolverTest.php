<?php

namespace Tests\Unit;

use App\Filament\Pages\Auth\ProfileInstitutionalDataResolver;
use App\Models\Admin\InfoUser;
use App\Models\Admin\Lotacao;
use App\Models\Cadastro\Setores;
use App\Models\UserEgap;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ProfileInstitutionalDataResolverTest extends TestCase
{
    public function test_retorna_traco_para_todos_os_campos_quando_nao_ha_vinculo_legado(): void
    {
        $data = ProfileInstitutionalDataResolver::resolve(null, null, null, []);

        $this->assertSame('-', $data['cargo']);
        $this->assertSame('-', $data['unidade_judiciaria']);
        $this->assertSame('-', $data['setor']);
        $this->assertSame('-', $data['bloqueado_legado']);
        $this->assertSame('Nenhum perfil atribuído', $data['perfis_acesso']);
        $this->assertSame('-', $data['data_cadastro']);
        $this->assertSame('-', $data['ultimo_acesso']);
    }

    public function test_monta_cargo_lotacao_e_perfis_a_partir_dos_vinculos_legados(): void
    {
        // setRawAttributes (em vez de fill()) evita que o cast de data do
        // Eloquent chame getConnection() ao formatar a data para armazenamento,
        // o que exigiria um resolver de conexão de banco só disponível com o
        // framework Laravel inicializado (fora do escopo de um teste unitário puro).
        $userEgap = new UserEgap;
        $userEgap->setRawAttributes([
            'block' => 1,
            'registerDate' => Carbon::parse('2020-03-10 08:00:00'),
            'lastvisitDate' => Carbon::parse('2026-08-10 14:30:00'),
        ]);

        $infoUser = new InfoUser(['cargo' => 'ANALISTA JUDICIARIO']);

        $lotacao = new Lotacao;
        $lotacao->setRelation('unidadeJudiciaria', new Setores(['Setor' => 'TRIBUNAL DE JUSTICA']));
        $lotacao->setRelation('setorRef', new Setores(['Setor' => 'Seção de Patrimônio']));

        $data = ProfileInstitutionalDataResolver::resolve($userEgap, $infoUser, $lotacao, ['Administrador', 'Almoxarife']);

        $this->assertSame('ANALISTA JUDICIARIO', $data['cargo']);
        $this->assertSame('TRIBUNAL DE JUSTICA', $data['unidade_judiciaria']);
        $this->assertSame('Seção de Patrimônio', $data['setor']);
        $this->assertSame('Sim', $data['bloqueado_legado']);
        $this->assertSame('Administrador, Almoxarife', $data['perfis_acesso']);
        $this->assertSame('10/03/2020', $data['data_cadastro']);
        $this->assertSame('10/08/2026 14:30', $data['ultimo_acesso']);
    }
}
