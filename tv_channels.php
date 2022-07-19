<?php
  $sql = "SELECT file FROM files WHERE filename='tv_channels.m3u'";
  $result = query($sql);
  $m3u = $result->fetch_row();
  echo gzuncompress($m3u[0]);

  function query($sql) {
    $url = getenv('JAWSDB_MARIA_URL');
    $dbparts = parse_url($url);

    $hostname = $dbparts['host'];
    $username = $dbparts['user'];
    $password = $dbparts['pass'];
    $database = ltrim($dbparts['path'],'/');

    $conn = new mysqli($hostname, $username, $password, $database);

    if ($conn->connect_error)
      die("Connection failed: " . $conn->connect_error);

    $result = $conn->query($sql);
    $conn->close();
    return ($result === TRUE) ? $result : $conn->error;
  }
//end tv_channels.php