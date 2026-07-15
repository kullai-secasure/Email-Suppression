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
        // Validate filename contains no path traversal
        $basename = basename($source);

        // Construct full path within allowed directory
        $fullPath = realpath($this->uploadDirectory . $basename);

        // Verify the resolved path is within allowed directory
        if ($fullPath === false || strpos($fullPath, realpath($this->uploadDirectory)) !== 0) {
            throw new InvalidArgumentException('Invalid file path');
        }

        // Verify it's a regular file, not a URL
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
