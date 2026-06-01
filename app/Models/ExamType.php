<?php

namespace App\Models;

use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $ai_prompt_template
 * @property string|null $expected_json_schema
 */
class ExamType extends Model
{
    protected static string $table = 'exam_types';

    protected static array $columns = [
        'name',
        'description',
        'ai_prompt_template',
        'expected_json_schema'
    ];

    public function validates(): void
    {
        Validations::notEmpty('name', $this, 'Nome nao pode ser vazio!');
        Validations::notEmpty('ai_prompt_template', $this, 'Prompt AI nao pode ser vazio!');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'exam_type_id');
    }
}
