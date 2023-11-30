<?php
$dom = new DOMDocument();
$dom->load('xml.xml');
//$dom->formatOutput = true;
//echo $dom->saveXML();

//var_dump($dom);
//echo htmlentities($dom->textContent);

$arr = array();
 $i = $dom->documentElement;
  foreach ($i->childNodes AS $item) {
    $arr[] = $item->textContent;
  }
  //var_dump($arr[1]);
  echo htmlentities($arr[1]);

?>