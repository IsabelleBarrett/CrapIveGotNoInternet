<?
// Custom functions
function insertAppointmentParts($xcnId, $apId){
	//Insert a new part required into the appointmentParts table
	/* Need the following information
	   - apDate & apTechnician
	*/
	$db = new DB;
	
	$db->query("SELECT apDate, apTechnician, apJbId, jbPartLoc FROM appointments, jobs WHERE apId='$apId' AND apJbId=jbId");
	$db->next_record();
	list($apDate, $apTechnician, $apJbId, $jbPartLoc) = $db->Record;
	
	//Insert this information into appointmentParts
	$db->query("INSERT INTO appointmentParts SET atApId='$apId', atDateCreated='".date("Y-m-d H:i")."', atCreatedBy='$xcnId', atApDate='$apDate', atTechId='$apTechnician', atJbId='$apJbId', atPartLocation='$jbPartLoc'");
	
}

function getLongLat($postcode){
	//Pass in postcode & return Longitude/Latitude as an Object	
	//This function first checks the postcodesdetailed table for the long/lat
	//If not found, uses the Google Geocoding API to calculate and updates the postcodesdetailed table
	$db = new DB;
	//$postcode = "DY5 2UA";
	$object = new stdClass();
	
	$postcode = trim($postcode);
	//Check to see if postcode exists in postcodesdetailed table
	$postcode_a = strtoupper(str_replace(" ","",$postcode));
	$postcode_a = addslashes($postcode_a);
	$db->query("SELECT pdLat, pdLong FROM postcodesdetailed WHERE pdPostcode = '$postcode_a'");
	$db->next_record();
	list ($pdLat, $pdLong) = $db->Record;
	
	if (($pdLat != '') && ($pdLong != '')){
		$object->long = $pdLong;
		$object->lat = $pdLat;
		$object->status = "OK"; //Should return 'OK' if everything fine
	} else {
		
		$address = str_replace(" ", "+", $postcode);
		$url = "http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=UK";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		
		$response_a = json_decode($response);	
		
		$object->long = $response_a->results[0]->geometry->location->lng;
		$object->lat = $response_a->results[0]->geometry->location->lat;
		$object->status = $response_a->status; //Should return 'OK' if everything fine
		
		//If Long & Lat, INSERT into postcodesdetailed table so that next time we won't need to use the Google API
		if ($response_a->status == 'OK'){
			$db->query("INSERT INTO postcodesdetailed SET pdPostcode='".$postcode_a."', pdLat='".$object->lat."', pdLong='".$object->long."'");
		}
	}
	
	/* Status codes
		- "OK" - No Errors.
		- "ZERO_RESULTS" - successful but returned no results. Invalid address or a latlng in a remote location.
		- "OVER_QUERY_LIMIT" - indicates that you are over your quota.
		- "REQUEST_DENIED" - request was denied, generally because of lack of a sensor parameter.
		- "INVALID_REQUEST" - indicates that the query (address or latlng) is missing.
		- UNKNOWN_ERROR - request could not be processed due to a server error. The request may succeed if you try again.
	*/
	//var_dump($response_a);
	return $object;
}

/*
//function backup
function getLongLat($postcode){
	
	//Pass in postcode & return Longitude/Latitude as an Object	
	//$postcode = "DY5 2UA";
	
	$postcode = trim($postcode);
	
	$address = str_replace(" ", "+", $postcode);
	$url = "http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=UK";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$response = curl_exec($ch);
	curl_close($ch);
	
	$response_a = json_decode($response);
	//echo "Postcode: $postcode<br />";
	
	$object = new stdClass();
	$object->long = $response_a->results[0]->geometry->location->lng;
	$object->lat = $response_a->results[0]->geometry->location->lat;
	$object->status = $response_a->status; //Should return 'OK' if everything fine
	
	//var_dump($response_a);
	
	
	return $object;
}
*/


