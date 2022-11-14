<?php

namespace wikipedia_log_analysis;

class SetupDB
{
    // TODO: 環境変数から取得する
    private const DB_NAME = 'database';
    private const DB_HOST = 'db';
    private const USER = 'user';
    private const DB_PASSWORD = 'pass';
    private const LOG_FILE_NAME = 'pageviews';
    public $dbh;

    public function __construct()
    {
        $this->dbh =  $this->connectDB(self::DB_NAME, self::DB_HOST, self::USER, self::DB_PASSWORD);
        $this->askImportDB();
    }

    public function connectDB()
    {
        $dsn = "mysql:host=" . self::DB_HOST .
            ";dbname=" . self::DB_NAME .
            ";charset=utf8mb4";

        try {
            $dbh = new \PDO(
                $dsn,
                self::USER,
                self::DB_PASSWORD,
                array(\PDO::MYSQL_ATTR_LOCAL_INFILE => 1)
            );
        } catch (\PDOException $e) {
            exit('接続に失敗しました。' . $e->getMessage() . PHP_EOL);
        }

        return $dbh;
    }

    public function askImportDB(): void
    {
        while (true) {
            echo "DBをインポートしますか？ 'y' or 'n'" . PHP_EOL;
            $input = trim(fgets(STDIN));
            if ($input === 'y') {
                $this->initializeTable();
                $this->importLogFile();
                break;
            } elseif ($input === 'n') {
                break;
            } else {
                echo '入力が正しくありません。' . PHP_EOL;
            }
        }
    }

    function importLogFile()
    {
        echo 'ファイルをデータベースにインポート中です。' . PHP_EOL;
        $logFileName = self::LOG_FILE_NAME;

        $sql = <<<SQL
        LOAD DATA LOCAL INFILE '/app/wikipedia_log_data/${logFileName}'
        INTO TABLE page_views
        FIELDS TERMINATED BY ' '
        SQL;

        try {
            $this->dbh->query($sql);
        } catch (\PDOException $e) {
            exit('インポートに失敗しました。' . $e);
        }

        echo 'インポートが完了しました。' . PHP_EOL;
    }

    function initializeTable()
    {
        $sql = <<<SQL
        DROP TABLE IF EXISTS page_views;

        CREATE TABLE page_views (
        domain_code VARCHAR(255),
        page_title MEDIUMTEXT,
        count_views INTEGER,
        total_response_size INTEGER
        )
        SQL;

        try {
            $this->dbh->query($sql);
        } catch (\PDOException $e) {
            exit('テーブルの初期化に失敗しました' . $e);
        }
    }
}
