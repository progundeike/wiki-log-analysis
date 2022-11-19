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
            $inputValue = trim(fgets(STDIN));
            $inputValueArray = explode(' ', $inputValue);

            // 配列を順番に回して、入力がドメインの形式か確認する。
            $domainCodes = [];
            foreach ($inputValueArray as $domainCode) {
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
     * @return PDOStatement
     */
    public function makeStmt(PDO $pdo): PDOStatement
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
        return $stmt;
    }
}
