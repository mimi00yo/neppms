<?php
include("C:/xampp/htdocs/edoc/connection.php");
$res = $database->query("SHOW TABLES");
while($row = $res->fetch_row()){
    echo $row[0]."\n";
}
?>
