<?php
  ini_set('default_socket_timeout', 1);

  if(!$fp = @fopen("https://iptv-sniper.herokuapp.com/localtv.php?channel=808", "r")) {
    echo "no stream";
  }
  else {
    echo "streaming";
    fclose($fp);
  }
//end test_stream.php