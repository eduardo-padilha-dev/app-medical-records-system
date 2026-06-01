<?php

namespace App\Services;

use Core\Constants\Constants;
use App\Models\Exam;
use RuntimeException;

class ExamUploadService
{
    /** @var array{tmp_name?: string, error?: int, size?: int, type?: string} */
    private array $file;
    /** @var array{allowed_mimes: array<int, string>, max_size: int} */
    private array $validations = [
        'allowed_mimes' => ['application/pdf'],
        'max_size' => 5 * 1024 * 1024 // 5MB
    ];
    /** @var array<int, string> */
    private array $errors = [];
    private string $generatedFileName = '';

    /** @param array{tmp_name?: string, error?: int, size?: int, type?: string} $file */
    public function processUpload(array $file, Exam $exam): bool
    {
        $this->file = $file;

        if (!$this->isValidUpload()) {
            return false;
        }

        $this->generateUniqueFileName();

        if ($this->moveFile($exam)) {
            $exam->file_path = $this->getRelativeSavedPath($exam);
            return true;
        }

        return false;
    }

    /** @return array<int, string> */
    public function getErrors(): array
    {
        return $this->errors;
    }

    private function moveFile(Exam $exam): bool
    {
        $tempPath = $this->file['tmp_name'];
        if (empty($tempPath)) {
            return false;
        }

        $destinationPath = $this->getAbsoluteDestinationPath($exam);

        $resp = move_uploaded_file($tempPath, $destinationPath);

        if (!$resp) {
            $error = error_get_last();
            throw new RuntimeException('Falha ao mover arquivo de exame: ' . ($error['message'] ?? 'Erro desconhecido'));
        }

        return true;
    }

    private function generateUniqueFileName(): void
    {
        $this->generatedFileName = 'exam_' . uniqid() . '.pdf';
    }

    private function getAbsoluteDestinationPath(Exam $exam): string
    {
        return $this->storeDir($exam) . $this->generatedFileName;
    }

    private function getRelativeSavedPath(Exam $exam): string
    {
        return $this->baseDir($exam) . $this->generatedFileName;
    }

    private function baseDir(Exam $exam): string
    {
        return "/assets/uploads/exams/paciente_{$exam->patient_id}/";
    }

    private function storeDir(Exam $exam): string
    {
        $path = Constants::rootPath()->join('public' . $this->baseDir($exam));
        if (!is_dir($path)) {
            mkdir(directory: $path, recursive: true);
        }
        return $path;
    }

    private function isValidUpload(): bool
    {
        if ($this->file['error'] !== UPLOAD_ERR_OK) {
             $this->errors[] = "Erro interno no upload do arquivo.";
             return false;
        }

        if ($this->file['size'] > $this->validations['max_size']) {
            $this->errors[] = 'O arquivo excede o limite de 5MB.';
            return false;
        }

        $mimeType = $this->file['type'];
        if (!in_array($mimeType, $this->validations['allowed_mimes'])) {
            $this->errors[] = 'Formato inválido. O sistema aceita exclusivamente arquivos PDF.';
            return false;
        }

        return empty($this->errors);
    }
}
