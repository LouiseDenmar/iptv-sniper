<?php
  $url = getenv("JAWSDB_MARIA_URL");
  $dbparts = parse_url($url);

  $hostname = $dbparts['host'];
  $username = $dbparts['user'];
  $password = $dbparts['pass'];
  $database = ltrim($dbparts['path'],'/');

  $conn = new mysqli($hostname, $username, $password, $database);

  if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

  $setup = array(
    "File Storage" => "CREATE TABLE files (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, filename VARCHAR(30) NOT NULL, file BLOB NOT NULL)",
    "Token Manager" => "CREATE TABLE token (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, token VARCHAR(40) NOT NULL)"
  );

  foreach ($setup as $key => $sql) {
    $result = $conn->query($sql);
    echo ($result === TRUE) ? "Success setting up: $key...<br>" : $conn->error;
  }

  $conn->close();
//end db_init.php