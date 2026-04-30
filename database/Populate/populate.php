<?php

require __DIR__ . '/../../config/bootstrap.php';

use Core\Database\Database;

Database::exec(<<<'SQL'
INSERT INTO users (name, email, encrypted_password, status, created_at, updated_at) VALUES
('Administrador', 'admin@clinica.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/LLG', 1, NOW(), NOW()),
('Mariana Silva', 'mariana@clinica.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/LLG', 1, NOW(), NOW()),
('Fernanda Costa', 'fernanda@clinica.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/LLG', 1, NOW(), NOW()),
('Dr. João Paulo', 'joao.paulo@clinica.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/LLG', 1, NOW(), NOW()),
('Dr. Ana Beatriz', 'ana.beatriz@clinica.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/LLG', 1, NOW(), NOW()),
('Dr. Carlos Mendes', 'carlos.mendes@clinica.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/LLG', 1, NOW(), NOW()),
('Pedro Oliveira', 'pedro@paciente.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/LLG', 1, NOW(), NOW()),
('Maria Santos', 'maria@paciente.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/LLG', 1, NOW(), NOW()),
('Lucas Ferreira', 'lucas@paciente.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/LLG', 1, NOW(), NOW()),
('Juliana Rocha', 'juliana@paciente.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/LLG', 1, NOW(), NOW()),
('Roberto Alves', 'roberto@paciente.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/LLG', 1, NOW(), NOW());

INSERT INTO secretaries (user_id, cpf, created_at, updated_at) VALUES
(2, '123.456.789-00', NOW(), NOW()),
(3, '987.654.321-00', NOW(), NOW());

INSERT INTO doctors (user_id, license_number, specialty, created_at, updated_at) VALUES
(4, 'CRM123456', 'Clínica Geral', NOW(), NOW()),
(5, 'CRM123457', 'Cardiologia', NOW(), NOW()),
(6, 'CRM123458', 'Pneumologia', NOW(), NOW());

INSERT INTO patients (user_id, cpf, birth_date, phone, created_at, updated_at) VALUES
(7, '111.222.333-44', '1980-05-15', '11987654321', NOW(), NOW()),
(8, '222.333.444-55', '1992-11-22', '11998765432', NOW(), NOW()),
(9, '333.444.555-66', '1975-03-10', '11976543210', NOW(), NOW()),
(10, '444.555.666-77', '1988-07-30', '11988765432', NOW(), NOW()),
(11, '555.666.777-88', '1995-12-05', '11997654321', NOW(), NOW());
SQL);
