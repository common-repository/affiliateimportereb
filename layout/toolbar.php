<?php
$imagePath = plugins_url('assets/img/toolbar', EBDN_FILE_FULLNAME);

$menu = [
    [
        'name' => 'Dasboard',
        'icon' => 'dashboard',
        'link' => ''
    ],
    [
        'name' => 'Add product',
        'icon' => 'add',
        'link' => 'add'
    ],
    [
        'name' => 'Shedule',
        'icon' => 'schedule',
        'link' => 'schedule'
    ],
    [
        'name' => 'Statistics',
        'icon' => 'stat',
        'link' => 'stats'
    ],
    [
        'name' => 'Settings',
        'icon' => 'settings',
        'link' => 'settings'
    ],
    [
        'name' => 'Backup/Restore',
        'icon' => 'backup',
        'link' => 'backup'
    ],
    [
        'name' => 'Status',
        'icon' => 'status',
        'link' => 'status'
    ],
    [
        'name' => 'Support',
        'icon' => 'support',
        'link' => '-',
        'exteral_link' => 'http://cr1000team.com/support/',
        'class' => 'right'
    ]
];

?>
<div class="dnlb_toolbar">
    <ul class="dnlb_menu">
        <?php
        foreach ($menu as $menuEl) {
            ?>
            <li class="<?= $menuEl['class'] ?>" data-rel="<?= $menuEl['link'] ?>">
                <a href="<?= (isset($menuEl['exteral_link']) ? $menuEl['exteral_link'] : admin_url('admin.php?page=ebdn' . ($menuEl['link'] ? '-' . $menuEl['link'] : ''))) ?>"<?= ($activePage === $menuEl['link'] ? ' class="active_page"' : '') ?><?= (isset($menuEl['exteral_link'])?' target="_blank"':'') ?>>
                    <img src="<?= $imagePath . '/' . $menuEl['icon'] ?>.png"/>
                    <span><?= $menuEl['name'] ?></span>
                </a>
            </li>
            <?php
        }
        ?>
    </ul>
    <div style="clear: both;"></div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery(".dnlb_menu a").mouseover(function () {
            setActivePage(this);
        });

        jQuery(".dnlb_menu a").mouseout(function () {
            jQuery(".dnlb_menu a").removeClass('active');

            jQuery('img', this).attr("src", jQuery('img', this).attr("data-normal"));
        });

        setActivePage(jQuery("*[data-rel='<?=$activePage?>'] a"));
    });

    function setActivePage(object) {
        jQuery(".dnlb_menu a").removeClass('active');
        jQuery(object).addClass('active');

        var attr = jQuery('img', object).attr('data-hover');

        if (!(typeof attr !== typeof undefined && attr !== false)) {
            jQuery('img', object).attr("data-hover", jQuery('img', object).attr("src").replace(/.png/, '_active.png'));
        }
        jQuery('img', object).attr("data-normal", jQuery('img', object).attr("src"));
        jQuery('img', object).attr("src", jQuery('img', object).attr("data-hover"));
    }
</script> 