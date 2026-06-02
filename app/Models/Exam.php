<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property int $patient_id
 * @property int|null $appointment_id
 * @property int $upload_by
 * @property int $exam_type_id
 * @property string $exam_date
 * @property bool $is_verified
 * @property int|null $is_verified_by
 * @property string $source
 * @property string $file_path
 * @property string $ai_status
 * @property string|null $extracted_data_json
 */

class Exam extends Model
{
    protected static string $table = 'exams';

    protected static array $columns = [
        'patient_id',
        'appointment_id',
        'upload_by',
        'exam_type_id',
        'exam_date',
        'is_verified',
        'is_verified_by',
        'source',
        'file_path',
        'ai_status',
        'extracted_data_json'
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const SOURCE_UPLOAD = 'upload';
    public const SOURCE_WHATSAPP = 'whatsapp';

    public function validates(): void
    {
        Validations::notEmpty('patient_id', $this, 'Paciente não pode ser vazio!');
        //Validations::notEmpty('appointment_id', $this, 'Consulta não pode ser vazia!');
        Validations::notEmpty('exam_type_id', $this, 'Tipo de exame não pode ser vazio!');
        Validations::notEmpty('exam_date', $this, 'Erro de sistema: A data do exame não foi registrada.');
        Validations::notEmpty('upload_by', $this, 'Falha de segurança: Usuário responsável pelo upload não identificado.');
        Validations::notEmpty('file_path', $this, 'Falha no processamento: O arquivo do exame não foi salvo no servidor.');
        Validations::notEmpty('ai_status', $this, 'Erro interno: O status de processamento da IA não foi definido.');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upload_by');
    }

    public function examType(): BelongsTo
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'is_verified_by');
    }

    /**
    * @return array<Exam>
    */
    public static function findByPatientId(int $patientId): array
    {
        return self::where(['patient_id' => $patientId]);
    }
}
