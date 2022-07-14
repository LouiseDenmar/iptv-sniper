<?php
  if (!file_exists("localtv_token.txt"))
    include("localtv_auth.php");

  $token = file_get_contents("localtv_token.txt");
  $channel = "http://103.105.213.251:8443" . $token . $_GET["channel"] . ".m3u8";
  $channel_headers = get_headers($channel);

  if ($channel_headers[0] == "HTTP/1.1 410 Gone") {
    include("localtv_auth.php");
    $token = file_get_contents("localtv_token.txt");
  }

  header("Location: " . "http://103.105.213.251:8443" . $token . $_GET["channel"] . ".m3u8");
//end localtv.php