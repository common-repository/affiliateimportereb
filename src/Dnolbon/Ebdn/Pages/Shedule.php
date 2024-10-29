<?php
namespace Dnolbon\Ebdn\Pages;

use Dnolbon\Ebdn\Tables\SheduleTable;
use Dnolbon\Ebdn\Wordpress\WpListTable;

class Shedule
{
    /**
     * @var WpListTable $table
     */
    private $table;

    public function render()
    {
        $activePage = 'schedule';
        include EBDN_ROOT_PATH . '/layout/toolbar.php';

        $this->getTable()->prepareItems();
        include EBDN_ROOT_PATH . '/layout/shedule.php';
    }

    /**
     * @return WpListTable
     */
    public function getTable()
    {
        if ($this->table === null) {
            $this->table = new SheduleTable();
        }
        return $this->table;
    }
}
