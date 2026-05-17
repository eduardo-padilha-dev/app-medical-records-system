<?php

namespace Tests\Unit\Models\Users;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    private User $user;
    private User $user2;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User([
            'name' => 'User 1',
            'email' => 'fulano@example.com',
            'cpf' => '12345678901',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $this->user->save();

        $this->user2 = new User([
            'name' => 'User 2',
            'email' => 'fulano1@example.com',
            'cpf' => '98765432101',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $this->user2->save();
    }

    public function test_should_create_new_user(): void
    {
        $this->assertCount(2, User::all());
    }

    public function test_all_should_return_all_users(): void
    {
        $this->user2->save();

        $users[] = $this->user->id;
        $users[] = $this->user2->id;

        $all = array_map(fn($user) => $user->id, User::all());

        $this->assertCount(2, $all);
        $this->assertEquals($users, $all);
    }

    public function test_destroy_should_remove_the_user(): void
    {
        $this->user->destroy();
        $this->assertCount(1, User::all());
    }

    public function test_set_id(): void
    {
        $this->user->id = 10;
        $this->assertEquals(10, $this->user->id);
    }

    public function test_set_name(): void
    {
        $this->user->name = 'User name';
        $this->assertEquals('User name', $this->user->name);
    }

    public function test_set_email(): void
    {
        $this->user->email = 'outro@example.com';
        $this->assertEquals('outro@example.com', $this->user->email);
    }

    public function test_errors_should_return_errors(): void
    {
        $user = new User();

        $this->assertFalse($user->isValid());
        $this->assertFalse($user->save());
        $this->assertTrue($user->hasErrors());

        $this->assertEquals('não pode ser vazio!', $user->errors('name'));
        $this->assertEquals('não pode ser vazio!', $user->errors('email'));
    }

    public function test_errors_should_return_password_confirmation_error(): void
    {
        $user = new User([
            'name' => 'User 3',
            'email' => 'fulano3@example.com',
            'password' => '123456',
            'password_confirmation' => '1234567'
        ]);

        $this->assertFalse($user->isValid());
        $this->assertFalse($user->save());

        $this->assertEquals('as senhas devem ser idênticas!', $user->errors('password'));
    }

    public function test_find_by_id_should_return_the_user(): void
    {
        $this->assertEquals($this->user->id, User::findById($this->user->id)->id);
    }

    public function test_find_by_id_should_return_null(): void
    {
        $this->assertNull(User::findById(3));
    }

    public function test_find_by_email_should_return_the_user(): void
    {
        $this->assertEquals($this->user->id, User::findByEmail($this->user->email)->id);
    }

    public function test_find_by_email_should_return_null(): void
    {
        $this->assertNull(User::findByEmail('not.exits@example.com'));
    }

    public function test_authenticate_should_return_the_true(): void
    {
        $this->assertTrue($this->user->authenticate('123456'));
        $this->assertFalse($this->user->authenticate('wrong'));
    }

    public function test_authenticate_should_return_false(): void
    {
        $this->assertFalse($this->user->authenticate(''));
    }

    public function test_update_should_not_change_the_password(): void
    {
        $this->user->password = '654321';
        $this->user->save();

        $this->assertTrue($this->user->authenticate('123456'));
        $this->assertFalse($this->user->authenticate('654321'));
    }

    public function test_is_admin_returns_true_when_user_is_admin(): void
    {
        // Criar um admin relacionado ao usuário
        $admin = new \App\Models\Admin([
            'user_id' => $this->user->id,
            'phone' => '999999999'
        ]);
        $admin->save();

        $user = User::findById($this->user->id);
        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_returns_false_when_user_is_not_admin(): void
    {
        $this->assertFalse($this->user->isAdmin());
    }

    public function test_is_doctor_returns_true_when_user_is_doctor(): void
    {
        // Criar um doctor relacionado ao usuário
        $doctor = new \App\Models\Doctor([
            'user_id' => $this->user->id,
            'license_number' => 'CRM123456',
            'specialty' => 'Cardiologia'
        ]);
        $doctor->save();

        $user = User::findById($this->user->id);
        $this->assertTrue($user->isDoctor());
    }

    public function test_is_doctor_returns_false_when_user_is_not_doctor(): void
    {
        $this->assertFalse($this->user->isDoctor());
    }

    public function test_is_secretary_returns_true_when_user_is_secretary(): void
    {
        // Criar uma secretary relacionada ao usuário
        $secretary = new \App\Models\Secretary([
            'user_id' => $this->user->id
        ]);
        $secretary->save();

        $user = User::findById($this->user->id);
        $this->assertTrue($user->isSecretary());
    }

    public function test_is_secretary_returns_false_when_user_is_not_secretary(): void
    {
        $this->assertFalse($this->user->isSecretary());
    }

    public function test_is_patient_returns_true_when_user_is_patient(): void
    {
        // Criar um patient relacionado ao usuário
        $patient = new \App\Models\Patient([
            'user_id' => $this->user->id,
            'birth_date' => '1990-01-01',
            'phone' => '11999999999'
        ]);
        $patient->save();

        $user = User::findById($this->user->id);
        $this->assertTrue($user->isPatient());
    }

    public function test_is_patient_returns_false_when_user_is_not_patient(): void
    {
        $this->assertFalse($this->user->isPatient());
    }

    public function test_type_returns_admin_when_user_is_admin(): void
    {
        $admin = new \App\Models\Admin([
            'user_id' => $this->user->id,
            'phone' => '999999999'
        ]);
        $admin->save();

        $user = User::findById($this->user->id);
        $this->assertEquals('admin', $user->type());
    }

    public function test_type_returns_doctor_when_user_is_doctor(): void
    {
        $doctor = new \App\Models\Doctor([
            'user_id' => $this->user->id,
            'license_number' => 'CRM123456',
            'specialty' => 'Cardiologia'
        ]);
        $doctor->save();

        $user = User::findById($this->user->id);
        $this->assertEquals('doctor', $user->type());
    }

    public function test_type_returns_secretary_when_user_is_secretary(): void
    {
        $secretary = new \App\Models\Secretary([
            'user_id' => $this->user->id
        ]);
        $secretary->save();

        $user = User::findById($this->user->id);
        $this->assertEquals('secretary', $user->type());
    }

    public function test_type_returns_patient_when_user_is_patient(): void
    {
        $patient = new \App\Models\Patient([
            'user_id' => $this->user->id,
            'birth_date' => '1990-01-01',
            'phone' => '11999999999'
        ]);
        $patient->save();

        $user = User::findById($this->user->id);
        $this->assertEquals('patient', $user->type());
    }

    public function test_type_returns_null_when_user_has_no_profile(): void
    {
        $user = User::findById($this->user->id);
        $this->assertNull($user->type());
    }

    public function test_admin_method_returns_admin_instance(): void
    {
        $admin = new \App\Models\Admin([
            'user_id' => $this->user->id,
            'phone' => '999999999'
        ]);
        $admin->save();

        $user = User::findById($this->user->id);
        $this->assertInstanceOf(\App\Models\Admin::class, $user->admin());
    }

    public function test_doctor_method_returns_doctor_instance(): void
    {
        $doctor = new \App\Models\Doctor([
            'user_id' => $this->user->id,
            'license_number' => 'CRM123456',
            'specialty' => 'Cardiologia'
        ]);
        $doctor->save();

        $user = User::findById($this->user->id);
        $this->assertInstanceOf(\App\Models\Doctor::class, $user->doctor());
    }

    public function test_secretary_method_returns_secretary_instance(): void
    {
        $secretary = new \App\Models\Secretary([
            'user_id' => $this->user->id
        ]);
        $secretary->save();

        $user = User::findById($this->user->id);
        $this->assertInstanceOf(\App\Models\Secretary::class, $user->secretary());
    }

    public function test_patient_method_returns_patient_instance(): void
    {
        $patient = new \App\Models\Patient([
            'user_id' => $this->user->id,
            'birth_date' => '1990-01-01',
            'phone' => '11999999999'
        ]);
        $patient->save();

        $user = User::findById($this->user->id);
        $this->assertInstanceOf(\App\Models\Patient::class, $user->patient());
    }

    public function test_admin_method_returns_null_when_no_profile(): void
    {
        $user = User::findById($this->user->id);
        $this->assertNull($user->admin());
    }

    public function test_doctor_method_returns_null_when_no_profile(): void
    {
        $user = User::findById($this->user->id);
        $this->assertNull($user->doctor());
    }

    public function test_secretary_method_returns_null_when_no_profile(): void
    {
        $user = User::findById($this->user->id);
        $this->assertNull($user->secretary());
    }

    public function test_patient_method_returns_null_when_no_profile(): void
    {
        $user = User::findById($this->user->id);
        $this->assertNull($user->patient());
    }
}
