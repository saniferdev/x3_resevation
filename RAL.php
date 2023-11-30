<?php
session_start();

/*ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);*/

include('includes/config.php');

date_default_timezone_set('Indian/Antananarivo');
setlocale(LC_ALL, "fr_FR.utf8");

require_once dirname(__FILE__) . '/php_classes/PHPExcel.php';

if(strlen($_SESSION['alogin'])==0)
{
    header('location:index.php');
}
else{
	if($_REQUEST['g']==1){
		$q = $q_w = "";       
	    if(isset($_REQUEST['date_debut'])){
	        $current_date_time = date_create($_REQUEST['date_debut']);
	        $user_current_date = date_format($current_date_time, "Y-d-m"); 
	        $d = date('Y-m-d', strtotime($_REQUEST['date_fin']. ' + 1 days'));
	        $t= date_create($d);
	        $dateFin = date_format($t, "Y-d-m");

	        $q_w = "AND (dbo.facture_entete.DO_Date >= '" . $user_current_date. "' AND dbo.facture_entete.DO_Date <='" . $dateFin. "')";
	    }
	    else{
	        $current_date_time = new DateTime("now");
	        $user_current_date = $current_date_time->format("Y-m-d");

	        $q_w = "AND CAST(dbo.facture_entete.DO_Date AS date) = '" . $user_current_date. "' ";
	    }
	    
	    if(isset($_REQUEST['num']) && !empty($_REQUEST['num'])){
	        $q = " AND (dbo.facture_ligne.AR_Ref = '".$_REQUEST['num']."' OR dbo.facture_ligne.DO_Piece = '".$_REQUEST['num']."' OR dbo.article.FA_CodeFamille = '".$_REQUEST['num']."') ";
	    }
	    $tsql = "
	            SELECT 
	                DO_Piece,
	                DO_Tiers,
	                CT_Intitule,
	                AR_Ref,
	                DL_Design, 
	                DL_Qte,
	                DL_QteP,
	                (DL_Qte-DL_QteP) AS RAL,
	                FA_CodeFamille,
	                DO_Coord01 + ' - ' + DO_Coord02 + ' - ' + DO_Coord03 + ' - ' + DO_Coord04 AS comm,
	                entDate,
	                DL
	            FROM (
	                SELECT
	                    dbo.facture_ligne.DO_Piece,
	                    dbo.facture_entete.DO_Date AS entDate,
	                    dbo.facture_ligne.AR_Ref AS AR_Ref,
	                    dbo.facture_ligne.DL_Qte AS DL_Qte,
	                    dbo.facture_ligne.DL_QteP AS DL_QteP,
	                    dbo.facture_ligne.DL_Design AS DL_Design,
	                    dbo.facture_entete.DO_Tiers,
	                    dbo.article.AR_Design,
	                    dbo.article.FA_CodeFamille,
	                    dbo.client.CT_Intitule,
	                    dbo.facture_entete.DO_Coord01,
	                    dbo.facture_entete.DO_Coord02,
	                    dbo.facture_entete.DO_Coord03,
	                    dbo.facture_entete.DO_Coord04,
	                    dbo.article.DL
	                FROM
	                    dbo.facture_entete
	                    INNER JOIN dbo.facture_ligne ON dbo.facture_entete.DO_Piece = dbo.facture_ligne.DO_Piece
	                    INNER JOIN dbo.article ON dbo.facture_ligne.AR_Ref = dbo.article.AR_Ref_New
	                    LEFT JOIN dbo.client ON dbo.facture_entete.DO_Tiers = dbo.client.CT_Num
	                WHERE
	                    ".$_SESSION['where']." 
	                    ".$q."
	                    AND dbo.facture_entete.DO_type IN(6,7,23)
	                    ".$q_w."
	                    AND ( dbo.facture_ligne.statut = 3 OR (dbo.facture_ligne.DL_Qte <> dbo.facture_ligne.DL_QTEP AND dbo.facture_ligne.statut != 9 )) 
	                    AND dbo.facture_entete.DO_Provenance IN(0,3)
	                    AND dbo.facture_ligne.AR_Ref NOT IN ('LIVRAISON','ZDIVERS','ZTAXE','DISCOUNT','05024349','05024350','05024351','05024355','SAVDEP','SAVHB','SAVHT','SAVPCDET','10037140','10037151','90010009','90010003','10037149','90010001','10037162')
	                    AND dbo.facture_ligne.DL_Qte > 0
	                ) AS tabEnt
	                ORDER BY     
	                    entDate DESC";

	        $getData = $dbh->query($tsql);
	        $objPHPExcel    = new PHPExcel();
	        $etatTitle      = "Reste à Livrer";

	        $titre_         = 'Reste à Livrer:   du '.date('d/m/Y');
	        $lin = 1;

	        
	        $colWidth = 'L';    
	        $objPHPExcel->getProperties()->setCreator("SANIFER - Etats informatisés")
	                    ->setLastModifiedBy("SANIFER - Etats informatisés")
	                    ->setTitle($etatTitle)
	                    ->setSubject($etatTitle)
	                    ->setDescription($etatTitle);

	        $xlsxSheet      = $objPHPExcel->setActiveSheetIndex(0); 
	    while ($val = $getData->fetch(PDO::FETCH_ASSOC)) {          	        
        
	        if ($lin == 1) {
	                    $xlsxSheet->setCellValue("A1", "Facture");
	                    $xlsxSheet->setCellValue("B1", "N° client");
	                    $xlsxSheet->setCellValue("C1", "Intitulé");
	                    $xlsxSheet->setCellValue("D1", "Ref");
	                    $xlsxSheet->setCellValue("E1", "Désignation");
	                    $xlsxSheet->setCellValue("F1", "Qte facturée");
	                    $xlsxSheet->setCellValue("G1", "Qte préparée");
	                    $xlsxSheet->setCellValue("H1", "RAL");
	                    $xlsxSheet->setCellValue("I1", "Famille");
	                    $xlsxSheet->setCellValue("J1", "Commentaire");
	                    $xlsxSheet->setCellValue("K1", "Date de la facture");
	                    $xlsxSheet->setCellValue("L1", "Dépôt");
	        }
	        ++$lin; 
	        for ($col = 'A'; $col < $colWidth; ++$col) {
	            $xlsxSheet->setCellValue("A".$lin, $val["DO_Piece"]);
		        $xlsxSheet->setCellValue("B".$lin, $val["DO_Tiers"]);
		        $xlsxSheet->setCellValue("C".$lin, $val["CT_Intitule"]);
		        $xlsxSheet->setCellValue("D".$lin, $val["AR_Ref"]);
		        $xlsxSheet->setCellValue("E".$lin, $val["DL_Design"]);
		        $xlsxSheet->setCellValue("F".$lin, $val["DL_Qte"]);
		        $xlsxSheet->setCellValue("G".$lin, $val["DL_QteP"]);
		        $xlsxSheet->setCellValue("H".$lin, $val["RAL"]);
		        $xlsxSheet->setCellValue("I".$lin, $val["FA_CodeFamille"]);
		        $xlsxSheet->setCellValue("J".$lin, $val["comm"]);
		        $xlsxSheet->setCellValue("K".$lin, $val["entDate"]);
		        $xlsxSheet->setCellValue("L".$lin, $val["DL"]);
	        }         
	         
	               
	        
	    }
	        

					 
	    			$globalStyleArray = array(
					    'borders' => array(
					      'allborders' => array(
					        'style' => PHPExcel_Style_Border::BORDER_THIN,
					    ),
					    'font'  => array(
					        'size'  => 10,
					    )

					    )
					  );

					  $xlsxSheet->getStyle('A1:' . $xlsxSheet->getHighestColumn() . $xlsxSheet->getHighestRow())->applyFromArray($globalStyleArray);

					  $xlsxSheet
					    ->getStyle( $xlsxSheet->calculateWorksheetDimension() )
					    ->getAlignment()
					    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

					  $xlsxSheet
					    ->getStyle( $xlsxSheet->calculateWorksheetDimension() )
					    ->getAlignment()
					    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

					  $headerStyleArray = array(
					    'fill' => array(
					      'type' => PHPExcel_Style_Fill::FILL_SOLID,
					      'color' => array('rgb' => '7aa2e2'),
					    ),
					    'font'  => array(
					        'bold'  => true,
					        'color' => array('rgb' => 'FFFFFF'),
					        'size'  => 12,
					    )
					  );

					  $xlsxSheet->getStyle('A1:' . $xlsxSheet->getHighestColumn() . '1')->applyFromArray($headerStyleArray);

					  $numberFormat = '###\ ###\ ###\ ###\ ##0.00';

	                $xlsxSheet->getStyle('A2:A' . $lin)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	                $xlsxSheet->getStyle('B2:B' . $lin)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	                $xlsxSheet->getStyle('C2:C' . $lin)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	                $xlsxSheet->getStyle('D2:D' . $lin)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	                $xlsxSheet->getStyle('E2:E' . $lin)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	                $xlsxSheet->getStyle('F2:F' . $lin)->getNumberFormat()->setFormatCode($numberFormat);
	                $xlsxSheet->getStyle('G2:G' . $lin)->getNumberFormat()->setFormatCode($numberFormat);
	                $xlsxSheet->getStyle('H2:H' . $lin)->getNumberFormat()->setFormatCode($numberFormat);
	                $xlsxSheet->getStyle('I2:I' . $lin)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	                $xlsxSheet->getStyle('J2:J' . $lin)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	                $xlsxSheet->getStyle('K2:K' . $lin)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

	              
	                $sheetName = 'RAL_'.date('d-m-Y');
	                
	                $xlsxSheet->setTitle($sheetName);

	                $objPHPExcel->setActiveSheetIndex(0);

	               

	                $excelFileName = "RAL".'_'.date('d-m-Y').".xlsx";

	                for($col = 'A'; $col < $colWidth; ++$col)
				    $xlsxSheet->getColumnDimension($col)->setAutoSize(true);


				  $xlsxSheet->freezePane('A2');

				  $objPHPExcel->setActiveSheetIndex(0);

				  $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
				  $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
				  $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
				  $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				  $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

				  $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter($etatTitle . ' - Page &P / &N');
				  $objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter($etatTitle . ' - Page &P / &N');

	            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	              header('Content-Disposition: attachment;filename="' . $excelFileName . '"');
	              header('Cache-Control: max-age=0');
	              header('Cache-Control: max-age=1');

	              header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
	              header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); 
	              header ('Cache-Control: cache, must-revalidate'); 
	              header ('Pragma: public'); // HTTP/1.0

	              $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	              ob_end_clean();
	              $objWriter->save('php://output');
	              exit;
	    }
}


?>