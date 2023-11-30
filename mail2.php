<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

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
  //  $mail->SMTPSecure = 'tls'; 
    $mail->Host       = 'mail.fripesenligne.fr';                     
    $mail->Username   = 'kibocom@fripesenligne.fr';                    
    $mail->Password   = 'dx2sy3gl%`3@';                              
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
    $mail->Port       = 465;   
    $mail->SetFrom('informatique@fripesenligne.fr', 'KIBO');
    $mail->Subject    = "Commande test mail";
    $mail->CharSet    = 'UTF-8';
    $mail->Body       = "Test mail commande kibo";
    $mail->AddAddress('winny.info@talys.mg');
    $mail->AddAddress('rocky.info@talys.mg');
    //$mail->AddCC('informatique@sanifer.mg');
    //$mail->AddCC('fabien.tozzo@talys.mg');
    //$mail->AddBCC('winny.info@talys.mg');
    $mail->addReplyTo('informatique@sanifer.mg');
    $mail->isHTML(true);
    if(!$mail->Send()) {
      echo "Erreur d'envoi de mail: ".$mail->ErrorInfo;
    } else {
      echo 'Mail envoyé avec succès!';
    }
?>