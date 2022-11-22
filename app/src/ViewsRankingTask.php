<?php

namespace wikipedia_log_analysis;

use PDO;

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
            echo '表示する記事数を入力してください' . PHP_EOL;
            $input = trim(fgets(STDIN));
            $inputToHalfWidth = mb_convert_kana($input, "n");
            $numberOfArticles = filter_var($inputToHalfWidth, FILTER_VALIDATE_INT);
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
     * @return array
     */
    public function fetchResult(PDO $pdo): array
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
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $results;
    }
}
