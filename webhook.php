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

  if (isset($data['inline_query']['from']['language_code'])) {
    $langCode = $data['inline_query']['from']['language_code'];
  } else {
    $langCode = '';
  }

  $messageText = getCustomMessage($senderUserId);
  if (empty($messageText)) {
    if ($langCode === 'de') {
      $messageText = "🏳️‍🌈 Ich bin $gay% schwul!";
      //setCustomMessage($senderUserId, '🏳️‍🌈 Ich bin %gay% schwul!');
    } else {
      $messageText = "🏳️‍🌈 I am $gay% gay!";
    }
  } else {
    $messageText = str_replace('%gay%', $gay . '%', $messageText);
  }

  if (empty($search)) {
    $translation['title'] = '🏳️‍🌈 How gay are you?';
    $translation['description'] = 'Send your current gayness to this chat.';
    if ($langCode === 'de') {
      $translation['title'] = '🏳️‍🌈 Wie schwul bist du?';
      $translation['description'] = 'Sende deine derzeitige schwul-heit!';
      $translation['help']['title'] = '🏳️‍🌈 Hilfe';
      $translation['help']['description'] = 'Sendet den Hilfetext in den Chat.';
      $translation['help']['text'] = 'Drücke entweder den Knopf an dieser Nachricht und wähle den gewünschten Chat aus oder schreibe einfach "@HowGayBot " in das Textfeld.

Um einen personalisierten Text zu setzen, schreibe @HowGayBot privat eine Nachricht.';
    }
    $results = [
      [
        'type' => 'article',
        'id' => 1,
        //'title' => '🏳️‍🌈 How gay are you?',
        'title' => $translation['title'],
        'input_message_content' => array(
          'message_text' => $messageText,
          'parse_mode' => 'html',
          'disable_web_page_preview' => true
        ),
        'reply_markup' => $replyMarkup,
        //'description' => 'Send your current gayness to this chat.',
        'description' => $translation['description'],
        'thumb_url' => 'https://img.kieran.de/8N3nfe4.png'
      ]
    ];
  } else {
    $translation['title'] = '🏳️‍🌈 How gay is ' . $search . '?';
    $translation['description'] = 'Send ' . $search . '\'s gayness to this chat.';
    if ($langCode === 'de') {
      $translation['title'] = '🏳️‍🌈 Wie schwul ist '. $search .'?';
      $translation['description'] = 'Sende '. $search .'s derzeitige schwul-heit!';
      $translation['help']['title'] = '🏳️‍🌈 Hilfe';
      $translation['help']['description'] = 'Sendet den Hilfetext in den Chat.';
      $translation['help']['text'] = 'Drücke entweder den Knopf an dieser Nachricht und wähle den gewünschten Chat aus oder schreibe einfach "@HowGayBot " in das Textfeld.

Um einen personalisierten Text zu setzen, schreibe @HowGayBot privat eine Nachricht.';
    }

    $search = str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $search);
    $results = [
      [
        'type' => 'article',
        'id' => 1,
        //'title' => '🏳️‍🌈 How gay is ' . $search . '?',
        'title' => $translation['title'],
        'input_message_content' => array(
          'message_text' => "🏳️‍🌈 $search is $gay% gay!",
          'parse_mode' => 'html',
          'disable_web_page_preview' => true
        ),
        'reply_markup' => $replyMarkup,
        //'description' => 'Send ' . $search . '\'s gayness to this chat.',
        'description' => $translation['description'],
        'thumb_url' => 'https://img.kieran.de/8N3nfe4.png'
      ]
    ];
  }

  if($langCode !== 'de'){
  $translation['help']['title'] = '🏳️‍🌈 Help';
  $translation['help']['description'] = 'Send the usage guidelines to this chat.';
  $translation['help']['text'] = 'Either press the button attached to this message and select the chat you would like to post in or simply enter "@HowGayBot " into your text box.

For a personalized gay message, send @HowGayBot a message!';
  }

  array_push($results, [
    'type' => 'article',
    'id' => 2,
    //'title' => '🏳️‍🌈 Help',
    'title' => $translation['help']['title'],
    'input_message_content' => array(
      //'message_text' => 'Either press the button attached to this message and select the chat you would like to post in or simply enter "@HowGayBot " into your text box.
//
//For a personalized gay message, send @HowGayBot a message!',
      'message_text' => $translation['help']['text'],
      'parse_mode' => 'html',
      'disable_web_page_preview' => true
    ),
    'reply_markup' => $replyMarkup,
    //'description' => 'Send the usage guidelines to this chat.',
    'description' => $translation['help']['description'],
    'thumb_url' => 'https://img.kieran.de/8N3nfe4.png'
  ]);

  $name = (isset($data['inline_query']['from']['username']) ? $data['inline_query']['from']['username'] : $data['inline_query']['from']['first_name']);
  logUsage($senderUserId, $name, $search);
  answerInlineQuery($inlineQueryId, $results, $offset);
  die();
} else if (isset($data['chosen_inline_result'])) {
  die();
}

if (isset($data['message']['text'])) {
  $text = $data['message']['text'];
}

if (isset($data['message'])) {
  $chatId = $data['message']['chat']['id'];
  $chatType = $data['message']['chat']['type'];
  $senderUserId = preg_replace("/[^0-9]/", "", $data['message']['from']['id']);
  $messageId = $data['message']['message_id'];
} else if (isset($data['edited_message'])) {
  die();
} else {
  mail('admin@kieran.de', 'Debug empty msg', print_r($data, true));
}

if (isset($text)) {
  if (substr($text, '0', '1') == '/') {
    $messageArr = explode(' ', $text);
    $command = explode('@', $messageArr[0])[0];
    if ($messageArr[0] == '/start' && isset($messageArr[1])) {
      $command = '/' . $messageArr[1];
    }
  }

  if (!isset($command)) {
    $command = '/start';
  }

  $command = strtolower($command);

  switch ($command) {
    case '/start':
    case '/help':
      sendMessage($chatId, 'Hello!
      
To use this bot, simply type "@HowGayBot " into your text box and click one of the results or click the button attached to this message.

To set a custom gay text, write /text to me.', '', $replyMarkup);
      break;
    case '/text':
      $customText = explode(' ', $text, 2);
      if (!isset($customText[1])) {
        sendMessage($chatId, 'You forgot to specify your text! <code>Example: /text I am not %gay% gay...</code>');
      } else {
        $customText = $customText[1];
        if (stripos($customText, '%gay%') === false || substr_count($customText, '%gay%') > 1) {
          sendMessage($chatId, 'The custom message must contain one %gay%, which will be replaced with the gay-centage. (e.g. 50%)');
        } else {
          $customText = str_replace('<', '&lt;', $customText);
          $customText = str_replace('>', '&gt;', $customText);
          setCustomMessage($senderUserId, $customText);
          sendMessage($chatId, 'Custom text was set. It may take a couple of minutes until it will show up.');
        }
      }
      break;
  }
}
