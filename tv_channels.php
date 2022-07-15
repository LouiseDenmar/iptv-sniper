<?php
//   header ("Content-Type: video/vnd.mpegurl");
//   header ("Content-Disposition: attachment;filename=tv_channels.m3u");
//   header ("Pragma: no-cache");
//   header ("Expires: 0");

  $m3u = json_decode(file_get_contents("channels.json"));
  ini_set('default_socket_timeout', 1);
?>
#EXTM3U url-tvg="<?php echo $m3u->epg; ?>"

<?php foreach ($m3u->channels as $channel): ?>
#EXTINF:-1 tvg-id="<?php echo $channel->id; ?>" tvg-name="<?php echo $channel->name; ?>" tvg-logo="<?php echo $channel->logo; ?>" group-title="<?php echo $channel->group; ?>",<?php echo $channel->name . "\n"; ?>
<?php
  $ctr = 0;

  foreach ($channel->sources as $source) {
    if ($fp = @fopen($source, "r")){
      echo $source . "\n";
      break;
    }
    else {
      $ctr++;
      continue;
    }
    echo $ctr . "\n";
  }
?>
<?php endforeach; ?>