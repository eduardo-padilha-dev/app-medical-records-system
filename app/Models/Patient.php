<?php

namespace App\Models;

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

    public function user(): ?User
    {
        return User::findById((int) $this->user_id);
    }
}
