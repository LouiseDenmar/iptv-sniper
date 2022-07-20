<?php
  $auth_server = file_get_contents(getenv("FP_AUTH_URL"));

  preg_match('/103.105.213.251:8443(.*?)3.m3u8/s', $auth_server, $matches);

  $token = preg_replace('/\\\\/', "", $matches[1]);

  $url = getenv("JAWSDB_MARIA_URL");
  $dbparts = parse_url($url);

  $hostname = $dbparts["host"];
  $username = $dbparts["user"];
  $password = $dbparts["pass"];
  $database = ltrim($dbparts["path"], "/");

  $conn = new mysqli($hostname, $username, $password, $database);

  if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

  $token = mysqli_real_escape_string($conn, $token);
  $sql = "INSERT INTO token (id, token) VALUES (1, '$token') ON DUPLICATE KEY UPDATE token=VALUES(token)";
  $result = $conn->query($sql);
  $conn->close();
  echo ($result === TRUE) ? "[FP Token Updater] Auth token for local tv has been successfully updated.\n" : $conn->error;
//end localtv_auth.php