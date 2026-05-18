<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Lib\Validations;
use Core\Database\ActiveRecord\Model;

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
    protected static array $columns = ['patient_id', 'doctor_id', 'secretary_id', 'scheduled_at', 'status'];

    public function validates(): void
    {
        Validations::notEmpty('patient_id', $this);
        Validations::notEmpty('doctor_id', $this);
        Validations::notEmpty('scheduled_at', $this);

        Validations::uniqueness(['scheduled_at', 'doctor_id'], $this);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'fk_appointments_doctor_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'fk_appointments_patient_id');
    }

    public function secretary(): BelongsTo
    {
        return $this->belongsTo(Secretary::class, 'fk_appointments_secretary_id');
    }
}
