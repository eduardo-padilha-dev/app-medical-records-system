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

        Validations::notEmpty('patient_id', $this);
        Validations::notEmpty('doctor_id', $this);
        Validations::notEmpty('record_date', $this);
        Validations::notEmpty('diagnosis', $this);
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
