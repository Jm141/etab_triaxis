<?php
/**
 * Contestant Controller
 */

class ContestantController extends Controller {
    
    public function index($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        if (!$event) {
            die("Event not found");
        }
        
        $contestants = $this->db->fetchAll(
            "SELECT * FROM contestants WHERE event_id = ? ORDER BY contestant_number",
            [$eventId]
        );
        
        $this->view('contestant/index', [
            'event' => $event,
            'contestants' => $contestants
        ]);
    }
    
    public function store($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($eventId);
        
        $errors = $this->validateInput($_POST, [
            'contestant_number' => 'required',
            'name' => 'required|min:2'
        ]);
        
        if (!empty($errors)) {
            Session::set('error_message', implode(', ', $errors));
            $this->redirect('/tabulation/events/' . $eventId . '/contestants');
            return;
        }
        
        // Check if number already exists
        $existing = $this->db->fetchOne(
            "SELECT * FROM contestants WHERE event_id = ? AND contestant_number = ?",
            [$eventId, $_POST['contestant_number']]
        );
        
        if ($existing) {
            Session::set('error_message', 'Contestant number already exists');
            $this->redirect('/tabulation/events/' . $eventId . '/contestants');
            return;
        }
        
        $this->db->query(
            "INSERT INTO contestants (event_id, contestant_number, name, team_name, category, bio, status)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $eventId,
                $_POST['contestant_number'],
                $_POST['name'],
                $_POST['team_name'] ?? null,
                $_POST['category'] ?? null,
                $_POST['bio'] ?? null,
                $_POST['status'] ?? 'Active'
            ]
        );
        
        $this->logAudit('CREATE', 'contestants', $this->db->lastInsertId(), null, $_POST);
        
        Session::set('success_message', 'Contestant added successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/contestants');
    }
    
    public function edit($eventId, $id) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        $contestant = $this->db->fetchOne(
            "SELECT * FROM contestants WHERE id = ? AND event_id = ?",
            [$id, $eventId]
        );
        
        if (!$contestant) {
            die("Contestant not found");
        }
        
        $event = $this->db->fetchOne("SELECT * FROM events WHERE id = ?", [$eventId]);
        
        $this->view('contestant/edit', [
            'event' => $event,
            'contestant' => $contestant
        ]);
    }
    
    public function update($eventId, $id) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($eventId);
        
        $oldValues = $this->db->fetchOne(
            "SELECT * FROM contestants WHERE id = ?",
            [$id]
        );
        
        $this->db->query(
            "UPDATE contestants SET contestant_number = ?, name = ?, team_name = ?, 
             category = ?, bio = ?, status = ?
             WHERE id = ?",
            [
                $_POST['contestant_number'],
                $_POST['name'],
                $_POST['team_name'] ?? null,
                $_POST['category'] ?? null,
                $_POST['bio'] ?? null,
                $_POST['status'] ?? 'Active',
                $id
            ]
        );
        
        $newValues = array_merge($oldValues, $_POST);
        $this->logAudit('UPDATE', 'contestants', $id, $oldValues, $newValues);
        
        Session::set('success_message', 'Contestant updated successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/contestants');
    }
    
    public function delete($eventId, $id) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($eventId);
        
        $this->db->query("DELETE FROM contestants WHERE id = ?", [$id]);
        $this->logAudit('DELETE', 'contestants', $id, null, null);
        
        Session::set('success_message', 'Contestant deleted successfully');
        $this->redirect('/tabulation/events/' . $eventId . '/contestants');
    }
    
    /**
     * Download CSV template for contestant import
     */
    public function downloadTemplate() {
        $templateFile = __DIR__ . '/../templates/contestants_import_template.csv';
        
        if (!file_exists($templateFile)) {
            die("Template file not found");
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="contestants_import_template.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        readfile($templateFile);
        exit;
    }
    
    public function import($eventId) {
        $this->restrictJudges();
        $this->requireEventAccess($eventId);
        
        // Prevent modifications to finished events
        $this->preventFinishedEventModification($eventId);
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Session::set('error_message', 'File upload failed');
            $this->redirect('/tabulation/events/' . $eventId . '/contestants');
            return;
        }
        
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        if ($handle === false) {
            Session::set('error_message', 'Could not open file');
            $this->redirect('/tabulation/events/' . $eventId . '/contestants');
            return;
        }
        
        $imported = 0;
        $skipped = 0;
        
        // Skip header row
        fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 2) continue;
            
            $number = trim($data[0]);
            $name = trim($data[1]);
            $team = isset($data[2]) && !empty(trim($data[2])) ? trim($data[2]) : null;
            $category = isset($data[3]) && !empty(trim($data[3])) ? trim($data[3]) : null;
            $bio = isset($data[4]) && !empty(trim($data[4])) ? trim($data[4]) : null;
            
            if (empty($number) || empty($name)) {
                $skipped++;
                continue;
            }
            
            // Check if exists
            $existing = $this->db->fetchOne(
                "SELECT * FROM contestants WHERE event_id = ? AND contestant_number = ?",
                [$eventId, $number]
            );
            
            if ($existing) {
                $skipped++;
                continue;
            }
            
            $this->db->query(
                "INSERT INTO contestants (event_id, contestant_number, name, team_name, category, bio)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$eventId, $number, $name, $team, $category, $bio]
            );
            
            $imported++;
        }
        
        fclose($handle);
        
        Session::set('success_message', "Imported {$imported} contestants. {$skipped} skipped.");
        $this->redirect('/tabulation/events/' . $eventId . '/contestants');
    }
}


