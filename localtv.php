<?php
  if(!file_exists("localtv_token.txt"))
    include("localtv_auth.php");

  $token = file_get_contents("localtv_token.txt");

  $channel = "http://103.105.213.251:8443" . $token . $_GET["channel"] . ".m3u8";
  $context = stream_context_create(['http'=>['ignore_errors'=>true]]);
  $channel_headers = get_headers($channel);

  if ($channel_headers[0] == "HTTP/1.1 410 Gone") {
    include("localtv_auth.php");
    $token = file_get_contents("localtv_token.txt");
  }

  header("Location: " . $channel);
//end localtv.php