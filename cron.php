<?php
  $conn = new mysqli("lcpbq9az4jklobvq.cbetxkdyhwsb.us-east-1.rds.amazonaws.com", "nsvwm2u733p490gm", "kdj0cijcfrceu8gk", "ofagdoh1n25jv0tw");

  if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

  $sql = "CREATE TABLE files (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(30) NOT NULL,
    file text NOT NULL
  )";

  echo ($conn->query($sql) === TRUE) ? "Table files created successfully!" : "Error creating table: " . $conn->error;
  $conn->close();
//end cron.php