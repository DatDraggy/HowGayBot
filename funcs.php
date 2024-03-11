<?php
function buildDatabaseConnection($config) {
    //Connect to DB only here to save response time on other commands
    try {
        $dbConnection = new PDO('mysql:dbname=' . $config['dbname'] . ';host=' . $config['dbserver'] . ';port=' . $config['dbport'] . ';charset=utf8mb4', $config['dbuser'], $config['dbpassword'], array(PDO::ATTR_TIMEOUT => 25));
        $dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        notifyOnException('Database Connection', $config, '', $e);
        return false;
    }
    return $dbConnection;
}

function notifyOnException($subject, $config, $sql = '', $e = '', $fail = false) {
    $to = $config['mail'];
    $txt = __FILE__ . ' ' . $sql . ' Error: ' . $e;
    $headers = 'From: ' . $config['mail'];
    mail($to, $subject, $txt, $headers);
    http_response_code(200);
    if ($fail) {
        die();
    }
}

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
    global $config;

    $textB = $text;

    $dbConnection = buildDatabaseConnection($config);

    try {
        $stmt = $dbConnection->prepare('INSERT INTO howgay_text VALUES (:user_id, :text) ON DUPLICATE KEY UPDATE `text`=:textB');
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':text', $text);
        $stmt->bindParam(':textB', $textB);
        $stmt->execute();
    } catch (\PDOException $e) {
        notifyOnException('Database Insert', $config, '', $e);
        die();
    }
}

function getCustomMessage($userId) {
    global $config;

    $dbConnection = buildDatabaseConnection($config);

    try {
        $stmt = $dbConnection->prepare('SELECT text FROM howgay_text WHERE user_id = :user_id');
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $row = $stmt->fetch();
    } catch (\PDOException $e) {
        notifyOnException('Database Select', $config, '', $e);
        die();
    }

    if ($stmt->rowCount() === 1) {
        return $row['text'];
    } else {
        return '';
    }
}

function logUsage($id, $name, $search) {
    $file = 'log.txt';
    file_put_contents($file, time() . '|' . $id . '|' . $name . '|' . $search . "\n", FILE_APPEND);
}
