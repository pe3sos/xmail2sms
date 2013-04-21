<?php 
ini_set('display_errors',1);

include "class.database.php";
include "functions.php";


session_start();
$db=new Database(); 

if(isset($_POST['trimite']) && !isset($_POST['message']{799})){
  
$telefoane = $db->Escape($_POST['telefon']);
$message = $db->Escape($_POST['message']);
$message =  str_replace('\r\n',"\r\n",$message);

$telefoane = explode('\r\n',$telefoane);

foreach($telefoane as $telefon){
$telefon = trim($telefon);
$text = "To:".$telefon."\n";
$text .= "Alphabet:ISO\n";
$text .= "Autosplit:2\n\n";
$text .= "".$message ."\n\n";
$text .= "--Sent by RaspUSHteam--\n";
 
   $filename = sendsms($text);
  
    savemsj($telefon,$message,$filename);
  }
    setstatus("Mesajul a fost pregatit pentru trimitere! Va multumim pentru colaborare!");

  redirect('trimite.php');
}


  
 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <title>SMS Gateway </title>
      <style type="text/css">
      @import url(http://fonts.googleapis.com/css?family=Roboto+Condensed:300italic,400italic,700italic,400,700,300);
        *{margin:0;padding:0;}
        body{font-family:arial, helvetica,sans-serif;color:#444;}
        h1{font-size:60px;text-align:center;margin:25px 25px 50px;font-family: 'Roboto Condensed', sans-serif;text-shadow:0px 0px 2px #555;
        line-height:0.8;
        }
        h2{font-size:16px;}
        h3{font-size:14px;}
        h4{font-size:12px;}
        
        #wrapp{width:490px;margin:0  auto;}
        .listt{margin-top:50px;}
        .listt p{overflow:hidden;padding:4px;;}
        .listt input,.listt textarea{padding:2px;border:1px solid #555;font-size:12px;}
        .listt p label{display:block;text-align:left;text-transform:uppercase;margin-right:10px;}
        .tels{margin:10px;}
        .status{text-align:center;background:#C0FF95;padding:5px;font-size:12px;}
        .nrchar{text-align:right;font-size:12px;}
      </style>
      <script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
      <script type="text/javascript">
      $(function(){
        
        $('#status').delay(5000).slideUp(1000);
        $(".textbox").keyup(function(){
        var nrc = $('.textbox').val().length;
            $('.nrchar').text(nrc);        
        });
      });
      </script>
</head>

<body>
<div id="wrapp">
<h1> SMS <br/>
Gateway </h1>
<?php /*
<div class="tels">
<h2>Sergiu:40741497441</h2>
<h2>Alex:40723643417</h2>
</div>
<input type="text" value="40744520056" id="nume" name="telefon" class="required inpt">
*/ ?>

<?php pstatus(); ?>

<form action="" method="post" id="sms-form" class="listt"> 
<input type="hidden" value="1" name="trimite">   
<div class="form-left">              

<p style="float:left;width:100px;">
<label for="nume">Telefon: </label>
<textarea cols="10" rows="7" name="telefon" class="textbox">40744520056</textarea>
</p>  
<p><label for="nume">Mesaj: </label>
  <textarea cols="50" rows="4" name="message" class="textbox"></textarea>
  <div class="nrchar">0</div></p>  
<p style="text-align:center;"><input type="submit" value="Trimite mesajul" name="submit" id="submit-button" />      </p>
</div>
</form>
</div>
</body>
</html>
