<?php
define('BASEPATH', true);
define('ENVIRONMENT', 'development');
require_once __DIR__ . '/../application/config/database.php';
$cfg = $db['default'];
$conn = @mysqli_connect($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if (!$conn) {
    echo "DB connect failed: " . mysqli_connect_error() . "\n";
    exit(0);
}
$res = mysqli_query($conn, "SHOW COLUMNS FROM help_desk");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " | " . $row['Type'] . " | Null=" . $row['Null'] . " | Default=" . var_export($row['Default'], true) . "\n";
}
