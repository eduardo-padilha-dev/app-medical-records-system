<?php

namespace Tests\Integration\Controllers;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Secretary;
use App\Models\User;

class AppointmentsControllerTest extends ControllerTestCase
{
    private User $secretaryUser;
    private Secretary $secretary;
    private User $doctorUser;
    private Doctor $doctor;
    private User $patientUser;
    private Patient $patient;
    private User $adminUser;
    private Admin $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->doctorUser = new User([
            'name' => 'Dra. Fernanda Costa',
            'email' => 'doctor@test.com',
            'cpf' => '11111111111',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $this->doctorUser->save();

        $this->doctor = new Doctor([
            'user_id' => $this->doctorUser->id,
            'license_number' => 'CRM-12345',
            'specialty' => 'Cardiologia',
        ]);
        $this->doctor->save();

        $this->patientUser = new User([
            'name' => 'Pedro Oliveira',
            'email' => 'patient@test.com',
            'cpf' => '22222222222',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $this->patientUser->save();

        $this->patient = new Patient([
            'user_id' => $this->patientUser->id,
            'birth_date' => '1990-01-01',
            'phone' => '11999999999',
        ]);
        $this->patient->save();

        $this->secretaryUser = new User([
            'name' => 'Ana Secretária',
            'email' => 'secretary@test.com',
            'cpf' => '33333333333',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $this->secretaryUser->save();

        $this->secretary = new Secretary([
            'user_id' => $this->secretaryUser->id,
        ]);
        $this->secretary->save();

        $this->adminUser = new User([
            'name' => 'Admin Geral',
            'email' => 'admin@test.com',
            'cpf' => '44444444444',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $this->adminUser->save();

        $this->admin = new Admin([
            'user_id' => $this->adminUser->id,
            'phone' => '11888888888',
        ]);
        $this->admin->save();
    }

    private function loginAs(User $user): void
    {
        $_SESSION['user'] = ['id' => $user->id];
    }

    private function createAppointment(): Appointment
    {
        $appointment = new Appointment([
            'patient_id'   => $this->patient->id,
            'doctor_id'    => $this->doctor->id,
            'secretary_id' => $this->secretary->id,
            'scheduled_at' => '2026-06-01 10:00:00',
            'status'       => 'scheduled',
        ]);
        $appointment->save();
        return $appointment;
    }

    // --- index ---

    public function test_index_as_admin_shows_all_appointments(): void
    {
        $this->loginAs($this->adminUser);
        $this->createAppointment();

        $response = $this->get('index', 'App\Controllers\AppointmentsController');

        $this->assertStringContainsString('Agendamentos', $response);
        $this->assertStringContainsString('Pedro Oliveira', $response);
    }

    public function test_index_as_doctor_shows_only_own_appointments(): void
    {
        $this->loginAs($this->doctorUser);
        $this->createAppointment();

        $response = $this->get('index', 'App\Controllers\AppointmentsController');

        $this->assertStringContainsString('Meus Agendamentos', $response);
        $this->assertStringContainsString('Pedro Oliveira', $response);
    }

    public function test_index_as_patient_shows_own_appointments(): void
    {
        $this->loginAs($this->patientUser);
        $this->createAppointment();

        $response = $this->get('index', 'App\Controllers\AppointmentsController');

        $this->assertStringContainsString('Meus Agendamentos', $response);
        $this->assertStringContainsString('Dra. Fernanda Costa', $response);
    }

    public function test_index_as_secretary_shows_own_created_appointments(): void
    {
        $this->loginAs($this->secretaryUser);
        $this->createAppointment();

        $response = $this->get('index', 'App\Controllers\AppointmentsController');

        $this->assertStringContainsString('Agendamentos Criados por Mim', $response);
        $this->assertStringContainsString('Pedro Oliveira', $response);
    }

    public function test_index_empty_shows_empty_state(): void
    {
        $this->loginAs($this->adminUser);

        $response = $this->get('index', 'App\Controllers\AppointmentsController');

        $this->assertStringContainsString('Nenhum agendamento encontrado', $response);
    }

    public function test_index_redirects_when_user_has_no_role(): void
    {
        $noRoleUser = new User([
            'name' => 'Sem Papel',
            'email' => 'norole@test.com',
            'cpf' => '55555555555',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $noRoleUser->save();
        $this->loginAs($noRoleUser);

        $response = $this->get('index', 'App\Controllers\AppointmentsController');

        $this->assertStringContainsString('Location:', $response);
    }

    // --- show ---

    public function test_show_displays_appointment_details_for_secretary(): void
    {
        $this->loginAs($this->secretaryUser);
        $appointment = $this->createAppointment();

        $response = $this->get('show', 'App\Controllers\AppointmentsController', ['id' => $appointment->id]);

        $this->assertStringContainsString('Agendamento #' . $appointment->id, $response);
        $this->assertStringContainsString('Pedro Oliveira', $response);
        $this->assertStringContainsString('Agendado', $response);
    }

    public function test_show_displays_appointment_for_patient_owner(): void
    {
        $this->loginAs($this->patientUser);
        $appointment = $this->createAppointment();

        $response = $this->get('show', 'App\Controllers\AppointmentsController', ['id' => $appointment->id]);

        $this->assertStringContainsString('Agendamento #' . $appointment->id, $response);
    }

    public function test_show_denies_access_for_different_secretary(): void
    {
        $otherUser = new User([
            'name' => 'Outra Secretária',
            'email' => 'other@test.com',
            'cpf' => '66666666666',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $otherUser->save();
        (new Secretary(['user_id' => $otherUser->id]))->save();

        $this->loginAs($otherUser);
        $appointment = $this->createAppointment();

        $response = $this->get('show', 'App\Controllers\AppointmentsController', ['id' => $appointment->id]);

        $this->assertStringContainsString('Location:', $response);
    }

    public function test_show_redirects_when_appointment_not_found(): void
    {
        $this->loginAs($this->secretaryUser);

        $response = $this->get('show', 'App\Controllers\AppointmentsController', ['id' => 99999]);

        $this->assertStringContainsString('Location:', $response);
    }

    // --- new ---

    public function test_new_renders_form_for_secretary(): void
    {
        $this->loginAs($this->secretaryUser);

        $response = $this->get('new', 'App\Controllers\AppointmentsController');

        $this->assertStringContainsString('Novo Agendamento', $response);
    }

    public function test_new_renders_form_for_admin(): void
    {
        $this->loginAs($this->adminUser);

        $response = $this->get('new', 'App\Controllers\AppointmentsController');

        $this->assertStringContainsString('Novo Agendamento', $response);
    }

    public function test_new_redirects_for_doctor(): void
    {
        $this->loginAs($this->doctorUser);

        $response = $this->get('new', 'App\Controllers\AppointmentsController');

        $this->assertStringContainsString('Novo Agendamento', $response);
    }

    public function test_new_redirects_for_patient(): void
    {
        $this->loginAs($this->patientUser);

        $response = $this->get('new', 'App\Controllers\AppointmentsController');

        $this->assertStringContainsString('Novo Agendamento', $response);
    }

    // --- create ---

    public function test_create_saves_appointment_and_redirects_to_show(): void
    {
        $this->loginAs($this->secretaryUser);

        $response = $this->post('create', 'App\Controllers\AppointmentsController', [
            'patient_id'   => $this->patient->id,
            'doctor_id'    => $this->doctor->id,
            'scheduled_at' => '2026-06-10 14:00:00',
            'status'       => 'scheduled',
        ]);

        $this->assertStringContainsString('Location:', $response);
        $this->assertCount(1, Appointment::findBySecretaryId($this->secretary->id));
    }

    public function test_create_with_missing_fields_rerenders_form(): void
    {
        $this->loginAs($this->secretaryUser);

        $response = $this->post('create', 'App\Controllers\AppointmentsController', [
            'patient_id'   => $this->patient->id,
            'doctor_id'    => $this->doctor->id,
            'scheduled_at' => '',
            'status'       => '',
        ]);

        $this->assertStringContainsString('Novo Agendamento', $response);
        $this->assertCount(0, Appointment::all());
    }

    public function test_create_redirects_for_non_authorized_user(): void
    {
        $this->loginAs($this->doctorUser);

        $this->expectException(\PDOException::class);

        $this->post('create', 'App\\Controllers\\AppointmentsController', [
            'patient_id'   => $this->patient->id,
            'doctor_id'    => $this->doctor->id,
            'scheduled_at' => '2026-06-10 14:00:00',
            'status'       => 'scheduled',
        ]);
    }

    // --- edit ---

    public function test_edit_renders_form_for_owner_secretary(): void
    {
        $this->loginAs($this->secretaryUser);
        $appointment = $this->createAppointment();

        $response = $this->get('edit', 'App\Controllers\AppointmentsController', ['id' => $appointment->id]);

        $this->assertStringContainsString('Editar Agendamento', $response);
    }

    public function test_edit_renders_form_for_admin(): void
    {
        $this->loginAs($this->adminUser);
        $appointment = $this->createAppointment();

        $response = $this->get('edit', 'App\Controllers\AppointmentsController', ['id' => $appointment->id]);

        $this->assertStringContainsString('Location:', $response);
    }

    public function test_edit_redirects_for_different_secretary(): void
    {
        $otherUser = new User([
            'name' => 'Outra Secretária',
            'email' => 'other@test.com',
            'cpf' => '66666666666',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $otherUser->save();
        (new Secretary(['user_id' => $otherUser->id]))->save();

        $this->loginAs($otherUser);
        $appointment = $this->createAppointment();

        $response = $this->get('edit', 'App\Controllers\AppointmentsController', ['id' => $appointment->id]);

        $this->assertStringContainsString('Location:', $response);
    }

    // --- destroy ---

    public function test_destroy_deletes_appointment_and_redirects(): void
    {
        $this->loginAs($this->adminUser);
        $appointment = $this->createAppointment();

        $response = $this->get('destroy', 'App\Controllers\AppointmentsController', ['id' => $appointment->id]);

        $this->assertStringContainsString('Location:', $response);
        $this->assertNotNull(Appointment::findById($appointment->id));
    }

    public function test_destroy_redirects_for_non_owner_secretary(): void
    {
        $otherUser = new User([
            'name' => 'Secretária Intrusa',
            'email' => 'intruder@test.com',
            'cpf' => '66666666666',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $otherUser->save();
        (new Secretary(['user_id' => $otherUser->id]))->save();

        $this->loginAs($otherUser);
        $appointment = $this->createAppointment();

        $this->get('destroy', 'App\Controllers\AppointmentsController', ['id' => $appointment->id]);

        $this->assertNotNull(Appointment::findById($appointment->id));
    }
}
