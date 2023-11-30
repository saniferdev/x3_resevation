<?php
$wsdl   = "http://192.168.124.130:8124/soap-wsdl/syracuse/collaboration/syracuse/CAdxWebServiceXmlCC?wsdl"; 
ini_set('soap.wsdl_cache_enabled', 0);
ini_set('soap.wsdl_cache_ttl', 900);
ini_set('default_socket_timeout', 15);

$client = new SoapClient($wsdl, array('trace' => TRUE));
var_dump($client->__getFunctions()); 

$Auth = new stdClass();
$Auth->UserName = "admin";
$Auth->Password = "admin";

$soap_method_name = 'run';

$callContext    = ' <codeLang>FRA</codeLang>
                    <poolAlias>PROD</poolAlias>';

$publicName     = 'YPANIER';

$inputXml       = '<PARAM>
                      <GRP ID="IN">
                        <FLD NAM="YSTOFCY">SAN01</FLD>
                      </GRP>
                      <TAB ID="IND">
                        <LIN ID="IND" NUM="1">
                          <FLD NAM="YITMREF">10037008</FLD>
                          <FLD NAM="YSAU">M</FLD>
                          <FLD NAM="YQTY">10</FLD>
                        </LIN>
                        <LIN ID="IND" NUM="2">
                          <FLD NAM="YITMREF">10037130</FLD>
                          <FLD NAM="YSAU">M</FLD>
                          <FLD NAM="YQTY">5</FLD>
                        </LIN>
                      </TAB>
                    </PARAM>';

$header = new SoapHeader($wsdl,'Auth',$Auth,false);


        // Attach header
        $client->__setSoapHeaders($header);

        // Creating request structure
        $xml = '<wss:' . $soap_method_name . ' soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'
                . '<wss:callContext>' . $callContext . '</wss:callContext>'
                . '<publicName>'.$publicName.'</publicName>'
                . '<inputXml>'.$inputXml.'</inputXml>'
                . '</wss:' . $soap_method_name . '>';

        $query = new SoapVar($xml, XSD_ANYXML);
        //var_dump($query);
       // exit;
        // Call wsdl function
        $client->__soapCall($soap_method_name, array($query));

        //var_dump($client);
        //exit;
        // XML response
        $response = $client->__getLastResponse();

        $resp = json_decode(json_encode(simplexml_load_string(strtr($response, array(' xmlns:' => ' ')))), 1);
        $resp_to_clean = $resp['soapenv:Body']['wss:runResponse']['wss:return'];

        $resp_to_encode = array();
        foreach ($resp_to_clean as $index_resp => $resp_to_clean_row) {
            $new_index_resp = preg_replace('#^(.*)\:(.*)$#', '\2' , $index_resp);
            $resp_to_encode[$new_index_resp] = $resp_to_clean_row;
        }

        echo (json_encode($resp_to_encode));


?>