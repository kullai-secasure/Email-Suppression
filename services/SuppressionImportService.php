<?php
require_once 'models/SuppressionEntry.php';

class SuppressionImportService
{
    private $model;

    public function __construct()
    {
        $this->model = new SuppressionEntry();
    }

    public function importFromSource($source)
    {
        $lines = file($source, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
