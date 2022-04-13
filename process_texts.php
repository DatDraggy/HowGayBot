<?php
$json = json_decode(file_get_contents('texts.json'), true);
foreach ($json as &$userjson) {
  if (stripos($userjson['text'], ' %gay%') === false) {
    $userjson['text'] = str_replace('%gay%', ' %gay%', $userjson['text']);
  }
}

file_put_contents('texts.json', json_encode($json));