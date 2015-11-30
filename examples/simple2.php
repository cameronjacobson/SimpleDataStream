<?php

// SimpleDataStream is not opinionated.
//  whatever you pass into stream you just need to handle appropriately inside callbacks


require_once(dirname(__DIR__).'/vendor/autoload.php');

use SimpleDataStream\SimpleDataStream as SDS;

$ds = new SDS();
$ds->map(function(&$v){
		if(is_array($v)){
			$v[0] *= 2;
		}
		else{
			$v *= 2;
		}
	})
	->filter(function($v){
		if(is_array($v)){
			return $v[0] < 10 && $v[0] > 2;
		}
		else{
			return $v < 10 && $v > 2;
		}
	});

for($x=0;$x<10;$x++){
	$ds->put($x);
	$ds->put(array($x));
}

// more efficient way to iterate simple stream
while($v = $ds()){
	process($v);
}

function process($v){
	if(is_array($v)){
		print_r($v);
	}
	else{
		echo $v.PHP_EOL;
	}
}
