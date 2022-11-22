<?php

namespace wikipedia_log_analysis;

use PDO;
use PDOStatement;

/**
 * 実装するクラスでは、各タスクに応じた入力を受け取り、SQL文を組み立ててPDOStatementを作成する
 */
interface Task
{
    public function fetchResult(PDO $pdo): array;
}
