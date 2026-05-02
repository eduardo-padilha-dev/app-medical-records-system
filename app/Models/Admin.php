<?php
namespace App\Models;

use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $phone
 */
class Admin extends Model
{
    protected static string $table = 'admins';
    protected static array $columns = ['user_id', 'phone'];

    public static function findByUserId(int $userId): ?Admin
    {
        return self::findBy(['user_id' => $userId]);
    }

    public function user(): ?User
    {
        return User::findById((int)$this->user_id);
    }
}