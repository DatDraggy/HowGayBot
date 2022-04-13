<?php
function sendMessage($chatId, $text, $replyTo = '', $replyMarkup = '') {
  global $config;
  $data = array(
    'disable_web_page_preview' => true,
    'parse_mode' => 'html',
    'chat_id' => $chatId,
    'text' => $text,
    'reply_to_message_id' => $replyTo,
    'reply_markup' => $replyMarkup
  );
  makeApiRequest('sendMessage', $data);
}

function leaveChat($chatId) {
  global $config;
  $data = array(
    'chat_id' => $chatId
  );
  makeApiRequest('leaveChat', $data);
}

function makeApiRequest($method, $data) {
  global $config, $client;
  if (!($client instanceof \GuzzleHttp\Client)) {
    $client = new \GuzzleHttp\Client(['base_uri' => $config['url']]);
  }
  try {
    $response = $client->request('POST', $method, array('json' => $data));
  } catch (\GuzzleHttp\Exception\BadResponseException $e) {
    $body = $e->getResponse()->getBody()->getContents();
    $json = json_decode($body);
    if (ignoreError($json)) {
      return false;
    }
    mail($config['mail'], 'Error', print_r($body, true) . "\n" . print_r($data, true) . "\n" . __FILE__);
    return false;
  }
  return json_decode($response->getBody(), true)['result'];
}

function ignoreError($json) {
  //Not the coolest but oh well, it works for now
  $descriptions = [
      'Bad Request: query is too old and response timeout expired or query ID is invalid'
  ];

  if (in_array($json->description, $descriptions)) {
    return true;
  }
  return false;
}

function answerInlineQuery($inlineQueryId, $results, $offset) {
  $data = array(
    'inline_query_id' => $inlineQueryId,
    'results' => $results,
    'cache_time' => 60,
    'is_personal' => true
  );
  return makeApiRequest('answerInlineQuery', $data);
}

function setCustomMessage($userId, $text) {
  $json = json_decode(file_get_contents('texts.json'), true);
  $json[$userId]['text'] = $text;
  file_put_contents('texts.json', json_encode($json));
}

function getCustomMessage($userId) {
  $customMsgs = json_decode(file_get_contents('texts.json'), true);
  if (isset($customMsgs[$userId])) {
    return $customMsgs[$userId]['text'];
  } else {
    return '';
  }
}

function logUsage($id, $name, $search) {
  $file = 'log.txt';
  file_put_contents($file, time() . '|' . $id . '|' . $name . '|' . $search . "\n", FILE_APPEND);
}
