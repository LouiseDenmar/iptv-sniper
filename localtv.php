<?php
  $token = file_get_contents("localtv_token.txt");
  header("Location: http://103.105.213.251:8443" . $token . $_GET["channel"] . ".m3u8");
//end localtv.php