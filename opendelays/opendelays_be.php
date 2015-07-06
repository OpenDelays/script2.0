<?php
header('Content-Type: text/html; charset=utf-8');

   
function _v($val)
	{
	$val = str_replace("\'","'",$val);
	$val = str_replace("'","\'",$val);
	return $val;
	} 
	
function pdidb($l_data,$nbf) //put data in database
	{
	global $day;
	include 'connect.php';
	echo "QUERY TOTALI $nbf<br><br>";
     for ($m = 0; $m < $nbf; $m++)
		{
		$query1 = "
			INSERT INTO opendelays_be (datetime, arrivalStation, delay, `type-num`,`day` ) VALUES";
	    $l_data[0][$m] = _v($l_data[0][$m]);
		$l_data[1][$m] = _v($l_data[1][$m]);
		$l_data[2][$m] = _v($l_data[2][$m]);
		$l_data[3][$m] = _v($l_data[3][$m]);
		$query2="('".$l_data[0][$m]."','".$l_data[1][$m]."','".$l_data[2][$m]."','".$l_data[3][$m]."','".$day."')";
		
		
		$query=$query1.$query2;
		echo "$m -".$query."<br>";
        mysql_query($query) or die ("ERRORE : ".mysql_error());
		}
	}

		
		
	function get_data($url) {
	$ch = curl_init();
	$timeout = 120;

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	
	return $data;
}


function  openUrl($station,$date,$time)
{

    $u1 = 'http://api.irail.be/liveboard/?station='.urlencode($station);
    $u2 = '&fast=true&date='.$date;
    $u3 = '&time='.$time.'&arrdep=arr';
    $url=$u1.$u2.$u3;
    echo $url."<br><br>";

    $website= get_data($url);
    
    return $website;
}	




function getStations()
{
    global $l_stations;
    $l_stations=array();
    $nbCh=0;
    $url="http://api.irail.be/stations/";
    $stationsF=get_data($url);
    $sizef=strlen($stationsF);
    $station="";
	for ($i = 0; $i < $sizef; $i++)
    {
	
	
        if ((substr($stationsF,$i,1) == chr(62)) or (substr($stationsF,$i,1) == chr(60))) 
		
		{
		$nbCh=$nbCh+1;
		if (strlen($station) > 3)
			{
			
			for ($j = 0; $j < strlen($station); $j++) if (substr($station,$j,1)=="/") {$station=substr($station,$j+1);};
			$station = mb_convert_encoding($station, "ISO-8859-1", "auto"); 
			echo $station."<br>";
			array_push($l_stations,$station);
			
			}
			
		$station='';
		}
            
        if (($nbCh % 2 == 0) and ($nbCh > 2) and (substr($stationsF,$i,1) != chr(62))) {$station=$station.substr($stationsF,$i,1);};
	}
    return $l_stations;
}
	
function giorno_prima($gg,$mm,$aaaa)
{
  return date('mdy', mktime(0,0,0,$mm,$gg-1,$aaaa));
}
	
$day =0; // giorno della settimana
function getData($l_stations)
{
    $l_data=array(array(),array(),array(),array()); $nb=0;
        $localtime =  date('H');
	
    $stime = $localtime-1;
	if ($stime==-1) $stime = 23;
    echo "stime = $stime<br>";
	
	
    $stime = $stime."00";
    if (strlen($stime)==3)$stime="0".$stime;
    $dbtime = $localtime.":00";
    
	
	$sdate1 = date("Y/m/d");
	global $day;
	$day = date("w");
    if ($localtime=="00") {$parti = explode("/",$sdate1); $sdate = giorno_prima($parti[2],$parti[1],$parti[0]);if ($day!=0)$day--;else $day=6;}
    else $sdate=date('mdy');
    for ($i = 0; $i < count($l_stations); $i++)
	//for ($i = 0; $i < 20; $i++)
	    {
		
        $data=openUrl($l_stations[$i],$sdate,$stime); 
		
		$xml = simplexml_load_string($data);
		
				foreach($xml->arrivals->arrival as $dep)
			{                     
           	
			$dbdate  = date("Y-m-d H:i:s",(int)$dep->time); 
			$delay   = $dep['delay'];
			$vehicle = str_replace("BE.NMBS.","",$dep->vehicle);
			//if ((substr($dbdate,-8,2))== substr($stime,0,2)) $con =1; else $con =0;
            //echo substr($dbdate,-8,2)." - ".substr($stime,0,2)." $con <br>";
						if ((substr($dbdate,-8,2))== substr($stime,0,2)) // se coincide l'ora disattivo per TEST!!!!!!
						{
					    $nb++;
						echo substr($dbdate,-8,2)."==".substr($stime,0,2);
						array_push($l_data[0],$dbdate);
						array_push($l_data[1],$l_stations[$i]);
						array_push($l_data[2],$delay);
						array_push($l_data[3],$vehicle);
					   
					   echo "$nb-ARRIVAL".$dbdate;
					   echo " ";
					   echo $l_stations[$i];
					   echo " ";
					   echo $delay;
					   echo " ";
					   echo $vehicle;
					   echo "<br>";
					   }		   
			}
			
		foreach($xml->departures->departure as $dep)
			{                     
            
			$dbdate  = date("Y-m-d H:i:s",(int)$dep->time); 
			$delay   = $dep['delay'];
			$vehicle = str_replace("BE.NMBS.","",$dep->vehicle);

						if ((substr($dbdate,-8,2))== substr($stime,0,2)) // se coincide l'ora disattivo per TEST!!!!!!
						{
						echo substr($dbdate,-8,2)."==".substr($stime,0,2);
						$nb++;
						array_push($l_data[0],$dbdate);
						array_push($l_data[1],$l_stations[$i]);
						array_push($l_data[2],$delay);
						array_push($l_data[3],$vehicle);
					   
					   echo "$nb-DEPARTURE".$dbdate;
					   echo " ";
					   echo $l_stations[$i];
					   echo " ";
					   echo $delay;
					   echo " ";
					   echo $vehicle;
					   echo "<br>";
					   }		   
			}
		}
pdidb($l_data,$nb);
}





$l_stations = array();
getStations(); 
getData($l_stations);









##http://api.irail.be/vehicle/?id=BE.NMBS.IC2340&fast=true


?>