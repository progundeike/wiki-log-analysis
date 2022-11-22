<?php

namespace wikipedia_log_analysis;

require_once(__DIR__ . '/Task.php');

use PDO;
use PDOStatement;

/**
 * タスク2: ドメインコードの入力を受け取り、
 * 人気順にソートし、ドメインコード名と合計ビュー数を表示する
 */
class DomainCodeRankingTask implements Task
{
    /**
     * ソートするドメインコードの入力を受け取る
     * 入力がドメインの形式でない場合は、再入力させる
     *
     * @return array
     */
    public function getDomainCodes(): array
    {
        do {
            $isInputError = false;
            // ドメインコードをスペース区切りで受け取り、配列にする
            echo '表示するドメインコードをスペース区切りで入力してください(例: de ja fr)' . PHP_EOL;
            $input = trim(fgets(STDIN));
            $inputToHalfWidth = mb_convert_kana($input, "r");

            $inputArray = explode(' ', $inputToHalfWidth);

            // 配列を順番に回して、入力がドメインの形式か確認する。
            $domainCodes = [];
            foreach ($inputArray as $domainCode) {
                if (filter_var($domainCode, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                    $domainCodes[] = $domainCode;
                } else {
                    $isInputError = true;
                    echo 'ドメインコードの入力形式に誤りがあります。' . PHP_EOL;
                    break;
                }
            }
        } while ($isInputError);

        return $domainCodes;
    }

    /**
     * 関数内部でソートするドメインコードを受け取り、
     * SQL文を組み立ててPDOStatementを返す
     *
     * @param PDO $pdo
     * @return array
     */
    public function fetchResult(PDO $pdo): array
    {
        $domainCodes = $this->getDomainCodes();

        $holderArray = array_fill(0, count($domainCodes), '?');
        $placeholder = implode(",", $holderArray);

        $sql = <<<SQL
        SELECT
            domain_code,
            SUM(count_views) AS total_views
        FROM
            page_views
        WHERE
            domain_code IN ($placeholder)
        GROUP BY domain_code
        ORDER BY total_views DESC
        SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($domainCodes);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $results;
    }
}
