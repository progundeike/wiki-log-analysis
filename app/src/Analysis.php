<?php

namespace wikipedia_log_analysis;

require_once(__DIR__ . '/DomainCodeRankingTask.php');
require_once(__DIR__ . '/ViewsRankingTask.php');
require_once(__DIR__ . '/SetupDB.php');
require_once(__DIR__ . '/Task.php');

use PDO;

class Analysis
{
    public PDO $pdo;

    /**
     * DBとの接続に使用するインスタンスを生成
     */
    public function __construct()
    {
        $setupDB = new SetupDB();
        $this->pdo = $setupDB->pdo;
    }

    /**
     * プログラムを開始して、selectTaskで終了が選択されるまで処理を繰り返す。
     *
     * @return void
     */
    public function start(): void
    {
        // @phpstan-ignore-next-line
        while (true) {
            $selectedTask = $this->selectTask();
            $results = $selectedTask->fetchResult($this->pdo);
            $this->showResult($results);
        }
    }

    /**
     * 使用するタスクインスタンスを生成する
     *
     * @return Task
     */
    public function selectTask(): Task
    {
        while (true) {
            echo '解析するタスクナンバーを入力してください。終了するには0を入力してください' . PHP_EOL;
            echo 'タスク1: 記事数を指定して、閲覧数の多い順に表示します' . PHP_EOL;
            echo 'タスク2: ドメインコードを入力し、閲覧数の多い順に合計閲覧数を表示します' . PHP_EOL;

            $input = trim(fgets(STDIN));
            $taskNumber = mb_convert_kana($input, "n");
            if ($taskNumber === "1") {
                return new ViewsRankingTask();
            } elseif ($taskNumber === "2") {
                return new DomainCodeRankingTask();
            } elseif ($taskNumber === "0") {
                exit('プログラムを終了します' . PHP_EOL);
            } else {
                echo 'タスクナンバーの入力に誤りがあります' . PHP_EOL;
            }
        }
    }

    /**
     * DBから取得してきた結果を引数に渡して、結果を表示する
     * 結果が空の配列の場合はメッセージを表示する
     *
     * @param array $results
     * @return void
     */
    public function showResult(array $results): void
    {
        if (count($results) === 0) {
            echo '一致するデータがありません' . PHP_EOL;
        } else {
            foreach ($results as $result) {
                echo  implode(' ', $result) . PHP_EOL;
            }
        }
        echo PHP_EOL;
    }
}
