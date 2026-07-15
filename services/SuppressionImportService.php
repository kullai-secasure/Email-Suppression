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

    public function importFromSource($source, $userId)
    {
        $basename = basename($source);

        $allowedDir = realpath($this->uploadDirectory);
        if ($allowedDir === false) {
            throw new InvalidArgumentException('Invalid file path');
        }
        $allowedPrefix = $allowedDir . DIRECTORY_SEPARATOR;

        $candidate = $allowedPrefix . $basename;
        clearstatcache(true, $candidate);
        if (is_link($candidate)) {
            throw new InvalidArgumentException('Invalid file path');
        }

        $fullPath = realpath($candidate);
        if ($fullPath === false || strncmp($fullPath, $allowedPrefix, strlen($allowedPrefix)) !== 0) {
            throw new InvalidArgumentException('Invalid file path');
        }

        $handle = fopen($fullPath, 'rb');
        if ($handle === false) {
            throw new InvalidArgumentException('File not found');
        }

        $descriptorStat = fstat($handle);
        clearstatcache(true, $fullPath);
        $linkStat = lstat($fullPath);
        if ($linkStat === false
            || ($descriptorStat['mode'] & 0170000) !== 0100000
            || $descriptorStat['ino'] !== $linkStat['ino']
            || $descriptorStat['dev'] !== $linkStat['dev']) {
            fclose($handle);
            throw new InvalidArgumentException('Invalid file path');
        }

        $lines = [];
        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line !== '') {
                $lines[] = $line;
            }
        }
        fclose($handle);

        return $this->parseAndStore($lines, $userId);
    }

    private function parseAndStore($lines, $userId)
    {
        $imported = 0;
        foreach ($lines as $line) {
            $data = str_getcsv($line);
            if (filter_var($data[0], FILTER_VALIDATE_EMAIL)) {
                $this->model->addEntry($data[0], $userId, $this->sanitizeReason($data[1] ?? 'imported'));
                $imported++;
            }
        }
        return $imported;
    }

    private function sanitizeReason($reason)
    {
        $reason = strip_tags((string) $reason);
        $reason = preg_replace('/[\x00-\x1F\x7F]/', '', $reason);
        $reason = trim(mb_substr($reason, 0, 255, 'UTF-8'));
        return $reason === '' ? 'imported' : $reason;
    }
}
?>
