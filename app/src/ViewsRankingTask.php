<?php

namespace wikipedia_log_analysis;

use PDO;
use PDOStatement;

require_once(__DIR__ . '/Task.php');

class ViewsRankingTask implements Task
{
    public function getNumberOfDisplayArticles(): int
    {
        // $numberOfArticles = 0;
        while (true) {
            echo '表示する記事数を入力してください(半角数字)' . PHP_EOL;
            $input = trim(fgets(STDIN));
            $numberOfArticles = filter_var($input, FILTER_VALIDATE_INT);

            if (!$numberOfArticles) {
                echo '記事数の入力に誤りがあります。' . PHP_EOL;
            } else {
                return (int) $numberOfArticles;
            }
        }
    }

    public function makeStmt(PDO $pdo): PDOStatement
    {
        $placeHolder = $this->getNumberOfDisplayArticles();

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
                :numberOfArticles
            SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":numberOfArticles", $placeHolder, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }
}
