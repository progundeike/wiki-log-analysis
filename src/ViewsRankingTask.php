<?php

namespace wikipedia_log_analysis;

use PDO;
use PDOStatement;

require_once(__DIR__ . '/Task.php');

class ViewsRankingTask implements Task
{
    public function getSqlArg(): string
    {
        $numberOfArticles = false;
        while (!$numberOfArticles) {
            echo '表示する記事数を入力してください(半角数字)' . PHP_EOL;
            $inputValue = trim(fgets(STDIN));
            $numberOfArticles = filter_var($inputValue, FILTER_VALIDATE_INT);
            //バリデーション
            if (!$numberOfArticles) {
                echo '記事数の入力に誤りがあります。' . PHP_EOL;
            }
        }

        return (string) $numberOfArticles;
    }

    public function makeStmt(PDO $dbh): PDOStatement
    {
        $numberOfArticles = $this->getSqlArg();
        $sql = <<<SQL
            SELECT
                domain_code,
                page_title,
                count_views
            FROM
                page_views
            ORDER BY
                count_views DESC
            LIMIT
                :test
            SQL;

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(":test", $numberOfArticles);
        var_dump($stmt);
        $stmt->execute();

        return $stmt;
    }
}
