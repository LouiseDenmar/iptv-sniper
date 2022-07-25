<?php
  require __DIR__ . "/../vendor/autoload.php";

  $parser = new \Smalot\PdfParser\Parser();
  $pdf = $parser->parseContent(file_get_contents("https://img.mytfc.com/cmsroot/abscms/media/mytfctv/grids/2022/tfc/jul/tfc-tv-guide-guam-" . rangeWeek(date("Y-m-d")) . ".pdf"));

  $text = $pdf->getPages()[0]->getDataTm();;
  die("<pre>" . print_r($text, true) . "</pre>");

  function rangeWeek($datestr) {
    date_default_timezone_set(date_default_timezone_get());
    $dt = strtotime($datestr);
    $start = (date('N', $dt) == 6) ? date('Fd-', $dt) : date('Fd-', strtotime('last saturday', $dt));
    $end   = (date('N', $dt) == 5) ? date('d', $dt) : date('d', strtotime('next friday', $dt));
    return (strtolower($start) . $end);
  }
//   echo rangeWeek("2022-07-22") . "<br>";
//   echo rangeWeek("2022-07-23") . "<br>";
//   echo rangeWeek("2022-07-24") . "<br>";
//   echo rangeWeek("2022-07-25") . "<br>";
//   echo rangeWeek("2022-07-26") . "<br>";
//   echo rangeWeek("2022-07-27") . "<br>";
//   echo rangeWeek("2022-07-28") . "<br>";
//   echo rangeWeek("2022-07-29") . "<br>";
//   echo rangeWeek("2022-07-30");
//end tfc.php