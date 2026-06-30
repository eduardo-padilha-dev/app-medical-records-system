<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property int $user_id
 * @property string $phone
 */
class Admin extends Model
{
    protected static string $table = 'admins';
    protected static array $columns = ['user_id', 'phone'];

    public function validates(): void
    {
        Validations::notEmpty('user_id', $this);
        Validations::notEmpty('phone', $this);
        Validations::uniqueness('user_id', $this);
    }

    public static function findByUserId(int $userId): ?Admin
    {
        return self::findBy(['user_id' => $userId]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
