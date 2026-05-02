<?php

require __DIR__ . '/../../config/bootstrap.php';

use Core\Database\Database;

$passwordHash = password_hash('password', PASSWORD_DEFAULT);

$sql = <<<SQL
INSERT INTO users (name, email, encrypted_password, status, created_at, updated_at) VALUES
('Administrador', 'admin@clin.com', '{$passwordHash}', 1, NOW(), NOW()),
('Mariana Silva', 'secretary@clin.com', '{$passwordHash}', 1, NOW(), NOW()),
('Fernanda Costa', 'doctor@clin.com', '{$passwordHash}', 1, NOW(), NOW()),
('Pedro Oliveira', 'patient@patient.com', '{$passwordHash}', 1, NOW(), NOW());


INSERT INTO secretaries (user_id, cpf, created_at, updated_at) VALUES
(2, '123.456.789-00', NOW(), NOW());

INSERT INTO doctors (user_id, license_number, specialty, created_at, updated_at) VALUES
(3, 'CRM123456', 'Clínica Geral', NOW(), NOW());

INSERT INTO patients (user_id, cpf, birth_date, phone, created_at, updated_at) VALUES
(4, '111.222.333-44', '1980-05-15', '11987654321', NOW(), NOW());
SQL;

Database::exec($sql);
