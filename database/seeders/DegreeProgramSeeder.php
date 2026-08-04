<?php

namespace Database\Seeders;

use App\Models\DegreeProgram;
use Illuminate\Database\Seeder;

/** The 14 degree programs in scope, per the institutional standard. */
class DegreeProgramSeeder extends Seeder
{
    /** @var list<string> */
    private const PROGRAMS = [
        'Administración y Gestión de Recursos Humanos',
        'Administración Aduanera',
        'Ingeniería en Tecnologías de Información - Tecnologías de Información',
        'Ingeniería del Software - Tecnologías Informáticas',
        'Contabilidad y Finanzas - Contaduría Pública',
        'Asistencia Administrativa',
        'Inglés como Lengua Extranjera',
        'Administración Agroindustrial',
        'Gestión de Centros de Servicios Compartidos',
        'Ingeniería en Mantenimiento Agroindustrial Sostenible - Mantenimiento Agroindustrial Sostenible',
        'Ingeniería en Gestión Ambiental',
        'Ingeniería en Salud Ocupacional y Ambiente - Salud Ocupacional',
        'Ingeniería en Tecnología de Alimentos - Tecnología de Alimentos',
        'Administración del Comercio Exterior',
    ];

    public function run(): void
    {
        foreach (self::PROGRAMS as $name) {
            DegreeProgram::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
