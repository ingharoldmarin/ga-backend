<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurriculumGridSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Helper: get ID by name from a table
        $id = fn(string $table, string $name) =>
            DB::table($table)->where('name', $name)->value('id');

        // IDs de grados
        $gTrans  = $id('grade', 'Transición');
        $g1      = $id('grade', 'Primero');
        $g2      = $id('grade', 'Segundo');
        $g3      = $id('grade', 'Tercero');
        $g5      = $id('grade', 'Quinto');
        $g6      = $id('grade', 'Sexto');
        $g9      = $id('grade', 'Noveno');
        $g10     = $id('grade', 'Décimo');
        $g11     = $id('grade', 'Undécimo');

        // IDs de materias
        $sMatem  = $id('subject', 'Matemáticas');
        $sLeng   = $id('subject', 'Lengua Castellana');
        $sCiNat  = $id('subject', 'Ciencias Naturales');
        $sCiSoc  = $id('subject', 'Ciencias Sociales');
        $sIngles = $id('subject', 'Inglés');

        // IDs de topics
        $tNum    = $id('topic', 'Números y Operaciones');
        $tGeo    = $id('topic', 'Pensamiento Geométrico');
        $tMet    = $id('topic', 'Pensamiento Métrico');
        $tVar    = $id('topic', 'Pensamiento Variacional');
        $tProb   = $id('topic', 'Pensamiento Aleatorio');
        $tCompr  = $id('topic', 'Comprensión Lectora');
        $tProd   = $id('topic', 'Producción Textual');
        $tOral   = $id('topic', 'Comunicación Oral');
        $tLit    = $id('topic', 'Literatura');
        $tVivos  = $id('topic', 'Los Seres Vivos');
        $tMat    = $id('topic', 'La Materia y sus Cambios');
        $tEco    = $id('topic', 'Ecosistemas y Ambiente');
        $tHist   = $id('topic', 'Historia de Colombia');
        $tGeog   = $id('topic', 'Geografía y Territorio');
        $tDem    = $id('topic', 'Democracia y Ciudadanía');
        $tListen = $id('topic', 'Listening & Speaking');
        $tRead   = $id('topic', 'Reading & Writing');
        $tGram   = $id('topic', 'Grammar & Vocabulary');

        // IDs de components
        $cNum    = $id('component', 'Componente Numérico-Variacional');
        $cGeo    = $id('component', 'Componente Geométrico-Métrico');
        $cAleat  = $id('component', 'Componente Aleatorio');
        $cLect   = $id('component', 'Comunicativa-Lectora');
        $cEscr   = $id('component', 'Comunicativa-Escritora');
        $cGram   = $id('component', 'Gramática y Vocabulario');
        $cVivo   = $id('component', 'Entorno Vivo');
        $cFis    = $id('component', 'Entorno Físico');
        $cCTS    = $id('component', 'Ciencia, Tecnología y Sociedad');
        $cHistC  = $id('component', 'Historia y Cultura');
        $cEspG   = $id('component', 'Espacio Geográfico');
        $cEticP  = $id('component', 'Relaciones Ético-Políticas');
        $cOral   = $id('component', 'Oral Communication');
        $cWrite  = $id('component', 'Written Communication');
        $cLangU  = $id('component', 'Language Use');

        // IDs de standards
        $stUso   = $id('standard', 'Uso comprensivo del conocimiento científico');
        $stExp   = $id('standard', 'Explicación de fenómenos');
        $stInd   = $id('standard', 'Indagación');
        $stInt   = $id('standard', 'Interpretación y representación');
        $stFRP   = $id('standard', 'Formulación y resolución de problemas');
        $stCom   = $id('standard', 'Comunicación');
        $stRaz   = $id('standard', 'Razonamiento y argumentación');
        $stMod   = $id('standard', 'Modelación');
        $stProd  = $id('standard', 'Producción textual');
        $stCompT = $id('standard', 'Comprensión e interpretación textual');
        $stLit   = $id('standard', 'Literatura');

        // IDs de competences (buscar por LIKE en description)
        $cid = fn(string $kw) => DB::table('competence')
            ->where('description', 'LIKE', $kw . '%')
            ->value('id');
        $kInterp = $cid('Interpretativa');
        $kArg    = $cid('Argumentativa');
        $kProp   = $cid('Propositiva');
        $kCom    = $cid('Comunicativa');
        $kCien   = $cid('Científica');
        $kCiud   = $cid('Ciudadana');
        $kMat    = $cid('Matemática');
        $kLect   = $cid('Lectora');

        // IDs de affirmations (DBA)
        $aM1 = DB::table('affirmation_dna_dba')->where('name', 'DBA M-1')->value('id');
        $aM2 = DB::table('affirmation_dna_dba')->where('name', 'DBA M-2')->value('id');
        $aM3 = DB::table('affirmation_dna_dba')->where('name', 'DBA M-3')->value('id');
        $aM4 = DB::table('affirmation_dna_dba')->where('name', 'DBA M-4')->value('id');
        $aM5 = DB::table('affirmation_dna_dba')->where('name', 'DBA M-5')->value('id');
        $aL1 = DB::table('affirmation_dna_dba')->where('name', 'DBA L-1')->value('id');
        $aL2 = DB::table('affirmation_dna_dba')->where('name', 'DBA L-2')->value('id');
        $aL3 = DB::table('affirmation_dna_dba')->where('name', 'DBA L-3')->value('id');
        $aL4 = DB::table('affirmation_dna_dba')->where('name', 'DBA L-4')->value('id');
        $aCN1= DB::table('affirmation_dna_dba')->where('name', 'DBA CN-1')->value('id');
        $aCN2= DB::table('affirmation_dna_dba')->where('name', 'DBA CN-2')->value('id');
        $aCN3= DB::table('affirmation_dna_dba')->where('name', 'DBA CN-3')->value('id');
        $aCS1= DB::table('affirmation_dna_dba')->where('name', 'DBA CS-1')->value('id');
        $aCS2= DB::table('affirmation_dna_dba')->where('name', 'DBA CS-2')->value('id');
        $aCS3= DB::table('affirmation_dna_dba')->where('name', 'DBA CS-3')->value('id');

        // IDs de evidences
        $eM1 = DB::table('evidence_dna_dba')->where('name', 'Ev M-1')->value('id');
        $eM2 = DB::table('evidence_dna_dba')->where('name', 'Ev M-2')->value('id');
        $eM3 = DB::table('evidence_dna_dba')->where('name', 'Ev M-3')->value('id');
        $eM4 = DB::table('evidence_dna_dba')->where('name', 'Ev M-4')->value('id');
        $eM5 = DB::table('evidence_dna_dba')->where('name', 'Ev M-5')->value('id');
        $eL1 = DB::table('evidence_dna_dba')->where('name', 'Ev L-1')->value('id');
        $eL2 = DB::table('evidence_dna_dba')->where('name', 'Ev L-2')->value('id');
        $eL3 = DB::table('evidence_dna_dba')->where('name', 'Ev L-3')->value('id');
        $eCN1= DB::table('evidence_dna_dba')->where('name', 'Ev CN-1')->value('id');
        $eCN2= DB::table('evidence_dna_dba')->where('name', 'Ev CN-2')->value('id');
        $eCN3= DB::table('evidence_dna_dba')->where('name', 'Ev CN-3')->value('id');
        $eCS1= DB::table('evidence_dna_dba')->where('name', 'Ev CS-1')->value('id');
        $eCS2= DB::table('evidence_dna_dba')->where('name', 'Ev CS-2')->value('id');
        $eIN1= DB::table('evidence_dna_dba')->where('name', 'Ev IN-1')->value('id');
        $eIN2= DB::table('evidence_dna_dba')->where('name', 'Ev IN-2')->value('id');

        // ── Definición de mallas curriculares ─────────────────────────────
        // Cada entrada: (grade, subject, period, topics[], components[], standards[], competences[], affirmations[], evidences[])
        $grids = [
            // MATEMÁTICAS - Primero P1
            [$g1, $sMatem, 1, [$tNum], [$cNum], [$stFRP, $stCom], [$kMat, $kInterp], [$aM1, $aM4], [$eM1, $eM2], 'Números naturales hasta 100 y operaciones básicas', 1],
            [$g1, $sMatem, 2, [$tGeo], [$cGeo], [$stInt, $stRaz], [$kMat, $kProp],   [$aM2],       [$eM4],       'Figuras planas y sólidos geométricos', 2],
            [$g1, $sMatem, 3, [$tMet], [$cGeo], [$stInt],          [$kMat],           [$aM1],       [$eM5],       'Medidas de longitud y tiempo', 3],
            [$g1, $sMatem, 4, [$tProb],[$cAleat],[$stInd],         [$kInterp],        [$aM5],       [$eM2],       'Introducción a la estadística y probabilidad', 4],
            // MATEMÁTICAS - Tercero P1
            [$g3, $sMatem, 1, [$tNum,$tVar], [$cNum], [$stFRP,$stMod], [$kMat,$kArg], [$aM3,$aM4], [$eM1,$eM2,$eM3], 'Fracciones y operaciones con números naturales', 1],
            [$g3, $sMatem, 2, [$tGeo],[$cGeo], [$stInt,$stRaz],  [$kMat,$kProp],    [$aM2],      [$eM4],           'Propiedades de figuras geométricas', 2],
            // LENGUA CASTELLANA - Segundo P1
            [$g2, $sLeng,  1, [$tCompr,$tOral],[$cLect,$cGram],[$stCompT,$stCom], [$kLect,$kCom], [$aL1,$aL3], [$eL1], 'Comprensión de textos narrativos cortos', 1],
            [$g2, $sLeng,  2, [$tProd],[$cEscr],[$stProd],        [$kCom,$kProp], [$aL2], [$eL2], 'Producción de textos descriptivos', 2],
            [$g2, $sLeng,  3, [$tLit], [$cLect],[$stLit],         [$kLect],       [$aL4], [$eL3], 'Literatura infantil y fábulas', 3],
            // LENGUA CASTELLANA - Sexto P1
            [$g6, $sLeng,  1, [$tCompr,$tLit],[$cLect],[$stCompT,$stLit],[$kLect,$kInterp],[$aL1,$aL4],[$eL1,$eL3],'Textos narrativos: cuento y novela corta', 1],
            [$g6, $sLeng,  2, [$tProd,$tOral],[$cEscr,$cGram],[$stProd,$stCom],[$kCom,$kArg],[$aL2,$aL3],[$eL2],'Producción de textos expositivos y argumentativos', 2],
            // CIENCIAS NATURALES - Quinto P1
            [$g5, $sCiNat, 1, [$tVivos],[$cVivo],[$stUso,$stInd],[$kCien,$kInterp],[$aCN1],[$eCN1,$eCN3],'Clasificación de los seres vivos y ecosistemas', 1],
            [$g5, $sCiNat, 2, [$tMat], [$cFis], [$stExp,$stInd], [$kCien,$kProp],  [$aCN2],[$eCN2],      'Propiedades de la materia y cambios', 2],
            [$g5, $sCiNat, 3, [$tEco], [$cCTS], [$stInd,$stExp], [$kCien,$kCiud],  [$aCN3],[$eCN3],      'Ecosistemas locales y cuidado del ambiente', 3],
            // CIENCIAS SOCIALES - Noveno P1
            [$g9, $sCiSoc, 1, [$tHist],[$cHistC],[$stUso,$stRaz],[$kArg,$kInterp],[$aCS1],[$eCS1],'Historia de Colombia: colonia y república', 1],
            [$g9, $sCiSoc, 2, [$tGeog],[$cEspG], [$stInt,$stInd],[$kInterp],      [$aCS3],[$eCS2],'Geografía colombiana: regiones naturales', 2],
            [$g9, $sCiSoc, 3, [$tDem], [$cEticP],[$stUso,$stExp],[$kCiud,$kArg],  [$aCS2],[$eCS1,$eCS2],'Democracia, derechos humanos y participación', 3],
            // INGLÉS - Décimo P1
            [$g10, $sIngles, 1, [$tListen],[$cOral], [$stCom], [$kCom],        [null], [$eIN1], 'Listening and speaking: daily routines and plans', 1],
            [$g10, $sIngles, 2, [$tRead],  [$cWrite],[$stProd], [$kCom,$kLect], [null], [$eIN2], 'Reading and writing: formal and informal texts', 2],
            [$g10, $sIngles, 3, [$tGram],  [$cLangU],[$stInt],  [$kInterp],     [null], [$eIN1,$eIN2], 'Grammar: tenses, conditionals and modals', 3],
        ];

        $count = 0;
        foreach ($grids as [$gradeId, $subjectId, $period, $topicIds, $compIds, $stdIds, $compIds2, $affIds, $evIds, $desc, $order]) {
            // Check if this specific evidence is already in a grid for this grade+subject+period
            $firstEvId = $evIds[0] ?? null;
            if ($firstEvId) {
                $exists = DB::table('curriculum_grid as cg')
                    ->join('curriculum_grid_evidence as cge', 'cge.curriculum_grid_id', '=', 'cg.id')
                    ->where('cg.grade_id',   $gradeId)
                    ->where('cg.subject_id', $subjectId)
                    ->where('cg.period',     $period)
                    ->where('cge.evidence_id', $firstEvId)
                    ->whereNull('cg.deleted_at')
                    ->exists();
                if ($exists) continue;
            }

            $gridId = DB::table('curriculum_grid')->insertGetId([
                'grade_id'   => $gradeId,
                'subject_id' => $subjectId,
                'period'     => $period,
                'description'=> $desc,
                'order'      => $order,
                'active'     => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach (array_unique(array_filter($topicIds)) as $tid) {
                DB::table('curriculum_grid_topic')->insertOrIgnore(['curriculum_grid_id' => $gridId, 'topic_id' => $tid, 'created_at' => $now, 'updated_at' => $now]);
            }
            foreach (array_unique(array_filter($compIds)) as $cid) {
                DB::table('curriculum_grid_component')->insertOrIgnore(['curriculum_grid_id' => $gridId, 'component_id' => $cid, 'created_at' => $now, 'updated_at' => $now]);
            }
            foreach (array_unique(array_filter($stdIds)) as $sid) {
                DB::table('curriculum_grid_standard')->insertOrIgnore(['curriculum_grid_id' => $gridId, 'standard_id' => $sid, 'created_at' => $now, 'updated_at' => $now]);
            }
            foreach (array_unique(array_filter($compIds2)) as $kid) {
                DB::table('curriculum_grid_competence')->insertOrIgnore(['curriculum_grid_id' => $gridId, 'competence_id' => $kid, 'created_at' => $now, 'updated_at' => $now]);
            }
            foreach (array_unique(array_filter($affIds)) as $aid) {
                DB::table('curriculum_grid_affirmation')->insertOrIgnore(['curriculum_grid_id' => $gridId, 'affirmation_id' => $aid, 'created_at' => $now, 'updated_at' => $now]);
            }
            foreach (array_unique(array_filter($evIds)) as $eid) {
                DB::table('curriculum_grid_evidence')->insertOrIgnore(['curriculum_grid_id' => $gridId, 'evidence_id' => $eid, 'created_at' => $now, 'updated_at' => $now]);
            }

            $count++;
        }

        $this->command->info("✓ {$count} mallas curriculares creadas con sus relaciones.");
    }
}
