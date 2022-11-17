<?php

namespace wikipedia_log_analysis;

require_once(__DIR__ . '/Task.php');

use PDO;
use PDOStatement;

class DomainCodeRankingTask implements Task
{
    public function getDomainCodes(): array
    {
        do {
            $isInputError = false;
            echo '表示するドメインコードをスペース区切りで入力してください(例: de ja fr)' . PHP_EOL;
            $inputValue = trim(fgets(STDIN));
            $inputValueArray = explode(' ', $inputValue);

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

    public function makeStmt(PDO $pdo): PDOStatement
    {
        $domainCodes = $this->getDomainCodes();

        $holderArray = array_fill(0, count($domainCodes), '?');
        $placeholder = implode(",", $holderArray);
        var_dump($placeholder);

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
