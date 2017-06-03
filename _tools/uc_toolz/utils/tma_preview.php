<?php
	
	//require("../dirs.php");
	$name = "style.tma";
	$world = "world9";
	$texturePack = "factory1";
	$sciezka = "C:\\PROGRAMY\\wamp\\www\\uc_toolz\\tma\\".$world."\\".$name;
	$tma = fopen($sciezka,'r');
	fseek($tma, 0x8);
	
	for($i = 0; $i < 200; $i++)
	{
		set_time_limit(30);
		$style = imageCreateTrueColor(1280,330);
		$pozycja = ftell($tma);
		$chunk = fread($tma, 0x14);
		for($j = 0; $j < 5; $j++)
		{
			$grupa = ord($chunk[$j*4]);
			$uvX = ord($chunk[$j*4+1]);
			$uvY = ord($chunk[$j*4+2]);
			$mirrored = ord($chunk[$j*4+3]);
//			echo $j." [".$pozycja."]) ".$grupa, $uvX, $uvY."<br />";
			$nazwa = $grupa."_U".$uvX."_V".$uvY.".png";
			$sciezka = "../out/txc/".$texturePack."/".$nazwa;
			$brush = imageCreateFromPNG($sciezka);
			imageSetBrush($style, $brush);
			$x = $j*256+128;
			$y = 128;
			imageLine($style, $x, $y, $x, $y, IMG_COLOR_BRUSHED);
			$textColor = imageColorAllocate($style, 255, 255, 255);
			imageString($style, 5, $x-30, 274, $nazwa, $textColor);
			imageString($style, 5, $x-30, 290, "0x".strToUpper(dechex($pozycja+$j*4)), $textColor);
			if($mirrored == 1)
			{
				imageString($style, 5, $x-60, 274, "[M]", $textColor);
			}
		}
		imageString($style, 4, 5, 269, $texturePack, $textColor);
		imageString($style, 4, 5, 280, $world, $textColor);
		imageString($style, 4, 5, 291, "#".$i, $textColor);
		imageString($style, 5, 98, 305, "left", $textColor);
		imageString($style, 5, 354, 305, "normal", $textColor);
		imageString($style, 5, 610, 305, "right", $textColor);
		imageString($style, 5, 866, 305, "alt1", $textColor);
		imageString($style, 5, 1122, 305, "alt2", $textColor);
		imagePNG($style, "../out/tma/".$world."/".$i.".png");
	}
	fclose($tma);
	imagedestroy($style);
	imagedestroy($brush);
	
?>