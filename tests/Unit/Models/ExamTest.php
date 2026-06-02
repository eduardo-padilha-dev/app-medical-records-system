<?php

namespace Tests\Unit\Models;

use App\Models\Doctor;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Patient;
use App\Models\User;
use Tests\TestCase;

class ExamTest extends TestCase
{
    private User $patientUser;
    private Patient $patient;
    private User $uploaderUser;
    private ExamType $examType;
    private Doctor $doctor;

    public function setUp(): void
    {
        parent::setUp();

        $this->patientUser = new User([
            'name' => 'Paciente de Exame',
            'email' => 'patient.exam@test.com',
            'cpf' => '11111111111',
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

        $this->uploaderUser = new User([
            'name' => 'Secretária Upload',
            'email' => 'uploader.exam@test.com',
            'cpf' => '22222222222',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $this->uploaderUser->save();

        $this->examType = new ExamType([
            'name' => 'Hemograma',
            'description' => 'Exame de sangue',
            'ai_prompt_template' => 'Analise o laudo do exame',
        ]);
        $this->examType->save();

        $this->doctor = new Doctor([
            'user_id' => $this->uploaderUser->id,
            'license_number' => 'CRM-12345',
            'specialty' => 'Clínica Geral',
        ]);
        $this->doctor->save();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createExam(array $attributes = []): Exam
    {
        $exam = new Exam(array_merge([
            'patient_id' => $this->patient->id,
            'appointment_id' => null,
            'upload_by' => $this->uploaderUser->id,
            'exam_type_id' => $this->examType->id,
            'exam_date' => '2026-06-01',
            'is_verified' => 0,
            'is_verified_by' => null,
            'source' => Exam::SOURCE_UPLOAD,
            'file_path' => '/assets/uploads/exams/paciente_' . $this->patient->id . '/exam_123.pdf',
            'ai_status' => Exam::STATUS_PENDING,
            'extracted_data_json' => null,
        ], $attributes));

        $exam->save();

        return $exam;
    }

    public function test_validates_required_fields(): void
    {
        $exam = new Exam();

        $this->assertFalse($exam->isValid());
        $this->assertFalse($exam->save());

        $this->assertEquals('Paciente não pode ser vazio!', $exam->errors('patient_id'));
        $this->assertEquals('Tipo de exame não pode ser vazio!', $exam->errors('exam_type_id'));
        $this->assertEquals('Falha de segurança: Usuário responsável pelo upload não identificado.', $exam->errors('upload_by'));
        $this->assertEquals('Falha no processamento: O arquivo do exame não foi salvo no servidor.', $exam->errors('file_path'));
        $this->assertEquals('Erro interno: O status de processamento da IA não foi definido.', $exam->errors('ai_status'));
    }

    public function test_findByPatientId_should_return_patient_exams(): void
    {
        $exam = $this->createExam();

        $exams = Exam::findByPatientId($this->patient->id);

        $this->assertCount(1, $exams);
        $this->assertSame($exam->id, $exams[0]->id);
    }

    public function test_relationships_should_return_related_models(): void
    {
        $exam = $this->createExam([
            'is_verified' => 1,
            'is_verified_by' => $this->doctor->id,
        ]);

        $this->assertInstanceOf(Patient::class, $exam->patient()->get());
        $this->assertInstanceOf(User::class, $exam->uploadedBy()->get());
        $this->assertInstanceOf(ExamType::class, $exam->examType()->get());
        $this->assertInstanceOf(Doctor::class, $exam->verifiedBy()->get());
    }
}
