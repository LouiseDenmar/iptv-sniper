<?php
  if (!file_exists("localtv_token.txt"))
    include("localtv_auth.php");

  $token = get_token();
  $channel = "http://103.105.213.251:8443" . $token . $_GET["channel"] . ".m3u8";
  $channel_headers = get_headers($channel);

  if ($channel_headers[0] == "HTTP/1.1 410 Gone") {
    include("localtv_auth.php");
    $token = get_token();
  }

  header("Location: " . "http://103.105.213.251:8443" . $token . $_GET["channel"] . ".m3u8");

  function get_token() {
    $url = getenv("JAWSDB_MARIA_URL");
    $dbparts = parse_url($url);

    $hostname = $dbparts['host'];
    $username = $dbparts['user'];
    $password = $dbparts['pass'];
    $database = ltrim($dbparts['path'],'/');

    $conn = new mysqli($hostname, $username, $password, $database);

    if ($conn->connect_error)
      die("Connection failed: " . $conn->connect_error);

    $token = $conn->query("SELECT token FROM token WHERE id=1 LIMIT 1")
                  ->fetch_object()
                  ->token;

    $conn->close();
    return $token;
  }
//end localtv.php