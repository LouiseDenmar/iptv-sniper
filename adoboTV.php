<?php
  header('Content-type: application/x-gzip');
  header('Content-Disposition: attachment; filename=adoboTV.xml.gz');

  $url = getenv("JAWSDB_MARIA_URL");
  $dbparts = parse_url($url);

  $hostname = $dbparts['host'];
  $username = $dbparts['user'];
  $password = $dbparts['pass'];
  $database = ltrim($dbparts['path'],'/');

  $conn = new mysqli($hostname, $username, $password, $database);

  if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

  $xml = $conn->query("SELECT UNCOMPRESS(file) AS file FROM files WHERE filename='adoboTV.xml' LIMIT 1")
              ->fetch_object()
              ->file;

  $conn->close();
  echo gzencode($xml, 9);
//end adoboTV.php