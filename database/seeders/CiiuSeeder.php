<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CiiuSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            // Sección A — Agricultura
            ['code' => '0111', 'name' => 'Cultivo de cereales (excepto arroz), legumbres y semillas oleaginosas', 'section' => 'A', 'division' => '01'],
            ['code' => '0141', 'name' => 'Cría de ganado bovino y bufalino', 'section' => 'A', 'division' => '01'],
            ['code' => '0150', 'name' => 'Producción agropecuaria combinada', 'section' => 'A', 'division' => '01'],

            // Sección C — Industria manufacturera
            ['code' => '1011', 'name' => 'Procesamiento y conservación de carne y productos cárnicos', 'section' => 'C', 'division' => '10'],
            ['code' => '1081', 'name' => 'Elaboración de productos de panadería', 'section' => 'C', 'division' => '10'],
            ['code' => '1511', 'name' => 'Curtido y recurtido de cueros; recurtido y teñido de pieles', 'section' => 'C', 'division' => '15'],
            ['code' => '1810', 'name' => 'Actividades de impresión', 'section' => 'C', 'division' => '18'],
            ['code' => '2511', 'name' => 'Fabricación de productos metálicos para uso estructural', 'section' => 'C', 'division' => '25'],
            ['code' => '2620', 'name' => 'Fabricación de ordenadores y equipo periférico', 'section' => 'C', 'division' => '26'],

            // Sección F — Construcción
            ['code' => '4111', 'name' => 'Construcción de edificios residenciales', 'section' => 'F', 'division' => '41'],
            ['code' => '4112', 'name' => 'Construcción de edificios no residenciales', 'section' => 'F', 'division' => '41'],
            ['code' => '4290', 'name' => 'Construcción de otras obras de ingeniería civil', 'section' => 'F', 'division' => '42'],
            ['code' => '4321', 'name' => 'Instalaciones eléctricas', 'section' => 'F', 'division' => '43'],

            // Sección G — Comercio al por mayor y menor
            ['code' => '4511', 'name' => 'Comercio de vehículos automotores nuevos', 'section' => 'G', 'division' => '45'],
            ['code' => '4610', 'name' => 'Comercio al por mayor a cambio de una retribución o por contrato', 'section' => 'G', 'division' => '46'],
            ['code' => '4620', 'name' => 'Comercio al por mayor de materias primas agropecuarias; animales vivos', 'section' => 'G', 'division' => '46'],
            ['code' => '4631', 'name' => 'Comercio al por mayor de productos alimenticios', 'section' => 'G', 'division' => '46'],
            ['code' => '4641', 'name' => 'Comercio al por mayor de productos textiles, productos confecciones y calzado', 'section' => 'G', 'division' => '46'],
            ['code' => '4649', 'name' => 'Comercio al por mayor de otros utensilios domésticos', 'section' => 'G', 'division' => '46'],
            ['code' => '4651', 'name' => 'Comercio al por mayor de computadores, equipo periférico y de telecomunicaciones', 'section' => 'G', 'division' => '46'],
            ['code' => '4659', 'name' => 'Comercio al por mayor de otros tipos de maquinaria y equipo', 'section' => 'G', 'division' => '46'],
            ['code' => '4690', 'name' => 'Comercio al por mayor no especializado', 'section' => 'G', 'division' => '46'],
            ['code' => '4711', 'name' => 'Comercio al por menor en establecimientos no especializados con surtido compuesto principalmente de alimentos, bebidas o tabaco', 'section' => 'G', 'division' => '47'],
            ['code' => '4719', 'name' => 'Comercio al por menor en establecimientos no especializados, con surtido compuesto principalmente de productos diferentes de alimentos, bebidas o tabaco', 'section' => 'G', 'division' => '47'],
            ['code' => '4721', 'name' => 'Comercio al por menor de productos agrícolas para el consumo en establecimientos especializados', 'section' => 'G', 'division' => '47'],
            ['code' => '4731', 'name' => 'Comercio al por menor de combustible para automotores', 'section' => 'G', 'division' => '47'],
            ['code' => '4741', 'name' => 'Comercio al por menor de computadores, equipos periféricos, programas de informática y equipos de telecomunicaciones en establecimientos especializados', 'section' => 'G', 'division' => '47'],
            ['code' => '4751', 'name' => 'Comercio al por menor de productos textiles en establecimientos especializados', 'section' => 'G', 'division' => '47'],
            ['code' => '4753', 'name' => 'Comercio al por menor de tapices, alfombras y cubrimientos para paredes y pisos en establecimientos especializados', 'section' => 'G', 'division' => '47'],
            ['code' => '4771', 'name' => 'Comercio al por menor de prendas de vestir y sus accesorios (incluye artículos de piel) en establecimientos especializados', 'section' => 'G', 'division' => '47'],
            ['code' => '4772', 'name' => 'Comercio al por menor de todo tipo de calzado y artículos de cuero y sucedáneos del cuero en establecimientos especializados', 'section' => 'G', 'division' => '47'],
            ['code' => '4773', 'name' => 'Comercio al por menor de productos farmacéuticos y medicinales, cosméticos y artículos de tocador en establecimientos especializados', 'section' => 'G', 'division' => '47'],
            ['code' => '4789', 'name' => 'Comercio al por menor de otros productos en puestos de venta móviles o en mercados', 'section' => 'G', 'division' => '47'],

            // Sección H — Transporte
            ['code' => '4921', 'name' => 'Transporte de pasajeros', 'section' => 'H', 'division' => '49'],
            ['code' => '4923', 'name' => 'Transporte de carga por carretera', 'section' => 'H', 'division' => '49'],
            ['code' => '5320', 'name' => 'Actividades de mensajería', 'section' => 'H', 'division' => '53'],

            // Sección I — Alojamiento y comidas
            ['code' => '5511', 'name' => 'Alojamiento en hoteles', 'section' => 'I', 'division' => '55'],
            ['code' => '5611', 'name' => 'Expendio a la mesa de comidas preparadas', 'section' => 'I', 'division' => '56'],
            ['code' => '5613', 'name' => 'Expendio de comidas preparadas en cafeterías', 'section' => 'I', 'division' => '56'],
            ['code' => '5621', 'name' => 'Catering para eventos', 'section' => 'I', 'division' => '56'],

            // Sección J — Información y comunicaciones
            ['code' => '5813', 'name' => 'Edición de periódicos, revistas y otras publicaciones periódicas', 'section' => 'J', 'division' => '58'],
            ['code' => '5911', 'name' => 'Actividades de producción de películas cinematográficas, videos, programas, anuncios y comerciales de televisión', 'section' => 'J', 'division' => '59'],
            ['code' => '6110', 'name' => 'Actividades de telecomunicaciones alámbricas', 'section' => 'J', 'division' => '61'],
            ['code' => '6120', 'name' => 'Actividades de telecomunicaciones inalámbricas', 'section' => 'J', 'division' => '61'],
            ['code' => '6190', 'name' => 'Otras actividades de telecomunicaciones', 'section' => 'J', 'division' => '61'],
            ['code' => '6201', 'name' => 'Actividades de desarrollo de sistemas informáticos (planificación, análisis, diseño, programación, pruebas)', 'section' => 'J', 'division' => '62'],
            ['code' => '6202', 'name' => 'Actividades de consultoría informática y actividades de administración de instalaciones informáticas', 'section' => 'J', 'division' => '62'],
            ['code' => '6209', 'name' => 'Otras actividades de tecnología de información y actividades de servicios informáticos', 'section' => 'J', 'division' => '62'],

            // Sección K — Actividades financieras
            ['code' => '6411', 'name' => 'Banco central', 'section' => 'K', 'division' => '64'],
            ['code' => '6412', 'name' => 'Bancos comerciales', 'section' => 'K', 'division' => '64'],
            ['code' => '6491', 'name' => 'Cooperativas de ahorro y crédito', 'section' => 'K', 'division' => '64'],

            // Sección L — Actividades inmobiliarias
            ['code' => '6810', 'name' => 'Actividades inmobiliarias realizadas con bienes propios o arrendados', 'section' => 'L', 'division' => '68'],
            ['code' => '6820', 'name' => 'Actividades inmobiliarias realizadas a cambio de una retribución o por contrato', 'section' => 'L', 'division' => '68'],

            // Sección M — Actividades profesionales y científicas
            ['code' => '6910', 'name' => 'Actividades jurídicas', 'section' => 'M', 'division' => '69'],
            ['code' => '6920', 'name' => 'Actividades de contabilidad, teneduría de libros, auditoría financiera y asesoría tributaria', 'section' => 'M', 'division' => '69'],
            ['code' => '7010', 'name' => 'Actividades de administración empresarial', 'section' => 'M', 'division' => '70'],
            ['code' => '7020', 'name' => 'Actividades de consultoría de gestión', 'section' => 'M', 'division' => '70'],
            ['code' => '7110', 'name' => 'Actividades de arquitectura e ingeniería y otras actividades conexas de consultoría técnica', 'section' => 'M', 'division' => '71'],
            ['code' => '7310', 'name' => 'Publicidad', 'section' => 'M', 'division' => '73'],
            ['code' => '7490', 'name' => 'Otras actividades profesionales, científicas y técnicas no clasificadas previamente', 'section' => 'M', 'division' => '74'],

            // Sección N — Actividades de servicios administrativos
            ['code' => '7810', 'name' => 'Actividades de agencias de empleo', 'section' => 'N', 'division' => '78'],
            ['code' => '8020', 'name' => 'Actividades de servicios de sistemas de seguridad', 'section' => 'N', 'division' => '80'],
            ['code' => '8110', 'name' => 'Actividades combinadas de apoyo a instalaciones', 'section' => 'N', 'division' => '81'],
            ['code' => '8211', 'name' => 'Actividades combinadas de servicios administrativos de oficina', 'section' => 'N', 'division' => '82'],
            ['code' => '8219', 'name' => 'Fotocopiado, preparación de documentos y otras actividades especializadas de apoyo a oficina', 'section' => 'N', 'division' => '82'],
            ['code' => '8230', 'name' => 'Organización de convenciones y eventos comerciales', 'section' => 'N', 'division' => '82'],

            // Sección P — Educación
            ['code' => '8511', 'name' => 'Educación de la primera infancia', 'section' => 'P', 'division' => '85'],
            ['code' => '8512', 'name' => 'Educación preescolar', 'section' => 'P', 'division' => '85'],
            ['code' => '8521', 'name' => 'Educación básica primaria', 'section' => 'P', 'division' => '85'],
            ['code' => '8522', 'name' => 'Educación básica secundaria', 'section' => 'P', 'division' => '85'],
            ['code' => '8530', 'name' => 'Educación técnica y tecnológica', 'section' => 'P', 'division' => '85'],
            ['code' => '8541', 'name' => 'Educación universitaria', 'section' => 'P', 'division' => '85'],
            ['code' => '8549', 'name' => 'Otros tipos de educación n.c.p.', 'section' => 'P', 'division' => '85'],

            // Sección Q — Salud humana
            ['code' => '8610', 'name' => 'Actividades de hospitales y clínicas, con internación', 'section' => 'Q', 'division' => '86'],
            ['code' => '8621', 'name' => 'Actividades de la práctica médica, sin internación', 'section' => 'Q', 'division' => '86'],
            ['code' => '8690', 'name' => 'Otras actividades de atención de la salud humana', 'section' => 'Q', 'division' => '86'],

            // Sección S — Otras actividades de servicios
            ['code' => '9311', 'name' => 'Gestión de instalaciones deportivas', 'section' => 'S', 'division' => '93'],
            ['code' => '9601', 'name' => 'Lavado y limpieza, incluso la limpieza en seco, de productos textiles y de piel', 'section' => 'S', 'division' => '96'],
            ['code' => '9602', 'name' => 'Peluquería y otros tratamientos de belleza', 'section' => 'S', 'division' => '96'],
        ];

        foreach ($codes as $code) {
            DB::table('ciiu_codes')->updateOrInsert(
                ['code' => $code['code']],
                array_merge($code, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
