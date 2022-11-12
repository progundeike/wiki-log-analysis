<?php

namespace wikipedia_log_analysis;

require_once(__DIR__ . '/ViewsRankingTask.php');
require_once(__DIR__ . '/DomainCodeRankingTask.php');
require_once(__DIR__ . '/DataImport.php');

// 実行部分仮置き
$dbh = new SetupDB();

$taskAnalysis = new LogAnalysis();


$dataImport->importLogFile();

$logAnalysis = new LogAnalysis;

$selectedTask = $logAnalysis->selectTask();
$sql_arg = $selectedTask->getSqlArg();
$sql = $selectedTask->makeSql($sql_arg);
$query = $dbh->query($sql);

// showResult($query);

class LogAnalysis
{
    public function __construct()
    {
    }

    public function showResult($query): void
    {
        while ($fetchedData = $query->fetch(\PDO::FETCH_ASSOC)) {
            echo  implode(' ', $fetchedData) . PHP_EOL;
        }
        echo PHP_EOL;
    }

    public function selectTask()
    {
        // ガイダンスを表示
        echo '解析するタスクナンバーを入力してください(半角数字)' . PHP_EOL;
        echo 'タスク1: 記事数を指定して、閲覧数の多い順に表示します' . PHP_EOL;
        echo 'タスク2: ドメインコードを入力し、閲覧数の多い順に合計閲覧数を表示します' . PHP_EOL;

        $taskNumber = trim(fgets(STDIN));

        if ($taskNumber === "1") {
            $task1 = new ViewsRankingTask();
            return $task1;
        } elseif ($taskNumber === "2") {
            return new DomainCodeRankingTask();
        } else {
            echo 'タスクナンバーの入力に誤りがあります。' . PHP_EOL;
            $this->selectTask();
        }
    }
}
