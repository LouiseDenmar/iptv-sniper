<?php
  function test($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_exec($curl);
    $data = curl_getinfo($curl);
    curl_close($curl);
    return $data;
  }

  $url = "https://iptv-sniper.herokuapp.com/localtv.php?channel=16";
  $result = test($url);

  if ($result["http_code"] == "302")
    $result = test($result["redirect_url"]);

  echo ($result["http_code"] == "200") ? $url : "https://raw.githubusercontent.com/benmoose39/YouTube_to_m3u/main/assets/moose_na.m3u";
//end test_stream.php