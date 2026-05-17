<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property int $patient_id
 * @property int $doctor_id
 * @property int|null $appointment_id
 * @property string $record_date
 * @property string $diagnosis
 * @property string|null $prescription
 * @property string|null $notes
 */
class MedicalRecord extends Model
{
    protected static string $table   = 'medical_records';
    protected static array $columns = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'record_date',
        'diagnosis',
        'prescription',
        'notes',
    ];

    public function validates(): void
    {
        Validations::notEmpty('patient_id', $this, 'Paciente não pode ser vazio!');
        Validations::notEmpty('doctor_id', $this, 'Médico não pode ser vazio!');
        Validations::notEmpty('record_date', $this, 'Data do prontuário não pode ser vazia!');
        Validations::notEmpty('diagnosis', $this, 'Diagnóstico não pode ser vazio!');
    }

    public function patient(): ?Patient
    {
        return Patient::findById((int) $this->patient_id);
    }

    public function doctor(): ?Doctor
    {
        return Doctor::findById((int) $this->doctor_id);
    }

    /**
      * @return array<MedicalRecord>
    */
    public static function findByPatientId(int $patientId): array
    {
        return self::where(['patient_id' => $patientId]);
    }

    /**
      * @return array<MedicalRecord>
    */
    public static function findByDoctorId(int $doctorId): array
    {
        return self::where(['doctor_id' => $doctorId]);
    }
}
