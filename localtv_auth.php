<?php
  $auth_server = file_get_contents('https://ersupport.com/plugins/QuickWebProxy/miniProxy.php/https://fp.visualsec.net:8445/player_api.php?username=phc_authserver&password=Qwertyuiop321&action=get_live_streams');
  preg_match('/103.105.213.251:8443(.*?)3.m3u8/s', $auth_server, $matches);
  $token = preg_replace('/\\\\/', '', $matches[1]);
  file_put_contents("localtv_token.txt", $token);
//end localtv_auth.php