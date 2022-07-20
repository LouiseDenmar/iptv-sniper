<?php
  header("Content-Type: video/vnd.mpegurl");
  header("Content-Disposition: attachment; filename=tv_channels.m3u");

  $url = getenv("JAWSDB_MARIA_URL");
  $dbparts = parse_url($url);

  $hostname = $dbparts['host'];
  $username = $dbparts['user'];
  $password = $dbparts['pass'];
  $database = ltrim($dbparts['path'],'/');

  $conn = new mysqli($hostname, $username, $password, $database);

  if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

  echo $conn->query("SELECT UNCOMPRESS(file) AS file FROM files WHERE filename='tv_channels.m3u' LIMIT 1")
            ->fetch_object()
            ->file;

  $conn->close();
//end tv_channels.php