function statusUpdateMultiyork($jobID, $status){
	$jobID=0; //Remove line to switch function back on
	//This function send Multiyork the new job status via a SOAP Web Service
	$db = new DB;
	//if called on localhost, set $jobID to 0
	$serverName = $_SERVER['HTTP_HOST'];
	if (stristr($serverName, 'localhost')){
		//Localhost so ignore this function
		$jobID=0;
	}
	
	if (($jobID > 0) && ($status != '')){
		$jobID = addslashes($jobID);
		//Need to make sure that the Job ID belongs to Multiyork
		$multiyork = 0;
		$multiyork = $db->getval("SELECT coId FROM companies, jobs WHERE jbCoId=coId AND jbId='$jobID'
		AND coName LIKE '%multiyork%'","coId");
		//echo "Multiyork: $multiyork";
		if ($multiyork > 0){
			//Job belongs to multiyork so pull out the Order Ref & send the details to Multiyork
			$myorkid = $db->getval("SELECT jbRef FROM jobs WHERE jbId='$jobID'","jbRef");
			if (!$myorkid){
				//if jbRef blank, get jbItemNo
				$myorkid = $db->getval("SELECT jbItemNo FROM jobs WHERE jbId='$jobID'","jbItemNo");
			}
			if ($myorkid){
				//Everything checks out so send the update to Multiyork
				// The URL to POST to
				$url = "https://edi.multiyork.co.uk/webservices/test/homeserve_status/service1.asmx";
				
				// The value for the SOAPAction: header
				$action = "https://edi.multiyork.co.uk/webservices/test/homeserve_status/addStatus";
				
				// Get the SOAP data into a string, I am using HEREDOC syntax
				// but how you do this is irrelevant, the point is just get the
				// body of the request into a string
				$mySOAP = <<<EOD
				<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
				  <soap:Body>
					<addStatus xmlns="https://edi.multiyork.co.uk/webservices/test/homeserve_status/">
					  <jobid>$jobID</jobid>
					  <myorkid>$myorkid</myorkid>
					  <status>$status</status>
					</addStatus>
				  </soap:Body>
				</soap:Envelope>
EOD;
				
				  // The HTTP headers for the request (based on image above)
				  $headers = array(
					'Content-Type: text/xml; charset=utf-8',
					'Content-Length: '.strlen($mySOAP),
					'SOAPAction: '.$action
				  );
				
				  // Build the cURL session
				  $ch = curl_init();
				  curl_setopt($ch, CURLOPT_URL, $url);
				  curl_setopt($ch, CURLOPT_POST, TRUE);
				  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				  curl_setopt($ch, CURLOPT_POSTFIELDS, $mySOAP);
				  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				
				  // Send the request and check the response
				  if (($result = curl_exec($ch)) === FALSE) {
					//die('cURL error: '.curl_error($ch)."<br />\n");
				  } else {
					//echo "Success! - $result<br />\n";
				  }
				  curl_close($ch);
				
				  // Handle the response from a successful request
				  $xmlobj = simplexml_load_string($result);
				  //var_dump($xmlobj);

			
			} //end if $myorkid
		} //end if multiyork > 0
		
	} 	
}



function getLastNoteUser($coId, $jbId){
	//This function gets the next person in the list who a note should be assigned to uses array $unreadNoteUsers in Global_Vars
	$db1 = new DB;
	$dbtest = new DB;
$t=0;
$jbSofaStore=$dbtest->getval("SELECT jbSofaStore FROM jobs WHERE jbId='$jbId'","jbSofaStore");
$dbtest->query("SELECT coParentId, coId FROM companies WHERE coParentId='$coId' OR coId='$coId'"); //check if OFL
$dbtest->next_record();
		list($qcoParentId, $qcoId) = $dbtest->Record;

	if($qcoId=='640' || $qcoParentId=='640'|| $jbSofaStore=='1'){
		$db1->query("SELECT cnId FROM contacts WHERE cnCoId='1' AND cnOakNotesActive='1'"); //if OFL assign only to OFL pod
	} else if($qcoId=='1007'){
		$db1->query("SELECT cnId FROM contacts WHERE cnCoId='1' AND cnId='3334'"); //if Harveys assign to Adam
	} else {
		$db1->query("SELECT cnId FROM contacts WHERE cnCoId='1' AND cnNotesActive='1'");
	}
		while ($db1->next_record()){ 
			list($ncnId) = $db1->Record;
			$unreadNoteUsers[$t]=$ncnId;
			$t++;
		}
	$contactArr= $unreadNoteUsers;
	$ntAssignedTo = $db1->getval("SELECT ntAssignedTo FROM notes WHERE ntAssignedTo > '0' AND ntAssignedTo!='2285' 
								  AND ntAssignedTo!='2518' AND ntAssignedTo!='76' AND ntAssignedTo!='3334' AND ntAssignedTo !='1142' AND ntAssignedTo !='4301' 
								  AND ntAssignedTo !='3677' AND ntAssignedTo !='4025'   
								  ORDER BY ntId DESC LIMIT 1","ntAssignedTo");

	if($ncnId==2518 || $ncnId==76 || $ncnId==2285 || $ncnId==4301 || $ncnId==3677 || $ncnId==4025){// If Sian or Naz use different array
		
		//"0" => "2285", //Sian
		//"1" => "2518", //Naz
		//"2" => "76",  //Kirst
		//"3" => "4301",  //Luke
		//"4" => "3677",  //Lauren
		//"5" => "4025");  //Katie		
//		$oflNoteUsers = array( 
	
//		"0" => "2285", //Sian
//		"1" => "2518",  //Naz 
//		"2" => "4301",  //Luke
//		"3" => "3677",  //Lauren
//		"4" => "4025");  //Katie
		
//		$oflNoteUsers = array("0" => "2285", "1" => "76");
//		$contactArr=$oflNoteUsers;
		$ntAssignedTo = $db1->getval("SELECT ntAssignedTo FROM notes WHERE ntAssignedTo > '0' AND (ntAssignedTo='2285' 
								       OR ntAssignedTo='2518' OR ntAssignedTo='76' OR ntAssignedTo='4301' OR ntAssignedTo='3677'
									   OR ntAssignedTo='4025') ORDER BY ntId DESC LIMIT 1","ntAssignedTo");
									   //OR ntAssignedTo='2518'
									   
		
		if (!$ntAssignedTo) {
			$arrayPos=0;
		} else {
			
			$arrayPos = array_search($ntAssignedTo,$contactArr);
			//echo "ArrayPos: $arrayPos<br />";
			$arrayPos++;
			if ($arrayPos >= count($contactArr)){
				$arrayPos=0;
			}	
		}	
		
	} else if($ncnId==3334){// If Adam use different array
				
				$harveysNoteUsers = array( 
				"0" => "3334",
				"1" => "3334");
				
				$contactArr=$harveysNoteUsers;
				$ntAssignedTo = $db1->getval("SELECT ntAssignedTo FROM notes WHERE ntAssignedTo > '0' AND ntAssignedTo='3334' ORDER BY ntId DESC 
											  LIMIT 1","ntAssignedTo");
				
				if (!$ntAssignedTo) {
					$arrayPos=0;
				} else {
					
					$arrayPos = array_search($ntAssignedTo,$contactArr);
					//echo "ArrayPos: $arrayPos<br />";
					$arrayPos++;
					if ($arrayPos >= count($contactArr)){
						$arrayPos=0;
					}		
				}
				
	} else{ 
		if (!$ntAssignedTo) {
			$arrayPos=0;
		} else {
			
			$arrayPos = array_search($ntAssignedTo,$contactArr);
			//echo "ArrayPos: $arrayPos<br />";
			$arrayPos++;
			if ($arrayPos >= count($contactArr)){
				$arrayPos=0;
			}
			
		}
	}
	//echo "<pre>";
	//print_r($contactArr);
	//echo "</pre>";
	//echo "ntAssignedTo: $ntAssignedTo<br />
	//Position: $arrayPos<br />
	//New Assigned To: ".$contactArr[$arrayPos]."<br />";
	return $contactArr[$arrayPos];
	
}


function updateInvoiceTotal($apIdTmp) {
	//Called when a new part is added or labour is added
	$db1 = new DB;

	if (is_array($apIdTmp)) {
		foreach ($apIdTmp as $xapId) {
			$apIdx = $xapId;
		}
	}
	//echo "ApId: $apIdx";
	$apIdTmp = $apIdx;
	if ($apIdTmp) {
		$apInvoiceTotalx = $db1->getval("SELECT apInvoiceTotal FROM appointments WHERE apId='$apIdTmp'","apInvoiceTotal");
	}
	if ($apInvoiceTotalx) {
		$apInvoiceDatex = $db1->getval("SELECT apInvoiceDate FROM appointments WHERE apId='$apIdTmp'","apInvoiceDate");
		$partsTotalx = $db1->getval("SELECT SUM(ptQty*ptPriceEach) as partsTotal FROM parts WHERE ptApId = '$apIdTmp' GROUP BY ptApId", "partsTotal");
		$labourTotalx = $db1->getval("SELECT SUM(lbQty*lbPriceEach) as labourTotal FROM labour WHERE lbApId = '$apIdTmp' GROUP BY lbApId", "labourTotal");
		$newInvTotal = $partsTotalx + $labourTotalx;
		//$db->query("INSERT INTO reissuedinvoice SET riApId ='$issueInvoice', riOriginalTotal='$apInvoiceTotalx', riOriginalDate='$apInvoiceDatex', riCnId='$xcnId', riRequestDate=CURRENT_DATE, riNewTotal='$newInvTotal'");
		//$db->query("UPDATE appointments SET apInvoiceRequired='1', apInvoiceDate='0000-00-00' WHERE apId='$issueInvoice'");
		$db1->query("UPDATE appointments SET apInvoiceTotal='$newInvTotal' WHERE apId='$apIdTmp'");
		//$url = "invoices.php";
	}
}

function get_driving_information($start, $finish, $raw = false) {
	// function from http://www.paul-norman.co.uk/2009/07/using-google-to-calculate-driving-distance-time-in-php/
    # Convert any lon / lat coordingates
    if(preg_match('@-?[0-9]+\.?[0-9]*\s*,\s*-?[0-9]+\.?[0-9]@', $start)){
        $url = 'http://maps.google.co.uk/m/local?q='.urlencode($start);
        if($data = file_get_contents($url)){
            if(preg_match('@<div class="cpfxql"><a href="[^"]*defaultloc=(.*?)&amp;ll=@smi', $data, $found)){
                $start = trim(urldecode($found[1]));
            }
            else {
                throw new Exception('Start lon / lat coord conversion failed');
            }
        } else {
            throw new Exception('Start lon / lat coord conversion failed');
        }
    }

    if(preg_match('@-?[0-9]+\.?[0-9]*\s*,\s*-?[0-9]+\.?[0-9]@', $finish)) {
        $url = 'http://maps.google.co.uk/m/local?q='.urlencode($finish);
        if($data = file_get_contents($url)) {
            if(preg_match('@<div class="cpfxql"><a href="[^"]*defaultloc=(.*?)&amp;ll=@smi', $data, $found)) {
                $finish = trim(urldecode($found[1]));
            } else {
                throw new Exception('Finish lon / lat coord conversion failed');
            }
        } else {
            throw new Exception('Finish lon / lat coord conversion failed');
        }
    }

    if(strcmp($start, $finish) == 0) {
        $time = 0;
        if($raw) {
            $time .= ' seconds';
        }

        return array('distance' => 0, 'time' => $time);
    }

    $start  = urlencode($start);
    $finish = urlencode($finish);

    $distance   = 'unknown';
    $time       = 'unknown';

    $url = 'http://maps.google.co.uk/m/directions?saddr='.$start.'&daddr='.$finish.'&hl=en&oi=nojs&dirflg=d';
    if($data = file_get_contents($url)) {
        //if(preg_match('@<span[^>]+>([^<]+) (mi|km)</span>@smi', $data, $found))
		if(preg_match('@([^<br/>]*) (mi|km) -@mi', $data, $found)) //customised by me
        {
            $distanceNum    = trim($found[1]);
            $distanceUnit   = trim($found[2]);

            if($raw) {
                $distance = $distanceNum.' '.$distanceUnit;
            } else {
                $distance = number_format($distanceNum, 2);
                if(strcmp($distanceUnit, 'km') == 0) {
                    $distance = $distanceNum / 1.609344;
                }
            }
        } else {
            throw new Exception('Could not find that route');
        }

        if(preg_match('@<b>([^<]*)</b>@smi', $data, $found)) {
            $timeRaw = trim($found[1]);

            if($raw) {
                $time = $timeRaw;
            } else {
                $time = 0;

                $parts = preg_split('@days?@i', $timeRaw);
                if(count($parts) > 1) {
                    $time += (86400 * $parts[0]);
                    $timeRaw = $parts[1];
                }

                $parts = preg_split('@hours?@i', $timeRaw);
                if(count($parts) > 1) {
                    $time += (3600 * $parts[0]);
                    $timeRaw = $parts[1];
                }

                $time += (60 * (int)$timeRaw);
            }
        }

        return array('distance' => $distance, 'time' => $time);
    }
    else
    {
        throw new Exception('Could not resolve URL');
    }
}

function getDistanceGoogle($postcode1, $postcode2, $switchGoogleOn=0){

	//$switchGoogleOn = '0'; //0 = use normal distance fucntion, 1 = use google API

	if ($switchGoogleOn=='1'){
		//Get driving distance using Google Maps API
		try
		{
			$info = get_driving_information($postcode1, $postcode2);
			return $info['distance'];
			//echo $info['distance'].' miles '.$info['time'].' seconds';
		}
		catch(Exception $e)
		{
			echo 'Caught exception: '.$e->getMessage()."\n";
		}
	} else {
		//echo "Using Normal Function";
		return getDistance($postcode1, $postcode2);
	}

	
}

function shuffle_assoc($list) { 
//Function to shuffle array but maintain key values
  if (!is_array($list)) return $list; 

  $keys = array_keys($list); 
  shuffle($keys); 
  $random = array(); 
  foreach ($keys as $key) { 
    $random[] = $list[$key]; 
  }
  return $random; 
} 


function getDistance($postcode1, $postcode2){
	require_once("../phpcoord/phpcoord-2.3.php");
	
	/*
	$dbpd = new DB;
	$postcode1 = trim(str_replace(' ','',$postcode1)); //remove blank spaces
	$postcode2 = trim(str_replace(' ','',$postcode2)); //remove blank spaces
	
	$dbpd->query("SELECT pdLat, pdLong FROM postcodesdetailed WHERE pdPostcode='$postcode1'");
	$dbpd->next_record();
	list ($lat1, $long1) = $dbpd->Record;
	$ll1 = new LatLng($lat1,$long1);
	
	$dbpd->query("SELECT pdLat, pdLong FROM postcodesdetailed WHERE pdPostcode='$postcode2'");
	$dbpd->next_record();
	list ($lat2, $long2) = $dbpd->Record;
	$ll2 = new LatLng($lat2,$long2);
	*/
	
	//This new method checks to see if the data is stored in the postcodesdetailed table
	//If not stored, get Long/Lat via Google API and INSERT into table.
	$longlat = new stdClass();
	$longlat = getLongLat($postcode1); 
	$ll1 = new LatLng($longlat->lat,$longlat->long);
	
	$longlat2 = new stdClass();
	$longlat2 = getLongLat($postcode2); 
	$ll2 = new LatLng($longlat2->lat,$longlat2->long);
	
	
	return $distance = $ll1->distance($ll2); //return distance in miles
}


function get_working_days($start_date, $end_date, $holidays = array()) 
//Pass dates in the following format: dd-mm-YYYY
{ 
    $start_ts = strtotime($start_date); 
    $end_ts = strtotime($end_date); 
    foreach ($holidays as & $holiday) { 
        $holiday = strtotime($holiday); 
    } 
    $working_days = 0; 
    $tmp_ts = $start_ts; 
    while ($tmp_ts <= $end_ts) { 
        $tmp_day = date('D', $tmp_ts); 
        if (!($tmp_day == 'Sun') && !($tmp_day == 'Sat') && !in_array($tmp_ts, $holidays)) { 
            $working_days++; 
        } 
        $tmp_ts = strtotime('+1 day', $tmp_ts); 
    } 
	//Returning an extra day (2 instead of 1) so if $working_days > 0, deduct one
	if ($working_days > 0) {
		$working_days--;
	}
    return $working_days; 
}

function add_cdata($string){
//Add CDATA tags, use when outputting XML which might contain dodgy characters
	$newstring = '<![CDATA['.$string.']]>';
	return $newstring;
}

function strip_cdata($string) { 
//Strip out CDATA tags, useful if reading in an XML feed that might contain these tags
    preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $string, $matches); 
    return str_replace($matches[0], $matches[1], $string); 
} 

function strrtrim($message, $strip) { 
	//Remove blank lines from a string of text
    // break message apart by strip string 
    $lines = explode($strip, $message); 
    $last  = ''; 
    // pop off empty strings at the end 
    do { 
        $last = array_pop($lines); 
    } while (empty($last) && (count($lines))); 
    // re-assemble what remains 
    return implode($strip, array_merge($lines, array($last))); 
} 

function customXmlClean($tmp) {
	//XML can't output certain characters so I need to do a string replace with an entity reference
	//Use this function for returning data to the Android app
		
	$new = str_replace('&','and',$tmp);
	$new = str_replace('<','&lt;',$new);
	$new = str_replace('>','&gt;',$new);
	$new = str_replace('"','',$new); //Remove speech marks
	$new = str_replace("'",'',$new); //Remove apostrophes
	//$new = str_replace("£",'&pound;',$new);
	$new = str_replace("£",'',$new);
	$new = str_replace("*",'',$new);
	$new = str_replace(array("\r", "\r\n", "\n"), ',', $new); //Remove new lines and replace with a comma
	$new = htmlspecialchars($new);
	$new = trim($new);
	$new = rtrim($new);
	
	if (!$new) $new = 0;
	return $new;
}

function xmlClean($tmp) {
//XML can't output certain characters so I need to do a string replace with an entity reference

$new = str_replace('&','&amp;',$tmp);
$new = str_replace('<','&lt;',$new);
$new = str_replace('>','&gt;',$new);
$new = str_replace('"','&quot;',$new);
$new = str_replace("'",'&apos;',$new);
$new = str_replace("£",'&pound;',$new);
$new = trim($new);

if (!$new) $new = 0;
return $new;
}



function getParentId($storeId){
	$db = new DB;
	
	$parentId=99999;
	
	$parentId = $db->getval("SELECT coParentId FROM companies WHERE coId = '$storeId'","coParentId");
	if ($parentId=='0') {
		return $storeId;
	} else if ($parentId < 99999){
		return $parentId;
	}	

}

function mround($number, $precision=0) { 
    //round function was returning the incorrect value so I'm using this instead
		$precision = ($precision == 0 ? 1 : $precision);    
		$pow = pow(10, $precision); 
		
		$ceil = ceil($number * $pow)/$pow; 
		$floor = floor($number * $pow)/$pow; 
		
		$pow = pow(10, $precision+1); 
		
		$diffCeil     = $pow*($ceil-$number); 
		$diffFloor     = $pow*($number-$floor)+($number < 0 ? -1 : 1); 
		
		if($diffCeil >= $diffFloor) return $floor; 
		else return $ceil; 
	} 


function flipdate($dt, $seperator_in = '-', $seperator_out = '/') {
return implode($seperator_out, array_reverse(explode($seperator_in, substr($dt,0,10))));
}
function statusSelect2($jbStatus, $name="jbStatus", $allowBlank=0, $limit=1)
{
	//exactly the same as the 'statusSelect' function but without the border & blue background
	global $statusArray;
	// check limit array
	$limitArray = $statusArray[$jbStatus][3];
	$ret = "<select style='border:1px solid #9b9b9b; background-color:#ffffff; height:25px' name='$name' id='$name'>";
	if ($allowBlank) $ret .= "<option></option>";
	foreach($statusArray as $key => $val)
	{
		if(!$limit || ($limit && ( !is_array($limitArray) || ( is_array($limitArray) && ($jbStatus == $key || in_array($key, $limitArray))))))
		{
			$ret .= "<option value='$key'";
			if ($jbStatus == $key) $ret .= "selected";
			$ret .= ">$val[0]</option>";
		}
	}
	$ret .= "</select>";
	echo $ret;
}

function statusSelect($jbStatus, $name="jbStatus", $allowBlank=0, $limit=1)
{
	global $statusArray;
	// check limit array
	$limitArray = $statusArray[$jbStatus][3];
	$ret = "<select name='$name' id='$name'>";
	if ($allowBlank) $ret .= "<option></option>";
	foreach($statusArray as $key => $val)
	{
		if(!$limit || ($limit && ( !is_array($limitArray) || ( is_array($limitArray) && ($jbStatus == $key || in_array($key, $limitArray))))))
		{
			$ret .= "<option value='$key'";
			if ($jbStatus == $key) $ret .= "selected";
			$ret .= ">$val[0]</option>";
		}
	}
	$ret .= "</select>";
	echo $ret;
}
function leadingZero($x){
	if(strlen($x) < 2){
   		if($x < 10){
   			$x = "0".$x;
   		}
   	}
   return $x;   
}

function getVAT($date='',$rate=0,$perc=0){
	if(!$date) $date = date("Y-m-d");
	if(($date >= '2008-12-01') && ($date <= '2010-01-01')){ //15% VAT
		$vatrate = 0.15;
	} else if(($date >= '2011-01-03') && ($date <= '2017-12-01')){ //20% VAT 4/01/2011 onwards, Louise asked to put this from 3/1/2011 
																   //as 3rd is a Monday & it'll keep things consistent
		$vatrate = 0.2;
	} else {
		$vatrate = 0.175; //17.5%
	}
	if($rate) $vatrate = 1+$vatrate;
	if($perc) $vatrate = 100 * $vatrate;	
	return $vatrate;
}
/*
function getVAT($date='',$rate=0,$perc=0){
	if(!$date) $date = date("Y-m-d");
	if(($date >= '2008-12-01') && ($date <= '2010-01-01')){
		$vatrate = 0.15;
	} else {
		$vatrate = 0.175;
	}
	if($rate) $vatrate = 1+$vatrate;
	if($perc) $vatrate = 100 * $vatrate;	
	return $vatrate;
}
*/
?>