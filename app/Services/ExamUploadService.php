<?php

namespace App\Services;

use Core\Constants\Constants;
use App\Models\Exam;
use RuntimeException;

class ExamUploadService
{
    private array $file;
    private string $generatedFileName = '';

    private array $rules = [
        'extension' => 'pdf',
        'max_size'  => 5 * 1024 * 1024 // 5MB
    ];

    public function __construct(private Exam $exam)
    {
    }

    /** @param array{tmp_name?: string, error?: int, size?: int, type?: string, name?: string} $file */
    public function store(array $file): bool
    {
        $this->file = $file;

        if (!$this->isValidPdf()) {
            return false;
        }

        $this->generatedFileName = 'exam_' . uniqid() . '.pdf';

        if ($this->moveFile()) {
            $this->exam->file_path = $this->getRelativeSavedPath();
            return true;
        }

        return false;
    }

    public function destroyPhysicalFile(): void
    {
        $path = $this->exam->file_path;
        if (empty($path)) {
            return;
        }

        $fullPath = Constants::rootPath()->join('public' . $path);
        if (file_exists($fullPath)) {
            @unlink((string) $fullPath);
        }
    }

    private function moveFile(): bool
    {
        $tempPath = $this->file['tmp_name'] ?? '';
        if (empty($tempPath)) {
            return false;
        }

        $destinationPath = $this->getAbsoluteDestinationPath();
        $resp = move_uploaded_file($tempPath, $destinationPath);

        if (!$resp) {
            $error = error_get_last();
            throw new RuntimeException('Falha ao mover arquivo: ' . ($error['message'] ?? 'Erro desconhecido'));
        }

        return true;
    }

    private function getAbsoluteDestinationPath(): string
    {
        return $this->storeDir() . $this->generatedFileName;
    }

    private function getRelativeSavedPath(): string
    {
        return $this->baseDir() . $this->generatedFileName;
    }

    private function baseDir(): string
    {
        return "/assets/uploads/exams/paciente_{$this->exam->patient_id}/";
    }

    private function storeDir(): string
    {
        $path = Constants::rootPath()->join('public' . $this->baseDir());
        if (!is_dir($path)) {
            mkdir(directory: $path, recursive: true);
        }
        return $path;
    }

    private function isValidPdf(): bool
    {
        if (!isset($this->file['error']) || $this->file['error'] !== UPLOAD_ERR_OK) {
             $this->exam->addError('file', 'Erro no upload ou arquivo não enviado/corrompido.');
             return false;
        }

        $fileNameSplitted  = explode('.', $this->file['name'] ?? '');
        $fileExtension = strtolower(end($fileNameSplitted));
        
        if ($fileExtension !== $this->rules['extension']) {
            $this->exam->addError('file', 'Formato inválido. Apenas PDFs são aceitos.');
        }

        if (($this->file['size'] ?? 0) > $this->rules['max_size']) {
            $this->exam->addError('file', 'O arquivo excede o limite de 5MB.');
        }

        return $this->exam->errors('file') === null;
    }
}
