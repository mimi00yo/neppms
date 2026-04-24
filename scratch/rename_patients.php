<?php
include 'connection.php';

$updates = [
    'patient@edoc.com' => 'Anupama Rai',
    'sita.thapa2.p@edoc.com' => 'Bimal Thapa',
    'krishna.gurung3.p@edoc.com' => 'Chandra Gurung',
    'goma.magar4.p@edoc.com' => 'Deepak Karki',
    'bikash.rai5.p@edoc.com' => 'Eshani Magar',
    'suman.limbu6.p@edoc.com' => 'Firoz Khan'
];

foreach ($updates as $email => $newName) {
    $stmt = $database->prepare("UPDATE patient SET pname = ? WHERE pemail = ?");
    $stmt->bind_param("ss", $newName, $email);
    if ($stmt->execute()) {
        echo "Updated $email to $newName\n";
    } else {
        echo "Failed to update $email\n";
    }
}
?>
