<?php
$soapClient = new SoapClient(
    "http://192.168.124.130:8124/soap-wsdl/syracuse/collaboration/syracuse/CAdxWebServiceXmlCC?wsdl",
    array(
        'login'    => 'admin',
        'password' => 'admin',
    )
);

$context 		= array('codeLang'=>'FRA', 'poolAlias'=>"PREPROD",'poolId'=>'?','requestConfig'=>'');	
$publicName     = 'YPANIER';
$inputXml       = '<PARAM>
					  <GRP ID="IN">
					    <FLD NAM="YSTOFCY">SAN01</FLD>
					  </GRP>
					  <TAB ID="IND">
					    <LIN ID="IND" NUM="1">
					      <FLD NAM="YITMREF">03015706</FLD>
					      <FLD NAM="YSAU">UN</FLD>
					      <FLD NAM="YQTY">2</FLD>
					    </LIN>
					  </TAB>
					</PARAM>';

$inputXml  = preg_replace("/\n/", "", $inputXml);
$inputXml  = preg_replace("/>\s*</", "><", $inputXml);

$result    = $soapClient->__call("run",array($context,$publicName,$inputXml));

$status = (int)$result->status;


//var_dump($result);
echo'<br>-----------------------<br>';
$xml        = simplexml_load_string($result->resultXml);
$article    = $xml->TAB[1];
            $lin        = $article->LIN;
            $arr        = array();
            foreach ($lin as $key => $value) {
                echo $value['NUM'] ."-".$value->FLD[0];
                echo'<br>-----------------------<br>';
            }


?>