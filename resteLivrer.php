<?php
session_start();
error_reporting(1);
include('includes/config.php');
if(strlen($_SESSION['alogin'])==0)
{
    header('location:index.php');
}
else{
    $q = $q_w = "";    
    if($_SESSION['site'] == "S3"){
        $qs = " AND dbo.facture_ligne.AR_Ref IN ( SELECT dbo.article.AR_Ref_New FROM dbo.dep_s3_s4 INNER JOIN dbo.article ON dbo.article.AR_Ref = dbo.dep_s3_s4.S3_DEP ) ";
    }
    elseif($_SESSION['site'] == "S4"){
        $qs = " AND dbo.facture_ligne.AR_Ref IN ( SELECT dbo.article.AR_Ref_New FROM dbo.dep_s3_s4 INNER JOIN dbo.article ON dbo.article.AR_Ref = dbo.dep_s3_s4.S4_DEP ) ";
    }
    else{
        $qs = "";
    }   
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
                entDate
            FROM (
                SELECT
                    dbo.facture_ligne.DO_Piece,
                    dbo.facture_entete.statut AS entStat,
                    dbo.facture_ligne.DO_Ref,
                    dbo.facture_entete.DO_Date AS entDate,
                    dbo.facture_ligne.statut AS lineStat,
                    dbo.facture_ligne.AR_Ref AS AR_Ref,
                    dbo.facture_ligne.DL_Qte AS DL_Qte,
                    dbo.facture_ligne.DL_QteP AS DL_QteP,
                    dbo.facture_ligne.DO_Date AS lineDate,
                    dbo.facture_ligne.DL_Design AS DL_Design,
                    dbo.article.AR_Design,
                    dbo.article.FA_CodeFamille,
                    dbo.article.DL,
                    dbo.facture_entete.DO_Tiers,
                    dbo.facture_entete.prepa,
                    dbo.facture_entete.DO_Coord01,
                    dbo.facture_entete.DO_Coord02,
                    dbo.facture_entete.DO_Coord03,
                    dbo.facture_entete.DO_Coord04,
                    dbo.client.CT_Intitule,
                    dbo.client.CT_Adresse
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
                    ".$qs."
                    AND ( dbo.facture_ligne.statut = 3 OR (dbo.facture_ligne.DL_Qte <> dbo.facture_ligne.DL_QTEP AND dbo.facture_ligne.statut != 9 )) 
                    AND dbo.facture_entete.DO_Provenance IN (0,3)
                    AND dbo.facture_ligne.AR_Ref NOT IN ('LIVRAISON','ZDIVERS','ZTAXE','DISCOUNT','05024349','05024350','05024351','05024355','SAVDEP','SAVHB','SAVHT','SAVPCDET','10037140','10037151','90010009','90010003','10037149','90010001','10037162')
                    AND dbo.facture_ligne.DL_Qte > 0
                ) AS tabEnt
                ORDER BY     
                    entDate DESC";

        $getData = $dbh->query($tsql);

        while ($row = $getData->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        $val =  json_encode($data);

}
?>

<?php include('includes/entete.php'); ?>
    <div class="row">
        <div class="col-md-12"  ng-app="dynamicApp" ng-controller="dynamicCtrl" class="container" ng-init="fetchData()">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h5>Reste Livrer</h5>
                        </div>


                        <div class="panel-body">
                            
                            <form method="get" action="resteLivrer.php" class="form-horizontal" enctype="multipart/form-data">
                                <div class="col-sm-4" style="margin-bottom: 20px">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" style="position: relative;top: -15px;">N° Facture <br> Famille <br> Réf Article :</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control numFact" id="num" name="num" value="<?php echo (isset($_REQUEST['num']) && !empty($_REQUEST['num'])) ? $_REQUEST['num'] : '' ; ?>" />
                                    </div>
                                </div>
                            </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">Date du :</label>
                                        <div class="col-sm-8">
                                            <input id="datepicker_debut" id="date_debut"  name="date_debut" value="<?php if(isset($_REQUEST['date_debut'])){ echo $_REQUEST['date_debut']; }else{ echo $current_date_time->format("Y-m-d"); } ?>"/>
                                        </div>
                                    </div>
                                </div>

                                  <div class="col-sm-3">
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label"> au :</label>
                                                <div class="col-sm-8">
                                                    <input id="datepicker_fin" id="date_fin"   class="form-control"  name="date_fin" value="<?php if($_REQUEST['date_fin']){ echo $_REQUEST['date_fin']; }else{ echo $current_date_time->format("Y-m-d"); } ?>"/>
                                                </div>
                                            </div>
                                        </div>

                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <div class="col-sm-8 col-sm-offset-2">
                                            <button class="btn btn-primary" name="rechercher" type="submit">Rechercher</button>

                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="col-md-12 exp" style="padding-bottom: 15px; margin-top: -25px;">
                                <button class="btn btn-success xlsx" >Export to Excel</button>
                            </div>

                            <table id="listeBon" name="listeBon" class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                                <thead>
                                <tr style="background: #0d72d8;color: #fff;">
                                    <th>N° Facture</th>
                                    <th>Client</th>
                                    <th>Article</th>
                                    <th>Désignation</th>
                                    <th>Qte facturée</th>
                                    <th>Qte préparée</th>
                                    <th>Reste à Livrer</th>
                                    <th>Famille</th>
                                    <th>Date de la facture</th>
                                </tr>
                                </thead>

                                <tbody>

                                <tr ng-repeat="data in namesData | filter:numFacture">
                                    <td><a href="gestion.php?numBont={{ data.DO_Piece }}">{{ data.DO_Piece }}</a></td>
                                    <td><a href="gestion.php?numBont={{ data.DO_Piece }}">{{ data.DO_Tiers }} - {{ data.CT_Intitule }}</a></td>
                                    <td><a href="gestion.php?numBont={{ data.DO_Piece }}">{{ data.AR_Ref }}</a></td>
                                    <td><a href="gestion.php?numBont={{ data.DO_Piece }}">{{ data.DL_Design }}</a></td>
                                    <td><a href="gestion.php?numBont={{ data.DO_Piece }}">{{ data.DL_Qte | number: 6 }}</a></td>
                                    <td><a href="gestion.php?numBont={{ data.DO_Piece }}">{{ data.DL_QteP | number: 6 }}</a></td>
                                    <td><a href="gestion.php?numBont={{ data.DO_Piece }}">{{ data.RAL | number: 6 }}</a></td>
                                    <td><a href="gestion.php?numBont={{ data.DO_Piece }}">{{ data.FA_CodeFamille }}</a></td>
                                    <td>{{ data.entDate| toDate | date:'dd/MM/yyyy HH:mm' }}</td>

                                </tr>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="js/angular.js"></script>
    <script type="text/javascript">
        var app = angular.module('dynamicApp', []);
        app.controller('dynamicCtrl', function($scope, $http){
            $scope.fetchData = function(){
                $http.get('facture.php').success(function(data){
                    $scope.namesData = <?php echo $val; ?>;
                    // console.log($scope.namesData);
                });
            }

            $scope.query = "";
        });
         app.filter('toDate', function() {
                      return function(items) {
                        return new Date(items);
                      };
                    });

    </script>

<?php include('includes/footer.php'); ?>