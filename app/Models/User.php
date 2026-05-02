<?php

namespace App\Models;

use Lib\Validations;
use Core\Database\ActiveRecord\Model;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\Patient;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $cpf
 * @property string $encrypted_password
 */
class User extends Model
{
    protected static string $table = 'users';
    protected static array $columns = ['name', 'cpf', 'email', 'encrypted_password'];

    protected ?string $password = null;
    protected ?string $password_confirmation = null;
    private ?Admin $cachedAdmin = null;
    private ?Doctor $cachedDoctor = null;
    private ?Secretary $cachedSecretary = null;
    private ?Patient $cachedPatient = null;

    public function validates(): void
    {
        Validations::notEmpty('name', $this);
        Validations::notEmpty('email', $this);

        Validations::uniqueness('email', $this);

        if ($this->newRecord()) {
            Validations::passwordConfirmation($this);
        }
    }

    public function authenticate(string $password): bool
    {
        if ($this->encrypted_password == null) {
            return false;
        }

        return password_verify($password, $this->encrypted_password);
    }

    public static function findByEmail(string $email): ?User
    {
        return User::findBy(['email' => $email]);
    }

    public function __set(string $property, mixed $value): void
    {
        parent::__set($property, $value);

        if (
            $property === 'password' &&
            $this->newRecord() &&
            $value !== null && $value !== ''
        ) {
            $this->encrypted_password = password_hash($value, PASSWORD_DEFAULT);
        }
    }

    public function admin(): ?Admin
    {
        if ($this->cachedAdmin !== null) return $this->cachedAdmin;
        return $this->cachedAdmin = Admin::findByUserId($this->id);
    }

    public function doctor(): ?Doctor
    {
        if ($this->cachedDoctor !== null) return $this->cachedDoctor;
        return $this->cachedDoctor = Doctor::findByUserId($this->id);
    }

    public function secretary(): ?Secretary
    {
        if ($this->cachedSecretary !== null) return $this->cachedSecretary;
        return $this->cachedSecretary = Secretary::findByUserId($this->id);
    }

    public function patient(): ?Patient
    {
        if ($this->cachedPatient !== null) return $this->cachedPatient;
        return $this->cachedPatient = Patient::findByUserId($this->id);
    }

    public function isAdmin(): bool
    {
        return $this->admin() !== null;
    }

    public function isDoctor(): bool
    {
        return $this->doctor() !== null;
    }

    public function isSecretary(): bool
    {
        return $this->secretary() !== null;
    }

    public function isPatient(): bool
    {
        return $this->patient() !== null;
    }

    public function type(): ?string
    {
        if ($this->isAdmin()) return 'admin';  
        if ($this->isDoctor()) return 'doctor';
        if ($this->isSecretary()) return 'secretary';
        if ($this->isPatient()) return 'patient';
        return null;
    }
}
