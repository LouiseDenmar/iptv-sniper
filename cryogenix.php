<?php
  $type = (isset($_GET["format"]) && $_GET["format"] == "xml") ? "application/xml" : "application/gzip";
  header("Content-Type: $type");

  if ($type == "application/gzip")
    header("Content-Disposition: attachment; filename=cryogenix.xml.gz");

  $url = getenv("JAWSDB_MARIA_URL");
  $dbparts = parse_url($url);

  $hostname = $dbparts["host"];
  $username = $dbparts["user"];
  $password = $dbparts["pass"];
  $database = ltrim($dbparts["path"], "/");

  $conn = new mysqli($hostname, $username, $password, $database);

  if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

  $xml = $conn->query("SELECT UNCOMPRESS(file) AS file FROM files WHERE filename='cryogenix.xml' LIMIT 1")
              ->fetch_object()
              ->file;

  $conn->close();
  echo ($type == "application/gzip") ? gzencode($xml, 9) : $xml;
//end cryogenix.php