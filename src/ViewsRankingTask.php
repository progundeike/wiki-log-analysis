<?php

namespace wikipedia_log_analysis;

class ViewsRankingTask
{
    public function __construct()
    {
    }

    public function getSqlArg(): int
    {
        echo '表示する記事数を入力してください(半角数字)' . PHP_EOL;
        $inputValue = trim(fgets(STDIN));
        $numberOfArticles = filter_var($inputValue, FILTER_VALIDATE_INT);
        //バリデーション
        if (!$numberOfArticles) {
            echo '記事数の入力に誤りがあります。' . PHP_EOL;
            $this->getSqlArg();
        }

        return $numberOfArticles;
    }

    public function makeSql(int $numberOfArticles): string
    {
        $sql =  <<<SQL
        SELECT
            domain_code,
            page_title,
            count_views
        FROM
            page_views
        ORDER BY
            count_views DESC
        LIMIT
            $numberOfArticles
        SQL;

        return $sql;
    }
}
