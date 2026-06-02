<?php

namespace Database\Populate;

use App\Models\ExamType;

class ExamTypesPopulate
{
    public static function populate(): void
    {
        $examTypes = [
            [
                'name' => 'Hemograma Completo',
                'description' => 'Analise geral do sangue para avaliar saude geral.',
                'ai_prompt_template' => 'Extraia os principais indicadores do hemograma em JSON.',
                'expected_json_schema' => null,
            ],
            [
                'name' => 'Glicemia em Jejum',
                'description' => 'Mede a glicose no sangue apos jejum.',
                'ai_prompt_template' => 'Extraia o valor da glicose em mg/dL e a data do exame.',
                'expected_json_schema' => null,
            ],
            [
                'name' => 'Colesterol e Triglicerides',
                'description' => 'Perfil lipidico do paciente.',
                'ai_prompt_template' => 'Extraia colesterol total, HDL, LDL e triglicerides em JSON.',
                'expected_json_schema' => null,
            ],
            [
                'name' => 'TSH e T4 Livre',
                'description' => 'Avalia funcao tireoidiana.',
                'ai_prompt_template' => 'Extraia TSH e T4 livre com unidades e data do exame.',
                'expected_json_schema' => null,
            ],
            [
                'name' => 'Creatinina e Ureia',
                'description' => 'Avalia funcao renal.',
                'ai_prompt_template' => 'Extraia creatinina e ureia com unidades e data do exame.',
                'expected_json_schema' => null,
            ],
        ];

        foreach ($examTypes as $data) {
            $examType = new ExamType($data);
            $examType->save();
        }

        echo "Exam types populated successfully.\n";
    }
}
