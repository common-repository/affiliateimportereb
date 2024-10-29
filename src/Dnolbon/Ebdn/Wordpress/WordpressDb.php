<?php
namespace Dnolbon\Ebdn\Wordpress;

class WordpressDb
{
    /**
     * @var WordpressDb $instance
     */
    protected static $instance;

    /**
     * @return WordpressDb
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new WordpressDb();
        }

        return self::$instance;
    }

    /**
     * @return \wpdb
     */
    public function getDb()
    {
        global $wpdb;
        return $wpdb;
    }
}
