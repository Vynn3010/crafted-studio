<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

try {
    $db = getDB();
    
    // 1. Check getAutoAssignEditor
    echo "1. Testing getAutoAssignEditor():\n";
    $editor = getAutoAssignEditor();
    var_dump($editor);
    
    // 2. Check bookings for 'dikonfirmasi' status
    echo "\n2. Bookings with 'dikonfirmasi' status:\n";
    $stmt = $db->query("SELECT id_booking, id_fotografer, status FROM booking WHERE status = 'dikonfirmasi'");
    print_r($stmt->fetchAll());
    
    // 3. Test the update query
    echo "\n3. Testing if we can find them for selesai_foto:\n";
    $stmt = $db->query("SELECT id_booking FROM booking WHERE status = 'dikonfirmasi'");
    $b = $stmt->fetch();
    if ($b) {
        $stmt2 = $db->prepare("SELECT id_booking FROM booking WHERE id_booking = ? AND id_fotografer = ? AND status = 'dikonfirmasi'");
        $stmt2->execute([$b['id_booking'], $b['id_fotografer']]);
        echo "Found with precise query: ";
        var_dump($stmt2->fetch());
    } else {
        echo "No dikonfirmasi bookings found.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
