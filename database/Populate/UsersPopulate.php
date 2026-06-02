<?php

namespace Database\Populate;

use App\Models\Admin;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Secretary;
use App\Models\User;

class UsersPopulate
{
    public static function populate(): void
    {
        $passwordHash = password_hash('password', PASSWORD_DEFAULT);
        $numberOfUsers = 3;

        $user1 = new User([
            'name' => 'Administrator',
            'email' => 'admin@clin.com',
            'cpf' => '00000000000',
            'encrypted_password' => $passwordHash           
        ]);
        $user1->save();
        $admin = new Admin([
            'user_id' => $user1->id,
            'phone' => '12345678910',
        ]);
        $admin->save();

        $user2 = new User([
            'name' => 'Mariana Silva',
            'email' => 'secretary@clin.com',
            'cpf' => '11111111111',
            'encrypted_password' => $passwordHash           
        ]);
        $user2->save();
        $secretary = new Secretary(['user_id' => $user2->id]);
        $secretary->save();

        $user3 = new User([
            'name' => 'Fernanda Costa',
            'email' => 'doctor@clin.com',
            'cpf' => '22222222222',
            'encrypted_password' => $passwordHash,
        ]);
        $user3->save();
        $doctor1 = new Doctor([
            'user_id' => $user3->id,
            'license_number' => 'CRM123456',
            'specialty' => 'Clinica Geral',
        ]);
        $doctor1->save();

        $user4 = new User([
            'name' => 'Pedro Oliveira',
            'email' => 'patient@patient.com',
            'cpf' => '33333333333',
            'encrypted_password' => $passwordHash,
        ]);
        $user4->save();
        $patient = new Patient([
            'user_id' => $user4->id,
            'birth_date' => '1980-05-15',
            'phone' => '11987654321',
        ]);
        $patient->save();

        $user5 = new User([
            'name' => 'Roger Oliveira',
            'email' => 'doctor2@clin.com',
            'cpf' => '22322222222',
            'encrypted_password' => $passwordHash,
        ]);
        $user5->save();
        $doctor2 = new Doctor([
            'user_id' => $user5->id,
            'license_number' => 'CRM654321',
            'specialty' => 'teste',
        ]);
        $doctor2->save();

        for ($i = 0; $i < $numberOfUsers; $i++) {
            $secretaryData = [
                'name' => 'Secretary ' . $i . ' Name',
                'email' => 'secretary' . $i . '@clin.com',
                'cpf' => '4444444444' . ($i + 1),
                'encrypted_password' => $passwordHash,
            ];

            $secretaryUser = new User($secretaryData);
            $secretaryUser->save();

            $secretary = new Secretary(['user_id' => $secretaryUser->id]);
            $secretary->save();
        }

        for ($j = 0; $j < $numberOfUsers; $j++) {
            $doctorData = [
                'name' => 'Doctor ' . $j . ' Name',
                'email' => 'doctor' . $j . '@clin.com',
                'cpf' => '5555555555' . ($j + 1),
                'encrypted_password' => $passwordHash,
            ];

            $doctorUser = new User($doctorData);
            $doctorUser->save();

            $doctor = new Doctor([
                'user_id' => $doctorUser->id,
                'license_number' => 'CRM' . (100000 + $j),
                'specialty' => 'General',
            ]);
            $doctor->save();
        }

        for ($k = 0; $k < $numberOfUsers; $k++) {
            $patientData = [
                'name' => 'Patient ' . $k . ' Name',
                'email' => 'patient' . $k . '@clin.com',
                'cpf' => '6666666666' . ($k + 1),
                'encrypted_password' => $passwordHash,
            ];

            $patientUser = new User($patientData);
            $patientUser->save();

            $patient = new Patient([
                'user_id' => $patientUser->id,
                'birth_date' => '1990-01-0' . (($k % 9) + 1),
                'phone' => '1190000000' . ($k + 1),
            ]);
            $patient->save();
        }

        echo "Users populated successfully.\n";

    }
}