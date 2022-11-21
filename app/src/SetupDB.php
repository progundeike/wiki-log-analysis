<?php

namespace wikipedia_log_analysis;

require_once(__DIR__ . '/Analysis.php');

use PDO;
use PDOException;

class SetupDB
{
    private const DB_HOST = 'db';
    private const LOG_FILE_NAME = 'page_views';
    private $dbUser;
    private $dbPassword;
    private $dbName;
    public $pdo;

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
        $this->askImportFile();
    }

    /**
     * DBと接続する。失敗した場合はプログラムを終了
     *
     * @return PDO
     */
    public function connectDB(): PDO
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
     * プログラム開始時に、ログファイルをインポートするかを確認
     *
     * @return void
     */
    public function askImportFile(): void
    {
        while (true) {
            echo "DBをインポートしますか？ 'y' or 'n'" . PHP_EOL;
            $input = trim(fgets(STDIN));
            if ($input === 'y') {
                $this->initializeTable();
                $this->importLogFile();
                $this->createIndex();
                echo 'インポートが完了しました。' . PHP_EOL;
                break;
            } elseif ($input === 'n') {
                break;
            } else {
                echo '入力が正しくありません。' . PHP_EOL;
            }
        }
    }

    /**
     * ログファイルをインポートする。失敗した場合は、プログラムを終了
     *
     * @return void
     */
    private function importLogFile(): void
    {
        echo 'ログファイルをデータベースにインポート中です。' . PHP_EOL;
        $logFileName = self::LOG_FILE_NAME;

        $sql = <<<SQL
        LOAD DATA LOCAL INFILE '/app/log_file/{$logFileName}'
        INTO TABLE page_views
        FIELDS TERMINATED BY ' '
        SQL;

        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            exit('インポートに失敗しました。' . $e);
        }
    }

    /**
     * テーブルを初期化する。失敗した場合はプログラムを終了
     *
     * @return void
     */
    private function initializeTable(): void
    {
        $sql = <<<SQL
        DROP TABLE IF EXISTS page_views;

        CREATE TABLE page_views (
            domain_code VARCHAR(255),
            page_title VARCHAR(500),
            count_views INTEGER,
            total_response_size INTEGER,
            PRIMARY KEY (domain_code, page_title)
        )
        SQL;

        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            exit('テーブルの初期化に失敗しました' . $e);
        }
    }

    /**
     *  インデックスを作成する。エラーが出ても処理を続行する。
     *
     * @return void
     */
    private function createIndex(): void
    {
        $sql = <<<SQL
        CREATE INDEX
            count_views_index
        ON
            page_views (count_views)
        SQL;

        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            echo 'テーブルの最適化に失敗しました' . $e;
        }
    }
}
