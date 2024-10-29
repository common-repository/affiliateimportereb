<?php
$api_list = ebdn_get_api_list(true);
$current_module = (isset($_REQUEST['module']) && $_REQUEST['module']) ? $_REQUEST['module'] : 'common';

?>
<div class="ebdn-settings-content">
    <div class="wrap light-tabs" default-rel="<?php echo $current_module; ?>">
        <h2 class="nav-tab-wrapper">
            <a href="#" class="nav-tab<?php echo $current_module === 'common' ? ' nav-tab-active' : ''; ?>"
               rel="common">Common
                settings</a>
            <a href="#" class="nav-tab<?php echo $current_module === 'price_formula' ? ' nav-tab-active' : ''; ?>"
               rel="price_formula">Price Rules</a>
            <a href="#" class="nav-tab nav-tab-active" rel="shedule_settings">Shedule settings</a>
            <!--<a href="#" class="nav-tab nav-tab-active" rel="language">Language settings</a>-->
            <?php
            /**
             * @var EBDN_AbstractConfigurator $api
             */
            foreach ($api_list as $api): ?>
                <a href="#" class="nav-tab<?php echo $current_module === $api->getType() ? ' nav-tab-active' : ''; ?>"
                   rel="<?php echo $api->getType(); ?>"><?php echo $api->getConfigValues('dashboard_title'); ?>
                    settings</a>
            <?php endforeach; ?>
        </h2>
        <div class="tab_content" rel="common">
            <div class="separator"></div>

            <form method="post">
                <input type="hidden" name="setting_form" value="1"/>
                <input type="hidden" name="module" value="common"/>
                <div class="text_content">
                    <h3>Common setting</h3>
                    <table class="settings_table">
                        <tr valign="top">
                            <td scope="row" class="titledesc"><label for="ebdn_currency_conversion_factor">Currency
                                    conversion factor</label>
                            </td>
                            <td class="forminp forminp-text"><input type="text" id="ebdn_currency_conversion_factor"
                                                                    name="ebdn_currency_conversion_factor"
                                                                    value="<?php echo get_option('ebdn_currency_conversion_factor', '1'); ?>"/>
                            </td>
                        </tr>

                        <tr valign="top">
                            <td scope="row" class="titledesc">
                                <label for="ebdn_default_type">Default Product Type</label>
                            </td>
                            <td class="forminp forminp-select">
                                <?php $cur_ebdn_default_type = get_option('ebdn_default_type', 'simple'); ?>
                                <select name="ebdn_default_type" id="ebdn_default_type">
                                    <option value="simple"
                                            <?php if ($cur_ebdn_default_type === 'simple'): ?>selected="selected"<?php endif; ?>>
                                        Simple Product
                                    </option>
                                    <option value="external"
                                            <?php if ($cur_ebdn_default_type === 'external'): ?>selected="selected"<?php endif; ?>>
                                        External/Affiliate Product
                                    </option>
                                    <!--<option value="grouped" <?php if ($cur_ebdn_default_type === 'grouped'): ?>selected="selected"<?php endif; ?>>Grouped Product</option>-->
                                    <!--<option value="variable" <?php if ($cur_ebdn_default_type === 'variable'): ?>selected="selected"<?php endif; ?>>Variable Product</option>-->
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <td scope="row" class="titledesc">
                                <label for="ebdn_default_status">Default Product Status</label>
                            </td>
                            <td class="forminp forminp-select">
                                <?php $cur_ebdn_default_status = get_option('ebdn_default_status', 'publish'); ?>
                                <select name="ebdn_default_status" id="ebdn_default_status">
                                    <option value="publish"
                                            <?php if ($cur_ebdn_default_status === 'publish'): ?>selected="selected"<?php endif; ?>>
                                        publish
                                    </option>
                                    <option value="draft"
                                            <?php if ($cur_ebdn_default_status === 'draft'): ?>selected="selected"<?php endif; ?>>
                                        draft
                                    </option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="separator"></div>
                <div class="text_content">
                    <h3>Import setting</h3>
                    <table class="settings_table">
                        <tr valign="top">
                            <td scope="row" class="titledesc"><label for="ebdn_remove_link_from_desc">Remove links from
                                    description</label>
                            </th>
                            <td class="forminp forminp-text"><input type="checkbox" id="ebdn_remove_link_from_desc"
                                                                    name="ebdn_remove_link_from_desc" value="yes"
                                                                    <?php if (get_option('ebdn_remove_link_from_desc', false)): ?>checked<?php endif; ?>/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td scope="row" class="titledesc"><label for="ebdn_remove_img_from_desc">Remove images from
                                    description</label>
                            </th>
                            <td class="forminp forminp-text"><input type="checkbox" id="ebdn_remove_img_from_desc"
                                                                    name="ebdn_remove_img_from_desc" value="yes"
                                                                    <?php if (get_option('ebdn_remove_img_from_desc', false)): ?>checked<?php endif; ?>/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td scope="row" class="titledesc"><label for="ebdn_import_product_images_limit">Import
                                    product
                                    images limit</label>
                            </th>
                            <td class="forminp forminp-text"><input type="text" id="ebdn_import_product_images_limit"
                                                                    name="ebdn_import_product_images_limit"
                                                                    value="<?php echo get_option('ebdn_import_product_images_limit'); ?>"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td scope="row" class="titledesc"><label for="ebdn_min_product_quantity">Default product
                                    quantity</label>
                            </th>
                            <td class="forminp forminp-text">
                                from: <input type="text" style="width:60px" id="ebdn_min_product_quantity"
                                             name="ebdn_min_product_quantity"
                                             value="<?php echo get_option('ebdn_min_product_quantity', 5); ?>"/>
                                to: <input type="text" style="width:60px" id="ebdn_max_product_quantity"
                                           name="ebdn_max_product_quantity"
                                           value="<?php echo get_option('ebdn_max_product_quantity', 10); ?>"/>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="separator"></div>
                <div class="text_content">
                    <h3>Proxy settings</h3>
                    <table class="settings_table">
                        <tr valign="top">
                            <td scope="row" class="titledesc"><label for="ebdn_use_proxy">Use proxy</label>
                            </th>
                            <td class="forminp forminp-text"><input type="checkbox" id="ebdn_use_proxy"
                                                                    name="ebdn_use_proxy" value="yes"
                                                                    <?php if (get_option('ebdn_use_proxy', false)): ?>checked<?php endif; ?>/>
                            </td>
                        </tr>
                        <tr valign="top"
                            <?php if (!get_option('ebdn_use_proxy', false)): ?>style="display:none;"<?php endif; ?>>
                            <td scope="row" class="titledesc"><label for="ebdn_proxies_list">Proxy list</label>
                            </th>
                            <td class="forminp forminp-text">
                            <textarea id="ebdn_proxies_list" name="ebdn_proxies_list"
                                      style="width:500px;height: 150px;"><?php echo get_option('ebdn_proxies_list', ''); ?></textarea>
                                <div style="padding-top: 5px;">
                                <span class="description">
                                    Proxy example:<br/>
                                    proxy.example.com:8080<br/>
                                    username:password@proxy.example.com:8080<br/>
                                    <strong>You can buy proxies <a
                                                href="http://www.squidproxies.com/billing/aff.php?aff=1112"
                                                target="_blank">here</a></strong>
                                </span>
                                </div>
                                <div style="padding-top: 5px;">
                                    <a href="#" id="proxy_test" class="proxy_test">Test proxy</a>
                                    <div id="proxy_test_result" style="padding: 10px;font-size: 85%;"></div>
                                </div>
                            </td>
                        </tr>
                    </table>


                    <?php do_action('ebdn_print_common_setting_page'); ?>


                    <input class="button-primary" type="submit" value="Save settings"/><br/>
                </div>
            </form>
            <script>
                (function ($) {


                    jQuery("#ebdn_price_auto_update").change(function () {
                        jQuery("#ebdn_price_auto_update_period").prop('disabled', !jQuery(this).is(':checked'));
                        jQuery("#ebdn_regular_price_auto_update").prop('disabled', !jQuery(this).is(':checked'));
                        jQuery("#ebdn_regular_price_auto_update").prop('checked', jQuery(this).is(':checked'));
                        jQuery("#ebdn_update_per_schedule").prop('disabled', !jQuery(this).is(':checked'));
                        jQuery("#ebdn_not_available_product_status").prop('disabled', !jQuery(this).is(':checked'));
                        return true;
                    });

                    jQuery("#ebdn_use_proxy").change(function () {
                        if (jQuery(this).is(':checked')) {
                            jQuery("#ebdn_proxies_list").closest('tr').show();
                        } else {
                            jQuery("#ebdn_proxies_list").closest('tr').hide();
                        }
                    });

                    $(".proxy_test").click(function () {
                        var data = {'action': 'ebdn_proxy_test'};
                        $('#proxy_test_result').html('testing...');
                        $.post(ajaxurl, data, function (response) {
                            $('#proxy_test_result').html(response);
                        });
                        return false;
                    });
                })(jQuery);


            </script>

        </div>
        <div class="tab_content" rel="shedule_settings">
            <form method="POST">
                <input type="hidden" name="shedule_settings" value="1">
                <div class="separator"></div>
                <div class="text_content">
                    <table class="settings_table">
                        <tr valign="top">
                            <td><label for="ebdn_price_auto_update">Auto Update (stock avail. only)</label></td>
                            <td>
                                <input type="checkbox" id="ebdn_price_auto_update" name="ebdn_price_auto_update"
                                       value="yes"
                                <?= (get_option('ebdn_price_auto_update', false) ? ' checked' : '') ?>
                            </td>
                        </tr>

                        <tr valign="top">
                            <td><label for="ebdn_regular_price_auto_update">Auto Update
                                    Price</label></td>
                            <td><input type="checkbox" id="ebdn_regular_price_auto_update"
                                       name="ebdn_regular_price_auto_update" value="yes"
                                    <?= (!get_option('ebdn_price_auto_update', false) ? ' disabled' : '') ?>
                                    <?= (get_option('ebdn_regular_price_auto_update', false) ? ' checked' : '') ?>/>
                            </td>
                        </tr>

                        <tr valign="top">
                            <td>
                                <label for="ebdn_not_available_product_status">Not available product status</label>
                            </td>
                            <td class="forminp forminp-select">
                                <?php $notAvailableStatus = get_option('ebdn_not_available_product_status', 'trash'); ?>
                                <select name="ebdn_not_available_product_status"
                                        id="ebdn_not_available_product_status"
                                    <?= (!get_option('ebdn_price_auto_update', false) ? ' disabled' : '') ?>>
                                    <option value="trash"
                                            <?php if ($notAvailableStatus === 'trash'): ?>selected="selected"<?php endif; ?>>
                                        Trash
                                    </option>
                                    <option value="outofstock"
                                            <?php if ($notAvailableStatus === 'outofstock'): ?>selected="selected"<?php endif; ?>>
                                        Out of stock
                                    </option>
                                    <option value="instock"
                                            <?php if ($notAvailableStatus === 'instock'): ?>selected="selected"<?php endif; ?>>
                                        In stock
                                    </option>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <td>
                                <label for="ebdn_price_auto_update_period">Update Schedule</label>
                            </td>
                            <td class="forminp forminp-select">
                                <?php $cur_ebdn_price_auto_update_period = get_option('ebdn_price_auto_update_period', 'daily'); ?>
                                <select name="ebdn_price_auto_update_period" id="ebdn_price_auto_update_period"
                                        <?php if (!get_option('ebdn_price_auto_update', false)): ?>disabled<?php endif; ?>>
                                    <option value="ebdn_5_mins"
                                            <?php if ($cur_ebdn_price_auto_update_period === 'ebdn_5_mins'): ?>selected="selected"<?php endif; ?>>
                                        Every 5 Minutes
                                    </option>
                                    <option value="ebdn_15_mins"
                                            <?php if ($cur_ebdn_price_auto_update_period === 'ebdn_15_mins'): ?>selected="selected"<?php endif; ?>>
                                        Every 15 Minutes
                                    </option>
                                    <option value="hourly"
                                            <?php if ($cur_ebdn_price_auto_update_period === 'hourly'): ?>selected="selected"<?php endif; ?>>
                                        hourly
                                    </option>
                                    <option value="twicedaily"
                                            <?php if ($cur_ebdn_price_auto_update_period === 'twicedaily'): ?>selected="selected"<?php endif; ?>>
                                        twicedaily
                                    </option>
                                    <option value="daily"
                                            <?php if ($cur_ebdn_price_auto_update_period === 'daily'): ?>selected="selected"<?php endif; ?>>
                                        daily
                                    </option>
                                </select>
                            </td>
                        </tr>

                        <td><label for="ebdn_update_per_schedule">The number of products
                                update per schedule</label></td>
                        <td><input type="text" id="ebdn_update_per_schedule"
                                   name="ebdn_update_per_schedule"
                                   value="<?php echo get_option('ebdn_update_per_schedule', 20); ?>"
                                   <?php if (!get_option('ebdn_price_auto_update', false)): ?>disabled<?php endif; ?>/>
                        </td>
                    </table>
                </div>
                <div class="separator"></div>
                <div class="text_content btn_container">
                    <input class="button-primary" type="submit" value="Save settings"/><br/>
                </div>
            </form>
        </div>
        <div class="tab_content" rel="language">
            <form method="POST">
                <input type="hidden" name="language_settings" value="1">
                <div class="separator"></div>
                <div class="text_content">
                    <table class="settings_table">
                        <tr valign="top">
                            <td scope="row" class="titledesc">
                                <label for="ebdn_tr_ebay_language">Language</label>
                            </td>
                            <td class="forminp forminp-select">
                                <?php $cur_ebdn_tr_language = get_option('ebdn_tr_ebay_language', 'en'); ?>
                                <select name="ebdn_tr_ebay_language" id="ebdn_tr_ebay_language">
                                    <option value="en"
                                            <?php if ($cur_ebdn_tr_language == "en"): ?>selected="selected"<?php endif; ?>>
                                        English
                                    </option>
                                    <option value="ar"
                                            <?php if ($cur_ebdn_tr_language == "ar"): ?>selected="selected"<?php endif; ?>>
                                        Arabic
                                    </option>
                                    <option value="de"
                                            <?php if ($cur_ebdn_tr_language == "de"): ?>selected="selected"<?php endif; ?>>
                                        German
                                    </option>
                                    <option value="es"
                                            <?php if ($cur_ebdn_tr_language == "es"): ?>selected="selected"<?php endif; ?>>
                                        Spanish
                                    </option>
                                    <option value="fr"
                                            <?php if ($cur_ebdn_tr_language == "fr"): ?>selected="selected"<?php endif; ?>>
                                        French
                                    </option>
                                    <option value="it"
                                            <?php if ($cur_ebdn_tr_language == "it"): ?>selected="selected"<?php endif; ?>>
                                        Italian
                                    </option>
                                    <option value="pl"
                                            <?php if ($cur_ebdn_tr_language == "pl"): ?>selected="selected"<?php endif; ?>>
                                        Polish
                                    </option>
                                    <option value="ja"
                                            <?php if ($cur_ebdn_tr_language == "ja"): ?>selected="selected"<?php endif; ?>>
                                        Japanese
                                    </option>
                                    <option value="ko"
                                            <?php if ($cur_ebdn_tr_language == "ko"): ?>selected="selected"<?php endif; ?>>
                                        Korean
                                    </option>
                                    <option value="nl"
                                            <?php if ($cur_ebdn_tr_language == "nl"): ?>selected="selected"<?php endif; ?>>
                                        Notherlandish (Dutch)
                                    </option>
                                    <option value="pt"
                                            <?php if ($cur_ebdn_tr_language == "pt"): ?>selected="selected"<?php endif; ?>>
                                        Portuguese (Brasil)
                                    </option>
                                    <option value="ru"
                                            <?php if ($cur_ebdn_tr_language == "ru"): ?>selected="selected"<?php endif; ?>>
                                        Russian
                                    </option>
                                    <option value="th"
                                            <?php if ($cur_ebdn_tr_language == "th"): ?>selected="selected"<?php endif; ?>>
                                        Thai
                                    </option>
                                    <option value="id"
                                            <?php if ($cur_ebdn_tr_language == "id"): ?>selected="selected"<?php endif; ?>>
                                        Indonesian
                                    </option>
                                    <option value="tr"
                                            <?php if ($cur_ebdn_tr_language == "tr"): ?>selected="selected"<?php endif; ?>>
                                        Turkish
                                    </option>
                                    <option value="vi"
                                            <?php if ($cur_ebdn_tr_language == "vi"): ?>selected="selected"<?php endif; ?>>
                                        Vietnamese
                                    </option>
                                    <option value="he"
                                            <?php if ($cur_ebdn_tr_language == "he"): ?>selected="selected"<?php endif; ?>>
                                        Hebrew
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td scope="row" class="titledesc"><label for="ebdn_tr_ebay_bing_secret">Bing Secret
                                    password</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input type="text" id="ebdn_tr_ebay_bing_secret"
                                       name="ebdn_tr_ebay_bing_secret"
                                       value="<?php echo get_option('ebdn_tr_ebay_bing_secret', ''); ?>"/>
                                <span class="description">The Secret password for your Bing application</span>
                            </td>
                        </tr>
                        <tr valign="top">
                            <td scope="row" class="titledesc"><label for="ebdn_tr_ebay_bing_client_id">Bing
                                    Client
                                    ID</label>
                            </th>
                            <td class="forminp forminp-text">
                                <input type="text" id="ebdn_tr_ebay_bing_client_id"
                                       name="ebdn_tr_ebay_bing_client_id"
                                       value="<?php echo get_option('ebdn_tr_ebay_bing_client_id', ''); ?>"/>
                                <span class="description">The Client ID for your Bing application</span>&nbsp;<a
                                        href="https://www.microsoft.com/en-us/translator/getstarted.aspx"
                                        target="_blank">Get
                                    own Bing keys</a>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="separator"></div>
                <div class="text_content btn_container">
                    <input class="button-primary" type="submit" value="Save settings"/><br/>
                </div>
            </form>
        </div>
        <div class="tab_content" rel="price_formula">
            <div class="separator"></div>
            <div class="text_content">
                <h3>Add price rule</h3>
                <table>
                    <tr id="ebdn_price_formula_add_form">
                        <td>&nbsp;</td>
                        <td>
                            <select name="type">
                                <option value="">Any module</option>
                                <?php foreach ($api_list as $api): ?>
                                    <option
                                            value="<?php echo $api->getType(); ?>"><?php echo $api->getType(); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <?php $categories_tree = EBDN_Utils::getCategoriesTree(); ?>
                            <select name="category" style="width:100%">
                                <option value="">Any category</option>
                                <?php foreach ($categories_tree as $cat): ?>
                                    <option value="<?php echo $cat['term_id'] ?>"><?php
                                        for ($i = 1; $i < $cat['level']; $i++) {
                                            echo ' - ';
                                        }
                                        ?><?php echo $cat['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" name="min_price" value="" placeholder="Min price" class="small"/></td>
                        <td class="price_label"> < PRICE <</td>
                        <td><input type="text" name="max_price" value="" placeholder="Max price" class="small"/></td>
                        <td>
                            <select name="sign">
                                <option value="="> =</option>
                                <option value="+"> +</option>
                                <option value="*"> *</option>
                            </select>
                        </td>
                        <td><input type="text" name="value" class="small" value="" placeholder="Value"/></td>
                        <td class="discount">
                            Discount % <select name="discount1">
                                <option value="">source %</option>
                                <option value="0">0%</option>
                                <option value="5">5%</option>
                                <option value="10">10%</option>
                                <option value="15">15%</option>
                                <option value="20">20%</option>
                                <option value="25">25%</option>
                                <option value="30">30%</option>
                                <option value="35">35%</option>
                                <option value="40">40%</option>
                                <option value="45">45%</option>
                                <option value="50">50%</option>
                                <option value="55">55%</option>
                                <option value="60">60%</option>
                                <option value="65">65%</option>
                                <option value="70">70%</option>
                                <option value="75">75%</option>
                                <option value="80">80%</option>
                                <option value="85">85%</option>
                                <option value="90">90%</option>
                                <option value="95">95%</option>
                            </select>

                            - <select name="discount2">
                                <option value="">source %</option>
                                <option value="0">0%</option>
                                <option value="5">5%</option>
                                <option value="10">10%</option>
                                <option value="15">15%</option>
                                <option value="20">20%</option>
                                <option value="25">25%</option>
                                <option value="30">30%</option>
                                <option value="35">35%</option>
                                <option value="40">40%</option>
                                <option value="45">45%</option>
                                <option value="50">50%</option>
                                <option value="55">55%</option>
                                <option value="60">60%</option>
                                <option value="65">65%</option>
                                <option value="70">70%</option>
                                <option value="75">75%</option>
                                <option value="80">80%</option>
                                <option value="85">85%</option>
                                <option value="90">90%</option>
                                <option value="95">95%</option>
                            </select>
                        </td>
                        <td>
                            <button class="button-primary" id="ebdn_add_formula">Add</button>
                        </td>
                    </tr>
                </table>
                <div class="price_formula_description">Here you can configure your price modification algorithm.</div>
            </div>
            <div class="separator"></div>
            <div class="text_content">
                <h3>Price rules list</h3>
                <?php $formula_list = EBDN_PriceFormula::loadFormulasList(); ?>
                <table id="ebdn_price_formula" class="wp-list-table widefat fixed striped">
                    <thead>
                    <tr>
                        <td class="manage-column column-pos">#
                        </th>
                        <td class="manage-column column-module">Module
                        </th>
                        <td class="manage-column column-category">Category
                        </th>
                        <td class="manage-column column-price">Price
                        </th>
                        <td class="manage-column column-value">New Price
                        </th>
                        <td class="manage-column column-discount">Discount %
                        </th>
                        <td class="manage-column column-action">&nbsp;
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($formula_list as $formula): ?>
                        <tr formula-id="<?php echo $formula->id; ?>">
                            <td><?php echo $formula->pos; ?></td>
                            <td><?php echo $formula->type; ?></td>
                            <td><?php echo $formula->category_name; ?></td>
                            <td><?php echo $formula->min_price; ?> < PRICE < <?php echo $formula->max_price; ?></td>
                            <td><?php echo ($formula->sign === '=') ? $formula->value : ('PRICE ' . $formula->sign . ' ' . $formula->value); ?></td>
                            <td>
                                <?php
                                if (strlen(trim((string)$formula->discount1)) > 0 && strlen(trim((string)$formula->discount2)) > 0) {
                                    if ((int)$formula->discount1 > (int)$formula->discount2) {
                                        echo $formula->discount2 . '% &mdash; ' . $formula->discount1 . '%';
                                    } else {
                                        echo $formula->discount1 . '% &mdash; ' . $formula->discount2 . '%';
                                    }
                                } else if (trim((string)$formula->discount1) !== '' || trim((string)$formula->discount2) !== '') {
                                    echo (strlen(trim((string)$formula->discount1)) > 0 ? $formula->discount1 : $formula->discount2) . '%';
                                } else {
                                    echo 'source %';
                                }
                                ?>
                            </td>
                            <td>
                                <a class="button-primary ebdn_edit_formula">Edit</a>
                                <a class="button-primary ebdn_del_formula">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php foreach ($api_list as $api): ?>
            <div class="tab_content" rel="<?php echo $api->getType(); ?>">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="setting_form" value="1"/>
                    <input type="hidden" name="module" value="<?php echo $api->getType(); ?>"/>

                    <?php do_action('ebdn_print_api_setting_page', $api); ?>

                    <div class="text_content">
                        <h3>Common setting</h3>
                        <table class="settings_table">
                            <tr valign="top">
                                <td scope="row" class="titledesc"><label
                                            for="ebdn_ebay_per_page"><?php _ex('Products per page', 'Setting title', 'ebdn'); ?></label>
                                </td>
                                <td class="forminp forminp-text">
                                    <input type="text" id="ebdn_ebay_per_page" name="ebdn_ebay_per_page"
                                           value="<?php echo esc_attr(get_option('ebdn_ebay_per_page', 20)); ?>"/>
                                    <span class="description"><?php printf(_x('the maximum number of items is %d', 'Setting desc', 'ebdn'), 100); ?></span>
                                </td>
                            </tr>

                            <tr valign="top">
                                <td scope="row" class="titledesc"><label
                                            for="ebdn_ebay_extends_cats"><?php _ex('Use sub categories', 'Setting title', 'ebdn'); ?></label>
                                </td>
                                <td class="forminp forminp-text"><input type="checkbox" id="ebdn_ebay_extends_cats"
                                                                        name="ebdn_ebay_extends_cats" value="yes"
                                                                        <?php if (get_option('ebdn_ebay_extends_cats', false)): ?>checked<?php endif; ?>/>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="text_content">
                        <h3><?php _ex('Affiliate setting', 'Setting section', 'ebdn'); ?></h3>
                        <table class="settings_table">
                            <tr valign="top">
                                <td scope="row" class="titledesc"><label
                                            for="ebdn_ebay_custom_id"><?php _ex('CustomId', 'Setting title', 'ebdn'); ?></label>
                                </td>
                                <td class="forminp forminp-text"><input type="text" id="ebdn_ebay_custom_id"
                                                                        name="ebdn_ebay_custom_id"
                                                                        value="<?php echo esc_attr(get_option('ebdn_ebay_custom_id')); ?>"/>
                                </td>
                            </tr>
                            <tr valign="top">
                                <td scope="row" class="titledesc"><label
                                            for="ebdn_ebay_geo_targeting"><?php _ex('geoTargeting', 'Setting title', 'ebdn'); ?></label>
                                </td>
                                <td class="forminp forminp-text"><input type="checkbox" id="ebdn_ebay_geo_targeting"
                                                                        name="ebdn_ebay_geo_targeting" value="yes"
                                                                        <?php if (get_option('ebdn_ebay_geo_targeting', false)): ?>checked<?php endif; ?>/>
                                </td>
                            </tr>
                            <tr valign="top">
                                <td scope="row" class="titledesc"><label
                                            for="ebdn_ebay_network_id"><?php _ex('Network_id', 'Setting title', 'ebdn'); ?></label>
                                </td>
                                <td class="forminp forminp-select">
                                    <?php $cur_ebdn_ebay_network_id = get_option('ebdn_ebay_network_id', '9'); ?>
                                    <select name="ebdn_ebay_network_id" id="ebdn_ebay_network_id">
                                        <option value="2"
                                                <?php if ($cur_ebdn_ebay_network_id == "2"): ?>selected="selected"<?php endif; ?>><?php _ex('Be Free', 'Setting option', 'ebdn'); ?></option>
                                        <option value="3"
                                                <?php if ($cur_ebdn_ebay_network_id == "3"): ?>selected="selected"<?php endif; ?>><?php _ex('Affilinet', 'Setting option', 'ebdn'); ?></option>
                                        <option value="4"
                                                <?php if ($cur_ebdn_ebay_network_id == "4"): ?>selected="selected"<?php endif; ?>><?php _ex('TradeDoubler', 'Setting option', 'ebdn'); ?></option>
                                        <option value="5"
                                                <?php if ($cur_ebdn_ebay_network_id == "5"): ?>selected="selected"<?php endif; ?>><?php _ex('Mediaplex', 'Setting option', 'ebdn'); ?></option>
                                        <option value="6"
                                                <?php if ($cur_ebdn_ebay_network_id == "6"): ?>selected="selected"<?php endif; ?>><?php _ex('DoubleClick', 'Setting option', 'ebdn'); ?></option>
                                        <option value="7"
                                                <?php if ($cur_ebdn_ebay_network_id == "7"): ?>selected="selected"<?php endif; ?>><?php _ex('Allyes', 'Setting option', 'ebdn'); ?></option>
                                        <option value="8"
                                                <?php if ($cur_ebdn_ebay_network_id == "8"): ?>selected="selected"<?php endif; ?>><?php _ex('BJMT', 'Setting option', 'ebdn'); ?></option>
                                        <option value="9"
                                                <?php if ($cur_ebdn_ebay_network_id == "9"): ?>selected="selected"<?php endif; ?>><?php _ex('eBay Partner Network', 'Setting option', 'ebdn'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top">
                                <td scope="row" class="titledesc"><label
                                            for="ebdn_ebay_tracking_id"><?php _ex('TrackingId', 'Setting title', 'ebdn'); ?></label>
                                </td>
                                <td class="forminp forminp-text"><input type="text" id="ebdn_ebay_tracking_id"
                                                                        name="ebdn_ebay_tracking_id"
                                                                        value="<?php echo esc_attr(get_option('ebdn_ebay_tracking_id')); ?>"/>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="text_content">
                        <input class="button-primary" type="submit" value="Save settings"/><br/>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>

        <script>
            jQuery(".ebdn-settings-content .account-content a.use_custom_account_param").click(function () {
                jQuery(this).closest('form').find('input[name="account_type"]').remove();
                jQuery(this).closest('form').append('<input type="hidden" name="account_type" value="custom"/>');
                jQuery(this).closest('form').submit();
                return false;
            });

            jQuery(".ebdn-settings-content .account-content a.use_default_account_param").click(function () {
                jQuery(this).closest('form').find('input[name="account_type"]').remove();
                jQuery(this).closest('form').append('<input type="hidden" name="account_type" value="default"/>');
                jQuery(this).closest('form').submit();
                return false;
            });
        </script>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        if (location.hash != '') {
            var selector = "a[rel='" + location.hash.substring(1, location.hash.length) + "']";
            console.log(selector);
            jQuery(selector).click();
        }
    });
</script>