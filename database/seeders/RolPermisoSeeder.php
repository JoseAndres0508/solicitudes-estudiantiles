<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

/**
 * RBAC del estándar institucional (Sección 9.4).
 *
 * A diferencia del script SQL, que asigna permisos con IDs numéricos literales,
 * aquí la asignación se resuelve por nombre. Si otro módulo agrega roles o
 * permisos al núcleo compartido, los IDs se desplazan y una matriz por ID
 * quedaría mal asignada; por nombre no.
 */
class RolPermisoSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISOS = [
        'atestados.gestionar' => 'Crear y editar atestados de docentes',
        'catalogo.gestionar' => 'Crear versiones del catálogo de atinencias',
        'oferta.gestionar' => 'Crear grupos, horarios y asignaciones',
        'atinencia.verificar' => 'Ejecutar verificaciones de atinencia',
        'nota_tecnica.aprobar' => 'Aprobar la vía excepcional de Nota Técnica',
        'oferta.consultar' => 'Consultar la oferta académica',
        'usuarios.gestionar' => 'Administrar usuarios, roles y permisos',
        'archivos.subir' => 'Adjuntar documentos a los módulos',
        'archivos.descargar' => 'Descargar documentos adjuntos y reportes',
        'resoluciones.gestionar' => 'Registrar resoluciones de modalidad por curso',
        'reservas.gestionar' => 'Registrar y aprobar préstamos de aulas',
        'oferta.consolidar' => 'Consolidar la oferta y mover grupos de estado',
        'planes.gestionar' => 'Administrar planes de estudio, niveles y requisitos',
        'equiparaciones.gestionar' => 'Registrar equiparaciones entre planes',
        'solicitudes.crear' => 'Presentar solicitudes estudiantiles',
        'solicitudes.revisar' => 'Revisar y resolver solicitudes estudiantiles',
    ];

    /** @var array<string, array{descripcion: string, permisos: list<string>}> */
    private const ROLES = [
        'Administrador' => [
            'descripcion' => 'Gestión total: catálogo de atinencias, usuarios y configuración',
            'permisos' => ['*'],
        ],
        'Coordinadora de Docencia' => [
            'descripcion' => 'Registra atestados, consolida y gestiona asignaciones docentes',
            'permisos' => [
                'atestados.gestionar', 'oferta.gestionar', 'atinencia.verificar', 'oferta.consultar',
                'archivos.subir', 'archivos.descargar', 'resoluciones.gestionar', 'reservas.gestionar',
                'oferta.consolidar', 'planes.gestionar', 'equiparaciones.gestionar', 'solicitudes.revisar',
            ],
        ],
        'Docente' => [
            'descripcion' => 'Consulta su perfil, atestados y asignaciones',
            'permisos' => ['oferta.consultar', 'archivos.descargar'],
        ],
        'Consulta' => [
            'descripcion' => 'Acceso de solo lectura a la oferta académica',
            'permisos' => ['oferta.consultar'],
        ],
        'Director de Carrera' => [
            'descripcion' => 'Registra la oferta, planes y resoluciones de su propia carrera',
            'permisos' => [
                'oferta.gestionar', 'oferta.consultar', 'archivos.subir', 'archivos.descargar',
                'resoluciones.gestionar', 'planes.gestionar', 'equiparaciones.gestionar',
            ],
        ],
        'Coordinador CONTA' => [
            'descripcion' => 'Consolida la oferta de las carreras de su área',
            'permisos' => ['oferta.consultar', 'archivos.descargar', 'oferta.consolidar'],
        ],
        'Recursos Humanos' => [
            'descripcion' => 'Lectura de la oferta consolidada; sin acceso a atinencias',
            'permisos' => ['oferta.consultar', 'archivos.descargar'],
        ],
        'Estudiante' => [
            'descripcion' => 'Presenta y da seguimiento a sus propias solicitudes',
            'permisos' => ['solicitudes.crear', 'archivos.subir'],
        ],
        'Comisión Técnica' => [
            'descripcion' => 'Revisa y resuelve solicitudes de convalidación',
            'permisos' => ['solicitudes.revisar', 'archivos.descargar'],
        ],
    ];

    public function run(): void
    {
        foreach (self::PERMISOS as $name => $description) {
            Permiso::firstOrCreate(['name' => $name], ['description' => $description]);
        }

        $todos = Permiso::pluck('id', 'name');

        foreach (self::ROLES as $name => $config) {
            $rol = Rol::firstOrCreate(['name' => $name], ['description' => $config['descripcion']]);

            $ids = $config['permisos'] === ['*']
                ? $todos->values()->all()
                : $todos->only($config['permisos'])->values()->all();

            $rol->permisos()->syncWithPivotValues($ids, ['created_at' => now()]);
        }
    }
}
