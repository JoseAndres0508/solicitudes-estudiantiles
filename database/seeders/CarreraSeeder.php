<?php

namespace Database\Seeders;

use App\Models\Carrera;
use Illuminate\Database\Seeder;

/** Las 14 carreras del Manual de Atinencias, según el estándar institucional. */
class CarreraSeeder extends Seeder
{
    /** @var list<string> */
    private const CARRERAS = [
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
        foreach (self::CARRERAS as $nombre) {
            Carrera::firstOrCreate(['nombre' => $nombre], ['activa' => true]);
        }
    }
}
