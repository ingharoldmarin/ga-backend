<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurriculumItemsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── TOPICS (Unidades Temáticas / Ejes Temáticos) ──────────────────
        $topics = [
            // Matemáticas
            ['name' => 'Números y Operaciones',       'description' => 'Comprensión y uso de los sistemas numéricos y sus operaciones básicas'],
            ['name' => 'Pensamiento Geométrico',       'description' => 'Exploración y análisis de formas, figuras y estructuras del espacio'],
            ['name' => 'Pensamiento Métrico',          'description' => 'Uso de magnitudes, medidas y sistemas de medición'],
            ['name' => 'Pensamiento Variacional',      'description' => 'Relaciones de cambio, funciones y álgebra'],
            ['name' => 'Pensamiento Aleatorio',        'description' => 'Estadística, probabilidad y manejo de datos'],
            // Lenguaje
            ['name' => 'Comprensión Lectora',         'description' => 'Estrategias para comprender textos de diferentes tipologías'],
            ['name' => 'Producción Textual',           'description' => 'Planificación, escritura y revisión de textos con intención comunicativa'],
            ['name' => 'Comunicación Oral',            'description' => 'Expresión y escucha activa en contextos comunicativos'],
            ['name' => 'Literatura',                   'description' => 'Lectura y análisis de obras literarias de diferentes épocas y géneros'],
            // Ciencias Naturales
            ['name' => 'Los Seres Vivos',             'description' => 'Características, clasificación y relaciones de los organismos vivos'],
            ['name' => 'La Materia y sus Cambios',    'description' => 'Propiedades, estados y transformaciones de la materia'],
            ['name' => 'El Universo y la Tierra',     'description' => 'Estructura del universo, sistema solar y dinámica terrestre'],
            ['name' => 'Ecosistemas y Ambiente',      'description' => 'Relaciones entre los seres vivos y su entorno natural'],
            // Ciencias Sociales
            ['name' => 'Historia de Colombia',        'description' => 'Procesos históricos que configuraron la nación colombiana'],
            ['name' => 'Geografía y Territorio',      'description' => 'Características físicas y humanas del territorio colombiano y mundial'],
            ['name' => 'Democracia y Ciudadanía',     'description' => 'Principios democráticos, derechos y deberes del ciudadano'],
            // Inglés
            ['name' => 'Listening & Speaking',        'description' => 'Comprensión oral y expresión verbal en inglés'],
            ['name' => 'Reading & Writing',           'description' => 'Lectura comprensiva y producción escrita en inglés'],
            ['name' => 'Grammar & Vocabulary',        'description' => 'Estructuras gramaticales y enriquecimiento del vocabulario'],
        ];

        foreach ($topics as $t) {
            DB::table('topic')->insertOrIgnore(array_merge($t, ['created_at' => $now, 'updated_at' => $now]));
        }

        // ── COMPONENTS (Componentes del Área) ──────────────────────────────
        $components = [
            // Matemáticas
            ['name' => 'Componente Numérico-Variacional',   'description' => 'Aspectos relacionados con los números, las operaciones y el cambio'],
            ['name' => 'Componente Geométrico-Métrico',     'description' => 'Aspectos espaciales, geométricos y de medición'],
            ['name' => 'Componente Aleatorio',              'description' => 'Aspectos relacionados con el azar, la estadística y los datos'],
            // Lenguaje
            ['name' => 'Comunicativa-Lectora',              'description' => 'Comprensión e interpretación de textos en diferentes formatos'],
            ['name' => 'Comunicativa-Escritora',            'description' => 'Producción de textos con sentido y propósito comunicativo'],
            ['name' => 'Gramática y Vocabulario',           'description' => 'Conocimiento y uso de las convenciones del lenguaje'],
            // Ciencias Naturales
            ['name' => 'Entorno Vivo',                      'description' => 'Estudio de los organismos y sus interacciones con el ambiente'],
            ['name' => 'Entorno Físico',                    'description' => 'Propiedades físicas y químicas de la materia y la energía'],
            ['name' => 'Ciencia, Tecnología y Sociedad',   'description' => 'Relación entre el conocimiento científico y el contexto social'],
            // Ciencias Sociales
            ['name' => 'Historia y Cultura',                'description' => 'Comprensión de los procesos históricos y manifestaciones culturales'],
            ['name' => 'Espacio Geográfico',                'description' => 'Análisis del territorio y sus transformaciones'],
            ['name' => 'Relaciones Ético-Políticas',        'description' => 'Convivencia, democracia y derechos humanos'],
            // Inglés
            ['name' => 'Oral Communication',                'description' => 'Habilidades de escucha y producción oral en inglés'],
            ['name' => 'Written Communication',             'description' => 'Habilidades de lectura y escritura en inglés'],
            ['name' => 'Language Use',                      'description' => 'Uso contextual de estructuras y vocabulario del inglés'],
        ];

        foreach ($components as $c) {
            DB::table('component')->insertOrIgnore(array_merge($c, ['created_at' => $now, 'updated_at' => $now]));
        }

        // ── STANDARDS (Estándares de Competencia) ──────────────────────────
        $standards = [
            ['name' => 'Uso comprensivo del conocimiento científico',      'description' => 'Capacidad para entender y aplicar conceptos, teorías y modelos de las ciencias'],
            ['name' => 'Explicación de fenómenos',                         'description' => 'Capacidad para construir explicaciones sobre fenómenos naturales y sociales'],
            ['name' => 'Indagación',                                       'description' => 'Capacidad para plantear preguntas y procedimientos para buscar, organizar e interpretar información'],
            ['name' => 'Interpretación y representación',                  'description' => 'Capacidad para interpretar y producir representaciones gráficas, simbólicas y formales'],
            ['name' => 'Formulación y resolución de problemas',            'description' => 'Capacidad para identificar y aplicar estrategias para resolver situaciones problemáticas'],
            ['name' => 'Comunicación',                                     'description' => 'Capacidad para expresar ideas, procedimientos y resultados en lenguaje matemático o científico'],
            ['name' => 'Razonamiento y argumentación',                     'description' => 'Capacidad para construir y validar argumentos lógicos y críticos'],
            ['name' => 'Modelación',                                       'description' => 'Capacidad para identificar patrones y construir modelos que representen situaciones reales'],
            ['name' => 'Producción textual',                               'description' => 'Capacidad para producir textos que respondan a necesidades comunicativas específicas'],
            ['name' => 'Comprensión e interpretación textual',             'description' => 'Capacidad para comprender textos de distintas tipologías con distintos propósitos lectores'],
            ['name' => 'Literatura',                                       'description' => 'Capacidad para comprender la tradición oral, literaria y artística en su contexto'],
            ['name' => 'Medios de comunicación y otros sistemas simbólicos','description' => 'Capacidad para comprender e interpretar medios de comunicación masiva'],
        ];

        foreach ($standards as $s) {
            DB::table('standard')->insertOrIgnore(array_merge($s, ['created_at' => $now, 'updated_at' => $now]));
        }

        // ── TIPOS DE COMPETENCIA ──────────────────────────────────────────
        $tipoNames = [
            'Básica',
            'Específica',
            'Transversal',
            'Ciudadana',
            'Comunicativa',
            'Científica',
        ];

        foreach ($tipoNames as $tName) {
            DB::table('tipe_competence')->insertOrIgnore([
                'name' => $tName, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $tipoBasica   = DB::table('tipe_competence')->where('name', 'Básica')->value('id');
        $tipoEspec    = DB::table('tipe_competence')->where('name', 'Específica')->value('id');
        $tipoTrans    = DB::table('tipe_competence')->where('name', 'Transversal')->value('id');
        $tipoCiudad   = DB::table('tipe_competence')->where('name', 'Ciudadana')->value('id');
        $tipoCom      = DB::table('tipe_competence')->where('name', 'Comunicativa')->value('id');
        $tipoCien     = DB::table('tipe_competence')->where('name', 'Científica')->value('id');

        // ── COMPETENCES (solo campo 'description' + tipe_competence_id) ──
        $competences = [
            ['description' => 'Interpretativa: capacidad para comprender el sentido de un texto, problema o situación',  'tipe_competence_id' => $tipoBasica],
            ['description' => 'Argumentativa: capacidad para justificar o sustentar afirmaciones con razones y evidencias', 'tipe_competence_id' => $tipoBasica],
            ['description' => 'Propositiva: capacidad para generar hipótesis, soluciones y alternativas creativas',        'tipe_competence_id' => $tipoBasica],
            ['description' => 'Comunicativa: capacidad para expresar y comprender mensajes en distintos contextos',        'tipe_competence_id' => $tipoCom],
            ['description' => 'Científica: capacidad para observar, formular preguntas, experimentar y concluir',          'tipe_competence_id' => $tipoCien],
            ['description' => 'Ciudadana: capacidad para convivir, participar y respetar los derechos propios y ajenos',   'tipe_competence_id' => $tipoCiudad],
            ['description' => 'Matemática: capacidad para razonar, modelar y resolver situaciones con herramientas matemáticas', 'tipe_competence_id' => $tipoEspec],
            ['description' => 'Lectora: capacidad para extraer, inferir y reflexionar sobre el contenido de textos escritos', 'tipe_competence_id' => $tipoCom],
            ['description' => 'Practica operaciones básicas de suma y resta con números naturales hasta 1000 en situaciones cotidianas', 'tipe_competence_id' => $tipoEspec],
            ['description' => 'Identifica y describe figuras planas y sólidos geométricos presentes en el entorno',        'tipe_competence_id' => $tipoEspec],
            ['description' => 'Resuelve y formula situaciones problémicas que involucran operaciones básicas con números naturales', 'tipe_competence_id' => $tipoEspec],
            ['description' => 'Comprende textos narrativos y descriptivos de diversa procedencia, respondiendo preguntas inferenciales', 'tipe_competence_id' => $tipoCom],
            ['description' => 'Produce textos narrativos sencillos atendiendo a la coherencia y cohesión del discurso',    'tipe_competence_id' => $tipoCom],
            ['description' => 'Reconoce relaciones entre organismos vivos y su entorno en ecosistemas locales',            'tipe_competence_id' => $tipoCien],
            ['description' => 'Identifica cambios físicos y químicos de la materia en situaciones del diario vivir',      'tipe_competence_id' => $tipoCien],
        ];

        foreach ($competences as $c) {
            DB::table('competence')->insertOrIgnore(array_merge($c, ['created_at' => $now, 'updated_at' => $now]));
        }

        // ── AFFIRMATIONS (DNA / DBA) ───────────────────────────────────────
        $affirmations = [
            // Matemáticas
            ['name' => 'DBA M-1',  'description' => 'Agrupa objetos según características y los cuenta usando números hasta el 20'],
            ['name' => 'DBA M-2',  'description' => 'Describe trayectos y posiciones en el espacio usando términos como arriba, abajo, delante, detrás'],
            ['name' => 'DBA M-3',  'description' => 'Comprende y usa fracciones en contextos de medición y reparto equitativo'],
            ['name' => 'DBA M-4',  'description' => 'Resuelve situaciones aditivas y multiplicativas con números naturales'],
            ['name' => 'DBA M-5',  'description' => 'Representa y analiza datos en tablas y diagramas de barras y circulares'],
            // Lenguaje
            ['name' => 'DBA L-1',  'description' => 'Comprende textos literarios (cuentos, fábulas, mitos) e identifica personajes, conflicto y moraleja'],
            ['name' => 'DBA L-2',  'description' => 'Escribe textos descriptivos y narrativos respetando estructura, coherencia y ortografía básica'],
            ['name' => 'DBA L-3',  'description' => 'Interpreta la información de textos informativos y reconoce su intención comunicativa'],
            ['name' => 'DBA L-4',  'description' => 'Diferencia tipos de textos por sus características formales y propósitos comunicativos'],
            // Ciencias Naturales
            ['name' => 'DBA CN-1', 'description' => 'Comprende que los seres vivos tienen ciclos de vida y que los ecosistemas cambian con el tiempo'],
            ['name' => 'DBA CN-2', 'description' => 'Identifica propiedades de los materiales y los clasifica según criterios físicos y químicos'],
            ['name' => 'DBA CN-3', 'description' => 'Reconoce el impacto de las actividades humanas sobre los ecosistemas y propone acciones de cuidado'],
            // Ciencias Sociales
            ['name' => 'DBA CS-1', 'description' => 'Reconoce que Colombia tiene una historia de diversidad cultural, étnica y regional'],
            ['name' => 'DBA CS-2', 'description' => 'Comprende que los derechos y deberes son base de la convivencia democrática'],
            ['name' => 'DBA CS-3', 'description' => 'Describe las características físicas y humanas de las regiones de Colombia y su influencia en la vida de sus habitantes'],
        ];

        foreach ($affirmations as $a) {
            DB::table('affirmation_dna_dba')->insertOrIgnore(array_merge($a, ['created_at' => $now, 'updated_at' => $now]));
        }

        // ── EVIDENCES (Evidencias de Aprendizaje) ─────────────────────────
        $evidences = [
            // Matemáticas
            ['name' => 'Ev M-1', 'description' => 'Cuenta, lee y escribe números naturales hasta el 1 000 000 en diferentes contextos'],
            ['name' => 'Ev M-2', 'description' => 'Resuelve y formula situaciones de suma, resta, multiplicación y división con números naturales'],
            ['name' => 'Ev M-3', 'description' => 'Usa fracciones para representar partes de un todo o colección y las compara'],
            ['name' => 'Ev M-4', 'description' => 'Construye, describe y clasifica figuras planas y sólidos según sus características'],
            ['name' => 'Ev M-5', 'description' => 'Realiza mediciones de longitud, masa y capacidad usando unidades del sistema métrico'],
            // Lenguaje
            ['name' => 'Ev L-1', 'description' => 'Comprende textos de diversas tipologías respondiendo preguntas literales e inferenciales'],
            ['name' => 'Ev L-2', 'description' => 'Produce textos escritos con propósito claro, respetando normas de puntuación y ortografía'],
            ['name' => 'Ev L-3', 'description' => 'Identifica y usa recursos literarios (rima, metáfora, personificación) en textos propios y ajenos'],
            // Ciencias Naturales
            ['name' => 'Ev CN-1', 'description' => 'Clasifica seres vivos según criterios observables y explica sus funciones vitales'],
            ['name' => 'Ev CN-2', 'description' => 'Realiza experimentos sencillos para explorar propiedades de materiales y registra resultados'],
            ['name' => 'Ev CN-3', 'description' => 'Describe la cadena alimentaria de un ecosistema local e identifica sus componentes'],
            // Ciencias Sociales
            ['name' => 'Ev CS-1', 'description' => 'Elabora líneas de tiempo con eventos relevantes de la historia de Colombia'],
            ['name' => 'Ev CS-2', 'description' => 'Interpreta mapas temáticos sobre regiones naturales y localiza accidentes geográficos de Colombia'],
            ['name' => 'Ev CS-3', 'description' => 'Propone acciones de participación democrática en el aula y la comunidad'],
            // Inglés
            ['name' => 'Ev IN-1', 'description' => 'Comprende y produce oralmente frases y oraciones simples sobre temas cotidianos en inglés'],
            ['name' => 'Ev IN-2', 'description' => 'Lee textos cortos en inglés y responde preguntas de comprensión literal'],
        ];

        foreach ($evidences as $e) {
            DB::table('evidence_dna_dba')->insertOrIgnore(array_merge($e, ['created_at' => $now, 'updated_at' => $now]));
        }

        $this->command->info('✓ Elementos curriculares creados:');
        $this->command->info('  - ' . count($tipoNames)   . ' tipos de competencia');
        $this->command->info('  - ' . count($topics)      . ' unidades temáticas (topics)');
        $this->command->info('  - ' . count($components)  . ' componentes');
        $this->command->info('  - ' . count($standards)   . ' estándares');
        $this->command->info('  - ' . count($competences) . ' competencias');
        $this->command->info('  - ' . count($affirmations). ' afirmaciones DBA/DNA');
        $this->command->info('  - ' . count($evidences)   . ' evidencias de aprendizaje');
    }
}
