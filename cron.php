<?php
  set_time_limit(0);

  $app = (getenv("env") == "staging") ? "iptv-sniper-beta" : "iptv-sniper";
  $url = "https://$app.herokuapp.com/";

  $jobs = array(
    "epg.php?json=epg_config.json",
    "epg.php?json=cryogenix.json",
    "link_checker.php",
  );

  function check($urls) {
    $mh = curl_multi_init();
    $ch = array();
    $rs = array();
    $keys = array();

    foreach ($urls as $key => $url) {
      $ch[$url] = curl_init($url);
      $keys[] = $key;

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

      if ($running)
        curl_multi_select($mh);

      while (($info = curl_multi_info_read($mh)) !== false) {
        $rs[$keys[$index]] = array(
          "status"   => curl_getinfo($info['handle'], CURLINFO_HTTP_CODE),
          "redirect" => curl_getinfo($info['handle'], CURLINFO_REDIRECT_URL)
        );

        $index++;
      }
    }
    while ($running);

    foreach ($ch as $handle)
      curl_multi_remove_handle($mh, $handle);

    curl_multi_close($mh);

    return $rs;
  }
//end cron.php