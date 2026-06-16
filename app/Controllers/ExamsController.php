<?php

namespace App\Controllers;

use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\ExamUploadService;
use Core\Constants\Constants;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;

class ExamsController extends Controller
{
    public function index(): void
    {
        $user = $this->currentUser();

        if (!$user) {
            FlashMessage::danger('Você precisa estar autenticado para acessar exames.');
            $this->redirectTo(route('auth.check'));
            return;
        }

        if ($user->isSecretary()) {
            $exams = Exam::all();
            $subtitle = 'Todos os exames cadastrados';
        } elseif ($user->isPatient()) {
            $patient = Patient::findByUserId($user->id);
            $exams = $patient ? Exam::findByPatientId($patient->id) : [];
            $subtitle = 'Meus exames';
        } elseif ($user->isDoctor()) {
            $doctor = Doctor::findByUserId($user->id);
            $exams = $doctor ? Exam::where(['is_verified_by' => $doctor->id]) : [];
            $subtitle = 'Exames verificados por mim';
        } else {
            FlashMessage::danger('Você não tem permissão para acessar exames.');
            $this->redirectTo(route('auth.check'));
            return;
        }

        $title = 'Exames';

        $this->render('exam/index', compact('title', 'subtitle', 'exams'));
    }

    public function new(): void
    {
        $exam = new Exam();
        $patientsWithUser = Patient::allWithUser();
        $examTypes = ExamType::all();
        $title = 'Anexar Novo Exame';

        $this->render('exam/new', compact('title', 'exam', 'patientsWithUser', 'examTypes'));
    }

    public function create(Request $request): void
    {
        $user = $this->currentUser();
        $exam = new Exam([
            'patient_id' => $request->getParam('patient_id'),
            'appointment_id' => $request->getParam('appointment_id') ?: null,
            'exam_type_id' => $request->getParam('exam_type_id'),
            'exam_date' => date('Y-m-d'),
            'upload_by' => $user->id,
            'ai_status' => Exam::STATUS_PENDING,
            'source' => Exam::SOURCE_UPLOAD,
        ]);

        $uploadService = new ExamUploadService($exam);

        if (!isset($_FILES['exam_file']) || $_FILES['exam_file']['error'] === UPLOAD_ERR_NO_FILE) {
            FlashMessage::danger('É necessário enviar um arquivo PDF.');
            $this->renderNewExamForm($exam);
            return;
        }

        if (!$uploadService->store($_FILES['exam_file'])) {
            FlashMessage::danger($exam->errors('file') ?? 'Erro ao enviar o arquivo do exame.');
            $this->renderNewExamForm($exam);
            return;
        }

        if ($exam->save()) {
            FlashMessage::success('Exame PDF anexado com sucesso e enviado para análise.');
            $this->redirectTo(route('exams.index'));
        } else {
            $uploadService->destroyPhysicalFile();
            FlashMessage::danger('Erro ao salvar os dados do exame.');
            $this->renderNewExamForm($exam);
        }
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->getParam('id');
        $exam = Exam::findById($id);

        if (!$exam) {
            FlashMessage::danger('Exame não encontrado.');
            $this->redirectTo(route('exams.index'));
            return;
        }

        $uploadService = new ExamUploadService($exam);
        $uploadService->destroyPhysicalFile();

        if ($exam->destroy()) {
            FlashMessage::success('Exame excluído com sucesso!');
        } else {
            FlashMessage::danger('Não foi possível excluir o exame.');
        }

        $this->redirectTo(route('exams.index'));
    }

    public function show(Request $request): void
    {
        $id = (int) $request->getParam('id');
        $exam = Exam::findById($id);

        if (!$exam) {
            FlashMessage::danger('Exame não encontrado.');
            $this->redirectTo(route('exams.index'));
            return;
        }

        if (!$this->canAccess($exam)) {
            FlashMessage::danger('Você não tem permissão para ver este exame.');
            $this->redirectTo(route('exams.index'));
            return;
        }

        $title = 'Exame #' . $exam->id;
        $patient = $exam->patient()->get();
        $examType = $exam->examType()->get();
        $uploadedBy = $exam->uploadedBy()->get();
        $verifiedBy = $exam->is_verified_by ? $exam->verifiedBy()->get() : null;

        $this->render('exam/show', compact('title', 'exam', 'patient', 'examType', 'uploadedBy', 'verifiedBy'));
    }

    private function canAccess(Exam $exam): bool
    {
        $user = $this->currentUser();

        if ($user->isSecretary() || $user->isDoctor()) {
            return true;
        }

        if ($user->isPatient()) {
            $patient = Patient::findByUserId($user->id);
            return $patient && (int) $exam->patient_id === (int) $patient->id;
        }

        return false;
    }

    private function renderNewExamForm(Exam $exam): void
    {
        $patientsWithUser = Patient::allWithUser();
        $examTypes = ExamType::all();
        $title = 'Anexar Novo Exame';
        $this->render('exam/new', compact('title', 'exam', 'patientsWithUser', 'examTypes'));
    }
}
