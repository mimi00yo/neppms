<?php
include 'connection.php';

$addresses = [
    'aman.kapoor@edoc.com' => 'New Road, Kathmandu',
    'bina.singh@edoc.com' => 'Lakeside, Pokhara',
    'chetan.verma@edoc.com' => 'Jawalakhel, Lalitpur',
    'divya.das@edoc.com' => 'Dharan-12, Sunsari',
    'eklavya.reddy@edoc.com' => 'Butwal-6, Rupandehi',
    'farah.khan@edoc.com' => 'Bharatpur-10, Chitwan'
];

foreach ($addresses as $email => $addr) {
    $stmt = $database->prepare("UPDATE patient SET paddress = ? WHERE pemail = ?");
    $stmt->bind_param("ss", $addr, $email);
    $stmt->execute();
    echo "Updated address for $email to $addr\n";
}
?>
