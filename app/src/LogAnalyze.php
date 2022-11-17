<?php

namespace wikipedia_log_analysis;

require_once(__DIR__ . '/DomainCodeRankingTask.php');
require_once(__DIR__ . '/ViewsRankingTask.php');
require_once(__DIR__ . '/SetupDB.php');
require_once(__DIR__ . '/Task.php');

use PDO;
use PDOStatement;

class LogAnalyze
{
    public PDO $pdo;

    public function __construct()
    {
        $setupDB = new SetupDB();
        $this->pdo = $setupDB->pdo;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function analyzeStart()
    {
        // @phpstan-ignore-next-line
        while (true) {
            $selectedTask = $this->selectTask();
            $stmt = $selectedTask->makeStmt($this->pdo);
            $this->showResult($stmt);
        }
    }

    /**
     * 使用するタスクインスタンスを生成する
     *
     * @return Task
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function selectTask(): Task
    {
        while (true) {
            echo '解析するタスクナンバーを入力してください。終了するには0を入力してください。(半角数字)' . PHP_EOL;
            echo 'タスク1: 記事数を指定して、閲覧数の多い順に表示します' . PHP_EOL;
            echo 'タスク2: ドメインコードを入力し、閲覧数の多い順に合計閲覧数を表示します' . PHP_EOL;

            $taskNumber = trim(fgets(STDIN));
            if ($taskNumber === "1") {
                return new ViewsRankingTask();
            } elseif ($taskNumber === "2") {
                return new DomainCodeRankingTask();
            } elseif ($taskNumber === "0") {
                exit('プログラムを終了します' . PHP_EOL);
            } else {
                echo 'タスクナンバーの入力に誤りがあります。' . PHP_EOL;
            }
        }
    }

    /**
     * DBから結果を1行ずつ取得する
     *
     * @param PDOStatement $stmt
     * @return void
     */
    public function showResult(PDOStatement $stmt): void
    {
        $existsData = false;
        while ($fetchedData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo  implode(' ', $fetchedData) . PHP_EOL;
            $existsData = true;
        }
        if (!$existsData) {
            echo '一致するデータがありません。' . PHP_EOL;
        }
        echo PHP_EOL;
    }
}
