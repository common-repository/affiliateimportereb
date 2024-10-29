<?php
namespace Dnolbon\Ebdn\Wordpress\Translates;

class BingTranslateService
{
    private $clientSecret;
    private $clientID;

    private $authUrl = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
    private $scopeUrl = "http://api.microsofttranslator.com";
    private $grantType = "client_credentials";

    public function __construct($clientSecret, $clientID)
    {
        $this->clientID = $clientID;
        $this->clientSecret = $clientSecret;
    }

    public function translate($text, $to, $from = false)
    {

        $accessToken = $this->getAccessToken();

        //Create the authorization Header string.
        $authHeader = "Authorization: Bearer " . $accessToken;

        $params = "text=" . urlencode($text) . "&to=" . $to;
        if ($from) {
            $params .= "&from=" . $from;
        }

        $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";

        //Create the Translator Object.
        $translatorObj = new BingHTTPTranslator();

        //Get the curlResponse.
        $curlResponse = $translatorObj->curlRequest($translateUrl, $authHeader);

        $translatedStr = '';
        //Interprets a string of XML into an object.
        $xmlObj = simplexml_load_string($curlResponse);
        foreach ((array)$xmlObj[0] as $val) {
            $translatedStr = $val;
        }

        return $translatedStr;
    }

    private function getAccessToken()
    {
        //Create the AccessTokenAuthentication object.
        $authObj = new BingAccessTokenAuthentication();
        //Get the Access token.
        $accessToken = $authObj->getTokens(
            $this->grantType,
            $this->scopeUrl,
            $this->clientID,
            $this->clientSecret,
            $this->authUrl
        );

        return $accessToken;
    }

    public function translateArray($str_array, $to, $from = false)
    {

        $accessToken = $this->getAccessToken();

        //Create the authorization Header string.
        $authHeader = "Authorization: Bearer " . $accessToken;

        //Set the params.//

        $contentType = 'text/plain';
        //Create the Translator Object.
        $translatorObj = new BingHTTPTranslator();

        $requestXml = $translatorObj->createReqXML($from, $to, $contentType, $str_array);

        //HTTP TranslateMenthod URL.
        $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/TranslateArray";

        //Call HTTP Curl Request.
        $curlResponse = $translatorObj->curlRequest($translateUrl, $authHeader, $requestXml);

        $translatedStrArray = array();
        //Interprets a string of XML into an object.
        $xmlObj = simplexml_load_string($curlResponse);
        $i = 0;

        foreach ($xmlObj->TranslateArrayResponse as $translatedArrObj) {
            $i++;
            $translatedStrArray[] = (string)$translatedArrObj->TranslatedText;
        }

        return $translatedStrArray;
    }
}
