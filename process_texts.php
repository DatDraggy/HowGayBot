<?php
require_once(__DIR__ . '/funcs.php');
require_once(__DIR__ . '/config.php');

$json = json_decode(file_get_contents('texts.json'), true);
foreach ($json as $userId => &$userjson) {
  setCustomMessage($userId, $userjson['text']);
}
