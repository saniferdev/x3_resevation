<?php

  function envoiMail($sujet,$objet){
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
    $mail->SetFrom('winny@sanifer.mg', 'Winny');
    $mail->Subject    = $sujet;
    $mail->CharSet    = 'UTF-8';
    $mail->Body       = $objet;
    $mail->AddAddress('winny.info@talys.mg', 'Winny');
    $mail->addReplyTo('winny.info@talys.mg', 'Winny');
    $mail->isHTML(true);
    if(!$mail->Send()) {
      $return         = "Erreur d'envoi de mail: ".$mail->ErrorInfo;
    } else {
      $return         = 'Mail envoyé avec succès!';
    }
    return $return;
  }
envoiMail('test','test');
?>