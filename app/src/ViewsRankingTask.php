<?php

namespace wikipedia_log_analysis;

use PDO;
use PDOStatement;

require_once(__DIR__ . '/Task.php');

/**
 * タスク1: 表示する記事数の入力を受け取り、ビュー数の多い順に、
 * 「ドメインコード」、「ページタイトル」、「ビュー数」を表示する
 */
class ViewsRankingTask implements Task
{
    /**
     * 表示する記事数の入力を受け取り、入力が自然数か確認する
     *
     * @return integer
     */
    public function getNumberOfDisplayArticles(): int
    {
        while (true) {
            echo '表示する記事数を入力してください(半角数字)' . PHP_EOL;
            $input = trim(fgets(STDIN));
            $numberOfArticles = filter_var($input, FILTER_VALIDATE_INT);
            if (!$numberOfArticles || $numberOfArticles < 1) {
                echo '記事数の入力に誤りがあります。' . PHP_EOL;
            } else {
                return (int) $numberOfArticles;
            }
        }
    }

    /**
     * 表示する記事数の入力を関数内部で受け取り、SQL文を組み立ててPDOStatementを返す
     *
     * @param PDO $pdo
     * @return PDOStatement
     */
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
