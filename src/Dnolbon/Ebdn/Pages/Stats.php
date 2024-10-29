<?php
namespace Dnolbon\Ebdn\Pages;

use Dnolbon\Ebdn\Tables\StatsTable;
use Dnolbon\Ebdn\Wordpress\WpListTable;

class Stats
{
    /**
     * @var WpListTable $table
     */
    private $table;

    public function render()
    {
        $activePage = 'stats';
        include EBDN_ROOT_PATH . '/layout/toolbar.php';

        $this->getTable()->prepareItems();
        include EBDN_ROOT_PATH . '/layout/stats.php';
    }

    /**
     * @return WpListTable
     */
    public function getTable()
    {
        if ($this->table === null) {
            $this->table = new StatsTable();
        }
        return $this->table;
    }
}
