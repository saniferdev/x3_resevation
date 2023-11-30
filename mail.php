<?php 

/*phpinfo();

die();*/
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

envoiMail();

function envoiMail(){
    $mail = new PHPMailer(true);
    $mail->setLanguage('fr', '/PHPMailer/language/');
    $mail->IsSMTP(); 
    $mail->SMTPOptions = array(
      'ssl' => array(
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true
      )
    );
    $mail->SMTPDebug  = 4;  
    $mail->SMTPAuth   = true;  
    $mail->SMTPSecure = 'tls'; 
    $mail->Host       = gethostbyname('smtp.gmail.com');
    $mail->Port       = 587; 
    $mail->Username   = 'sanifer.informatique@gmail.com';
    $mail->Password   = '7dJbW5h8';
    $mail->SetFrom('admin@groupesanifer.com', 'Sanifer');

    $mail->Subject    = 'Commande test';
    $mail->CharSet    = 'UTF-8';
    $mail->Body       = 'test mail sanifer';
    $mail->AddAddress('rocky.info@talys.mg');
    $mail->AddCC('winny.info@talys.mg');

    $mail->addReplyTo('ass2.scecomm@sanifer.mg', 'Joujou');
    //$mail->addReplyTo('winny.info@talys.mg', 'Winny');
    $mail->isHTML(true);
    if(!$mail->Send()) {
      $return         = "Erreur d'envoi de mail: ".$mail->ErrorInfo;
    } else {
      $return         = 'Mail envoyé avec succès!';
    }
    return $return;
  }