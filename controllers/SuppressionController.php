<?php
require_once 'services/SuppressionImportService.php';
require_once 'models/SuppressionEntry.php';

class SuppressionController
{
    public function importList()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            include 'views/import_form.php';
            return;
        }

        $source = $_REQUEST['source'];
        $service = new SuppressionImportService();
        
        try {
            $count = $service->importFromSource($source);
            $_SESSION['message'] = "Successfully imported $count entries";
        } catch (Exception $e) {
            $_SESSION['error'] = 'Import failed: ' . $e->getMessage();
        }
        
        header('Location: ?action=list');
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
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="suppression_list.csv"');
        
        foreach ($entries as $entry) {
            echo $entry['email'] . ',' . $entry['reason'] . "\n";
        }
    }
}
?>
