<?php
require_once(__DIR__ . '/funcs.php');
require_once(__DIR__ . '/config.php');
require_once('/var/libraries/composer/vendor/autoload.php');
//^ guzzlehttp

$response = file_get_contents('php://input');
$data = json_decode($response, true);
$dump = print_r($data, true);

if (isset($data['inline_query'])) {
  $inlineQueryId = $data['inline_query']['id'];
  $senderUserId = $data['inline_query']['from']['id'];
  $search = $data['inline_query']['query'];
  $offset = 0;

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

  $gay = rand(0, 100);

  if (empty($search)) {
    $results = [
      [
        'type' => 'article',
        'id' => 1,
        'title' => '🏳️‍🌈 How gay are you?',
        'input_message_content' => array(
          'message_text' => "🏳️‍🌈 I am $gay% gay!",
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