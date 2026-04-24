<?php
include "connection.php";
echo "Starting test...\n";

$_POST = [
    'name' => 'Test Doctor',
    'nic' => '000000000',
    'oldemail' => 'doctor@edoc.com',
    'spec' => 1,
    'email' => 'doctor@edoc.com',
    'Tele' => '0110000000',
    'password' => '123',
    'cpassword' => '123',
    'id00' => 1
];

$file_path = __DIR__.'/img/test_avatar.jpg';
echo "File exists? " . (file_exists($file_path) ? "Yes" : "No") . "\n";

$_FILES = [
    'profile_image' => [
        'name' => 'test_avatar.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => $file_path,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($file_path)
    ]
];

// include edit-doc instead of hitting it via curl to see its internal states easily, but wait!
// edit-doc uses `move_uploaded_file` which only works on files uploaded via HTTP POST! It will fail internally if we fake it this way!
// Let me use `curl` instead but with `-v` to spot errors, or inspect the script manually.
