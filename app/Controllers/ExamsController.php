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

        if (isset($_FILES['exam_file']) && $_FILES['exam_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadService = new ExamUploadService();

            if (!$uploadService->processUpload($_FILES['exam_file'], $exam)) {
                FlashMessage::danger(implode('<br>', $uploadService->getErrors()));
                $patientsWithUser = Patient::allWithUser();
                $examTypes = ExamType::all();
                $title = 'Anexar Novo Exame';
                $this->render('exam/new', compact('title', 'exam', 'patientsWithUser', 'examTypes'));
                return;
            }
        }

        if ($exam->save()) {
            FlashMessage::success('Exame PDF anexado com sucesso e enviado para análise.');
            $this->redirectTo(route('exams.index'));
        } else {
            if (!empty($exam->file_path)) {
                $fullPath = Constants::rootPath()->join('public' . $exam->file_path);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

            $patientsWithUser = Patient::allWithUser();
            $examTypes = ExamType::all();
            $title = 'Anexar Novo Exame';
            $this->render('exam/new', compact('title', 'exam', 'patientsWithUser', 'examTypes'));
        }
    }
}
