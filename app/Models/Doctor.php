<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property int $user_id
 * @property string $license_number
 * @property string $specialty
 */
class Doctor extends Model
{
    protected static string $table = 'doctors';
    protected static array $columns = ['user_id', 'license_number', 'specialty'];

    public function validates(): void
    {
        Validations::notEmpty('user_id', $this);
        Validations::notEmpty('license_number', $this);
        Validations::notEmpty('specialty', $this);
        Validations::uniqueness('user_id', $this);
        Validations::uniqueness('license_number', $this);
    }

    public static function findByUserId(int $userId): ?Doctor
    {
        return self::findBy(['user_id' => $userId]);
    }

    public function user(): ?User
    {
        return User::findById((int) $this->user_id);
    }
}
