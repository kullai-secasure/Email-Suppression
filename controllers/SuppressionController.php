<?php
require_once 'services/SuppressionImportService.php';
require_once 'models/SuppressionEntry.php';

class SuppressionController
{
    public function importList()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $csrfToken = $this->issueCsrfToken();
            include 'views/import_form.php';
            return;
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            http_response_code(400);
            $_SESSION['error'] = 'Invalid request token';
            header('Location: ?action=list');
            exit;
        }

        $source = $_POST['source'] ?? '';
        $service = new SuppressionImportService();

        try {
            $count = $service->importFromSource($source);
            $_SESSION['message'] = "Successfully imported $count entries";
        } catch (Exception $e) {
            $_SESSION['error'] = 'Import failed';
        }

        header('Location: ?action=list');
        exit;
    }

    public function showList()
    {
        $model = new SuppressionEntry();
        $entries = $model->getAll($_SESSION['user_id']);
        include 'views/suppression_list.php';
    }

    public function exportList()
    {
        $model = new SuppressionEntry();
        $entries = $model->getAll($_SESSION['user_id']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="suppression_list.csv"');
        header('X-Content-Type-Options: nosniff');

        $output = fopen('php://output', 'w');
        foreach ($entries as $entry) {
            fputcsv($output, [
                $this->sanitizeCsvField($entry['email']),
                $this->sanitizeCsvField($entry['reason']),
            ]);
        }
        fclose($output);
    }

    private function issueCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private function validateCsrfToken($token)
    {
        $valid = is_string($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
        if ($valid) {
            unset($_SESSION['csrf_token']);
        }
        return $valid;
    }

    private function sanitizeCsvField($value)
    {
        $value = (string) $value;
        $firstChar = mb_substr($value, 0, 1, 'UTF-8');
        if ($firstChar !== '' && in_array($firstChar, ['=', '+', '-', '@', "\t", "\r"], true)) {
            $value = "'" . $value;
        }
        return $value;
    }
}
?>
