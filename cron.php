<?php
  set_time_limit(0);

  $app = (getenv("env") == "staging") ? "iptv-sniper-beta" : "iptv-sniper";
  $url = "https://$app.herokuapp.com/";

  $jobs = array(
    $url . "epg.php?json=epg_config.json",
    $url . "epg.php?json=cryogenix.json",
    $url . "link_checker.php"
  );

  $responses = check($jobs);

  foreach ($responses as $response)
    echo $response;

  function check($urls) {
    $mh = curl_multi_init();
    $ch = array();
    $rs = array();

    foreach ($urls as $key => $url) {
      $ch[$url] = curl_init($url);

      curl_setopt($ch[$url], CURLOPT_NOBODY, true);
      curl_setopt($ch[$url], CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch[$url], CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
      curl_setopt($ch[$url], CURLOPT_ENCODING , "");

      curl_multi_add_handle($mh, $ch[$url]);
    }

    $running = null;
    $index = 0;

    do {
      curl_multi_exec($mh, $running);
    }
    while ($running);

    foreach ($ch as $handle) {
      $rs[] = curl_multi_getcontent($handle);
      curl_multi_remove_handle($mh, $handle);
    }

    curl_multi_close($mh);

    return $rs;
  }
//end cron.php