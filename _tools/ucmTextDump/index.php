<?php
	error_reporting(E_ALL ^ E_NOTICE);
	
	$names = scanDir('./UCM');
	unset($names[array_search('.',$names)]);
	unset($names[array_search('..',$names)]);
	
	//$names = array_slice($names,0,1);
	
	foreach($names as $nv)
	{
		set_time_limit(30);
		$nazwa = './UCM/'.$nv;
		
		echo '<p><h2>'.$nv.'</h2></p>';
		$handle = fopen($nazwa, 'r');
		for($i = 268; $i <= 1048; $i+=260)
		{
			fseek($handle, $i);
			$dump = fread($handle, 100);
			echo '<p>'.$dump.'</p>';
			if($i == 528)
			{
				$dupa2 = trim($dump);
				$ile = $dupa[$dupa2];
				$dupa[$dupa2][count($ile)] = $nv;
			}
		}
		echo '<p>textdump: ';
		fseek($handle, 39466);
		$dump = fread($handle, 8000);
		echo $dump.'</p>';
		//var_dump(explode("\0",$dump));
		fclose($handle);
	}
	//var_dump($nazwy);
	var_dump($dupa);
	
?>