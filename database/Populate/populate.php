<?php

require __DIR__ . '/../../config/bootstrap.php';

use Core\Database\Database;

$passwordHash = password_hash('password', PASSWORD_DEFAULT);

$sql = <<<SQL
INSERT INTO users (name, email, cpf, encrypted_password, status, created_at, updated_at) VALUES
('Administrador', 'admin@clin.com', '00000000000', '{$passwordHash}', 1, NOW(), NOW()),
('Mariana Silva', 'secretary@clin.com', '11111111111', '{$passwordHash}', 1, NOW(), NOW()),
('Fernanda Costa', 'doctor@clin.com', '22222222222', '{$passwordHash}', 1, NOW(), NOW()),
('Pedro Oliveira', 'patient@patient.com', '33333333333', '{$passwordHash}', 1, NOW(), NOW());

INSERT INTO admins (user_id, phone, created_at, updated_at) VALUES
(1, '12345678910' , NOW(), NOW());

INSERT INTO secretaries (user_id, created_at, updated_at) VALUES
(2, NOW(), NOW());

INSERT INTO doctors (user_id, license_number, specialty, created_at, updated_at) VALUES
(3, 'CRM123456', 'Clínica Geral', NOW(), NOW());

INSERT INTO patients (user_id, birth_date, phone, created_at, updated_at) VALUES
(4, '1980-05-15', '11987654321', NOW(), NOW());
SQL;

Database::exec($sql);
