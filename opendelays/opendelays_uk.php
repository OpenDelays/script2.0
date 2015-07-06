<?php

function _v($val)
	{
	$val = str_replace("\'","'",$val);
	$val = str_replace("'","\'",$val);
	return $val;
	} 

function  pdidb($l_Data,$nbf,$stime) //put data in database
{   
$q=0;
include 'connect.php';
echo "$nbf numero query<br><br>";
    for ($m = 0; $m < $nbf; $m++)
	    {
		
		$l_Data[0][$m] = _v($l_Data[0][$m]);
		$l_Data[1][$m] = _v($l_Data[1][$m]);
		$l_Data[2][$m] = _v($l_Data[2][$m]);
		$l_Data[3][$m] = _v($l_Data[3][$m]);
		$l_Data[4][$m] = _v($l_Data[4][$m]);
		$l_Data[5][$m] = _v($l_Data[5][$m]);
		$l_Data[6][$m] = _v($l_Data[6][$m]);
		$l_Data[7][$m] = _v($l_Data[7][$m]);
		$l_Data[8][$m] = _v($l_Data[8][$m]);
		$l_Data[9][$m] = _v($l_Data[9][$m]);
		$l_Data[10][$m] = _v($l_Data[10][$m]);
		
        $query1 = "
            INSERT INTO opendelays_uk
            (`date`, `current_station`, `plan_time_arr`, `real_time_arr`, `from_station`, `pl`, `id_train`, `toc`, `to_station`, `plan_time_dep`, `real_time_dep`)
            VALUES";
        $query2="('".$l_Data[0][$m]."','".$l_Data[10][$m]."','".$l_Data[1][$m]."','".$l_Data[2][$m]."','".$l_Data[3][$m]."','".$l_Data[4][$m]."','".$l_Data[5][$m]."','".$l_Data[6][$m]."','".$l_Data[7][$m]."','".$l_Data[8][$m]."','".$l_Data[9][$m]."')";
        
		
	      $query=$query1.$query2;
		  echo "$m - $query<br>";
		  
		  	if (strlen($l_Data[6][$m]) > 1)
			    {
				if (strlen($l_Data[1][$m]) > 3)
					{
					$h=$l_Data[1][$m];
					//echo $stime."-".$h."<br>";
					if (substr($h,0,2) == substr($stime,0,2)) {mysql_query($query) or  die ("ERRORE : ".mysql_error()); echo "REALE $q <br>";$q++; }
					//else echo "no!<br>";
					}
				else {mysql_query($query) or  die ("ERRORE : ".mysql_error());echo "REALE $q<br>";$q++; }
				}
     
		}
		
}
			
			
function no_none($l_Data,$nbf)
{
    	for ($m = 0; $m < $nbf; $m++)
        for ($n = 1; $n < 11; $n++)
            if ($l_Data[$n][$m] === "none") $l_Data[$n][$m]="";
			if ($l_Data[$n][$m] === "null") $l_Data[$n][$m]="";
            $h=$l_Data[$n][$m];
			
			
            if (($n==1 OR $n==2 OR $n==8 OR $n==9) AND (strlen($h)> 3) AND ($h != "pass") AND ($h != "Canc") AND   (substr($h,2,1)!=":"))
				{
                $h=substr($h,0,2).":".substr($h,2,2);
                $l_Data[$n][$m]=$h;
				}
    return $l_Data;
}

function giorno_prima($gg,$mm,$aaaa)
{
  return date('Y/m/d', mktime(0,0,0,$mm,$gg-1,$aaaa));
}


function getData($l_stations)
{
	//structure
	$l_data=array(array(),array(),array(),array(),array(),array(),array(),array(),array(),array(),array());$nb_res=0; $nb_commit=0;
	$td = array();
	//prepare time for url call -2H actual time
    $localtime =  date('H');
	
    $stime = $localtime- 2;
	if ($stime==-1) $stime = 23;
	if ($stime==-2) $stime = 22;
	
    if (($stime) < 10) $stime="0".$stime."10";
    else $stime=$stime."10";
	

	//prepare date for url call
    $sdate = date("Y/m/d");
    if ($localtime=="00") {$parti = explode("/",$sdate); $sdate = giorno_prima($parti[2],$parti[1],$parti[0]);}
    $dbdate = date('Y-m-d');
	
	
	//make the url call for datas
	
	for ($i = 0; $i < count($l_stations); $i++) //UFFICIAL
    //for ($i = 0; $i < 10; $i++) // TEST
    { 
	//set_time_limit (5);
	echo "$i/".count($l_stations)." - ";
        $data=openUrl($l_stations[$i],$sdate,$stime);
		
        
		//echo "getstazione:$stazione<br><br>";
        if (strlen($data) > 100) //if we got the page
		{
        $data2=search($data,"/thead","<footer"); //isolate the block of info
        $stazione = getStation($data);
		
		//echo $data2;
		$data2 = str_replace('<tr class="wtt call_public">',"",$data2);
		$data2 = str_replace('<tr class="wtt nonpassenger pass">',"",$data2); //RINFORZARE IL PARSING QUALCHE NUOVA CLASS METTE IN CRISI
		
		$data2 = str_replace('<span>',"",$data2);
		$data2 = str_replace('</span>',"",$data2);
	
		$tr =  explode ("</tr>",$data2);
		
		$nb_res = count($tr)-1;
	    $nb_commit = $nb_commit + $nb_res; //count the sum of lines for all calls
		echo "NUMERO RIGHE =".$nb_commit."<br>";
		
		for ($k = 0; $k < count($tr); $k++) $td[$k] = explode("</td>",$tr[$k]);
		
	
		
		for ($j = 0; $j < count($tr)-1; $j++)
			{
			//array_pop($td[$j]);
			
			//for ($k = 0; $k < count($td[$j]); $k++) //produce rubbish at end
			for ($k = 1; $k < 10; $k++) 
				{
				
				
				$val = strip_tags($td[$j][$k]);
				array_push($l_data[$k],$val);
				
				//echo $val."|";
				}
			    //echo "<br>";
			array_push($l_data[10],$stazione);
			array_push($l_data[0],$dbdate);
			
			}
		
			echo "<br>";
		}
		
		
	}
	
    no_none($l_data,$nb_commit);
    pdidb($l_data,$nb_commit,$stime);
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


function  openUrl($station,$date,$time)
{

    $url = "http://www.realtimetrains.co.uk/search/advanced/".$station.'/'.$date.'/'.$time;
	echo $url."<br>";
    $website= get_data($url);
    return $website;
}






$l_stations = array();		

function getStations()
{    global $l_stations;
	$station="";
	
    $lines = file("project/stations_uk.txt");

	for ($i = 0; $i < count($lines); $i++)
	{
	if (trim($lines[$i])!="")
		{
		$lines[$i]=trim(str_replace("\n","",$lines[$i]));
		array_push($l_stations,$lines[$i]);
		
		}
	}
}


	
	

function getStation($data)
    {
	$station="";
    $station=search($data,"<title>","</title>");
	$station=$station."<";
    $station=search($station,"from ","<");
    return $station;
	}
	
	

function search($str,$word1, $word2)
	{  
	preg_match('~'.preg_quote($word1).'(.*?)'.preg_quote($word2).'~is', $str, $match);
	$res = isset($match[1]) ? $match[1] : "";
	return  $res;
	}
	




getStations();
getData($l_stations);



//http://www.realtimetrains.co.uk/search/advanced/ABW/2015/06/18/1200
//$data =  openUrl("ABW","2015/06/18","1100");

?>