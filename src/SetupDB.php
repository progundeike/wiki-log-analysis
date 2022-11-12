<?php

namespace wikipedia_log_analysis;

class SetupDB
{
    private const DB_NAME = 'database';
    private const DB_HOST = 'db';
    private const USER = 'user';
    private const DB_PASSWORD = 'pass';

    public $dbh;

    public function __construct()
    {
        $this->dbh =  $this->connectDB(self::DB_NAME, self::DB_HOST, self::USER, self::DB_PASSWORD);
    }

    public function connectDB()
    {
        $dsn = "mysql:host=${self::DB_HOST};dbname=${self::DB_NAME};charset=utf8mb4";

        try {
            $dbh = new \PDO(
                $dsn,
                self::USER,
                self::DB_PASSWORD,
                array(\PDO::MYSQL_ATTR_LOCAL_INFILE => 1)
            );
        } catch (\PDOException $e) {
            echo '接続に失敗しました。' . $e->getMessage() . PHP_EOL;
            die();
        }

        return $dbh;
    }

    function importLogFile(string $logFileName = 'pageviews')
    {
        echo 'ファイルをデータベースにインポート中です。' . PHP_EOL;

        $sql = <<<SQL
        LOAD DATA LOCAL INFILE '/app/wikipedia_log_data/${logFileName}'
        INTO TABLE page_views
        FIELDS TERMINATED BY ' '
        SQL;

        try {
            $this->dbh->query($sql);
        } catch (\PDOException $e) {
            echo 'インポートに失敗しました。' . PHP_EOL;
            echo $e;
            die();
        }

        echo 'インポートが完了しました。' . PHP_EOL;
    }
}
