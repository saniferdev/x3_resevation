<?php

Class API{

    public $url;
    public $key;
    public $link;

    public $url_soap;
    public $login;
    public $password;

    public $codeLang;
    public $poolAlias;

    public function getId($id){
        $queryParams    = array();
        $queryOptions   = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
        $query          = "SELECT commande FROM [COMMANDE].[dbo].[commande_en_ligne] WHERE commande = ".$id." ";

        $resultat       = sqlsrv_query($this->link, $query, $queryParams, $queryOptions);
        if ($resultat == FALSE) {
            return false;
        } elseif (sqlsrv_num_rows($resultat) == 0) {
            return 0;
        } else {
            return 1;
        }
    }

    public function getUnite($id){
        $queryParams    = array();
        $queryOptions   = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
        $query          = "SELECT SAU_0 FROM [192.168.130.50\TALYS].[x3v12prod].dbo.[ZARTICLES] WHERE ITMREF_0 = '".$id."'";

        $result = sqlsrv_query($this->link, $query, $queryParams, $queryOptions);
        if ($result == FALSE){
          return false;
        }
        elseif (sqlsrv_num_rows($result) == 0) {
          return false;
        }
        else
        {
          $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
          $unite = $row['SAU_0'];
          return $unite;
        }
    }

    public function insertId($id){
        $query  = "INSERT INTO [COMMANDE].[dbo].[commande_en_ligne] (commande) VALUES (".$id.") ";
        if(sqlsrv_query($this->link, $query)) return true;
        else return var_dump(sqlsrv_errors());
    }

    public function preg_replace_($xml){
        $xml = preg_replace("/\n/", "", $xml);
        $xml = preg_replace("/>\s*</", "><", $xml);
        return $xml;
    }

    public function soap_API($publicName,$inputXml){
        $soapClient = new SoapClient(
            $this->url_soap,
            array(
                'trace'    => true,
                'login'    => $this->login,
                'password' => $this->password,
            )
        );

        $context    = array('codeLang'=>$this->codeLang, 'poolAlias'=>$this->poolAlias,'poolId'=>'','requestConfig'=>'adxwss.trace.on=on&adxwss.trace.size=16384&adonix.trace.on=on&adonix.trace.level=3&adonix.trace.size=8');

        //$context    = array('codeLang'=>$this->codeLang, 'poolAlias'=>$this->poolAlias,'poolId'=>'','requestConfig'=>'');

        $inputXml   = $this->preg_replace_($inputXml);

        $result     = $soapClient->__call("run",array($context,$publicName,$inputXml));
        $xml        = simplexml_load_string($result->resultXml);
        //$status     = (int)$result->status;

        if($publicName == 'YPANIER'){
            $article    = $xml->TAB[1];
            $lin        = $article->LIN;
            $arr        = array();
            foreach ($lin as $key => $value) {
                $arr[]  = $value['NUM'] ."-".$value->FLD[0];
            }
            $return     = $arr;
        }
        else{
            $message   = $xml->GRP[1]->FLD[0];
            $return    = trim($message);
        }

        return $return;
    }

    public function deleteCommandeId($id){
        $query  = "DELETE FROM [COMMANDE].[dbo].[commande_en_ligne] WHERE commande = '".$id."' ";
        if(sqlsrv_query($this->link, $query)) return true;
        else return var_dump(sqlsrv_errors());
    }

    public function entete($client,$adresse,$tel,$email,$id){
        $mode_paiement  =   $this->getOrderPaiement($id);
        $array          =   array(
                                'Airtelmoney' => 'ESPJN',
                                'Chèque' => 'ESPJN',
                                'en_especes' => 'ESPJN',
                                'MVola' => 'ESPJN',
                                'Orangemoney' => 'ESPJN',
                                'Paiement en magasin' => 'ESPJN',
                                'Payer par carte Visa/Mastercard' => 'VIR100CDE',
                                'Transfert bancaire' => 'VIR100CDE'
                            );
        $YPTE           =   $array[$mode_paiement];
        $xml            =   '<GRP ID="IN">
                                <FLD NAM="YSALFCY">SAN01</FLD>
                                <FLD NAM="YSTOFCY">SAN01</FLD>
                                <FLD NAM="YBPCORD">P0100001</FLD>
                                <FLD NAM="YPTE">'.$YPTE.'</FLD>
                                <FLD NAM="YBPCNAM1">'.$client.'</FLD>
                                <FLD NAM="YBPCNAM2"></FLD>
                                <FLD NAM="YBPCADDLIG">'.$adresse.'</FLD>
                                <FLD NAM="YBPCADDLIG2">'.$tel.'</FLD>
                                <FLD NAM="YBPCADDLIG3">'.$email.'</FLD>
                                <FLD NAM="YBPCPOSCOD"></FLD>
                                <FLD NAM="YBPCCRY"></FLD>
                                <FLD NAM="YBPCCTY">MG</FLD>
                                <FLD NAM="YBPDNAM">'.$client.'</FLD>
                                <FLD NAM="YBPDNAM2"></FLD>
                                <FLD NAM="YBPDADDLIG">'.$adresse.'</FLD>
                                <FLD NAM="YBPDADDLIG2">'.$tel.'</FLD>
                                <FLD NAM="YBPDADDLIG3">'.$email.'</FLD>
                                <FLD NAM="YBPDPOSCOD"></FLD>
                                <FLD NAM="YBPDCTY"></FLD>
                                <FLD NAM="YBPDCRY">MG</FLD>
                              </GRP>';
        $xml            = $this->preg_replace_($xml);
        return $xml;
    }

    public function ligne($article,$qte,$unite,$nb_ligne){
        $xml    =   '<LIN ID="IND" NUM="'.$nb_ligne.'">
                        <FLD NAM="YITMREF">'.$article.'</FLD>
                        <FLD NAM="YSAU">'.$unite.'</FLD>
                        <FLD NAM="YQTY">'.$qte.'</FLD>
                    </LIN>';
        $xml    = $this->preg_replace_($xml);
        return $xml;
    }

    public function entete_qte(){
        $xml    =   '<GRP ID="IN">
                        <FLD NAM="YSTOFCY">SAN01</FLD>
                     </GRP>';
        $xml    = $this->preg_replace_($xml);
        return $xml;
    }

    public function xml2array($fname){
      $sxi      = new SimpleXmlIterator($fname);
      return $this->sxiToArray($sxi);
    }

    public function sxiToArray($sxi){
      $a = array();
      for( $sxi->rewind(); $sxi->valid(); $sxi->next() ) {
        if(!array_key_exists($sxi->key(), $a)){
          $a[$sxi->key()]   = array();
        }
        if($sxi->hasChildren()){
          $a[$sxi->key()][] = $this->sxiToArray($sxi->current());
        }
        else{
          $a[$sxi->key()][] = strval($sxi->current());
        }
      }
      return $a;
    }

    public function getClientDetail($id){
        $xml        = file_get_contents($this->url."/api/customers/".$id."/?ws_key=".$this->key);
        $getContent = $this->xml2array($xml);
        $iterator   = new RecursiveArrayIterator($getContent);
        $client     = "";
        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                foreach ($iterator->getChildren() as $value) {
                    $client = strtoupper($value["lastname"][0]).'  '.strtoupper($value["firstname"][0]).'-'.$value["email"][0];
                }
            }
            $iterator->next();
        }
        return $client;
    }

    public function getClientAdress($id){
        $xml        = file_get_contents($this->url."/api/addresses/".$id."/?ws_key=".$this->key);
        $getContent = $this->xml2array($xml);
        $iterator   = new RecursiveArrayIterator($getContent);
        $client     = "";
        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                foreach ($iterator->getChildren() as $value) {
                    $client = strtoupper($value["address1"][0]).'-'.$value["phone"][0];
                }
            }
            $iterator->next();
        }
        return $client;
    }

    public function qteArticle($num,$qte,$num2,$qte2){
        if($num == $num2 && $qte == $qte2) 
            return true;
        else 
            return false;
    }

    public function getOrderDetail($id){
        $xml        = file_get_contents($this->url."/api/orders/".$id."/?ws_key=".$this->key);
        $getContent = $this->xml2array($xml);
        $array      = array();
        $iterator   = new RecursiveArrayIterator($getContent);

        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                foreach ($iterator->getChildren() as $value) {
                    $client  = $this->getClientDetail($value["id_customer"][0]);
                    $adresse = $this->getClientAdress($value["id_address_invoice"][0]);
                    foreach(end($value["associations"]) as $val){
                        foreach($val[0]["order_row"] as $key => $valeur){
                            $array[]['article'] = $valeur['product_reference'][0].'-'.$valeur['product_quantity'][0].'-'.$client.'-'.$adresse;
                        }
                    }
                }
            }
            $iterator->next();
        }
        return $array;
    }

    public function getOrderPaiement($id){
        $xml        = file_get_contents($this->url."/api/orders/".$id."/?ws_key=".$this->key);
        $getContent = $this->xml2array($xml);
        $array      = array();
        $iterator   = new RecursiveArrayIterator($getContent);
        $payment    = '';

        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                foreach ($iterator->getChildren() as $value) {
                    $payment  = $value["payment"][0];
                }
            }
            $iterator->next();
        }
        
        return $payment;
    }

    public function getOrder(){
        $rand        = 0;
        $id_commande = 1;
        $date_       = $xml = $getXml = $orders = $id_commande = $lettre = '';
        $lettre      = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
        $date_       = new DateTime('now');
        $date_       = $date_->getTimestamp();
        $rand        = $lettre.''.($date_ * rand());        
        $xml         = file_get_contents($this->url."/api/orders?output_format=".$rand."&ws_key=".$this->key);
        $getXml     = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);

        if($getXml){
            $orders = $getXml->orders;
            foreach($orders as $val) {
                foreach ($val as $value) {
                    $id_commande = $this->getId($value["id"]);
                    if( $id_commande == 0 ){
                        if($this->insertId($value["id"]) == TRUE){
                            $i                = 0;
                            $e                = "";
                            $detail           = $this->getOrderDetail($value["id"]);

                            $xml_Ref          = '<PARAM>
                                                      <GRP ID="IN">
                                                        <FLD NAM="YSTOFCY">SAN01</FLD>
                                                      </GRP>';

                            foreach ($detail as $key => $article) {
                                        if(isset($article['article'])){
                                            $exp    = explode('-', $article['article']);
                                            $qteXml = TRUE;
                                            if($i == 0){

                                                $entete   = $this->entete($exp[2],$exp[4],$exp[5],$exp[3],$value["id"]);
                                                $ligne    = '<TAB ID="IND">';
                                                $xml_Ref .= '<TAB ID="IND">';
                                                $xml_Ref .= $this->entete_qte();
                                            }
                                            $i++;

                                            $unite      = $this->getUnite($exp[0]);

                                            $ligne      .= $this->ligne($exp[0],$exp[1],$unite,$i);

                                            $xml_Ref    .= $this->ligne($exp[0],$exp[1],$unite,$i);

                                            if ($article === end($detail)){
                                                $ligne   .= '</TAB>';
                                                $xml_Ref .= '</TAB>
                                                            </PARAM>';
                                            }

                                            /*$qteRetour  = $this->soap_API('YPANIER',$xml_Ref);

                                            foreach ($qteRetour as $val_qte) {
                                                $explode = explode('-',$val_qte);
                                                if($i == $explode[0]){
                                                    $qteXml  = $this->qteArticle($i,$exp[1],$explode[0],$explode[1]);
                                                }
                                            }*/
                                            //if($qteXml == FALSE) continue;

                                            $e .= '<strong>Réference :</strong> '.$exp[0].' - <strong>Quantité:</strong> '.$exp[1].'<br>';
                                            
                                            
                                            
                                        }
                            }   
                            if(isset($entete) && isset($ligne)){
                                $xml_soap    = '<PARAM>';
                                $xml_soap   .= $entete.$ligne;
                                $xml_soap   .='</PARAM>';

                                $commande    = $this->soap_API('YGENSOH',$xml_soap);

                                if( isset($commande) ) {
                                    $adresse = "ass2.scecomm@sanifer.mg";
                                    //$adresse = "winny.info@talys.mg";
                                    $sujet  = "Commande validée et crée dans X3";
                                    $objet  = "Bonjour,<br><br>";
                                    $objet .= "Une commande n° ".$value["id"]." a été crée sur le site WEB de sanifer.<br>";
                                    $objet .= "La commande a été crée dans sage X3.<br><br>";
                                    $objet .= "Ci-après les details de la commande:<br>";
                                    $objet .= "<strong>N° :</strong>".$commande." - <strong>Client Divers :</strong> ".$exp[2]."<br><br>";
                                    $objet .= $e."<br><br>";
                                    $objet .= "   <strong>Cordialement</strong><br>
                                                  <strong>Winny Tsiorintsoa RAZAFINDRAKOTO</strong><br>
                                                  <strong>DEVELOPPEUR</strong><br>
                                                  Lot II I 20 AA Morarano<br>
                                                  Antananarivo – MADAGASCAR<br>
                                                  Tél. : +261 34 07 635 84<br>
                                                  Tél. : +261 20 22 530 81<br>
                                                  Fax : +261 20 22 530 80<br>
                                                  Mail : winny.info@talys.mg<br> 
                                                  Site : www.sanifer.mg<br>";
                                    envoiMail($adresse,$sujet,$objet);
                                }
                            }
                            else{
                                $this->deleteCommandeId($value["id"]);
                                exit;
                            } 
                        }
                    }
                }
            }
        }
    }

}
?>