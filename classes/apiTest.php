<?php

Class API{

    public $link;
    public $url;
    public $key;


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

    public function insertId($id){
        $query  = "INSERT INTO [COMMANDE].[dbo].[commande_en_ligne] (commande) VALUES (".$id.") ";
        if(sqlsrv_query($this->link, $query)) return true;
        else return var_dump(sqlsrv_errors());
    }

    public function DO_Piece(){
        $queryParams    = array();
        $queryOptions   = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
        $query          = " 
                    SELECT 
                        DC_Piece
                    FROM 
                        [SANIFER].[dbo].[F_DOCCURRENTPIECE]
                    WHERE
                        DC_Souche = 0
                        AND DC_IdCol = 3  
                        AND DC_Domaine = 0 ";

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
          $num =  explode('BN',$row['DC_Piece']);
          return 'BN'.(intval($num[1]) + 3);
        }
    }

    public function QteEnStock($article,$qte){
        $queryParams    = array();
        $queryOptions   = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
        $query          = " 
                    SELECT 
                        AR_Ref
                    FROM 
                        SANIFER.[dbo].[F_ARTSTOCK] 
                    WHERE
                        AR_Ref = '".$article."'
                        AND AS_QteSto >= '".$qte."' 
                        AND DE_No = 31";

        $result = sqlsrv_query($this->link, $query, $queryParams, $queryOptions);
        if ($result == FALSE){
          return false;
        }
        elseif (sqlsrv_num_rows($result) == 0) {
          return false;
        }
        else{
          return true;
        }
    }

    public function ArticleNomenclature($article){
        $array          = array();
        $queryParams    = array();
        $queryOptions   = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
        $query          = " 
                    SELECT 
                        No_RefDet
                    FROM 
                        SANIFER.[dbo].[F_NOMENCLAT] 
                    WHERE
                        AR_Ref = '".$article."' ";

        $result = sqlsrv_query($this->link, $query, $queryParams, $queryOptions);
        if ($result == FALSE){
          return false;
        }
        elseif (sqlsrv_num_rows($result) == 0) {
          return false;
        }
        else{
           while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)){
                $array[] = $row['No_RefDet'];
           }
           return $array;
        }
    }

    public function deleteCommandeId($id){
        $query  = "DELETE FROM [COMMANDE].[dbo].[commande_en_ligne] WHERE commande = '".$id."' ";
        if(sqlsrv_query($this->link, $query)) return true;
        else return var_dump(sqlsrv_errors());
    }

    public function updateDO_Piece($DO_Piece){
        $query  = "
                ALTER TABLE SANIFER.[dbo].F_DOCCURRENTPIECE DISABLE TRIGGER ALL
                    UPDATE SANIFER.[dbo].F_DOCCURRENTPIECE SET DC_Piece = '".$DO_Piece."' WHERE DC_Souche = 0 AND DC_IdCol = 3 AND DC_Domaine = 0
                ALTER TABLE SANIFER.[dbo].F_DOCCURRENTPIECE ENABLE TRIGGER ALL ";
        if(sqlsrv_query($this->link, $query)) return true;
        else return var_dump(sqlsrv_errors());
    }

    public function deleteEntete($DO_Piece){
        $query  = "
                ALTER TABLE SANIFER.[dbo].F_DOCENTETE DISABLE TRIGGER ALL
                    DELETE FROM SANIFER.[dbo].F_DOCENTETE WHERE DO_Piece = '".$DO_Piece."'
                ALTER TABLE SANIFER.[dbo].F_DOCENTETE ENABLE TRIGGER ALL ";
        if(sqlsrv_query($this->link, $query)) return true;
        else return var_dump(sqlsrv_errors());
    }

    public function deleteLigne($DO_Piece){
        $query  = "
                ALTER TABLE SANIFER.[dbo].F_DOCLIGNE DISABLE TRIGGER ALL
                    DELETE FROM SANIFER.[dbo].F_DOCLIGNE WHERE DO_Piece = '".$DO_Piece."'
                ALTER TABLE SANIFER.[dbo].F_DOCLIGNE ENABLE TRIGGER ALL ";
        if(sqlsrv_query($this->link, $query)) return true;
        else return var_dump(sqlsrv_errors());
    }

    public function insertDocument_Entete($client,$adresse,$tel,$email){
        if(  $this->DO_Piece() !== false ){
            $DO_Piece = $this->DO_Piece();
        }
        else return false;
        $query  = "

                DECLARE @error int = 0;
                DECLARE @ErrorMessage NVARCHAR(4000);  
                DECLARE @ErrorSeverity INT;  
                DECLARE @ErrorState INT;  

                BEGIN

                     BEGIN TRY

                      BEGIN TRANSACTION TR_F_DOCENTETE_INS

                      ALTER TABLE SANIFER.[dbo].F_DOCENTETE DISABLE TRIGGER ALL

                       INSERT INTO
                        SANIFER.[dbo].[F_DOCENTETE] (
                            [DO_Domaine],
                            [DO_Type],
                            [DO_Piece],
                            [DO_Date],
                            [DO_Ref],
                            [DO_Tiers],
                            [CO_No],
                            [cbCO_No],
                            [DO_Period],
                            [DO_Devise],
                            [DO_Cours],
                            [DE_No],
                            [cbDE_No],
                            [LI_No],
                            [cbLI_No],
                            [CT_NumPayeur],
                            [DO_Expedit],
                            [DO_NbFacture],
                            [DO_BLFact],
                            [DO_TxEscompte],
                            [DO_Reliquat],
                            [DO_Imprim],
                            [CA_Num],
                            [DO_Coord01],
                            [DO_Coord02],
                            [DO_Coord03],
                            [DO_Coord04],
                            [DO_Souche],
                            [DO_DateLivr],
                            [DO_Condition],
                            [DO_Tarif],
                            [DO_Colisage],
                            [DO_TypeColis],
                            [DO_Transaction],
                            [DO_Langue],
                            [DO_Ecart],
                            [DO_Regime],
                            [N_CatCompta],
                            [DO_Ventile],
                            [AB_No],
                            [DO_DebutAbo],
                            [DO_FinAbo],
                            [DO_DebutPeriod],
                            [DO_FinPeriod],
                            [CG_Num],
                            [DO_Statut],
                            [CA_No],
                            [cbCA_No],
                            [CO_NoCaissier],
                            [cbCO_NoCaissier],
                            [DO_Transfere],
                            [DO_Cloture],
                            [DO_NoWeb],
                            [DO_Attente],
                            [DO_Provenance],
                            [CA_NumIFRS],
                            [MR_No],
                            [DO_TypeFrais],
                            [DO_ValFrais],
                            [DO_TypeLigneFrais],
                            [DO_TypeFranco],
                            [DO_ValFranco],
                            [DO_TypeLigneFranco],
                            [DO_Taxe1],
                            [DO_TypeTaux1],
                            [DO_TypeTaxe1],
                            [DO_Taxe2],
                            [DO_TypeTaux2],
                            [DO_TypeTaxe2],
                            [DO_Taxe3],
                            [DO_TypeTaux3],
                            [DO_TypeTaxe3],
                            [DO_MajCpta],
                            [DO_Motif],
                            [CT_NumCentrale],
                            [DO_Contact],
                            [DO_FactureElec],
                            [DO_TypeTransac],
                            [cbProt],
                            [cbCreateur],
                            [cbModification],
                            [cbReplication],
                            [cbFlag],
                            [Nom_du_bateau],
                            [Date_prevue_darrivee],
                            [Code_tiers],
                            [Motif_du_retour],
                            [Intit_tiers],
                            [Validation_VD],
                            [Derniere_modification],
                            [Montant_TOTAL_TTC],
                            [DO_TypeCalcul],
                            [DO_DocType],
                            [DO_Escompte],
                            [DO_StatutBAP],
                            [DO_Coffre],
                            [DO_Valide],
                            [ET_No],
                            [DO_DemandeRegul],
                            [DO_EStatut],
                            [DO_PieceOrig],
                            [DO_FactureFrs],
                            [DO_DateExpedition],
                            [DO_DateLivrRealisee]
                        )
                       VALUES (
                        0 ,
                        3 ,
                        '".$DO_Piece."' ,
                        CAST(CAST(GETDATE() AS DATE) AS DATETIME) ,
                        'WEB' ,
                        'C111001' ,
                        0 ,
                        NULL ,
                        1 ,
                        0 ,
                        0 ,
                        31 ,
                        31 ,
                        '10134',
                        '10134' ,
                        'C111001' ,
                        1 ,
                        1 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        '' ,
                        SUBSTRING(UPPER('".$client."'),0,24) ,
                        SUBSTRING('".$adresse."',0,24) ,
                        SUBSTRING('".$tel."',0,24) ,
                        SUBSTRING('".$email."',0,24) ,
                        6 ,
                        '1753-01-01 00:00:00.000' ,
                        1 ,
                        1 ,
                        1 ,
                        1 ,
                        11 ,
                        0 ,
                        0 ,
                        21 ,
                        1 ,
                        0 ,
                        0 ,
                        '1753-01-01 00:00:00.000' ,
                        '1753-01-01 00:00:00.000' ,
                        '1753-01-01 00:00:00.000' ,
                        '1753-01-01 00:00:00.000' ,
                        '411000' ,
                        2 ,
                        0,
                        NULL,
                        0,
                        NULL,
                        0 ,
                        0 ,
                        '' ,
                        0 ,
                        0 ,
                        '' ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        0 ,
                        '' ,
                        NULL ,
                        '' ,
                        0 ,
                        0 ,
                        0 ,
                        'COLS' ,
                        GETDATE() ,
                        0 ,
                        0 ,
                        '' ,
                        '1900-01-01 00:00:00' ,
                        'C111001' ,
                        '' ,
                        'CLIENTS DIVERS SANIFER 1',
                        '',
                        '',
                        '0',
                        '0',
                        3,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        '',
                        '',
                        '1753-01-01 00:00:00.000',
                        '1753-01-01 00:00:00.000'
                       )

                        ALTER TABLE SANIFER.[dbo].F_DOCENTETE ENABLE TRIGGER ALL

                        COMMIT TRANSACTION TR_F_DOCENTETE_INS

                        END TRY
                        BEGIN CATCH

                        ROLLBACK TRANSACTION TR_F_DOCENTETE_INS
                        PRINT 'ROLLBACK TR_F_DOCENTETE_INS'

                        SELECT   
                                    @ErrorMessage = ERROR_MESSAGE(),  
                                    @ErrorSeverity = ERROR_SEVERITY(),  
                                    @ErrorState = ERROR_STATE();  

                        RAISERROR (
                            @ErrorMessage, 
                            @ErrorSeverity, 
                            @ErrorState 
                        ); 
                        END CATCH
                   
                    END  ";
                
        if(sqlsrv_query($this->link, $query)) return $DO_Piece;
        else{
            return $query;
        }
    }

    public function insertDocument_Ligne($article,$qte,$DO_Piece){
        $query  = "

                
                    
                    DECLARE @DL_Ligne int = 1000;
                    DECLARE @error int = 0;
                    DECLARE @ErrorMessage NVARCHAR(4000);  
                    DECLARE @ErrorSeverity INT;  
                    DECLARE @ErrorState INT; 
                    BEGIN

                        BEGIN TRY
                            BEGIN TRANSACTION TR_F_DOCLIGNE_INS
                            ALTER TABLE SANIFER.[dbo].F_DOCLIGNE DISABLE TRIGGER ALL

                        INSERT INTO SANIFER.[dbo].[F_DOCLIGNE]
                        (
                            [DL_No],
                            [DO_Domaine],
                            [DO_Type],
                            [CT_Num],
                            [DO_Piece],
                            [DL_PieceBC],
                            [DL_PieceBL],
                            [DO_Date],
                            [DL_DateBC],
                            [DL_DateBL],
                            [DL_Ligne],
                            [DO_Ref],
                            [DL_TNomencl],
                            [DL_TRemPied],
                            [DL_TRemExep],
                            [AR_Ref],
                            [DL_Design],
                            [DL_Qte],
                            [DL_QteBC],
                            [DL_QteBL],
                            [DL_PoidsNet],
                            [DL_PoidsBrut],
                            [DL_Remise01REM_Valeur],
                            [DL_Remise01REM_Type],
                            [DL_Remise02REM_Valeur],
                            [DL_Remise02REM_Type],
                            [DL_Remise03REM_Valeur],
                            [DL_Remise03REM_Type],
                            [DL_PrixUnitaire],
                            [DL_PUBC],
                            [DL_Taxe1],
                            [DL_TypeTaux1],
                            [DL_TypeTaxe1],
                            [DL_Taxe2],
                            [DL_TypeTaux2],
                            [DL_TypeTaxe2],
                            [CO_No],
                            [cbCO_No],
                            [AG_No1],
                            [AG_No2],
                            [DL_PrixRU],
                            [DL_CMUP],
                            [DL_MvtStock],
                            [DT_No],
                            [cbDT_No],
                            [AF_RefFourniss],
                            [EU_Enumere],
                            [EU_Qte],
                            [DL_TTC],
                            [DE_No],
                            [cbDE_No],
                            [DL_NoRef],
                            [DL_TypePL],
                            [DL_PUDevise],
                            [DL_PUTTC],
                            [DO_DateLivr],
                            [CA_Num],
                            [DL_Taxe3],
                            [DL_TypeTaux3],
                            [DL_TypeTaxe3],
                            [DL_Frais],
                            [DL_Valorise],
                            [AR_RefCompose],
                            [DL_NonLivre],
                            [AC_RefClient],
                            [DL_MontantHT],
                            [DL_MontantTTC],
                            [DL_FactPoids],
                            [DL_Escompte],
                            [DL_PiecePL],
                            [DL_DatePL],
                            [DL_QtePL],
                            [DL_NoColis],
                            [DL_NoLink],
                            [cbDL_NoLink],
                            [RP_Code],
                            [DL_QteRessource],
                            [DL_DateAvancement],
                            [cbProt],
                            [cbCreateur],
                            [cbModification],
                            [cbReplication],
                            [cbFlag],
                            [PU_devise_facture],
                            [CoeffRevient],
                            [MOTIF],
                            [RESP],
                            [Utilisateur],
                            [Derniere_modification],
                            [PF_NUM],
                            [DL_PieceOFProd],
                            [DL_PieceDE],
                            [DL_DateDE],
                            [DL_QteDE],
                            [DL_Operation],
                            [DL_NoSousTotal],
                            [CA_No],
                            [cbCA_No],
                            [DO_DocType],
                            DL_CodeTaxe1
                        )
                        VALUES
                        (
                            (SELECT MAX(DL_No) + 1 FROM SANIFER.[dbo].F_DOCLIGNE),
                            0,
                            3,
                            'C111001',
                            '".$DO_Piece."',
                            '',
                            '',
                            CAST(CAST(GETDATE() AS DATE) AS DATETIME) ,
                            CAST(CAST(GETDATE() AS DATE) AS DATETIME) ,
                            CAST(CAST(GETDATE() AS DATE) AS DATETIME) ,
                            ISNULL( (SELECT MAX(DL_Ligne) FROM SANIFER.[dbo].F_DOCLIGNE WHERE DO_Piece = '".$DO_Piece."'), 0) + @DL_Ligne,
                            'WEB',
                            0,
                            0,
                            0,
                            '".$article."',
                            (SELECT AR_Design FROM SANIFER.[dbo].F_ARTICLE WHERE AR_REf = '".$article."'),
                            '".$qte."',
                            '".$qte."',
                            '".$qte."',
                            (
                                SELECT 
                                    CASE 
                                        WHEN AR_UnitePoids = 0 THEN  AR_PoidsNet * 1000000
                                        WHEN AR_UnitePoids = 1 THEN  AR_PoidsNet * 100000
                                        WHEN AR_UnitePoids = 2 THEN  AR_PoidsNet * 1000
                                        WHEN AR_UnitePoids = 3 THEN  AR_PoidsNet * 1
                                        ELSE 0
                                    END
                                FROM
                                    SANIFER.[dbo].F_ARTICLE
                                WHERE
                                    AR_Ref = '".$article."'
                            ),
                            (
                                SELECT 
                                    CASE 
                                        WHEN AR_UnitePoids = 0 THEN  AR_PoidsBrut * 1000000
                                        WHEN AR_UnitePoids = 1 THEN  AR_PoidsBrut * 100000
                                        WHEN AR_UnitePoids = 2 THEN  AR_PoidsBrut * 1000
                                        WHEN AR_UnitePoids = 3 THEN  AR_PoidsBrut * 1
                                        WHEN AR_UnitePoids = 5 THEN  AR_PoidsBrut * 0.001
                                    END
                                FROM
                                    SANIFER.[dbo].F_ARTICLE
                                WHERE
                                    AR_Ref = '".$article."'
                            ),
                            (
                                SELECT 
                                    CASE WHEN MO_No = 1 
                                        THEN '0'
                                        ELSE '5'
                                    END
                                FROM
                                    SANIFER.[dbo].[F_ARTMODELE]
                                WHERE 
                                    AR_REf = '".$article."'
                            ),
                            1,
                            0,
                            0,
                            0,
                            0,
                            (SELECT ROUND( (AR_PrixVen)/(1.2) ,2) FROM SANIFER.[dbo].F_ARTICLE WHERE AR_REf = '".$article."'),
                            0,
                            20,
                            0,
                            0,
                            0,
                            0,
                            0,
                            0,
                            NULL,
                            0,
                            0,
                            (
                                SELECT 
                                    CASE WHEN ISNULL(AR.AS_MontSto,0) = 0 THEN A.AR_PrixAch ELSE ROUND( COALESCE(ISNULL(AR.AS_MontSto,0)  / NULLIF(AR.AS_QteSto,0), 0) ,2)  END 
                                FROM 
                                    SANIFER.[dbo].F_ARTICLE A 
                                    LEFT JOIN SANIFER.[dbo].F_ARTSTOCK AR ON AR.AR_Ref = A.AR_Ref AND AR.DE_No = 31
                                WHERE
                                    A.AR_Ref = '".$article."' 
                            ),
                            (
                                SELECT 
                                    CASE WHEN ISNULL(AR.AS_MontSto,0) = 0 THEN A.AR_PrixAch ELSE ROUND( COALESCE(ISNULL(AR.AS_MontSto,0)  / NULLIF(AR.AS_QteSto,0), 0) ,2)  END 
                                FROM 
                                    SANIFER.[dbo].F_ARTICLE A 
                                    LEFT JOIN SANIFER.[dbo].F_ARTSTOCK AR ON AR.AR_Ref = A.AR_Ref AND AR.DE_No = 31
                                WHERE
                                    A.AR_Ref = '".$article."' 
                            ),
                            3,
                            0,
                            NULL,
                            ISNULL( (SELECT AF_RefFourniss FROM SANIFER.[dbo].F_ARTFOURNISS WHERE AR_Ref = '".$article."' AND AF_Principal = 1),''),
                            (SELECT TOP 1 U.U_Intitule FROM SANIFER.[dbo].F_ARTICLE A INNER JOIN SANIFER.[dbo].P_UNITE U ON A.AR_UniteVen = U.cbIndice WHERE A.AR_Ref = '".$article."'),
                            '".$qte."',
                            1,
                            31,
                            31,
                            1,
                            0,
                            0,
                            (SELECT AR_PrixVen FROM SANIFER.[dbo].F_ARTICLE WHERE AR_REf = '".$article."'),
                            '1753-01-01 00:00:00.000',
                            '',
                            0,
                            0,
                            0,
                            0,
                            1,
                            NULL,
                            0,
                            '',
                            (SELECT ROUND( ( (AR_PrixVen)/(1.2) ) * '".$qte."' ,2) FROM SANIFER.[dbo].F_ARTICLE WHERE AR_REf = '".$article."'),
                            (SELECT ROUND( (AR_PrixVen) * '".$qte."' ,2) FROM SANIFER.[dbo].F_ARTICLE WHERE AR_REf = '".$article."'),
                            0,
                            0,
                            '',
                            CAST(CAST(GETDATE() AS DATE) AS DATETIME) ,
                            '".$qte."',
                            '',
                            0,
                            NULL,
                            NULL,
                            0,
                            '1753-01-01 00:00:00.000',
                            0,
                            'COLS',
                            GETDATE() ,
                            0,
                            0,
                            0,
                            0,
                            '',
                            '',
                            '',
                            '',
                            '',
                            0,
                            '',
                            CAST(CAST(GETDATE() AS DATE) AS DATETIME) ,
                            ".$qte.",
                            '',
                            0,
                            0,
                            NULL,
                            3,
                            1
                        );
                            BEGIN TRANSACTION TRN_STOCK

                                UPDATE SANIFER.[dbo].F_ARTSTOCK
                                    SET AS_QteSto = AS_QteSto - ".$qte." ,
                                    AS_MontSto = AS_MontSto - (".$qte." * (
                                                                            SELECT 
                                                                                CASE WHEN ISNULL(AR.AS_MontSto,0) = 0 THEN A.AR_PrixAch ELSE ROUND( COALESCE(ISNULL(AR.AS_MontSto,0)  / NULLIF(AR.AS_QteSto,0), 0) ,2)  END 
                                                                            FROM 
                                                                                SANIFER.[dbo].F_ARTICLE A 
                                                                                LEFT JOIN SANIFER.[dbo].F_ARTSTOCK AR ON AR.AR_Ref = A.AR_Ref AND AR.DE_No = 31
                                                                            WHERE
                                                                                A.AR_Ref = '".$article."' 
                                                                        ) )
                                WHERE 
                                    AR_Ref = '".$article."'
                                    AND DE_No = 31

                                SET @error = @error + @@error                            

                            ALTER TABLE SANIFER.[dbo].F_DOCLIGNE ENABLE TRIGGER ALL
                            COMMIT TRANSACTION TR_F_DOCLIGNE_INS

                            IF @error = 0 
                                BEGIN
                                    COMMIT TRANSACTION TRN_STOCK
                                END
                            ELSE 
                                BEGIN
                                    ROLLBACK TRANSACTION TRN_STOCK
                                END                           
                            

                        END TRY
                        BEGIN CATCH


                            ROLLBACK TRANSACTION TR_F_DOCLIGNE_INS
                            PRINT 'ROLLBACK TR_F_DOCLIGNE_INS'


                        SELECT   
                                    @ErrorMessage = ERROR_MESSAGE(),  
                                    @ErrorSeverity = ERROR_SEVERITY(),  
                                    @ErrorState = ERROR_STATE();  

                        RAISERROR (
                            @ErrorMessage, 
                            @ErrorSeverity, 
                            @ErrorState 
                        ); 

                        END CATCH

                            
                    END ";
        if(sqlsrv_query($this->link, $query)) return true;
        else return $query;
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

    public function getOrder(){
        $xml    = file_get_contents($this->url."/api/orders?ws_key=".$this->key."&output_format=json");
        $getXml = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);

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
                            $DO_Piece         = $this->DO_Piece();
                            
                            if($DO_Piece !== FALSE){
                                foreach ($detail as $key => $article) {
                                    if(isset($article['article'])){
                                        $exp      = explode('-', $article['article']);
                                        $nomencl  = $this->ArticleNomenclature($exp[0]);
                                        
                                        if($nomencl === FALSE){
                                            $qtestock = $this->QteEnStock($exp[0],$exp[1]);
                                            if($qtestock === FALSE){
                                                $adresse = $exp[3];
                                                $sujet  = "Insuffisance de stock pour votre commande";
                                                $objet  = "Bonjour,<br><br>";
                                                $objet .= "Une commande n° ".$value["id"]." a été crée sur le site WEB de sanifer.<br>";
                                                $objet .= "Il y a une insuffisance de stock pour votre commande.<br><br>";
                                                $objet .= "Ci-après le detail de l'article:<br>";
                                                $objet .= "<strong>Réference :</strong> ".$exp[0]." - <strong>Quantité:</strong> ".$exp[1]."<br><br><br>";
                                                $objet .= "   <strong>Cordialement</strong><br>
                                                              <strong>SANIFER</strong><br>
                                                              Lot II I 20 AA Morarano<br>
                                                              Antananarivo – MADAGASCAR<br>
                                                              Tél. : +261 20 22 530 81<br>
                                                              Fax : +261 20 22 530 80<br>
                                                              Site : www.sanifer.mg<br>";
                                                envoiMail($adresse,$sujet,$objet);
                                                continue;
                                            }
                                        }
                                        else{
                                            foreach ($nomencl as $v) {
                                               $qtestock = $this->QteEnStock($v,$exp[1]);
                                               if($qtestock === FALSE){
                                                    $adresse = $exp[3];
                                                    $sujet  = "Insuffisance de stock pour votre commande";
                                                    $objet  = "Bonjour,<br><br>";
                                                    $objet .= "Une commande n° ".$value["id"]." a été crée sur le site WEB de sanifer.<br>";
                                                    $objet .= "Il y a une insuffisance de stock pour votre commande.<br><br>";
                                                    $objet .= "Ci-après le detail de l'article:<br>";
                                                    $objet .= "<strong>Réference :</strong> ".$v." - <strong>Quantité:</strong> ".$exp[1]."<br><br><br>";
                                                    $objet .= "   <strong>Cordialement</strong><br>
                                                                  <strong>SANIFER</strong><br>
                                                                  Lot II I 20 AA Morarano<br>
                                                                  Antananarivo – MADAGASCAR<br>
                                                                  Tél. : +261 20 22 530 81<br>
                                                                  Fax : +261 20 22 530 80<br>
                                                                  Site : www.sanifer.mg<br>";
                                                    envoiMail($adresse,$sujet,$objet);
                                                    continue;
                                                }
                                            }
                                        }

                                        

                                        if($i == 0){
                                            $nouveau_DO_Piece = $this->insertDocument_Entete($exp[2],$exp[4],$exp[5],$exp[3]);
                                        }
                                        $i++;

                                        if($nomencl === FALSE){
                                            $ligne = $this->insertDocument_Ligne($exp[0],$exp[1],$nouveau_DO_Piece);                                         
                                        }
                                        else{
                                            foreach ($nomencl as $y) {
                                                $this->insertDocument_Ligne($y,$exp[1],$nouveau_DO_Piece);
                                            } 
                                            $ligne = TRUE;
                                        }

                                        if($ligne === TRUE){
                                            if($nomencl === FALSE){                                                
                                                $e  .= '<strong>Réference :</strong> '.$exp[0].' - <strong>Quantité:</strong> '.$exp[1].'<br>';
                                            }
                                            else{
                                                foreach ($nomencl as $z) {
                                                    $e  .= '<strong>Réference :</strong> '.$z.' - <strong>Quantité:</strong> '.$exp[1].'<br>';
                                                }
                                            }
                                            
                                        }
                                        else{
                                            echo $ligne;
                                            //$this->deleteLigne($nouveau_DO_Piece);
                                            //$this->deleteEntete($nouveau_DO_Piece);
                                            return false;
                                            exit;
                                        } 
                                        echo $e;
                                        
                                    }
                                }
                            }
                            else{ 
                                return false;
                                exit;
                            }
                            if(isset($nouveau_DO_Piece) && !empty($nouveau_DO_Piece)){
                                $adresse = "winny.info@talys.mg";
                                $sujet  = "Commande validée et transformée en BL";
                                $objet  = "Bonjour,<br><br>";
                                $objet .= "Une commande n° ".$value["id"]." a été crée sur le site WEB de sanifer.<br>";
                                $objet .= "La commande a été transformée en Bon de Livraison dans sage.<br><br>";
                                $objet .= "Ci-après les details du bon:<br>";
                                $objet .= "<strong>N° du BL :</strong>".$nouveau_DO_Piece." - <strong>Client Divers :</strong> ".$exp[2]."<br><br>";
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
                                $this->updateDO_Piece($nouveau_DO_Piece);
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