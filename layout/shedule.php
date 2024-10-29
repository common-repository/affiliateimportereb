<?php
use Dnolbon\Ebdn\Pages\Shedule;

/**
 * @var Shedule $this
 */
?>
<div class="wrap"><h2 class="nav-tab-wrapper"></h2></div>
<div class="wrap light-tabs" default-rel="sheduled">
    <h2 class="nav-tab-wrapper">
        <a href="#" class="nav-tab nav-tab-active" rel="sheduled">Sheduled</a>
    </h2>
    <div id="ebdn-goods-table" class="tab_content ebdn-goods-table" rel="sheduled">
        <div class="separator"></div>
        <?php
        $this->getTable()->display();
        ?>
        <div class="separator"></div>
    </div>
    <div class="tab_content" rel="blacklist">
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        DnolbonColumns.init('stats');
    });
</script>
