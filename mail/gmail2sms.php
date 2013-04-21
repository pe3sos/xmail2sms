<?php
ini_set('display_errors',1);

include "../data/class.database.php";
include "../data/functions.php";

$username = 'xmail2sms@gmail.com';
$password = 'spiru2013';
//configurarea cont de gmail


// $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
 $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert/norsh}Inbox';
// $hostname= '{imap.gmail.com:993/ssl/novalidate-cert}';
/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

/* grab emails */
// $emails = imap_search($inbox,'ALL');
$emails = imap_search($inbox,'UNSEEN');//extragem doar mesajele citite

/* if emails are returned, cycle through each... */
if($emails) {
	
	/* begin output var */
	$output = '';
	
	/* put the newest emails on top */
	rsort($emails);
	
	/* for every email... */
	foreach($emails as $email_number) {
		
		/* get information specific to this email */
		$overview = imap_fetch_overview($inbox,$email_number,0);
		$message = imap_fetchbody($inbox,$email_number,2);
    
    		//Data se executa comanda ->Mailul a fost citit
    $message = imap_fetchbody($inbox,$email_number,2);
		
    $subject =  isset($overview[0]->subject)?$overview[0]->subject:'';
    $seen =  ($overview[0]->seen ? 'read' : 'unread');
    $datax =  strtotime($overview[0]->date);
    $data =  date( "Y-m-d H:i:s",$datax);
    $mesaj = strip_tags($message);

    if(is_numeric($subject) && (strlen($subject)==11)){
       $telefon =  $subject;
      $text = "To:".$telefon."\n";
      $text .= "Alphabet:ISO\n";
      $text .= "Autosplit:2\n\n";//Aplicatia stie singura sa imparta in bucati// bine sa nu exageram
      $text .= "".$mesaj ."\n\n";
      $text .= "--Sent by RaspUSHteam--\n";///watermark 

       $filename = sendsms($text);
       savemsj($telefon,$mesaj,$filename);
		 }
        
		/* output the email header information */
		$output.= '<div class="email">';
		$output.= '<div class="toggler '.$seen.'">';
		$output.= '<span class="from">'.$overview[0]->from.'</span>';
		$output.= '<span class="subject">'.$subject.'</span> ';
    $output.= '<span class="date">on '.$data.'</span>';
		$output.= '</div>';
		
		/* output the email body */
		$output.= '<div class="body">'.$message.'</div>';
		$output.= '</div>';

	}
	
	echo $output;
} 

/* close the connection */
imap_close($inbox);
?>
