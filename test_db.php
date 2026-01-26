<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable('.');
$dotenv->load();
echo 'Server: ' . $_ENV['servername'] . ', User: ' . $_ENV['username'] . ', DB: ' . $_ENV['dbname'] . PHP_EOL;
$conn = new mysqli($_ENV['servername'], $_ENV['username'], $_ENV['password']);
if ($conn->connect_error) {
  echo 'Error connecting to server: ' . $conn->connect_error;
} else {
  echo 'Connected to server' . PHP_EOL;
  $result = $conn->query("SHOW DATABASES");
  if ($result) {
    echo 'Databases:' . PHP_EOL;
    while ($row = $result->fetch_row()) {
      echo $row[0] . PHP_EOL;
    }
    $result->free();
  } else {
    echo 'Error listing databases: ' . $conn->error;
  }
  $conn->close();
}
?>