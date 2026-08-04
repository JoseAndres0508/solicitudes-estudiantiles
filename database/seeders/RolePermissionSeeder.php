<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * RBAC defined by the institutional standard (Section 9.4).
 *
 * Unlike the SQL script, which wires permissions with literal numeric IDs,
 * the assignment here is resolved by name. If another module adds roles or
 * permissions to the shared core the IDs shift, and an ID-based matrix would
 * silently grant the wrong permissions; a name-based one will not.
 *
 * Role names stay in Spanish because they are data defined by the standard,
 * not identifiers in the codebase.
 */
class RolePermissionSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'credentials.manage' => 'Crear y editar atestados de docentes',
        'catalog.manage' => 'Crear versiones del catálogo de atinencias',
        'offering.manage' => 'Crear grupos, horarios y asignaciones',
        'suitability.verify' => 'Ejecutar verificaciones de atinencia',
        'technical_note.approve' => 'Aprobar la vía excepcional de Nota Técnica',
        'offering.view' => 'Consultar la oferta académica',
        'users.manage' => 'Administrar usuarios, roles y permisos',
        'files.upload' => 'Adjuntar documentos a los módulos',
        'files.download' => 'Descargar documentos adjuntos y reportes',
        'resolutions.manage' => 'Registrar resoluciones de modalidad por curso',
        'reservations.manage' => 'Registrar y aprobar préstamos de aulas',
        'offering.consolidate' => 'Consolidar la oferta y mover grupos de estado',
        'plans.manage' => 'Administrar planes de estudio, niveles y requisitos',
        'equivalences.manage' => 'Registrar equiparaciones entre planes',
        'requests.create' => 'Presentar solicitudes estudiantiles',
        'requests.review' => 'Revisar y resolver solicitudes estudiantiles',
    ];

    /** @var array<string, array{description: string, permissions: list<string>}> */
    private const ROLES = [
        'Administrador' => [
            'description' => 'Gestión total: catálogo de atinencias, usuarios y configuración',
            'permissions' => ['*'],
        ],
        'Coordinadora de Docencia' => [
            'description' => 'Registra atestados, consolida y gestiona asignaciones docentes',
            'permissions' => [
                'credentials.manage', 'offering.manage', 'suitability.verify', 'offering.view',
                'files.upload', 'files.download', 'resolutions.manage', 'reservations.manage',
                'offering.consolidate', 'plans.manage', 'equivalences.manage', 'requests.review',
            ],
        ],
        'Docente' => [
            'description' => 'Consulta su perfil, atestados y asignaciones',
            'permissions' => ['offering.view', 'files.download'],
        ],
        'Consulta' => [
            'description' => 'Acceso de solo lectura a la oferta académica',
            'permissions' => ['offering.view'],
        ],
        'Director de Carrera' => [
            'description' => 'Registra la oferta, planes y resoluciones de su propia carrera',
            'permissions' => [
                'offering.manage', 'offering.view', 'files.upload', 'files.download',
                'resolutions.manage', 'plans.manage', 'equivalences.manage',
            ],
        ],
        'Coordinador CONTA' => [
            'description' => 'Consolida la oferta de las carreras de su área',
            'permissions' => ['offering.view', 'files.download', 'offering.consolidate'],
        ],
        'Recursos Humanos' => [
            'description' => 'Lectura de la oferta consolidada; sin acceso a atinencias',
            'permissions' => ['offering.view', 'files.download'],
        ],
        'Estudiante' => [
            'description' => 'Presenta y da seguimiento a sus propias solicitudes',
            'permissions' => ['files.upload', 'requests.create'],
        ],
        'Comisión Técnica' => [
            'description' => 'Revisa y resuelve solicitudes de Convalidación',
            'permissions' => ['files.download', 'requests.review'],
        ],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $name => $description) {
            Permission::firstOrCreate(['name' => $name], ['description' => $description]);
        }

        $all = Permission::pluck('id', 'name');

        foreach (self::ROLES as $name => $config) {
            $role = Role::firstOrCreate(['name' => $name], ['description' => $config['description']]);

            $ids = $config['permissions'] === ['*']
                ? $all->values()->all()
                : $all->only($config['permissions'])->values()->all();

            $role->permissions()->syncWithPivotValues($ids, ['created_at' => now()]);
        }
    }
}
