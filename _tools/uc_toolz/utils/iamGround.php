<?php
//	require("../dirs.php");
	$txc = "police1";
	$txcN = "../out/txc/".$txc."/";
	$iam = "disturb1";
	$iamN = "../iam/".$iam.".iam";
	
//	$x1 = 0;
//	$y1 = 0;
	$width = 32;
	$height = 32;	
	//max 63x62
	
	$map = fopen($iamN, 'r');
	$header = 0x08;

	for($x1 = 0; $x1 < 128; $x1 += 32)
	{
		for($y1 = 0; $y1 < 128; $y1 += 32)
		{
			$im = imageCreateTrueColor($width*64, $height*64);
			set_time_limit(30);
			for($i = 0; $i < $height; $i++) //y
			{	
				for($j = 0; $j < $width; $j++) //x
				{	
				//	czytanie tekstury i moda
					$offset = $header + ($y1*6*128) + ($x1*6) + ($i*6*128) + ($j*6);
					fseek($map, $offset);
					$px = fread($map, 1);
					$px = ord($px);
					
					$rt = fread($map, 1);
					$rt = ord($rt);
					$rt = dechex($rt);
					$rt = substr($rt, -1);
					$rt = hexdec($rt);
					$gMod = $rt % 4; $gMod--;
					
				//	ustalanie grupy i coordów
					$groups = $px/64;
					if(getType($groups) == "double")
					{
						$groups = floor($groups);
					}
					$uv = $px - ($groups*64);
					$uvY = $uv/8;
					if(getType($uvY) == "double")
					{
						$uvY = floor($uvY);
					}
					$uvX = $uv - ($uvY*8);
					$groups += 4 + ($gMod*4);
					$name = $groups."_U".$uvX."_V".$uvY.".png";
//					var_dump($px, $rt, $groups, $uv, $uvY, $uvX, $name);
					var_dump($gMod, $rt, $name);
					
				//	====== rysowanie
					$brush = imageCreateFromPNG($txcN.$name);
					switch($rt)
					{
						case 0:
						case 1:
						case 2:
						case 3:
							$brush = imageRotate($brush, 180, 0);
							break;
						case 4:
						case 5:
						case 6:
						case 7:
							$brush = imageRotate($brush, -90, 0);
							break;
						case 8:
						case 9:
						case 10:
						case 11:
							$brush = imageRotate($brush, 0, 0);
							break;
						case 12:
						case 13:
						case 14:
						case 15:
							$brush = imageRotate($brush, -270, 0);
							break;
					}
					imageSetBrush($im, $brush);
//					imageSetPixel($im, ($j*64)+32, ($i*64)+32, IMG_COLOR_BRUSHED);
					imageSetPixel($im, (($height-$i)*64)-32, (($width-$j)*64)-32, IMG_COLOR_BRUSHED);
					imageDestroy($brush);
				}
			}
		
		imagePNG($im, "out".$iam."_".$x1."_".$y1.".png");
		imageDestroy($im);
		}
	}	
	fclose($map);
	
?>