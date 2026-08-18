<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permissions e roles do módulo de Inventário (Bens Móveis), migrados a
 * partir do controle de acesso do sistema legado (Joomla + Fabrik).
 *
 * Referência no sistema antigo (banco `egap`, mesmas tabelas hoje residentes
 * em `patrimonio`):
 *
 * - jos_usergroups: grupos "Gestor" (id 12), "Inventário" (id 20) e
 *   "Comissão Levamentamento" (id 29) — bases dos roles abaixo.
 * - jos_viewlevels id 17 "Inventário" (rules=[20]) — nível de acesso que, via
 *   jos_menu (itens 209/210/263/264/265/267/268, todos access=17), controlava
 *   a exibição de TODO o menu do módulo: Gestão do Inventário, Unidades/
 *   Equipes, Atividades, Bens Móveis Inventários, Duplicidades e Não
 *   Localizados. É essa fronteira de menu que hoje corresponde a
 *   `inventarios.listar`.
 * - jos_fabrik_lists: id 42 "Gestão do Inventário" (mat_inventario), id 43
 *   "Unidades Inventariadas" (mat_unidadesinventario), id 44 "Itens
 *   Inventariados" (mat_itensinventario) e id 81 "Atividades do Inventário"
 *   (inv_atividades).
 * - inv_comissao e inv_equipes: tabelas legadas específicas para a comissão
 *   do inventário e as equipes de campo por unidade — hoje modeladas em
 *   InventarioComissao e InventarioEquipe.
 * - Módulo Externo (levantamento_comissao.php + api/assinatura.api.php,
 *   hoje em ExternoCluster\Livewire\Patrimonio\LevantamentoComissaoTable):
 *   assinatura eletrônica do Termo de Responsabilidade pelos bens já
 *   conferidos no setor — ação distinta de apenas conferir itens.
 *
 * A granularidade abaixo foi ajustada às telas reais do módulo (Inventario,
 * InventarioUnidade, InventarioEquipe, InventarioComissao, AtividadeInventario
 * e ItemInventario), mas os nomes e o recorte dos roles seguem, sempre que
 * possível, os grupos e níveis de acesso do sistema antigo.
 */
class InventarioPermissionsSeeder extends Seeder
{
    private const GUARD = 'web';

    private const PERMISSIONS = [
        // Inventário (mat_inventario / Fabrik "Gestão do Inventário", list 42)
        'inventarios.listar',
        'inventarios.visualizar',
        'inventarios.criar',
        'inventarios.editar',
        'inventarios.abrir',
        'inventarios.fechar',
        'inventarios.cancelar',

        // Planejamento: unidades, equipes de campo e comissão
        'inventarios.definir-unidades',
        'inventarios.definir-equipes',
        'inventarios.definir-comissoes',

        // Execução do levantamento em campo
        'inventarios.registrar-atividades',
        'inventarios.conferir-itens',
        'inventarios.finalizar-levantamento',
        'inventarios.assinar-termos',

        // Encerramento e relatórios
        'inventarios.consolidar',
        'inventarios.visualizar-relatorios',

        // Administração geral do módulo
        'inventarios.administrar',
    ];

    private const ROLES = [
        // jos_usergroups id 8 "SuperAdmin" / jos_assets root: core.admin
        'super-admin' => self::PERMISSIONS,

        // jos_usergroups id 12 "Gestor": acesso amplo de gestão, mas sem as
        // ações destrutivas/administrativas reservadas ao super-admin.
        'gestor-inventario' => [
            'inventarios.listar',
            'inventarios.visualizar',
            'inventarios.criar',
            'inventarios.editar',
            'inventarios.abrir',
            'inventarios.fechar',
            'inventarios.definir-unidades',
            'inventarios.definir-equipes',
            'inventarios.definir-comissoes',
            'inventarios.registrar-atividades',
            'inventarios.conferir-itens',
            'inventarios.finalizar-levantamento',
            'inventarios.consolidar',
            'inventarios.visualizar-relatorios',
        ],

        // jos_usergroups id 29 "Comissão Levamentamento": quem executa a
        // contagem em campo e assina os termos de responsabilidade.
        'comissao-inventario' => [
            'inventarios.listar',
            'inventarios.visualizar',
            'inventarios.registrar-atividades',
            'inventarios.conferir-itens',
            'inventarios.finalizar-levantamento',
            'inventarios.assinar-termos',
        ],

        'responsavel-abertura-inventario' => [
            'inventarios.listar',
            'inventarios.visualizar',
            'inventarios.criar',
            'inventarios.abrir',
            'inventarios.definir-unidades',
            'inventarios.definir-equipes',
            'inventarios.definir-comissoes',
        ],

        'responsavel-fechamento-inventario' => [
            'inventarios.listar',
            'inventarios.visualizar',
            'inventarios.fechar',
        ],

        'responsavel-consolidacao-inventario' => [
            'inventarios.listar',
            'inventarios.visualizar',
            'inventarios.consolidar',
            'inventarios.visualizar-relatorios',
        ],

        // jos_usergroups id 20 "Inventário": acesso geral de leitura ao
        // módulo, sem ações de escrita.
        'visualizador-inventario' => [
            'inventarios.listar',
            'inventarios.visualizar',
            'inventarios.visualizar-relatorios',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => self::GUARD,
            ]);
        }

        foreach (self::ROLES as $roleName => $permissions) {
            $role = Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => self::GUARD,
            ]);

            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
