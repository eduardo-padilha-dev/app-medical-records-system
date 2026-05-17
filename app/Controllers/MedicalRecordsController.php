<?php

namespace App\Controllers;

use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;

class MedicalRecordsController extends Controller
{

    public function index(): void
    {
        $user = $this->currentUser();

        if ($user->isAdmin()) {
            $medicalRecords = MedicalRecord::all();
            $subtitle = 'Todos os Prontuários';
        } elseif ($user->isDoctor()) {
            $doctor = Doctor::findByUserId($user->id);
            $medicalRecords = $doctor ? MedicalRecord::findByDoctorId($doctor->id) : [];
            $subtitle = 'Meus Prontuários';
        } elseif ($user->isPatient()) {
            $patient = Patient::findByUserId($user->id);
            $medicalRecords = $patient ? MedicalRecord::findByPatientId($patient->id) : [];
            $subtitle = 'Meus Prontuários';
        } else {
            FlashMessage::danger('Você não tem permissão para acessar prontuários.');
            $this->redirectTo(route('auth.check'));
            return;
        }

        $title = 'Prontuários Médicos';
        $this->render('medical_record/index', compact('title', 'subtitle', 'medicalRecords'));
    }

    public function show(Request $request): void
    {
        $medicalRecord = $this->findRecordOrRedirect($request);
        if (!$medicalRecord) {
            return;
        }

        // Segurança: verifica se o usuário tem permissão para ver este registro
        if (!$this->canAccess($medicalRecord)) {
            FlashMessage::danger('Acesso negado a este prontuário.');
            $this->redirectTo(route('medical_records.index'));
            return;
        }

        $title  = 'Prontuário #' . $medicalRecord->id;
        $patient = $medicalRecord->patient();
        $doctor  = $medicalRecord->doctor();

        $this->render('medical_record/show', compact('title', 'medicalRecord', 'patient', 'doctor'));
    }

    public function new(): void
    {
        // Somente médicos podem criar prontuários
        if (!$this->currentUser()->isDoctor()) {
            FlashMessage::danger('Apenas médicos podem criar prontuários.');
            $this->redirectTo(route('medical_records.index'));
            return;
        }

        $medicalRecord = new MedicalRecord();
        $patients      = Patient::all(); // lista de pacientes para o <select>
        $title         = 'Novo Prontuário';

        $this->render('medical_record/new', compact('title', 'medicalRecord', 'patients'));
    }

    public function create(Request $request): void
    {
        if (!$this->currentUser()->isDoctor()) {
            FlashMessage::danger('Apenas médicos podem criar prontuários.');
            $this->redirectTo(route('medical_records.index'));
            return;
        }

        $doctor = Doctor::findByUserId($this->currentUser()->id);

        $medicalRecord = new MedicalRecord([
            'patient_id'    => $request->getParam('patient_id'),
            'doctor_id'     => $doctor->id,  // sempre o médico logado
            'appointment_id' => $request->getParam('appointment_id') ?: null,
            'record_date'   => $request->getParam('record_date'),
            'diagnosis'     => $request->getParam('diagnosis'),
            'prescription'  => $request->getParam('prescription') ?: null,
            'notes'         => $request->getParam('notes') ?: null,
        ]);

        if ($medicalRecord->save()) {
            FlashMessage::success('Prontuário criado com sucesso!');
            $this->redirectTo(route('medical_records.show', ['id' => $medicalRecord->id]));
        } else {
            // Validação falhou — reexibe o formulário com os erros
            FlashMessage::danger('Erro ao criar prontuário. Verifique os campos.');
            $patients = Patient::all();
            $title    = 'Novo Prontuário';
            $this->render('medical_record/new', compact('title', 'medicalRecord', 'patients'));
        }
    }

    public function edit(Request $request): void
    {
        $medicalRecord = $this->findRecordOrRedirect($request);
        if (!$medicalRecord) {
            return;
        }

        if (!$this->canEdit($medicalRecord)) {
            FlashMessage::danger('Você não tem permissão para editar este prontuário.');
            $this->redirectTo(route('medical_records.index'));
            return;
        }

        $patients = Patient::all();
        $title    = 'Editar Prontuário #' . $medicalRecord->id;

        $this->render('medical_record/edit', compact('title', 'medicalRecord', 'patients'));
    }

    public function update(Request $request): void
    {
        $medicalRecord = $this->findRecordOrRedirect($request);
        if (!$medicalRecord) {
            return;
        }

        if (!$this->canEdit($medicalRecord)) {
            FlashMessage::danger('Você não tem permissão para editar este prontuário.');
            $this->redirectTo(route('medical_records.index'));
            return;
        }

        $data = [
            'patient_id'    => $request->getParam('patient_id'),
            'appointment_id' => $request->getParam('appointment_id') ?: null,
            'record_date'   => $request->getParam('record_date'),
            'diagnosis'     => $request->getParam('diagnosis'),
            'prescription'  => $request->getParam('prescription') ?: null,
            'notes'         => $request->getParam('notes') ?: null,
        ];

        foreach ($data as $key => $value) {
            $medicalRecord->$key = $value;
        }

        if (!$medicalRecord->isValid()) {
            $patients = Patient::all();
            $title    = 'Editar Prontuário #' . $medicalRecord->id;
            $this->render('medical_record/edit', compact('title', 'medicalRecord', 'patients'));
            return;
        }

        $medicalRecord->update($data);
        FlashMessage::success('Prontuário atualizado com sucesso!');
        $this->redirectTo(route('medical_records.show', ['id' => $medicalRecord->id]));
    }

    public function destroy(Request $request): void
    {
        $medicalRecord = $this->findRecordOrRedirect($request);
        if (!$medicalRecord) {
            return;
        }

        if (!$this->canEdit($medicalRecord)) {
            FlashMessage::danger('Você não tem permissão para excluir este prontuário.');
            $this->redirectTo(route('medical_records.index'));
            return;
        }

        if ($medicalRecord->destroy()) {
            FlashMessage::success('Prontuário excluído com sucesso!');
        } else {
            FlashMessage::danger('Não foi possível excluir o prontuário.');
        }

        $this->redirectTo(route('medical_records.index'));
    }

    private function findRecordOrRedirect(Request $request): ?MedicalRecord
    {
        $id     = (int) $request->getParam('id');
        $record = MedicalRecord::findById($id);

        if (!$record) {
            FlashMessage::danger('Prontuário não encontrado.');
            $this->redirectTo(route('medical_records.index'));
            return null;
        }

        return $record;
    }

    private function canAccess(MedicalRecord $record): bool
    {
        $user = $this->currentUser();

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDoctor()) {
            $doctor = Doctor::findByUserId($user->id);
            return $doctor && (int)$record->doctor_id === (int)$doctor->id;
        }

        if ($user->isPatient()) {
            $patient = Patient::findByUserId($user->id);
            return $patient && (int)$record->patient_id === (int)$patient->id;
        }

        return false;
    }

    private function canEdit(MedicalRecord $record): bool
    {
        $user = $this->currentUser();

        if (!$user->isDoctor()) {
            return false;
        }

        $doctor = Doctor::findByUserId($user->id);
        return $doctor && (int)$record->doctor_id === $doctor->id;
    }
}
