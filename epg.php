<?php
  require 'EpgParser.php';

  $url = "https://iptv-org.github.io/epg/guides/ph/clickthecity.com.epg.xml";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_URL, $url);
  $tvg = curl_exec($ch);
  curl_close($ch);

  $epgs = new EpgParser($tvg);
  die("<pre>" . print_r($epgs->array, true) . "</pre>");
//end epg.php