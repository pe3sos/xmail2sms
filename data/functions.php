<?php 


function savemsj($telefon,$mesaj,$filename){
global $db ;

$db->Execute("insert into mesaje set `to`='".$telefon."', `mesaj`='".$mesaj."', `data_trimitere`=now(), `file`='".$filename."' ;");
$id = $db->GetLastID();
return $id;
}
/* 
trimite sms 

  important sa fie link catre folderul 
  /var/spool/sms 
  **cu drepturi de editare bineinteles
*/
function sendsms($mesaj){
// $filename = 'mesaj_'.time();
$filename = 'mesaj_'.microtime(true);
$filepathname = 'sms/outgoing/'.$filename;
$fh = fopen($filepathname , "w+");
fwrite($fh , $mesaj);
fclose($fh);
return $filename;
}

//functie pentru redirect
function redirect($page){
  header('Location: '.$page.'');
  die();
}

function setstatus($status){
  $_SESSION['pstatus'] = $status;
}
function pstatus(){

  if(isset(  $_SESSION['pstatus']{3})){
  $e =   $_SESSION['pstatus'];
  echo '<div id="status" class="status">'.$e.'</div>';
  $_SESSION['pstatus'] ='';
  unset($_SESSION['pstatus']);
  }

}
 ?>