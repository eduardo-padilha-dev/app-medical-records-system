<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property int $user_id
 * @property string $birth_date
 * @property string $phone
 */
class Patient extends Model
{
    protected static string $table = 'patients';
    protected static array $columns = ['user_id', 'birth_date', 'phone'];

    public function validates(): void
    {
        Validations::notEmpty('user_id', $this);
        Validations::notEmpty('birth_date', $this);
        Validations::notEmpty('phone', $this);
        Validations::uniqueness('user_id', $this);
    }

    public static function findByUserId(int $userId): ?Patient
    {
        return self::findBy(['user_id' => $userId]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    /**
     * @return array<int, array{patient: Patient, user: ?object}>
     */
    public static function allWithUser(): array
    {
        $items = [];
        foreach (self::all() as $patient) {
            $items[] = [
                'patient' => $patient,
                'user' => $patient->user()->get(),
            ];
        }

        return $items;
    }
}
