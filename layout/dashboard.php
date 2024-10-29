<?php
/**
 * @var $dashboard EBDN_DashboardPage
 */
use Dnolbon\Ebdn\Tables\BlacklistTable;

$dashboard->prepare_items();

$errors = array_merge(get_settings_errors('ebdn_dashboard_error'), get_settings_errors('ebdn_goods_list'));

settings_errors('ebdn_dashboard_error');

settings_errors('ebdn_goods_list');

?>
<div class="wrap"><h2 class="nav-tab-wrapper"></h2></div>
<div class="wrap light-tabs"
     default-rel="<?= (((int)filter_input(INPUT_GET, 'is_results') === 1 && count($errors) === 0) ? 'results' : 'filter_settings') ?>"
>
    <h2 class="nav-tab-wrapper">
        <a href="#" class="nav-tab nav-tab-active" rel="filter_settings">Filter</a>
        <a href="#" class="nav-tab nav-tab-active" rel="results">Results</a>
        <a href="#" class="nav-tab nav-tab-active" rel="blacklist">Blacklist</a>
    </h2>
    <div class="tab_content" rel="filter_settings">
        <form id="ebdn-search-form" method="GET">
            <input type="hidden" name="is_results" value="1"/>

            <input type="hidden" name="type" value="<?php echo $dashboard->type; ?>"/>
            <input type="hidden" name="page" id="page"
                   value="<?php echo(isset($_GET['page']) ? sanitize_text_field($_GET['page']) : ''); ?>"/>
            <input type="hidden" id="reset" name="reset" value=""/>
            <?php
            if ($dashboard->show_dashboard) {
                ?>
                <div class="separator"></div>
                <div class="text_content">
                    <h2>Search Filter</h2>

                    <table class="settings_table">
                        <tbody>
                        <?php $filters = $dashboard->api->getFilters(); ?>
                        <?php
                        /**
                         * @var array $filter
                         */
                        foreach ($filters as $filter_id => $filter) :
                            $filterName = $filter['name'];
                            ?>
                            <tr>
                                <td>
                                    <?php if (isset($filter['config']['label'])) : ?>
                                        <label
                                            for="<?php echo is_array($filter['name']) ? reset($filter['name']) : $filter['name']; ?>">
                                            <?php echo $filter['config']['label']; ?>:
                                        </label>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($filter['config']['type']) && $filter['config']['type'] === 'select'): ?>
                                        <?php $is_multiple = isset($filter['config']['multiple']) && $filter['config']['multiple']; ?>
                                        <select <?php echo $is_multiple ? 'multiple' : ''; ?>
                                            id="<?php echo $filter['name']; ?>"
                                            name="<?php echo $filter['name']; ?><?php echo $is_multiple ? '[]' : ''; ?>"
                                            class="<?php echo isset($filter['config']['class']) ? $filter['config']['class'] : ''; ?>"
                                            style="<?php echo isset($filter['config']['style']) ? $filter['config']['style'] : ''; ?>">
                                            <?php if (is_array($filter['config']['data_source'])): ?>
                                                <?php foreach ($filter['config']['data_source'] as $c): ?>
                                                    <?php if ($is_multiple): ?>
                                                        <option
                                                            <?php if (isset($c['level'])): ?>class="level_<?php echo $c['level']; ?>"<?php endif; ?>
                                                            value="<?php echo $c['id']; ?>"<?php if (isset($dashboard->filter[$filterName]) && is_array($dashboard->filter[$filterName]) && in_array($c['id'], $dashboard->filter[$filterName], false)): ?> selected<?php endif; ?>><?php echo $c['name']; ?></option>
                                                    <?php else: ?>
                                                        <option
                                                            <?php if (isset($c['level'])): ?>class="level_<?php echo $c['level']; ?>"<?php endif; ?>
                                                            value="<?php echo $c['id']; ?>"<?php if (isset($dashboard->filter[$filterName]) && $dashboard->filter[$filterName] === $c['id']): ?> selected<?php endif; ?>><?php echo $c['name']; ?></option>
                                                    <?php endif; ?>

                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    <?php else: ?>
                                        <?php if (isset($filter['config']['type']) && $filter['config']['type'] === 'checkbox'): ?>
                                            <?php // echo $dashboard->filter[$filterName]; ?>
                                            <?php if (is_array($filter['name'])): ?>
                                                <?php foreach ($filter['name'] as $nn): ?>
                                                    <?php if (isset($filter['config'][$nn]['label'])): ?>
                                                        <label
                                                            for="<?php echo $nn; ?>"><?php echo $filter['config'][$nn]['label']; ?></label>
                                                    <?php endif; ?>
                                                    <input name="<?php echo $nn; ?>" id="<?php echo $nn; ?>"
                                                           value="<?php echo isset($dashboard->filter[$nn]) ? $dashboard->filter[$nn] : (isset($filter['config'][$nn]['default']) ? $filter['config'][$nn]['default'] : '') ?>"
                                                           <?php if (isset($dashboard->filter[$nn])) : ?>checked<?php endif; ?>
                                                           type="checkbox"/>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <input name="<?php echo $filter['name']; ?>"
                                                       id="<?php echo $filter['name']; ?>"
                                                       value="<?php echo isset($dashboard->filter[$filterName]) ? $dashboard->filter[$filterName] : (isset($filter['config']['default']) ? $filter['config']['default'] : '') ?>"
                                                       <?php if (isset($dashboard->filter[$filterName])) : ?>checked<?php endif; ?>
                                                       type="checkbox"/>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <?php if (is_array($filter['name'])): ?>
                                                <?php foreach ($filter['name'] as $nn): ?>
                                                    <?php if (isset($filter['config'][$nn]['label'])): ?>
                                                        <label
                                                            class="form_label"
                                                            for="<?php echo $nn; ?>"><?php echo $filter['config'][$nn]['label']; ?></label>
                                                    <?php endif; ?>
                                                    <input name="<?php echo $nn; ?>" id="<?php echo $nn; ?>"
                                                           placeholder="<?php echo isset($filter['config'][$nn]['placeholder']) ? $filter['config'][$nn]['placeholder'] : ''; ?>"
                                                           value="<?php echo isset($dashboard->filter[$nn]) ? $dashboard->filter[$nn] : (isset($filter['config'][$nn]['default']) ? $filter['config'][$nn]['default'] : '') ?>"
                                                           class="small-text" type="text"/>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <input name="<?php echo $filter['name']; ?>"
                                                       id="<?php echo $filter['name']; ?>"
                                                       placeholder="<?php echo isset($filter['config']['placeholder']) ? $filter['config']['placeholder'] : ''; ?>"
                                                       value="<?php echo isset($dashboard->filter[$filterName]) ? $dashboard->filter[$filterName] : (isset($filter['config']['default']) ? $filter['config']['default'] : '') ?>"
                                                       class="regular-text" type="text"/>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (isset($filter['config']['description'])): ?>
                                        <span
                                            class="description"><?php echo $filter['config']['description']; ?></span>
                                    <?php endif; ?>
                                </td>

                            </tr>

                            <?php if (isset($filter['config']['dop_row']) && $filter['config']['dop_row']): ?>
                            <tr>
                                <td colspan="2">
                                    <?php
                                    echo $filter['config']['dop_row'];
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>
                <div class="separator"></div>
                <div class="text_content">
                    <h2>Link to category</h2>

                    <table class="settings_table">
                        <tbody>

                        <tr>
                            <td><label for="category_id">Category:</label></td>
                            <td>
                                <select id="link_category_id" name="link_category_id" class="category_list"
                                        style="width:25em;">
                                    <option value=""></option>
                                    <?php foreach ($dashboard->link_categories as $c): ?>
                                        <option
                                            value="<?php echo $c['term_id']; ?>"<?php if (isset($dashboard->filter['link_category_id']) && $dashboard->filter['link_category_id'] === $c['term_id']): ?> selected<?php endif; ?>>
                                            <?php
                                            for ($i = 1; $i < $c['level']; $i++) {
                                                echo '-';
                                            }
                                            ?>
                                            <?php echo $c['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="separator"></div>
                <div class="text_content btn_container">
                    <input type="button" id="ebdn-do-filter" class="button button-primary" value="Search"/>
                </div>
                <?php
            }
            ?>
        </form>
    </div>
    <div class="tab_content" rel="results">
        <?php if ($dashboard->loader->hasAccount()) : ?>
            <div class="separator"></div>
            <div class="text_content">
                <h2>Products list</h2>
            </div>
            <div class="before_list">
                <?php do_action('ebdn_before_product_list', $dashboard); ?>
            </div>
            <div id="ebdn-goods-table" class="ebdn-goods-table">
                <div class='import_process_loader'></div>
                <?php
                $dashboard->display();
                ?>
            </div>

            <?php add_thickbox(); ?>

            <div class="separator"></div>
        <?php endif; ?>
    </div>
    <div class="tab_content" rel="blacklist">

        <div class="separator"></div>
        <div class="text_content">
            <h2>Blacklist</h2>
        </div>
        <div class="ebdn-goods-table" id="ebdn-goods-table-blacklist">
            <div class='import_process_loader'></div>
            <?php
            $blackListTable = new BlacklistTable();
            $blackListTable->prepareItems();
            $blackListTable->display();
            ?>
        </div>
        <div class="separator"></div>
    </div>
</div>
<?php if ($dashboard->api->isInstaled() && $dashboard->show_dashboard): ?>
    <div id="upload_image_dlg" style="display: none">
        <div>
            <form id="image_upload_form" method="post" action="#" enctype="multipart/form-data">
                <input type='hidden' value='<?php echo wp_create_nonce('upload_thumb'); ?>' name='_nonce'/>
                <input type="hidden" name="upload_product_id" id="upload_product_id" value=""/>
                <input type="hidden" name="action" id="action" value="ebdn_upload_image"/>
                <input type="file" name="upload_image" id="upload_image"/>
                <br/><br/>
                <input id="submit-ajax" name="submit-ajax" type="submit" value="Upload this Image"
                       class="button button-primary"/> <span id="upload_progress"></span>
            </form>
        </div>
    </div>

    <div id="edit_desc_dlg" style="display: none"></div>
<?php endif; ?>

<script type="text/javascript">
    jQuery(document).ready(function () {
        DnolbonColumns.init('dashboard');
    });
</script>

