<?php

namespace wikipedia_log_analysis;

use PDO;
use PDOStatement;

interface Task
{
    public function makeStmt(PDO $dbh): PDOStatement;
}
