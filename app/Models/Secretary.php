<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsTo;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;
use Lib\Validations;

/**
 * @property int $id
 * @property int $user_id
 */
class Secretary extends Model
{
    protected static string $table = 'secretaries';
    protected static array $columns = ['user_id'];

    public function validates(): void
    {
        Validations::notEmpty('user_id', $this);
        Validations::uniqueness('user_id', $this);
    }

    public static function findByUserId(int $userId): ?Secretary
    {
        return self::findBy(['user_id' => $userId]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'secretary_id');
    }
}
