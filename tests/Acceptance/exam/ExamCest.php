<?php

namespace Tests\Acceptance\exam;

use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Patient;
use App\Models\Secretary;
use App\Models\User;
use Tests\Acceptance\BaseAcceptanceCest;
use Tests\Support\AcceptanceTester;
use PHPUnit\Framework\Assert;

class ExamCest extends BaseAcceptanceCest
{
    private User $secretaryUser;
    private User $patientUser;
    private Patient $patient;
    private ExamType $examType;

    public function _before(AcceptanceTester $page): void
    {
        parent::_before($page);

        $this->secretaryUser = new User([
            'name' => 'Secretária de Exame',
            'email' => 'secretary.exam@test.com',
            'cpf' => '55555555555',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $this->secretaryUser->save();

        $secretary = new Secretary([
            'user_id' => $this->secretaryUser->id,
        ]);
        $secretary->save();

        $this->patientUser = new User([
            'name' => 'Paciente Exame',
            'email' => 'patient.exam.acceptance@test.com',
            'cpf' => '66666666666',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $this->patientUser->save();

        $this->patient = new Patient([
            'user_id' => $this->patientUser->id,
            'birth_date' => '1990-01-01',
            'phone' => '11977777777',
        ]);
        $this->patient->save();

        $this->examType = new ExamType([
            'name' => 'Hemograma Completo',
            'description' => 'Exame de sangue',
            'ai_prompt_template' => 'Analise o hemograma e extraia os dados',
        ]);
        $this->examType->save();
    }

    private function loginAsSecretary(AcceptanceTester $page): void
    {
        $page->login('secretary.exam@test.com', '123456');
    }

    private function uploadExam(AcceptanceTester $page): Exam
    {
        $page->amOnPage('/exams/new');
        $page->see('Anexar Novo Exame');

        $page->selectOption('select[name="patient_id"]', (string) $this->patient->id);
        $page->selectOption('select[name="exam_type_id"]', (string) $this->examType->id);
        $page->attachFile('input[name="exam_file"]', codecept_data_dir('exam_test.pdf'));
        $page->click('Anexar exame');

        $exams = Exam::findByPatientId($this->patient->id);
        Assert::assertNotEmpty($exams);

        return $exams[0];
    }

    public function uploadFileSuccessfully(AcceptanceTester $page): void
    {
        $this->loginAsSecretary($page);
        $exam = $this->uploadExam($page);

        $page->seeCurrentUrlEquals('/exams');
        $page->see('Exames');
        $page->see($this->patientUser->name);
        $page->see($this->examType->name);
        Assert::assertNotEmpty($exam->file_path);
    }

    public function viewUploadedFile(AcceptanceTester $page): void
    {
        $this->loginAsSecretary($page);
        $exam = $this->uploadExam($page);

        $page->amOnPage('/exams/' . $exam->id);
        $page->see('Detalhes completos do exame.');
        $page->see('Ver PDF');
        $page->see($this->patientUser->name);
        $page->see($this->examType->name);
    }

    public function removeUploadedFile(AcceptanceTester $page): void
    {
        $this->loginAsSecretary($page);
        $exam = $this->uploadExam($page);

        $page->amOnPage('/exams');
        $page->click('button[title="Excluir"]');
        $page->see('Confirmar Exclusão');
        $page->click('Excluir');

        $page->seeCurrentUrlEquals('/exams');
        $page->see('Nenhum exame encontrado');
        Assert::assertNull(Exam::findById($exam->id));
    }
}
