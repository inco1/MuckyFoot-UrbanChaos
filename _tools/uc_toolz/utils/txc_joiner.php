<?php
	
	require("../dirs.php");
	$filename = "mib";
	$dir = "../".$outDir."txc/".$filename."/";
	//echo $dir;
	$files = scanDir($dir);
	unset($files[array_search('.',$files)]);
	unset($files[array_search('..',$files)]);
	unset($files[array_search('Thumbs.db',$files)]);
	
	$licznik = 0;
	foreach($files as $plik)
	{
		if(strlen(substr($plik, 0, -4)) > 8)
		{
			unset($files[$licznik]);
		}
		$licznik++;
	}
	
	$gLimit = count($files)/64;
	if(getType($gLimit) == "double")
	{
		$gLimit = floor($gLimit);
	}
	for($i = 0; $i <= $gLimit; $i++)
	{
		$paleta = imageCreateTrueColor(2048, 2048);
		$white = imageColorAllocate($paleta, 255, 255, 255);
		for($j = 0; $j <= 7; $j++)
		{
			for($k = 0; $k <= 7; $k++)
			{
				$nazwa = $i."_U".$j."_V".$k.".png";
				$name = $dir.$nazwa;
				if(file_exists($name))
				{
					set_time_limit(30);
					$x = $j*256+128;
					$y = $k*256+128;
					$brush = imageCreateFromPNG($name);
					imageSetBrush($paleta, $brush);
					imageline($paleta, $x, $y, $x, $y, IMG_COLOR_BRUSHED);
				}
			}
		}
		imagePNG($paleta, "../".$groupsOutDir."/".$filename."/".$i.".png");
		imagedestroy($paleta);
		imagedestroy($brush);
	}
	
	
?>