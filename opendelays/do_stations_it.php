<?php




function get_data($url) {
	$ch = curl_init();
	$timeout = 100;
	
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	
	return $data;
}




    $counter=0;$res="";
	echo date("H:m:s");
	for ($i = 0; $i < 40000; $i++)
	{
	echo "$i<br>";
    $url="http://www.viaggiatreno.it/viaggiatrenonew/resteasy/viaggiatreno/cercaNumeroTrenoTrenoAutocomplete/".$i;
    //$s = get_data($url);
	$s = file_get_contents($url);
	if (trim($s)!="") {$counter++;$res= $res.$s."\n"; echo "$counter -$s<br>";}
	}
	
	$var=fopen("project/stations_it.txt","w");
	fwrite($var, $res);
	fclose($var);
?>