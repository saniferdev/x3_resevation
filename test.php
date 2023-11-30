<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create Contact class
class CAdxCallContext {
    public function __construct($codeLang, $poolAlias, $requestConfig) 
    {
        $this->codeLang = $codeLang;
        $this->poolAlias = $poolAlias;
        $this->requestConfig = $requestConfig;
    }
}

// Initialize WS with the WSDL
$client = new SoapClient("http://192.168.124.130:8124/soap-wsdl/syracuse/collaboration/syracuse/CAdxWebServiceXmlCC?wsdl"
							,array(array(
								        'trace'    => true,
								        'login'    => 'admin',
								        'password' => 'admin',
								    )));
var_dump($client);
// Create Contact obj
$context = new CAdxCallContext('FRA', "PREPROD",'');
$inputXml       = '<PARAM>
					  <GRP ID="IN">
					    <FLD NAM="YSTOFCY">SAN01</FLD>
					  </GRP>
					  <TAB ID="IND">
					    <LIN ID="IND" NUM="1">
					      <FLD NAM="YITMREF">02009621</FLD>
					      <FLD NAM="YSAU">UN</FLD>
					      <FLD NAM="YQTY">2</FLD>
					    </LIN>
					    <LIN ID="IND" NUM="2">
					      <FLD NAM="YITMREF">02009620</FLD>
					      <FLD NAM="YSAU">UN</FLD>
					      <FLD NAM="YQTY">3</FLD>
					    </LIN>
					  </TAB>
					</PARAM>
';
$inputXml = preg_replace("/\n/", "", $inputXml);
$inputXml = preg_replace("/>\s*</", "><", $inputXml);
// Set request params
$params = array(
  "CAdxCallContext" => $context,
  "publicName" => "YPANIER",
  "inputXml" => $inputXml,
);

// Invoke WS method (Function1) with the request params 
$response = $client->__soapCall("run", array($params));

// Print WS response
var_dump($response);