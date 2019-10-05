<?php
require_once(__DIR__ . '/funcs.php');
require_once(__DIR__ . '/config.php');
require_once('/var/libraries/composer/vendor/autoload.php');
//^ guzzlehttp

$response = file_get_contents('php://input');
$data = json_decode($response, true);
$dump = print_r($data, true);

$replyMarkup = array(
  'inline_keyboard' => array(
    array(
      array(
        'text' => 'Share your gayness! 🏳️‍🌈',
        'switch_inline_query' => ''
      )
    )
  )
);

if (isset($data['inline_query'])) {
  $inlineQueryId = $data['inline_query']['id'];
  $senderUserId = $data['inline_query']['from']['id'];
  $search = $data['inline_query']['query'];
  $offset = 0;

  $gay = rand(0, 100);

  $messageText = getCustomMessage($senderUserId);
  if (empty($messageText)) {
    $messageText = "🏳️‍🌈 I am $gay% gay!";
  } else {
    $messageText = str_replace('%gay%', $gay . '%', $messageText);
  }

  if (empty($search)) {
    $results = [
      [
        'type' => 'article',
        'id' => 1,
        'title' => '🏳️‍🌈 How gay are you?',
        'input_message_content' => array(
          'message_text' => $messageText,
          'parse_mode' => 'html',
          'disable_web_page_preview' => true
        ),
        'reply_markup' => $replyMarkup,
        'description' => 'Send your current gayness to this chat.',
        'thumb_url' => 'https://img.kieran.de/8N3nfe4.png'
      ]
    ];
  } else {
    $results = [
      [
        'type' => 'article',
        'id' => 1,
        'title' => '🏳️‍🌈 How gay is ' . $search . '?',
        'input_message_content' => array(
          'message_text' => "🏳️‍🌈 $search is $gay% gay!",
          'parse_mode' => 'html',
          'disable_web_page_preview' => true
        ),
        'reply_markup' => $replyMarkup,
        'description' => 'Send ' . $search . '\'s gayness to this chat.',
        'thumb_url' => 'https://img.kieran.de/8N3nfe4.png'
      ]
    ];
  }

  array_push($results, [
    'type' => 'article',
    'id' => 2,
    'title' => '🏳️‍🌈 Help',
    'input_message_content' => array(
      'message_text' => 'Either press the button attached to this message and select the chat you would like to post in or simply enter "@HowGayBot " into your text box.',
      'parse_mode' => 'html',
      'disable_web_page_preview' => true
    ),
    'reply_markup' => $replyMarkup,
    'description' => 'Send the usage guidelines to this chat.',
    'thumb_url' => 'https://img.kieran.de/8N3nfe4.png'
  ]);

  answerInlineQuery($inlineQueryId, $results, $offset);
  die();
} else if (isset($data['chosen_inline_result'])) {
  die();
}

if (isset($data['message']['text'])) {
  $text = $data['message']['text'];
}

$chatId = $data['message']['chat']['id'];
$chatType = $data['message']['chat']['type'];
$senderUserId = preg_replace("/[^0-9]/", "", $data['message']['from']['id']);
$messageId = $data['message']['message_id'];

if (isset($text)) {
  if (substr($text, '0', '1') == '/') {
    $messageArr = explode(' ', $text);
    $command = explode('@', $messageArr[0])[0];
    if ($messageArr[0] == '/start' && isset($messageArr[1])) {
      $command = '/' . $messageArr[1];
    }
  }

  $command = strtolower($command);

  switch ($command) {
    case '/start':
    case '/help':
      sendMessage($chatId, 'Hello!
Simply type "@HowGayBot " into your text box and click one of the results or click the button attached to this message.

To set a custom gay text, write /text to me.', '', $replyMarkup);
      break;
    case '/text':
      $customText = explode(' ', $text, 2)[1];
      if (empty($customText)) {
        sendMessage($chatId, 'You forgot to specify your text! <code>Example: /text I am not %gay% gay...</code>');
      } else {
        if (stripos($customText, '%gay%') === false) {
          sendMessage($chatId, 'The custom message must contain %gay%, which will be replaced with the gay-percentage. (e.g. 50%)');
        } else {
          setCustomMessage($senderUserId, $customText);
          sendMessage($chatId, 'Custom text was set. It may take a couple of minutes until it will show up.');
        }
      }
      break;
  }
}