<?php

namespace wikipedia_log_analysis;

require_once(__DIR__ . '/ViewsRankingTask.php');
require_once(__DIR__ . '/DomainCodeRankingTask.php');
require_once(__DIR__ . '/SetupDB.php');

// 実行部分仮置き
$setupDB = new SetupDB();
$logAnalysis = new LogAnalysis();
$selectedTask = $logAnalysis->selectTask();

$sqlArg = $selectedTask->getSqlArg();
$sql = $selectedTask->makeSql($sqlArg);

$query = $setupDB->dbh->query($sql);
$logAnalysis->showResult($query);

class LogAnalysis
{
    public function selectTask()
    {
        while (true) {
            // ガイダンスを表示
            echo '解析するタスクナンバーを入力してください。終了は0を入力してください。(半角数字)' . PHP_EOL;
            echo 'タスク1: 記事数を指定して、閲覧数の多い順に表示します' . PHP_EOL;
            echo 'タスク2: ドメインコードを入力し、閲覧数の多い順に合計閲覧数を表示します' . PHP_EOL;

            $taskNumber = trim(fgets(STDIN));
            if ($taskNumber === "1") {
                return new ViewsRankingTask();
            } elseif ($taskNumber === "2") {
                return new DomainCodeRankingTask();
            } elseif ($taskNumber === "0") {
                exit;
            } else {
                echo 'タスクナンバーの入力に誤りがあります。' . PHP_EOL;
            }
        }
    }

    public function showResult($query): void
    {
        $existsData = false;
        while ($fetchedData = $query->fetch(\PDO::FETCH_ASSOC)) {
            echo  implode(' ', $fetchedData) . PHP_EOL;
            $existsData = true;
        }
        if (!$existsData) {
            echo '一致するデータがありません。' . PHP_EOL;
        }
        echo PHP_EOL;
    }
}
