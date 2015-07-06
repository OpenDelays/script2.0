<?php

function _v($val)
	{
	$val = str_replace("\'","'",$val);
	$val = str_replace("'","\'",$val);
	return urldecode($val);
	} 
	
function  pdidb($l_Data,$nbf) //put data in database
{   
$q=0;
echo $nbf." - totale query <br><br>";
include 'connect.php';
    for ($m = 0; $m < $nbf; $m++)
	    {
		$l_Data[0][$m] = _v($l_Data[0][$m]);
		$l_Data[1][$m] = _v($l_Data[1][$m]);
		$l_Data[2][$m] = _v($l_Data[2][$m]);
		$l_Data[3][$m] = _v($l_Data[3][$m]);
		$l_Data[4][$m] = _v($l_Data[4][$m]);
		$l_Data[5][$m] = _v($l_Data[5][$m]);
		
        $query1 = "INSERT INTO opendelays_it(from_station, plan_time_arr, delay, current_station, id_train, to_station) VALUES";
        $query2="('".$l_Data[0][$m]."','".$l_Data[2][$m]."','".$l_Data[3][$m]."','".$l_Data[1][$m]."','".$l_Data[4][$m]."','".$l_Data[5][$m]."')";
        $query=$query1.$query2;
		echo $m." - ".$query."<br>";
        if (($l_Data[3][$m] > -1000) AND  ($l_Data[3][$m] < 1000)) 
			{
			echo "QUERY EFFETTIVE - $q<br>";$q++;
			mysql_query($query) or  die ("ERRORE : ".mysql_error());
			}
		}
}
function pTime()
	{
	
	$ptime = date("H:i:s");
	return $ptime;
	}

	
	
function timeStampToDelay($ts,$ts2)
		{		
        $delay=($ts2/1000-$ts/1000)/60; // divide for millisecond then second to obtain minutes of delays
        return $delay;
		}
		
		
function  timeStampToDate($ts)
{
        if ($ts <  1388530800000) return '2000-01-01 00:00:00' ;
        $ts=$ts/1000;
		$date=date("Y-m-d H:i:s",$ts);
        return $date;
       
}		


function verifTimeStamp($ts)

		{
		
        if ($ts <  1388530800000) return 0;
        $ts= $ts/1000;
		
        $stime= gmdate("Y-m-d H");
		$date = date("Y-m-d H",$ts);
		 if ($date == $stime) $con =1; else $con =0;
        echo "$ts $date - $stime == $con<br>";
        if ($date == $stime) return 1;
        else return 0;
		}

		
		
$train_id   = array(); 
$l_stations = array();		

function getStations()
{   
    global $train_id; 
    global $l_stations;
	$station="";
	
	
    $lines = file("project/stations_it.txt");
    echo "NUMERO LINEE FILE STAZIONI ".count($lines)." - ULTIMA STAZIONE".$lines[count($lines)-4]."<br>";
	
	$counter=0;
	for ($i = 0; $i < count($lines); $i++)
	{
	if (trim($lines[$i])!="")
		{
		$counter++;
		$lines[$i]=str_replace("\n","",$lines[$i]);
		$parte_destra = explode("|",$lines[$i]);
		$parte_destra = $parte_destra[1];
		$info =explode("-",$parte_destra);
		array_push($train_id,$info[0]);
		array_push($l_stations,$info[1]."/".$info[0]);
		echo $counter." -- ".$lines[$i]."<br>";
		
       
		}

	}
	echo "NUMERO REALE STAZIONI ".$counter." - ULTIMA STAZIONE".$l_stations[count($l_stations)-1]."----<br>";
	

}

function get_data($url) {
	$ch = curl_init();
	$timeout = 5;
	
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	
	return $data;
}


function  openUrl($station)
{
    //open website page
    //http://www.viaggiatreno.it/viaggiatrenonew/resteasy/viaggiatreno/tratteCanvas/N00001/13
    $url = "http://www.viaggiatreno.it/viaggiatrenonew/resteasy/viaggiatreno/tratteCanvas/".$station;
	echo $url."<br>";
    $website= get_data($url);
	
    //print u
    return $website;
}	






	



function getData($l_stations)
{ 
    $nb_t=0;
	global $train_id;
    
	$nb_res=0;$ts=0; 
	
	$l_data=array(array(),array(),array(),array(),array(),array());
    

	for ($i = 0; $i < count($l_stations); $i++)
	{
    echo  "$i ";
    $data=openUrl($l_stations[$i]);
    $obj = json_decode($data,true);
    $nb_res = count($obj);


	
	  
        if ($nb_res != 0)
            if ($obj[$nb_res-1]["fermata"]["programmata"]!==null)
                if (verifTimeStamp($obj[$nb_res-1]["fermata"]["programmata"])== 1) //controllo che non ripete valori già presi ATTIVA DOPO
					{
					
					for ($j = 0; $j < $nb_res; $j++)
					{
					if (($obj[$j]["fermata"]["programmata"]!==null) AND ($obj[$j]["fermata"]["effettiva"]!==null)) 
						{
						$ts = $obj[$j]["fermata"]["effettiva"];
						$ts_pro = $obj[$j]["fermata"]["programmata"]; 
						
						
				
						$nb_t++;
						
						
						array_push($l_data[0],$obj[0]["stazione"]);
						array_push($l_data[1],$obj[$j]["stazione"]);
						array_push($l_data[2],timeStampToDate( $ts_pro));
						array_push($l_data[3],timeStampToDelay($ts_pro,$ts));
						array_push($l_data[4],$train_id[$i]);
						array_push($l_data[5],$obj[count($obj)-1]["stazione"]);
						
						$x = count ($l_data[0])-1;
						echo "<span style='color:$colore;font-family:arial'>";
						echo "$nb_t - ";
							echo $l_data[0][$x];echo " ";
							echo $l_data[1][$x];echo " ";
							echo $l_data[2][$x];echo " ";
							echo $l_data[3][$x];echo " ";
							echo $l_data[4][$x];echo " ";
							echo $l_data[5][$x];echo "<br>";
						echo "</span>";
						}
					}
				}
	echo "<br><br>";
	}
    pdidb($l_data,$nb_t);
}


getStations();
getData($l_stations);
   
   
		
?>

