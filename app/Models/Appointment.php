<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
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
        Validations::notEmpty('patient_id', $this, 'Paciente não pode ser vazio!');
        Validations::notEmpty('doctor_id', $this, 'Médico não pode ser vazio!');
        Validations::notEmpty('scheduled_at', $this, 'Data e hora não podem ser vazias!');
        Validations::notEmpty('status', $this, 'Status não pode ser vazio!');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function secretary(): BelongsTo
    {
        return $this->belongsTo(Secretary::class, 'secretary_id');
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

    /**
     * @param array<int, Appointment> $appointments
     * @return array<int, array<string, mixed>>
     */
    public static function withUsers(array $appointments): array
    {
        $items = [];
        foreach ($appointments as $appointment) {
            /** @var ?Patient $patient */
            $patient = $appointment->patient()->get();
            /** @var ?Doctor $doctor */
            $doctor = $appointment->doctor()->get();
            /** @var ?Secretary $secretary */
            $secretary = $appointment->secretary()->get();

            $items[] = [
                'appointment' => $appointment,
                'patient' => $patient,
                'doctor' => $doctor,
                'secretary' => $secretary,
                'patientUser' => $patient ? $patient->user()->get() : null,
                'doctorUser' => $doctor ? $doctor->user()->get() : null,
                'secretaryUser' => $secretary ? $secretary->user()->get() : null,
            ];
        }

        return $items;
    }
}
