<?php
include "includes/inc_db.php";
$db=new DB;
$finalArray= array();
//text, keyword, msisdn

$message = explode("|", $text);

$textIn=$message[0];
$location = explode(",",$message[1]);

$testText = explode(" ",$text);
unset($testText[0]);
$text=implode(" ",$testText);

$text=filter_var($text,FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
$text=filter_var($text,FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

$text=addslashes($text);
$lat = $location[0];
$long = $location[1];


$db->query("INSERT INTO messageLog SET msInText='$textIn', msMode='$keyword', msFrom='$msisdn', msLong='$long', msLat='$lat'");

/*
here is where we need to go off to google to get the api location
*/

/*
send the message back out
*/













//https://maps.googleapis.com/maps/api/directions/json?origin=Disneyland&destination=Universal+Studios+Hollywood&mode=walking&key=AIzaSyDDB4wNh_eKWYescpP5V7sHo0VHsAu5Ml0
$googleApi = "AIzaSyDDB4wNh_eKWYescpP5V7sHo0VHsAu5Ml0";
$origin="$msLong,$msLat";
$destination = $text;
$mode = $keyword;




$sendUrl = "https://maps.googleapis.com/maps/api/directions/json?origin=$origin&destination=$destination&mode=$mode&key=$googleApi";
$ch = curl_init($sendUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$return = curl_exec($ch);

$directions = json_decode($return);


foreach($directions['routes'] as $key=>$value){
    foreach($value['legs'] as $key=>values){
        foreach($values['step'] as $key=>values1){
            
        }
    }
}



$returnString = "Your Location:Birmingham,<br>Destination:Not Birmingham,<br>Directions=>";

$lenght = strlen($returnString);
if($length>160){
    $array = str_split($returnString, 160);
    $finalArray[]=$array[0];

    while(strlen($array[1])>160){
        $array = str_split($array[1], 160);

        $finalArray[]=$array[0];
    }

    $finalArray[] = $array[1];
    
}else{
    $finalArray[] = $returnString;
}







foreach($finalArray as $sendMessage){
    $apiKey = "d9982ba0";
    $apiSecret = "0fZtF4hqogcNNa50";
    $text = $sendMessage;
    $to = $msisdn;
    $from = "NOINTERNET";

    $url = 'https://rest.nexmo.com/sms/json?' . http_build_query([
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'to' => $to,
            'from' => $from,
            'text' => $text
        ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
}











/*
have to seperate into multiple texts if string length is > 160
*/
?>



