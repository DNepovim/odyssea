<?php


function iriGoogleChart($title,$size,$data_array) {
	if(empty($data_array)) { return ''; }
	// get hash
	foreach($data_array as $key => $value ) {
		$values[] = $value;
		$labels[] = $key;
	}
	$maxValue=max($values);
	$simpleEncoding='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$chartData="s:";
	for($i=0;$i<count($values);$i++) {
		$currentValue=$values[$i];
		if($currentValue>-1) {
			$chartData.=substr($simpleEncoding,61*($currentValue/$maxValue),1);
		} else {
			$chartData.='_';
		}
	}
	$data=$chartData."&chxt=y&chxl=0:|0|".$maxValue;
	return "<img src=http://chart.apis.google.com/chart?chtt=".urlencode($title)."&cht=p3&chs=$size&chd=".$data."&chl=".urlencode(implode("|",$labels)).">";
}

function iriGoogleGeo($title,$size,$data_array) {
	if(empty($data_array)) { return ''; }
	// get hash
	foreach($data_array as $key => $value ) {
		$values[] = $value;
		$labels[] = $key;
	}
	return "<img src=http://chart.apis.google.com/chart?chtt=".urlencode($title)."&cht=t&chtm=world&chs=440x220&chco=eeeeee,FFffcc,cc3300&chd=t:0,".(implode(",",$values))."&chld=XX".(implode("",$labels)).">";
}

?>
