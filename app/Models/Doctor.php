<?php
namespace App\Models;

use Core\Database\ActiveRecord\Model;

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

    public static function findByUserId(int $userId): ?Doctor
    {
        return self::findBy(['user_id' => $userId]);
    }

    public function user(): ?User
    {
        return User::findById((int)$this->user_id);
    }
}