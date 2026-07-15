<?php
require_once 'models/SuppressionEntry.php';

class SuppressionImportService
{
    private $model;
    private $uploadDirectory = '/var/www/uploads/suppression/';

    public function __construct()
    {
        $this->model = new SuppressionEntry();
    }

    public function importFromSource($source)
    {
        $basename = basename($source);

        $allowedDir = realpath($this->uploadDirectory);
        if ($allowedDir === false) {
            throw new InvalidArgumentException('Invalid file path');
        }
        $allowedPrefix = $allowedDir . DIRECTORY_SEPARATOR;

        $candidate = $allowedPrefix . $basename;
        if (is_link($candidate)) {
            throw new InvalidArgumentException('Invalid file path');
        }

        $fullPath = realpath($candidate);
        if ($fullPath === false || strncmp($fullPath, $allowedPrefix, strlen($allowedPrefix)) !== 0) {
            throw new InvalidArgumentException('Invalid file path');
        }

        if (!is_file($fullPath)) {
            throw new InvalidArgumentException('File not found');
        }

        $lines = file($fullPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new Exception('Failed to read suppression list');
        }
        return $this->parseAndStore($lines);
    }

    private function parseAndStore($lines)
    {
        $imported = 0;
        foreach ($lines as $line) {
            $data = str_getcsv($line);
            if (filter_var($data[0], FILTER_VALIDATE_EMAIL)) {
                $this->model->addEntry($data[0], $data[1] ?? 'imported');
                $imported++;
            }
        }
        return $imported;
    }
}
?>
