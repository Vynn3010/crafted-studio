<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

try {
    $db = getDB();
    
    // Simulate what happens in selesai_foto:
    // Pick a booking that is 'dikonfirmasi' and has a fotografer.
    $stmt = $db->query("SELECT id_booking, id_fotografer FROM booking WHERE status = 'dikonfirmasi' AND id_fotografer IS NOT NULL LIMIT 1");
    $b = $stmt->fetch();
    if ($b) {
        echo "Found booking {$b['id_booking']} with fotografer {$b['id_fotografer']}.\n";
        
        $bookingId = $b['id_booking'];
        $fotograferId = $b['id_fotografer'];
        
        $stmt2 = $db->prepare(
            "SELECT id_booking FROM booking WHERE id_booking = ? AND id_fotografer = ? AND status = 'dikonfirmasi'"
        );
        $stmt2->execute([$bookingId, $fotograferId]);
        if ($stmt2->fetch()) {
            echo "Fetch success! Updating status...\n";
            // Simulating update but not really committing or just roll back
            // Actually let's just do it, it's dev environment.
            $db->prepare("UPDATE booking SET status = 'diproses' WHERE id_booking = ?")->execute([$bookingId]);
            echo "Updated status to diproses.\n";
            
            $editorId = getAutoAssignEditor();
            echo "Assigned editor: "; var_dump($editorId);
            
            if ($editorId) {
                echo "Calling createEditingTask...\n";
                createEditingTask($bookingId, $editorId);
                echo "createEditingTask succeeded.\n";
            }
        } else {
            echo "Fetch failed!\n";
        }
    } else {
        echo "No valid booking found to test.\n";
    }

} catch (Exception $e) {
    echo "EXCEPTION THROWN: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
