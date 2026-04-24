<?php
include 'connection.php';

$updates = [
    'patient@edoc.com' => 'Aman Kapoor',
    'sita.thapa2.p@edoc.com' => 'Bina Singh',
    'krishna.gurung3.p@edoc.com' => 'Chetan Verma',
    'goma.magar4.p@edoc.com' => 'Divya Das',
    'bikash.rai5.p@edoc.com' => 'Eklavya Reddy',
    'suman.limbu6.p@edoc.com' => 'Farah Khan'
];

foreach ($updates as $email => $newName) {
    $stmt = $database->prepare("UPDATE patient SET pname = ? WHERE pemail = ?");
    $stmt->bind_param("ss", $newName, $email);
    $stmt->execute();
    echo "Updated $email to $newName\n";
}
?>
