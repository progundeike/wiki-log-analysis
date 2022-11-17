<?php

namespace wikipedia_log_analysis;

require_once(__DIR__ . '/LogAnalyze.php');

use PDO;
use PDOException;

class SetupDB
{
    private const DB_HOST = 'db';
    private const LOG_FILE_NAME = 'page_views';
    public $pdo;
    public $dbUser;
    public $dbPassword;
    public $dbName;

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * 環境変数からDB接続情報を取得
     */
    public function __construct()
    {
        $this->dbUser = $_ENV['MYSQL_USER'];
        $this->dbPassword = $_ENV['MYSQL_PASSWORD'];
        $this->dbName = $_ENV['MYSQL_DATABASE'];

        $this->pdo = $this->connectDB();
        $this->askImportDB();
    }

    /**
     * DBと接続する。失敗した場合はプログラムを終了する。
     *
     * @return PDO
     */
    public function connectDB()
    {
        $dsn = "mysql:host=" . self::DB_HOST .
            ";dbname=" . $this->dbName .
            ";charset=utf8mb4";

        try {
            $pdo = new PDO(
                $dsn,
                $this->dbUser,
                $this->dbPassword,
                [PDO::MYSQL_ATTR_LOCAL_INFILE => 1]
            );
        } catch (PDOException $e) {
            exit('接続に失敗しました。' . $e->getMessage() . PHP_EOL);
        }

        return $pdo;
    }

    /**
     * プログラム開始時に、ログファイルをインポートするかを確認する。
     *
     * @return void
     */
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

    /**
     * ログファイルをインポートする。失敗した場合は、プログラムを終了する。
     *
     * @return void
     */
    private function importLogFile()
    {
        echo 'ログファイルをデータベースにインポート中です。' . PHP_EOL;
        $logFileName = self::LOG_FILE_NAME;

        $sql = <<<SQL
        LOAD DATA LOCAL INFILE '/app/wikipedia_log_data/{$logFileName}'
        INTO TABLE page_views
        FIELDS TERMINATED BY ' '
        SQL;

        try {
            $this->pdo->query($sql);
        } catch (PDOException $e) {
            exit('インポートに失敗しました。' . $e);
        }

        echo 'インポートが完了しました。' . PHP_EOL;
    }

    /**
     * テーブルを初期化する。失敗した場合はプログラムを終了する。
     *
     * @return void
     */
    private function initializeTable()
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
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            exit('テーブルの初期化に失敗しました' . $e);
        }
    }
}
