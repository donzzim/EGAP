<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InventarioPermissionsSeeder extends Seeder
{
    private const GUARD = 'web';

    private const PERMISSIONS = [
        'inventarios.listar',
        'inventarios.visualizar',
        'inventarios.criar',
        'inventarios.editar',
        'inventarios.abrir',
        'inventarios.fechar',
        'inventarios.cancelar',
        'inventarios.definir-unidades',
        'inventarios.definir-setores',
        'inventarios.definir-comissoes',
        'inventarios.consolidar',
        'inventarios.visualizar-relatorios',
        'inventarios.administrar',
    ];

    private const ROLES = [
        'super-admin' => self::PERMISSIONS,

        'gestor-inventario' => [
            'inventarios.listar',
            'inventarios.visualizar',
            'inventarios.criar',
            'inventarios.editar',
            'inventarios.abrir',
            'inventarios.fechar',
            'inventarios.definir-unidades',
            'inventarios.definir-setores',
            'inventarios.definir-comissoes',
            'inventarios.consolidar',
            'inventarios.visualizar-relatorios',
        ],

        'responsavel-abertura-inventario' => [
            'inventarios.listar',
            'inventarios.visualizar',
            'inventarios.criar',
            'inventarios.abrir',
            'inventarios.definir-unidades',
            'inventarios.definir-setores',
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
