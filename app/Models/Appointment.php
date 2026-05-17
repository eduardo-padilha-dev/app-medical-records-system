<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property int $patient_id
 * @property int $doctor_id
 * @property int $secretary_id
 * @property string $scheduled_at
 * @property string $status
 */
class Appointment extends Model
{
    protected static string $table = 'appointments';

    protected static array $columns = [
        'patient_id',
        'doctor_id',
        'secretary_id',
        'scheduled_at',
        'status',
    ];

    public function validates(): void
    {
        Validations::notEmpty('patient_id', $this);
        Validations::notEmpty('doctor_id', $this);
        Validations::notEmpty('secretary_id', $this);
        Validations::notEmpty('scheduled_at', $this);
        Validations::notEmpty('status', $this);
    }

    public function patient(): ?Patient
    {
        return Patient::findById((int) $this->patient_id);
    }

    public function doctor(): ?Doctor
    {
        return Doctor::findById((int) $this->doctor_id);
    }

    public function secretary(): ?Secretary
    {
        return Secretary::findById((int) $this->secretary_id);
    }

    /**
      * @return array<Appointment>
    */
    public static function findByDoctorId(int $doctorId): array
    {
        return self::where(['doctor_id' => $doctorId]);
    }

    /**
      * @return array<Appointment>
    */
    public static function findByPatientId(int $patientId): array
    {
        return self::where(['patient_id' => $patientId]);
    }


    /**
      * @return array<Appointment>
    */
    public static function findBySecretaryId(int $secretaryId): array
    {
        return self::where(['secretary_id' => $secretaryId]);
    }
}
