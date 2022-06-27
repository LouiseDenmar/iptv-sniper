<?php
  if (isset($_GET["debug"])) {
    header("Content-Type: audio/x-mpegurl");
    header("Content-Disposition: attachment; filename=iptv-org.m3u");
  }

  $countries = isset($_GET["country"]) ? str_getcsv($_GET['country']) : str_getcsv("us,uk,ca,au,hk,sg,ph");
  $quality   = isset($_GET["quality"]) ? str_getcsv($_GET["quality"]) : str_getcsv("0,240,480,720,1080,2160,4320");
  $nsfw      = isset($_GET["nsfw"]) ? $_GET["nsfw"] : 0;
  $debug = isset($_GET["debug"]) ? $_GET["debug"] : 1;

  $streams_api     = fetch("https://iptv-org.github.io/api/streams.json'");
  $channels        = json_decode($streams_api);
  $online_channels = array();

  foreach ($channels as $channel) {
    if ($channel->status == "online" && in_array(substr($channel->channel, -2), $countries) && property_exists($channel, "height") && in_array($channel->height, $quality))
      $online_channels[$channel->channel] = $channel;
  }

  // $channels_api = fetch('https://iptv-org.github.io/api/channels.json');
  // $channels     = json_decode($channels_api);
  // $channel_info = array();

  // foreach ($channels as $channel) {
  //   if ($channel->is_nsfw == $nsfw && array_key_exists($channel->id, $online_channels)) {
  //     $channel_info[$channel->id] = (object) array_merge((array) $channel, (array) $online_channels[$channel->id]);
  //     $channel_info[$channel->id]->stream_url = $online_channels[$channel->id]->url;
  //   }
  // }

  // $guides_api = fetch('https://iptv-org.github.io/api/guides.json');
  // $guides     = json_decode($guides_api);

  // foreach ($guides as $guide) {
  //   if (array_key_exists($guide->channel, $channel_info)) {
  //       $channel_info[$guide->channel] = (object) array_merge((array) $channel_info[$guide->channel], (array) $guide);
  //       $channel_info[$guide->channel]->guide_url = $guide->url;
  //   }
  // }

  // $tvg_urls = array();

  // foreach ($channel_info as $channel) {
  //   if(property_exists($channel, "guide_url") && !in_array($channel->guide_url, $tvg_urls))
  //     $tvg_urls[] = $channel->guide_url;
  // }

  if ($debug == true)
    die("<pre>" . print_r($online_channels, true) . "</pre>");

  function fetch($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
  }
