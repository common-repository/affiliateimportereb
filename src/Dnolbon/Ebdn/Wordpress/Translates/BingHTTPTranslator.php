<?php
namespace Dnolbon\Ebdn\Wordpress\Translates;

class BingHTTPTranslator
{
    /*
         * Create and execute the HTTP CURL request.
         *
         * @param string $url        HTTP Url.
         * @param string $authHeader Authorization Header string.
         * @param string $postData   Data to post.
         *
         * @return string.
         *
         */
    public function curlRequest($url, $authHeader, $postData = '')
    {
        //Initialize the Curl Session.
        $ch = curl_init();
        //Set the Curl url.
        curl_setopt($ch, CURLOPT_URL, $url);
        //Set the HTTP HEADER Fields.
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($authHeader, "Content-Type: text/xml"));
        //CURLOPT_RETURNTRANSFER- true to return the transfer as a string of the return value of curl_exec().
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($postData) {
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, true);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        //Execute the  cURL session.
        $curlResponse = curl_exec($ch);
        //Get the Error Code returned by Curl.
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
            $curlError = curl_error($ch);
            throw new \Exception($curlError);
        }
        //Close a cURL session.
        curl_close($ch);
        return $curlResponse;
    }

    /*
    * Create Request XML Format.
    *
    * @param string $fromLanguage   Source language Code.
    * @param string $toLanguage     Target language Code.
    * @param string $contentType    Content Type.
    * @param string $inputStrArr    Input String Array.
    *
    * @return string.
    */
    public function createReqXML($fromLanguage, $toLanguage, $contentType, $inputStrArr)
    {
        //Create the XML string for passing the values.
        $requestXml = "<TranslateArrayRequest>" .
            "<AppId/>" .
            "<From>" . ($fromLanguage ? $fromLanguage : '') . "</From>" .
            "<Options>" .
            "<Category xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "<ContentType xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\">
                $contentType
            </ContentType>" .
            "<ReservedFlags xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "<State xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "<Uri xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "<User xmlns=\"http://schemas.datacontract.org/2004/07/Microsoft.MT.Web.Service.V2\" />" .
            "</Options>" .
            "<Texts>";
        foreach ($inputStrArr as $inputStr) {
            $requestXml .= "<string xmlns=\"http://schemas.microsoft.com/2003/10/Serialization/Arrays\">
                            <![CDATA[" . $inputStr . "]]>
                            </string>";
        }
        $requestXml .= "</Texts>" .
            "<To>$toLanguage</To>" .
            "</TranslateArrayRequest>";
        return $requestXml;
    }
}
