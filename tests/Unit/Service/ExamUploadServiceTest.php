<?php

namespace Tests\Unit\Models;

use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Patient;
use App\Models\Secretary;
use App\Models\User;
use App\Services\ExamUploadService;
use Core\Constants\Constants;
use RuntimeException;
use Tests\TestCase;

class ExamUploadServiceTest extends TestCase
{
    private Exam $exam;
    private Patient $patient;
    private Secretary $secretary;

    public function setUp(): void
    {
        parent::setUp();

        $userPatient = new User([
            'id' => 1,
            'name' => 'Paciente Serviço',
            'email' => 'paciente@test.com',
            'cpf' => '33333333333',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $this->patient = new Patient([
            'user_id' => $userPatient->id,
            'birth_date' => '1990-01-01',
            'phone' => '11988888888',
        ]);


        $userSecretary = new User([
            'id' => 2,
            'name' => 'Secretaria',
            'email' => 'secretaria@test.com',
            'cpf' => '44444444444',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $this->secretary = new Secretary([ 'user_id' => $userSecretary->id ]);

        $examType = new ExamType([
            'id' => 1,
            'name' => 'Raio-X',
            'description' => 'Exame de imagem',
            'ai_prompt_template' => 'Leia o exame',
        ]);

        $this->exam = new Exam([
            'id' => 1,
            'patient_id' => $this->patient->id,
            'appointment_id' => null,
            'upload_by' => $this->secretary->id,
            'exam_type_id' => $examType->id,
            'exam_date' => '2026-06-01',
            'is_verified' => 0,
            'is_verified_by' => null,
            'source' => Exam::SOURCE_UPLOAD,
            'file_path' => '/assets/uploads/exams/paciente_' . $this->patient->id . '/old_exam.pdf',
            'ai_status' => Exam::STATUS_PENDING,
        ]);
    }

    private function createService(): ExamUploadService
    {
        return new ExamUploadService($this->exam);
    }

    public function test_store_rejects_non_pdf_files(): void
    {
        $service = $this->createService();
        $invalidPdf = Constants::rootPath()->join('tests/files/invalidPdf.pdf');
        $result = $service->store([
            'error' => UPLOAD_ERR_OK,
            'name' => 'arquivo.pdf',
            'size' => filesize($invalidPdf),
            'tmp_name' => $invalidPdf,
        ]);
        $this->assertFalse($result);
        $this->assertEquals('Formato inválido. Apenas PDFs são aceitos.', $this->exam->errors('file'));
    }

    public function test_store_rejects_files_above_size_limit(): void
    {
        $service = $this->createService();
        $invalidPdf = Constants::rootPath()->join('tests/files/validPdf.pdf');
        $result = $service->store([
            'error' => UPLOAD_ERR_OK,
            'name' => 'arquivo.pdf',
            'size' => 5 * 1024 * 1024 + 1,
            'tmp_name' => $invalidPdf,
        ]);

        $this->assertFalse($result);
        $this->assertEquals('O arquivo excede o limite de 5MB.', $this->exam->errors('file'));
    }

    public function test_store_throws_when_file_cannot_be_moved(): void
    {
        $this->expectException(RuntimeException::class);

        $service = $this->createService();
        $tempFile = tempnam(sys_get_temp_dir(), 'exam');
        file_put_contents($tempFile, '%PDF-1.4');

        $service->store([
            'error' => UPLOAD_ERR_OK,
            'name' => 'arquivo.pdf',
            'size' => filesize($tempFile),
            'tmp_name' => $tempFile,
        ]);
    }

    public function test_destroyPhysicalFile_removes_saved_file(): void
    {
        $relativePath = '/assets/uploads/exams/paciente_' . $this->patient->id . '/exam_to_delete.pdf';
        $fullPath = (string) Constants::rootPath()->join('public' . $relativePath);

        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), recursive: true);
        }

        file_put_contents($fullPath, 'pdf');

        $this->exam->file_path = $relativePath;

        $service = $this->createService();
        $service->destroyPhysicalFile();

        $this->assertFileDoesNotExist($fullPath);
    }
}
