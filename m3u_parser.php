<?php
  header('Content-Type: application/json');

  $m3ufile = (isset($_GET["url"])) ? file_get_contents($_GET["url"]) : file_get_contents("https://raw.githubusercontent.com/jmvbambico/iptv-sniper/master/channels.m3u");

  $re = '/#EXTINF:(.+?)[,]\s?(.+?)[\r\n]+?((?:https?|rtmp):\/\/(?:\S*?\.\S*?)(?:[\s)\[\]{};"\'<]|\.\s|$))/';
  $attributes = '/([a-zA-Z0-9\-\_]+?)="([^"]*)"/';

  $m3ufile = str_replace('tvg-logo', 'logo', $m3ufile);
  $m3ufile = str_replace('tvg-id', 'id', $m3ufile);
  $m3ufile = str_replace('tvg-name', 'name', $m3ufile);
  $m3ufile = str_replace('group-title', 'group', $m3ufile);

  preg_match_all($re, $m3ufile, $matches);

  $ctr = 1;
  $items = array();

  foreach($matches[0] as $list) {
    preg_match($re, $list, $matchList);

    $mediaURL = preg_replace("/[\n\r]/","",$matchList[3]);
    $mediaURL = preg_replace('/\s+/', '', $mediaURL);

    preg_match_all($attributes, $list, $matches, PREG_SET_ORDER);

    foreach ($matches as $match)
      $newdata[$match[1]] = $match[2];

    foreach ($matches as $match) {
      $newdata["number"] = $ctr;
      $newdata["sources"] = array($mediaURL);
    }

    $items[] = $newdata;
    $ctr++;
  }

  $globalist = array(
      "epg" => "https://iptv-sniper.herokuapp.com/iptv-sniper.xml.gz",
      "channels" => $items
  );

  file_put_contents("channels.json", json_encode($globalist, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
//end m3u_parser.php