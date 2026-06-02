<?php

namespace Tests\Integration\Controllers;

use App\Models\Doctor;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Patient;
use App\Models\Secretary;
use App\Models\User;

class ExamsControllerTest extends ControllerTestCase
{
    private User $secretaryUser;
    private User $patientUser;
    private User $doctorUser;
    private User $otherPatientUser;
    private ExamType $examType;
    private Patient $patient;
    private Doctor $doctor;

    public function setUp(): void
    {
        parent::setUp();

        $this->secretaryUser = new User([
            'name' => 'Sec',
            'email' => 'sec@test.com',
            'cpf' => '00000000001',
            'password' => '123',
            'password_confirmation' => '123'
        ]);
        $this->secretaryUser->save();
        $sec = new Secretary(['user_id' => $this->secretaryUser->id]);
        $sec->save();

        $this->patientUser = new User([
            'name' => 'Pat',
            'email' => 'pat@test.com',
            'cpf' => '00000000002',
            'password' => '123',
            'password_confirmation' => '123'
        ]);
        $this->patientUser->save();
        $this->patient = new Patient(['
        user_id' => $this->patientUser->id,
        'birth_date' => '2000-01-01',
        'phone' => '11999999999'
        ]);
        $this->patient->save();

        $this->otherPatientUser = new User([
            'name' => 'Pat2',
            'email' => 'pat2@test.com',
            'cpf' => '00000000003',
            'password' => '123',
            'password_confirmation' => '123'
        ]);
        $this->otherPatientUser->save();
        $otherPatient = new Patient([
            'user_id' => $this->otherPatientUser->id,
            'birth_date' => '2000-01-01',
            'phone' => '11999999999'
        ]);
        $otherPatient->save();

        $this->doctorUser = new User([
            'name' => 'Doc',
            'email' => 'doc@test.com',
            'cpf' => '00000000004',
            'password' => '123',
            'password_confirmation' => '123'
        ]);
        $this->doctorUser->save();
        $this->doctor = new Doctor(['user_id' => $this->doctorUser->id, 'license_number' => 'CRM123', 'specialty' => 'Geral']);
        $this->doctor->save();

        $this->examType = new ExamType([
            'name' => 'Sangue', 'description' => 'Desc', 'ai_prompt_template' => 'Prompt'
        ]);
        $this->examType->save();
    }

    private function login(User $user): void
    {
        $_SESSION['user']['id'] = $user->id;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createExam(array $attributes = []): Exam
    {
        $exam = new Exam(array_merge([
            'patient_id' => $this->patient->id,
            'exam_type_id' => $this->examType->id,
            'upload_by' => $this->secretaryUser->id,
            'exam_date' => date('Y-m-d'),
            'is_verified' => 0,
            'is_verified_by' => null,
            'source' => Exam::SOURCE_UPLOAD,
            'file_path' => '/tmp/file.pdf',
            'ai_status' => Exam::STATUS_PENDING,
        ], $attributes));
        $exam->save();
        return $exam;
    }

    public function test_index_redirects_unauthenticated(): void
    {
        $output = $this->get('index', 'App\Controllers\ExamsController');
        $this->assertStringContainsString('Location: /', $output);
    }

    public function test_index_as_secretary_shows_all_exams(): void
    {
        $this->login($this->secretaryUser);
        $this->createExam();

        $output = $this->get('index', 'App\Controllers\ExamsController');
        $this->assertStringContainsString('Todos os exames cadastrados', $output);
    }

    public function test_index_as_patient_shows_only_own_exams(): void
    {
        $this->login($this->patientUser);
        $this->createExam();

        $output = $this->get('index', 'App\Controllers\ExamsController');
        $this->assertStringContainsString('Meus exames', $output);
    }

    public function test_index_as_doctor_shows_verified_by_me_exams(): void
    {
        $this->login($this->doctorUser);
        $this->createExam(['is_verified_by' => $this->doctor->id]);

        $output = $this->get('index', 'App\Controllers\ExamsController');
        $this->assertStringContainsString('Exames verificados por mim', $output);
    }

    public function test_show_allows_secretary(): void
    {
        $exam = $this->createExam();
        $this->login($this->secretaryUser);

        $output = $this->get('show', 'App\Controllers\ExamsController', ['id' => $exam->id]);
        $this->assertStringContainsString('Exame #' . $exam->id, $output);
    }

    public function test_show_allows_patient_owner(): void
    {
        $exam = $this->createExam();
        $this->login($this->patientUser);

        $output = $this->get('show', 'App\Controllers\ExamsController', ['id' => $exam->id]);
        $this->assertStringContainsString('Exame #' . $exam->id, $output);
    }

    public function test_show_denies_other_patient(): void
    {
        $exam = $this->createExam();
        $this->login($this->otherPatientUser);

        $output = $this->get('show', 'App\Controllers\ExamsController', ['id' => $exam->id]);
        $this->assertStringContainsString('Location: /exams', $output);
    }

    public function test_destroy_deletes_exam_and_redirects(): void
    {
        $exam = $this->createExam();
        $this->login($this->secretaryUser);

        $output = $this->post('destroy', 'App\Controllers\ExamsController', ['id' => $exam->id]);
        $this->assertStringContainsString('Location: /exams', $output);
        $this->assertNull(Exam::findById($exam->id));
    }
}
