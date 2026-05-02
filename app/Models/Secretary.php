<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;

/**
 * @property int $id
 * @property int $user_id
 */
class Secretary extends Model
{
    protected static string $table = 'secretaries';
    protected static array $columns = ['user_id'];

    public static function findByUserId(int $userId): ?Secretary
    {
        return self::findBy(['user_id' => $userId]);
    }

    public function user(): ?User
    {
        return User::findById((int)$this->user_id);
    }
}
