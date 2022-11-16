<?php

namespace wikipedia_log_analysis;

require_once(__DIR__ . '/Task.php');

use PDO;
use PDOStatement;

class DomainCodeRankingTask implements Task
{
    public function getSqlArg(): string
    {
        $isInputError = true;
        while ($isInputError) {
            $isInputError = false;
            echo '表示するドメインコードをスペース区切りで入力してください(例: de ja fr)' . PHP_EOL;
            $inputValue = trim(fgets(STDIN));
            $inputDomainCodeArray = explode(' ', $inputValue);

            $domainCodesArray = [];
            foreach ($inputDomainCodeArray as $domainCode) {
                if (filter_var($domainCode, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                    $domainCodesArray[] = $domainCode;
                } else {
                    echo 'ドメインコードの入力形式に誤りがあります。' . PHP_EOL;
                    $isInputError = true;
                    break;
                }
            }
        }

        $selectedDomainCodes = implode("', '", $domainCodesArray);

        return $selectedDomainCodes;
    }

    public function makeStmt(PDO $dbh): PDOStatement
    {
        $selectedDomainCodes = $this->getSqlArg();

        $sql = <<<SQL
        SELECT
            domain_code,
            SUM(count_views) AS total_views
        FROM
            page_views
        WHERE
            domain_code IN (:domainCodes)
        GROUP BY domain_code
        ORDER BY total_views DESC
        SQL;

        $stmt = $dbh->prepare($sql);
        $stmt->execute(['domainCodes' => $selectedDomainCodes]);

        return $stmt;
    }
}
