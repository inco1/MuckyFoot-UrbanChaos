<?php

	$name = "oval1.iam";
	$imname = "oval1.iam0.png";
	
	mkdir("./wallsOut/wallsX/$name/");
	mkdir("./wallsOut/wallsY/$name/");
	
	$handle = fopen($name, "r");
	fseek($handle, 0x1800A); //sprawdzamy długość struktury?
	$ilegowna = ord(fread($handle, 1));
	$ilegowna--; //14
		
	//setup na ściany
	/*fseek($handle, 0x18038);
	for($i=0; $i<$ilegowna; $i++)
	{
		$co = fread($handle, 24);
		for($j = 0; $j < strlen($co); $j++)
		{
			echo dechex(ord($co[$j]))." ";
		}
		echo "<br />";
	}*/
	
	$im = imagecreatefrompng($imname);
	$color = imagecolorallocate($im, 0,0,0);
	fseek($handle, 0x1800C);
	//dwa pierwsze bajty określają ilość ścian, czytane od prawej
	$ilescian = fread($handle, 2);
	echo $A = (string)dechex(ord($ilescian[1])); echo "<br />";
	echo $B = (string)dechex(ord($ilescian[0])); echo "<br />"; 
	if(strlen($B) == 1)
	{
		echo $ilescian = "$A"."0"."$B"; echo "<br />";
	}
	else
	{
		echo $ilescian = "$A"."$B"; echo "<br />";
	}
	echo $ile_scian = (integer)hexdec($ilescian) - 2; echo "<br />";
	//setType($ile_scian, "integer");
	echo $ile_scian; echo "<br />";
	
	$offset = $ilegowna*24+14;
	fseek($handle, 0x18038+$offset);
	$green = imagecolorallocate($im, 0, 255, 0);
	for($i = 0; $i <= $ile_scian; $i++)
	{
		set_time_limit(30);
		//każda struktura ma 26 bajtów
		$sciana = fread($handle, 26);
		$X1 = ord($sciana[2]) / 128 * 512;
		$X2 = ord($sciana[3]) / 128 * 512;
		$Y1 = ord($sciana[8]) / 128 * 512;
		$Y2 = ord($sciana[9]) / 128 * 512;
		imageline($im, $X1, $Y1, $X2, $Y2, $green);
		echo dechex(ord($sciana[2]))." ".dechex(ord($sciana[8]))." ".dechex(ord($sciana[3]))." ".dechex(ord($sciana[9]))."<br />";
		echo $X1." ".$X2." ".$Y1." ".$Y2."<br />";
		echo "<br />";
		if($Y1 == $Y2) //Y są takie same, więc ściana rozchodzi się wzdłuż X
		{
			//mapa ma 128x128px
			//dlatego dzielimy ją aby wydobyć % szerokości współrzędnej
			//a potem mnożymy przez wymiary płótna
			$Z1 = ord($sciana[5]) / 128 * 512; 
			$Z2 = ord($sciana[6]) / 128 * 512;
			$floorH = ord($sciana[19]) / 16; //wysokość jednego piętra
			$floorN = ord($sciana[1]) / 4; //ile pięter
			$floors = $floorH * $floorN / 128 * 512;
						
			if(file_exists("./wallsOut/wallsY/$name/".$Y1.".png"))
			{
				$imX[$Y1] = imagecreatefrompng("./wallsOut/wallsY/$name/".$Y1.".png");
				imagerectangle($imX[$Y1], $X1, 512-$Z1, $X2, 512-$Z1-$floors, $green);
			}
			else
			{
				$imX[$Y1] = imagecreatetruecolor(512, 600);
				$color = imagecolorallocate($imX[$Y1], 0,0,0);
				imagerectangle($imX[$Y1], $X1, 512-$Z1, $X2, 512-$Z1-$floors, $green);
			}
			imagepng($imX[$Y1], "./wallsOut/wallsY/$name/".$Y1.".png");
			imagedestroy($imX[$Y1]);
		}
		elseif($X1 == $X2)
		{
			$Z1 = ord($sciana[5]) / 128 * 512;
			$Z2 = ord($sciana[6]) / 128 * 512;
			$floorH = ord($sciana[19]) / 16;
			$floorN = ord($sciana[1]) / 4;
			$floors = $floorH * $floorN / 128 * 512;
						
			if(file_exists("./wallsOut/wallsX/$name/".$X1.".png"))
			{
				$imY[$X1] = imagecreatefrompng("./wallsOut/wallsX/$name/".$X1.".png");
				imagerectangle($imY[$X1], $Y1, 512-$Z1, $Y2, 512-$Z1-$floors, $green);
			}
			else
			{
				$imY[$X1] = imagecreatetruecolor(512, 600);
				$color = imagecolorallocate($imY[$X1], 0,0,0);
				imagerectangle($imY[$X1], $Y1, 512-$Z1, $Y2, 512-$Z1-$floors, $green);
			}
			imagepng($imY[$X1], "./wallsOut/wallsX/$name/".$X1.".png");
			imagedestroy($imY[$X1]);
		}
	}
	
	
	imagepng($im, "dupa.png");
	imagedestroy($im);
	fclose($handle);
	
?>