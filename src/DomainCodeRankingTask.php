<?php

namespace wikipedia_log_analysis;

class DomainCodeRankingTask
{
    public function getSqlArg(): array
    {
        $isInputError = true;
        while ($isInputError) {
            $inputError = false;
            echo '表示するドメインコードをスペースで区切りで入力してください(例: de ja fr)' . PHP_EOL;
            $inputValue = trim(fgets(STDIN));
            $inputDomainCodeArray = explode(' ', $inputValue);

            foreach ($inputDomainCodeArray as $domainCode) {
                if (filter_var($domainCode, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                    $selectedDomainCodes[] = $domainCode;
                } else {
                    echo 'ドメインコードの入力形式に誤りがあります。' . PHP_EOL;
                    $isInputError = true;
                    break;
                }
            }
        }

        return $selectedDomainCodes;
    }

    public function makeSql(array $selectedDomainCodes): string
    {
        $selectedDomainCodeStrings = implode("', '", $selectedDomainCodes);

        $sql = <<<SQL
        SELECT
            domain_code,
            SUM(count_views) AS total_views
        FROM
            page_views
        WHERE
            domain_code IN ('${selectedDomainCodeStrings}')
        GROUP BY domain_code
        ORDER BY total_views DESC
        SQL;

        return $sql;
    }
}
