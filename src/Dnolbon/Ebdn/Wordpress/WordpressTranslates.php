<?php
namespace Dnolbon\Ebdn\Wordpress;

use Dnolbon\Ebdn\Wordpress\Translates\BingTranslateService;

class WordpressTranslates
{
    private $languages;
    /**
     * Microsoft Bing Translate API
     *
     * @var BingTranslateService
     */
    private $translateService = false;

    public function __construct()
    {
        $this->setupBingService();

        add_action('ebdn_tr_getLocalizedUrl', array($this, 'getLocalizedUrl'), 1000, 2);
        add_action('ebdn_tr_getLocalizedText', array($this, 'getLocalizedText'), 1000, 3);
        add_action('ebdn_tr_getLocalizedAttributes', array($this, 'getLocalizedAttributes'), 1000, 3);
        $this->languages['ebay'] = get_option('ebdn_tr_ebay_language', 'en');
    }

    private function setupBingService()
    {
        $bing_client_id = get_option('ebdn_tr_ebay_bing_client_id', '');
        $bing_client_secret = get_option('ebdn_tr_ebay_bing_secret', '');

        if ($bing_client_id && $bing_client_secret) {
            $this->translateService = new BingTranslateService($bing_client_secret, $bing_client_id);
        }

//        if (!$this->translateService) {
////            throw new \Exception('No translate settings');
//        }
    }

    public function getLocalizedUrl($url, $params)
    {
        $current_lang = $this->languages['ebay'];

        if ($params['type'] === 'ebay_desc' && $current_lang !== 'en') {
            $external_id = $params['external_id'];
            $url = "http://" . $current_lang . ".aliexpress.com/getSubsiteDescModuleAjax.htm?productId=" . $external_id;
        }

        if ($params['type'] === 'ebay_request') {
            $url = $url . "&language=" . $current_lang;
        }

        if ($params['type'] === 'ebay_reviews' && $current_lang !== 'en') {
            $url = str_replace('www', $current_lang, $url);
        }

        return $url;
    }

    public function getLocalizedAttributes($data, $api_type)
    {

        if ($this->translateService) {
            $target = $this->languages[$api_type];

            $names = array();
            $values = array();

            foreach ($data as $attr_key => $attr_val) {
                $names[] = $data[$attr_key]['name'];
                $values[] = $data[$attr_key]['value'];
            }

            $names = $this->translateService->translateArray($names, $target);
            $values = $this->translateService->translateArray($values, $target);

            for ($i = 0; $i <= count($data) - 1; $i++) {
                $data[$i] = array('name' => $names[$i], 'value' => $values[$i]);
            }
        }

        return $data;

    }

    public function getLocalizedText($data, $apiType, $ignoreLanguage = false)
    {

        if ($this->translateService) {
            $target = $this->languages[$apiType];

            if ($ignoreLanguage && strtolower($ignoreLanguage) === $target) {
                return $data;
            }

            $data = $this->translateService->translate($data, $target);
            if ($target === 'ru') {
                $data = iconv('UTF-8', 'WINDOWS-1251', $data);
            }
        }

        return $data;
    }

    public function install()
    {
        add_option('ebdn_tr_ebay_language', 'en', '', 'no');
        add_option('ebdn_tr_ebay_bing_secret', '', '', 'no');
        add_option('ebdn_tr_ebay_bing_client_id', '', '', 'no');

        do_action('ebdn_tr_translate_install_action');
    }

    public function uninstall()
    {
        delete_option('ebdn_tr_ebay_language');
        delete_option('ebdn_tr_ebay_bing_secret');
        delete_option('ebdn_tr_ebay_bing_client_id');

        do_action('ebdn_tr_translate_uninstall_action');
    }
}
