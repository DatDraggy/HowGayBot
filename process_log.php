<?php
require_once(__DIR__ . '/funcs.php');
require_once(__DIR__ . '/config.php');

$handle = fopen("log.txt", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $dbConnection = buildDatabaseConnection($config);

        $entries = explode('|', $line, 4);

        $time = $entries[0] ?? '';
        $id = $entries[1] ?? '';
        $name = $entries[2] ?? '';
        $search = $entries[3] ?? '';

        try {
            $stmt = $dbConnection->prepare("INSERT INTO howgay_log(user_id, nickname, search, created_at) VALUES (:user_id, :name, :search, date_add('1970-01-01', INTERVAL :time SECOND))");
            $stmt->bindParam(':user_id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':search', $search);
            $stmt->bindParam(':time', $time);
            $stmt->execute();
        } catch (\PDOException $e) {
            notifyOnException('Database Insert', $config, '', $e);
        }
    }

    fclose($handle);
}