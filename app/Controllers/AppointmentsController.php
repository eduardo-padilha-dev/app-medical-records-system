<?php

namespace App\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Secretary;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;

class AppointmentsController extends Controller
{
    public function index(): void
    {
        $user = $this->currentUser();

        if ($user->isAdmin()) {
            $appointments = Appointment::all();
            $subtitle = 'Todos os Agendamentos';
        } elseif ($user->isDoctor()) {
            $doctor = Doctor::findByUserId($user->id);
            $appointments = $doctor ? Appointment::findByDoctorId($doctor->id) : [];
            $subtitle = 'Meus Agendamentos';
        } elseif ($user->isPatient()) {
            $patient = Patient::findByUserId($user->id);
            $appointments = $patient ? Appointment::findByPatientId($patient->id) : [];
            $subtitle = 'Meus Agendamentos';
        } elseif ($user->isSecretary()) {
            $secretary = Secretary::findByUserId($user->id);
            $appointments = $secretary ? Appointment::findBySecretaryId($secretary->id) : [];
            $subtitle = 'Agendamentos Criados por Mim';
        } else {
            FlashMessage::danger('Você não tem permissão para acessar agendamentos.');
            $this->redirectTo(route('auth.check'));
            return;
        }

        $title = 'Agendamentos';

        $this->render('appointment/index', compact('title', 'subtitle', 'appointments'));
    }

    public function new(): void
    {
        $user = $this->currentUser();

        if (!$user->isSecretary() && !$user->isAdmin()) {
            FlashMessage::danger('Apenas secretárias ou admins podem criar agendamentos.');
            $this->redirectTo(route('appointments.index'));
            return;
        }

        $appointment = new Appointment();
        $patients = Patient::all();
        $doctors = Doctor::all();
        $title = 'Novo Agendamento';

        $this->render('appointment/new', compact('title', 'appointment', 'patients', 'doctors'));
    }

    public function create(Request $request): void
    {
        $user = $this->currentUser();

        if (!$user->isSecretary() && !$user->isAdmin()) {
            FlashMessage::danger('Você não tem permissão para criar agendamentos.');
            $this->redirectTo(route('appointments.index'));
            return;
        }

        $secretaryId = null;

        if ($user->isSecretary()) {
            $secretary = Secretary::findByUserId($user->id);
            $secretaryId = $secretary ? $secretary->id : null;
        }

        $appointment = new Appointment([
            'patient_id' => $request->getParam('patient_id'),
            'doctor_id' => $request->getParam('doctor_id'),
            'secretary_id' => $secretaryId,
            'scheduled_at' => $request->getParam('scheduled_at'),
            'status' => $request->getParam('status') ?: 'scheduled',
            'observation' => $request->getParam('observation') ?: null,
        ]);

        if ($appointment->save()) {
            FlashMessage::success('Agendamento criado com sucesso!');
            $this->redirectTo(route('appointments.show', ['id' => $appointment->id]));
            return;
        }

        FlashMessage::danger('Erro ao criar agendamento. Verifique os campos.');

        $patients = Patient::all();
        $doctors = Doctor::all();
        $title = 'Novo Agendamento';

        $this->render('appointment/new', compact('title', 'appointment', 'patients', 'doctors'));
    }

    public function show(Request $request): void
    {
        $appointment = $this->findAppointmentOrRedirect($request);

        if (!$appointment) {
            return;
        }

        if (!$this->canAccess($appointment)) {
            FlashMessage::danger('Você não tem permissão para ver este agendamento.');
            $this->redirectTo(route('appointments.index'));
            return;
        }

        $title = 'Agendamento #' . $appointment->id;
        $patient = $appointment->patient();
        $doctor = $appointment->doctor();
        $secretary = $appointment->secretary();

        $this->render('appointment/show', compact('title', 'appointment', 'patient', 'doctor', 'secretary'));
    }

    public function edit(Request $request): void
    {
        $appointment = $this->findAppointmentOrRedirect($request);

        if (!$appointment) {
            return;
        }

        if (!$this->canEdit($appointment)) {
            FlashMessage::danger('Você não tem permissão para editar este agendamento.');
            $this->redirectTo(route('appointments.index'));
            return;
        }

        $patients = Patient::all();
        $doctors = Doctor::all();
        $title = 'Editar Agendamento #' . $appointment->id;

        $this->render('appointment/edit', compact('title', 'appointment', 'patients', 'doctors'));
    }

    public function update(Request $request): void
    {
        $appointment = $this->findAppointmentOrRedirect($request);

        if (!$appointment) {
            return;
        }

        if (!$this->canEdit($appointment)) {
            FlashMessage::danger('Você não tem permissão para atualizar este agendamento.');
            $this->redirectTo(route('appointments.index'));
            return;
        }

        $data = [
            'patient_id' => $request->getParam('patient_id'),
            'doctor_id' => $request->getParam('doctor_id'),
            'scheduled_at' => $request->getParam('scheduled_at'),
            'status' => $request->getParam('status'),
            'observation' => $request->getParam('observation') ?: null,
        ];

        foreach ($data as $key => $value) {
            $appointment->$key = $value;
        }

        if (!$appointment->isValid()) {
            $patients = Patient::all();
            $doctors = Doctor::all();
            $title = 'Editar Agendamento #' . $appointment->id;
            $this->render('appointment/edit', compact('title', 'appointment', 'patients', 'doctors'));
            return;
        }

        $appointment->update($data);
        FlashMessage::success('Agendamento atualizado com sucesso!');
        $this->redirectTo(route('appointments.show', ['id' => $appointment->id]));
    }

    public function destroy(Request $request): void
    {
        $appointment = $this->findAppointmentOrRedirect($request);

        if (!$appointment) {
            return;
        }

        if (!$this->canEdit($appointment)) {
            FlashMessage::danger('Você não tem permissão para excluir este agendamento.');
            $this->redirectTo(route('appointments.index'));
            return;
        }

        if ($appointment->destroy()) {
            FlashMessage::success('Agendamento excluído com sucesso!');
        } else {
            FlashMessage::danger('Não foi possível excluir o agendamento.');
        }

        $this->redirectTo(route('appointments.index'));
    }

    private function findAppointmentOrRedirect(Request $request): ?Appointment
    {
        $id = (int) $request->getParam('id');
        $appointment = Appointment::findById($id);

        if (!$appointment) {
            FlashMessage::danger('Agendamento não encontrado.');
            $this->redirectTo(route('appointments.index'));
            return null;
        }

        return $appointment;
    }

    private function canAccess(Appointment $appointment): bool
    {
        $user = $this->currentUser();

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDoctor()) {
            $doctor = Doctor::findByUserId($user->id);
            return $doctor && (int) $appointment->doctor_id === (int) $doctor->id;
        }

        if ($user->isPatient()) {
            $patient = Patient::findByUserId($user->id);
            return $patient && (int) $appointment->patient_id === (int) $patient->id;
        }

        if ($user->isSecretary()) {
            $secretary = Secretary::findByUserId($user->id);
            return $secretary && (int) $appointment->secretary_id === (int) $secretary->id;
        }

        return false;
    }

    private function canEdit(Appointment $appointment): bool
    {
        $user = $this->currentUser();

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSecretary()) {
            $secretary = Secretary::findByUserId($user->id);
            return $secretary && (int) $appointment->secretary_id === (int) $secretary->id;
        }

        return false;
    }
}
