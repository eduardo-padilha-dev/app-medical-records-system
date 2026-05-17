<?php

namespace App\Controllers;

use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;

/**
 * CRUD de Prontuário Médico
 *
 * Rotas disponíveis:
 *   GET    /medical_records              → index   (lista; médico vê os seus, paciente vê os seus)
 *   GET    /medical_records/{id}         → show    (exibe um prontuário)
 *   GET    /medical_records/new          → new     (formulário de criação — apenas médico)
 *   POST   /medical_records              → create  (salva novo prontuário — apenas médico)
 *   GET    /medical_records/{id}/edit    → edit    (formulário de edição — apenas médico)
 *   PUT    /medical_records/{id}         → update  (atualiza — apenas médico)
 *   DELETE /medical_records/{id}         → destroy (exclui — apenas médico)
 */
class MedicalRecordsController extends Controller
{
    // ------------------------------------------------------------------
    // INDEX — lista prontuários conforme o perfil do usuário logado
    // ------------------------------------------------------------------

    public function index(): void
    {
        $user = $this->currentUser();

        if ($user->isDoctor()) {
            // O médico vê somente os prontuários que ele criou
            $doctor        = Doctor::findByUserId($user->id);
            $medicalRecords = MedicalRecord::findByDoctorId($doctor->id);
            $subtitle      = 'Meus Prontuários';
        } else {
            // O paciente vê somente seus próprios prontuários
            $patient       = Patient::findByUserId($user->id);
            $medicalRecords = MedicalRecord::findByPatientId($patient->id);
            $subtitle      = 'Meus Prontuários';
        }

        $title = 'Prontuários Médicos';
        $this->render('medical_record/index', compact('title', 'subtitle', 'medicalRecords'));
    }

    // ------------------------------------------------------------------
    // SHOW — exibe um prontuário específico
    // ------------------------------------------------------------------

    public function show(Request $request): void
    {
        $medicalRecord = $this->findRecordOrRedirect($request);
        if (!$medicalRecord) return;

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

    // ------------------------------------------------------------------
    // NEW — exibe formulário de criação (somente médico)
    // ------------------------------------------------------------------

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

    // ------------------------------------------------------------------
    // CREATE — processa o formulário e persiste o registro
    // ------------------------------------------------------------------

    public function create(Request $request): void
    {
        if (!$this->currentUser()->isDoctor()) {
            FlashMessage::danger('Apenas médicos podem criar prontuários.');
            $this->redirectTo(route('medical_records.index'));
            return;
        }

        $doctor = Doctor::findByUserId($this->currentUser()->id);

        // Monta o prontuário com os dados enviados pelo formulário
        $medicalRecord = new MedicalRecord([
            'patient_id'    => $request->getParam('patient_id'),
            'doctor_id'     => $doctor->id,  // sempre o médico logado
            'appointment_id'=> $request->getParam('appointment_id') ?: null,
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

    // ------------------------------------------------------------------
    // EDIT — exibe formulário de edição (somente médico dono do registro)
    // ------------------------------------------------------------------

    public function edit(Request $request): void
    {
        $medicalRecord = $this->findRecordOrRedirect($request);
        if (!$medicalRecord) return;

        if (!$this->canEdit($medicalRecord)) {
            FlashMessage::danger('Você não tem permissão para editar este prontuário.');
            $this->redirectTo(route('medical_records.index'));
            return;
        }

        $patients = Patient::all();
        $title    = 'Editar Prontuário #' . $medicalRecord->id;

        $this->render('medical_record/edit', compact('title', 'medicalRecord', 'patients'));
    }

    // ------------------------------------------------------------------
    // UPDATE — processa edição e persiste as alterações
    // ------------------------------------------------------------------

    public function update(Request $request): void
    {
        $medicalRecord = $this->findRecordOrRedirect($request);
        if (!$medicalRecord) return;

        if (!$this->canEdit($medicalRecord)) {
            FlashMessage::danger('Você não tem permissão para editar este prontuário.');
            $this->redirectTo(route('medical_records.index'));
            return;
        }

        // Atualiza apenas os campos editáveis
        $data = [
            'patient_id'    => $request->getParam('patient_id'),
            'appointment_id'=> $request->getParam('appointment_id') ?: null,
            'record_date'   => $request->getParam('record_date'),
            'diagnosis'     => $request->getParam('diagnosis'),
            'prescription'  => $request->getParam('prescription') ?: null,
            'notes'         => $request->getParam('notes') ?: null,
        ];

        // Aplica as alterações ao objeto e revalida antes de persistir
        foreach ($data as $key => $value) {
            $medicalRecord->$key = $value;
        }

        if ($medicalRecord->isValid() && $medicalRecord->update($data)) {
            FlashMessage::success('Prontuário atualizado com sucesso!');
            $this->redirectTo(route('medical_records.show', ['id' => $medicalRecord->id]));
        } else {
            FlashMessage::danger('Erro ao atualizar prontuário. Verifique os campos.');
            $patients = Patient::all();
            $title    = 'Editar Prontuário #' . $medicalRecord->id;
            $this->render('medical_record/edit', compact('title', 'medicalRecord', 'patients'));
        }
    }

    // ------------------------------------------------------------------
    // DESTROY — exclui o prontuário (somente médico dono do registro)
    // ------------------------------------------------------------------

    public function destroy(Request $request): void
    {
        $medicalRecord = $this->findRecordOrRedirect($request);
        if (!$medicalRecord) return;

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

    // ------------------------------------------------------------------
    // Métodos privados auxiliares
    // ------------------------------------------------------------------

    /** Busca o registro pelo {id} da URL; redireciona se não encontrar */
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

    /**
     * Verifica se o usuário logado pode VER este prontuário.
     * Médico vê os seus; paciente vê os seus; admin vê todos.
     */
    private function canAccess(MedicalRecord $record): bool
    {
        $user = $this->currentUser();

        if ($user->isAdmin()) return true;

        if ($user->isDoctor()) {
            $doctor = Doctor::findByUserId($user->id);
            return $doctor && (int)$record->doctor_id === $doctor->id;
        }

        if ($user->isPatient()) {
            $patient = Patient::findByUserId($user->id);
            return $patient && (int)$record->patient_id === $patient->id;
        }

        return false;
    }

    /**
     * Verifica se o usuário logado pode EDITAR/EXCLUIR este prontuário.
     * Somente o médico que criou pode alterar.
     */
    private function canEdit(MedicalRecord $record): bool
    {
        $user = $this->currentUser();

        if (!$user->isDoctor()) return false;

        $doctor = Doctor::findByUserId($user->id);
        return $doctor && (int)$record->doctor_id === $doctor->id;
    }
}
