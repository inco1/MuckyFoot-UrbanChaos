<?php

	$txc = "C:\\PROGRAMY\\wamp\\www\\uc_toolz\\out\\txc\\police1\\";
	$filename = "disturb1.iam";
	$map = imageCreateFromPNG($filename."0.png");
//	$Rmap = imageCreateFromPNG($filename."1.png");
	
	$x1 = 0;
	$y1 = 0;
	$width = 30;
	$height = 30;
	//max 63x62
	
//	$x = $x2 - $x1;
//	$y = $y2 - $y1;
	
	$im = imageCreateTrueColor($width*64, $height*64);
	
	 for($i = 0; $i < $height; $i++)
	 {
		 for($j = 0; $j < $width; $j++)
		{
		//	====== pobranie tekstury i modyfikatora
			$px = imageColorAt($map, $x1+$j, $y1+$i);
//			$rt = imageColorAt($Rmap, $x1+$j, $y1+$i);
//			$r = ($rgb >> 16) & 0xFF;
//			$g = ($rgb >> 8) & 0xFF;
			$c = $px & 0xFF;
//			$r = $rt & 0xFF;
//			$r = substr(dechex($r), -1);
//			$r = hexdec($r);
//			var_dump($r, $g, $b);
//			imageSetPixel($im, $j*64, $i*64, imageColorAllocate($im, $c, $c, $c));
//			$c = 72;

		//	==== obliczanie modyfikatora
/* 			$gMod = $r / 4;
			if(getType($gMod) == "double")
			{
				$gMod = floor($gMod);
			}
			$reszta = $r - $gMod*4;
			$reszta--; */
//			$rMod = $r / 

		//	======== określenie grupy i coordów
			$groups = $c/64;
			if(getType($groups) == "double")
			{
				$groups = floor($groups);
			}
			//$groups += $reszta;
			$uv = $c - ($groups*64);
			$uvY = $uv/8;
			if(getType($uvY) == "double")
			{
				$uvY = floor($uvY);
			}
			$uvX = $uv - ($uvY*8);
			$groups += 4;
			$name = $groups."_U".$uvX."_V".$uvY.".png";
			var_dump($c, $groups, $uv, $uvY, $uvX, $name, $r);

		//	====== rysowanie
			$brush = imageCreateFromPNG($txc.$name);
			imageSetBrush($im, $brush);
			imageSetPixel($im, ($j*64)+32, ($i*64)+32, IMG_COLOR_BRUSHED);
			imageDestroy($brush);
		}
		
	}
	imageDestroy($map);
	imagePNG($im, "out".$filename.".png");
	imageDestroy($im);
	imageDestroy($Rmap);
	

?>