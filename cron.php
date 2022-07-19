<?php
  set_time_limit(0);

  $app = (getenv("env") == "staging") ? "iptv-sniper-beta" : "iptv-sniper";
  $url = "https://$app.herokuapp.com/";

  $jobs = array(
    "epg.php?json=epg_config.json",
    "epg.php?json=cryogenix.json",
    "link_checker.php",
  );

  function fetch($ch, $url) {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
  }
//end cron.php