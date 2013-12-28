<?
# track.php
# By: john.b.hale@gmail.com
#
# Php script to track packages shipped via USPS and use a file filled with tracking numbers so you can mass track your shipments
# This was purely hacked for my own use. But, You may submit, pull requests and other things if you can make it better. And, I shall accept them as long as it works.
# The format of the trackingnumebrs.txt is simple. You just enter a tracking number and hit enter, and enter the next and so on.
# Date: Dec 15, 2013

// parts taken from php.net
function get_data($num,$carrier)
{



	if ($carrier == 'USPS')
	{
		#echo "DEBUG: we are tracking USPS\n";
		$fields = array(
			'tRef'=>'qt',
			'tLc' => '1',
			'tLabels'=> $num);
	}
	elseif ($carrier == 'CAN')
	{
		#echo "DEBUG: we are tracking CANADA\n";
		 $fields = array(
			'trackingNumber'=> $num,
        	       	'x' => '46',
        	      	'y'=> '9');
	}
	
	if ($carrier == 'USPS')
        	$first_url = "https://tools.usps.com/go/TrackConfirmAction!input.action";
	if ($carrier == 'CAN')
		$first_url = "http://www.canadapost.ca/cpotools/apps/track/personal/findByTrackNumber";

	
	$fields_string = http_build_query($fields);
	


   
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$first_url);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'text/plain'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_REFERER, $first_url);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE,$cookie);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
	curl_setopt($ch, CURLOPT_USERAGENT, USERAGENT);
        $html = curl_exec($ch);
        return $html;

}





$file = "trackingnumbers.txt";
$tracks = file_get_contents($file);

$track = explode("\n", $tracks);

for ($i=0;$i<count($track);$i++)  
{
    	$num = $track[$i];
	if (strstr($num,"LM"))
	{
		$carrier = "CAN";
	} else {
		$carrier = "USPS";
	}
			
	$data = get_data($num,$carrier);
	sleep(3);
	if($carrier == 'USPS') goto USPS; else goto CAN;


	USPS:
	if (strstr($data,"Delivered"))
	{
		echo $num . " " . "Has been Delivered\n";
	}
	// This is hack but too lazy to do it proper. Feel free to fix :)
	elseif (strstr($data,"Processed through USPS Sort Facility"))
        {
                echo $num . " " . "Being processed in the USA\n";
        }
	// Means it has not left the country/or still being processed
	elseif (strstr($data,"Origin Post is Preparing Shipment"))
	{
		echo $num . " " . "Is still located at shipping location\n";
	} 
	elseif (strstr($data,"double-check it"))
	{
		echo $num . " " . "Is an invalid tracking number or has not been scanned yet\n";
	}


	CAN:
	if (strstr($data,"Item has arrived in Canada and was sent for further processing."))
        {
                echo $num . " " . "Processing in Canada\n";
        }
	elseif (strstr($data,"International item has left originating country and is en route to Canada"))
        {
                echo $num . " " . "en route to Canada\n";
        }
	elseif (strstr($data,"International item mailed in originating country"))
        {
                echo $num . " " . "Is still located at shipping location\n";
        }


}	


?>
