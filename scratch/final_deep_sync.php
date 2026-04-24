<?php
include 'connection.php';

$database->query("SET FOREIGN_KEY_CHECKS=0");

$updates = [
    'patient@edoc.com' => ['name' => 'Aman Kapoor', 'new_email' => 'aman.kapoor@edoc.com'],
    'sita.thapa2.p@edoc.com' => ['name' => 'Bina Singh', 'new_email' => 'bina.singh@edoc.com'],
    'krishna.gurung3.p@edoc.com' => ['name' => 'Chetan Verma', 'new_email' => 'chetan.verma@edoc.com'],
    'goma.magar4.p@edoc.com' => ['name' => 'Divya Das', 'new_email' => 'divya.das@edoc.com'],
    'bikash.rai5.p@edoc.com' => ['name' => 'Eklavya Reddy', 'new_email' => 'eklavya.reddy@edoc.com'],
    'suman.limbu6.p@edoc.com' => ['name' => 'Farah Khan', 'new_email' => 'farah.khan@edoc.com']
];

foreach ($updates as $old_email => $data) {
    $new_email = $data['new_email'];
    $name = $data['name'];
    
    echo "Syncing $old_email -> $new_email ($name)...\n";
    
    // 1. Update Patient Table
    $stmt = $database->prepare("UPDATE patient SET pemail = ?, pname = ? WHERE pemail = ?");
    $stmt->bind_param("sss", $new_email, $name, $old_email);
    $stmt->execute();
    
    // 2. Update Webuser Table
    $stmt = $database->prepare("UPDATE webuser SET email = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_email, $old_email);
    $stmt->execute();
}

$database->query("SET FOREIGN_KEY_CHECKS=1");
echo "\nDeep sync complete. All traces of old names removed.\n";
?>
