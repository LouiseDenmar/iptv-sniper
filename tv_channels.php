<?php
  require __DIR__ . '/vendor/autoload.php';

  use Amp\Parallel\Worker;
  use Amp\Promise;

  // file_put_contents("nette.safe://tv_channels.m3u", $m3u);

  $m3u_source = json_decode(file_get_contents("nette.safe://channels.json"));
  $promises = [];

  foreach ($m3u_source->channels as $channel) {
    // $m3u = "#EXTM3U url-tvg=\"$m3u_source->epg\"\n\n";

    foreach ($channel->sources as $source) {
      $promises[$source] = Amp\call(function() use ($client, $source) {
          // "yield" inside a coroutine awaits the resolution of the promise
          // returned from Client::request(). The generator is then continued.
          $response = yield $client->request($url);

          // Same for the body here. Yielding an Amp\ByteStream\Message
          // buffers the entire message.
          $body = yield $response->getBody();
          echo $url;
          return $url;
      )}
    }
  }

  foreach ($promises as $url => $header) {
    echo $url . " - " . print_r($header, true) . "\n\n";
  }

  // foreach ($m3u_source->channels as $channel) {
  //   $m3u = "#EXTINF:-1 tvg-id=\"$channel->id\" tvg-name=\"$channel->name\" tvg-logo=\"$channel->logo\" group-title=\"$channel->group\",$channel->name\n";
  // }

  // foreach ($m3u_source->channels as $channel) {
  //   $m3u = "#EXTINF:-1 tvg-id=\"$channel->id\" tvg-name=\"$channel->name\" tvg-logo=\"$channel->logo\" group-title=\"$channel->group\",$channel->name\n";

  //   foreach ($channel->sources as $source) {
  //     $arguments = array("source" => $source, "m3u" => $m3u);

  //     Loop::run(static function () use ($arguments) {
  //       try {
  //         $client = HttpClientBuilder::buildDefault();

  //         $request = new Request($arguments["source"]);
  //         $response = yield $client->request($request);

  //         dumpRequestTrace($response->getRequest());
  //         dumpResponseTrace($response);
  //       }
  //       catch (HttpException $error) {
  //         echo $error;
  //       }
  //     });

  //     $jobId = $threader->thread(function($arguments) {
  //       $curl = curl_init();
  //       curl_setopt($curl, CURLOPT_URL, $arguments["source"]);
  //       curl_setopt($curl, CURLOPT_NOBODY, true);
  //       curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  //       curl_exec($curl);

  //       $result = curl_getinfo($curl);

  //       curl_close($curl);

  //       if ($result["http_code"] == "302") {
  //         $curl = curl_init();
  //         curl_setopt($curl, CURLOPT_URL, $result["redirect_url"]);
  //         curl_setopt($curl, CURLOPT_NOBODY, true);
  //         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  //         curl_exec($curl);

  //         $result = curl_getinfo($curl);

  //         curl_close($curl);
  //       }

  //       $arguments["m3u"] .= ($result["http_code"] == "200") ? $arguments["source"] . "\n\n" : "https://raw.githubusercontent.com/benmoose39/YouTube_to_m3u/main/assets/moose_na.m3u\n\n";
  //       file_put_contents("tv_channels.m3u", $arguments["m3u"]);
  //     });
  //   }
  // }
//end tv_channels.php