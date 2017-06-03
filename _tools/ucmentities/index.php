<?php
	
	$name = "skymiss2.ucm";
	$iam = "skymap30.iam0.png";
	
	echo "<p>name: ".$name."</p>";
	$handle = fopen($name, "r");
	
	//nagłówek
	$przeczytane = fread($handle, 8);
	$hex = "";
	for($i = 0; $i < strlen($przeczytane); $i++)
	{
		$hex .= ord($przeczytane[$i])." ";
	}
	echo "<p>header: ".$hex."</p>";
	
	//lgt
	echo "<p>lighting: ";
	fseek($handle, 0x10c);
	do
	{
		$przeczytane = fread($handle, 1);
		echo $przeczytane;
	}while(ord($przeczytane) != "");
	echo "</p>";
	
	//iam
	echo "<p>map: ";
	fseek($handle, 0x210);
	do
	{
		$przeczytane = fread($handle, 1);
		echo $przeczytane;
	}while(ord($przeczytane) != "");
	echo "</p>";
	
	//nazwa
	echo "<p>inside-name: ";
	fseek($handle, 0x314);
	do
	{
		$przeczytane = fread($handle, 1);
		echo $przeczytane;
	}while(ord($przeczytane) != "");
	echo "</p>";
	
	//========specyfiki=========
	fseek($handle, 0x523);
	$przeczytane = fread($handle, 1);
	echo "<p>pedestrain density: ".ord($przeczytane)."</p>";
	echo "<hr /";
	
	
	$im = imagecreatefrompng($iam);
	$player = 0; $npc = 0; $vehicle = 0; $boss = 0; $items = 0; $camera = 0; $motorbike = 0; $timer = 0; 
	$marker = 0; $bonus = 0; $fog = 0; $other = 0;
	for($i = 1392; $i < 39303; $i += 74)
	{
		set_time_limit(30);
		fseek($handle, $i);
		$type = fread($handle, 1);
		if(ord($type) != 0)
		{
			$_other = 0;
			switch(ord($type)){
				case 0:
					break;
				case 2:
					echo "<p>type: 2 (0x2) player </p>";
					fseek($handle, $i+4); $angle = fread($handle, 1); echo "<p>angle: ".ord($angle)."</p>";
					fseek($handle, $i+12); $model = fread($handle, 1); echo "<p>model: ".ord($model)." ";
					switch(ord($model))
					{
						case 1: echo "Darci Stern"; break;
						case 2: echo "Roper"; break;
						case 3: echo "Police Officer"; break;
						case 4: echo "Trainer/Wildcat"; break;
					}
					echo "</p>";
					fseek($handle, $i+16); $unarmed = fread($handle, 1); if(ord($unarmed) == 1) echo "<p>unarmed</p>";
					$color = imageColorAllocate($im, 0, 255, 255); //light blue
					$player++;
					break;
				case 3:
					echo "<p>type: 3 (0x3) npc </p>";
					fseek($handle, $i+4); $angle = fread($handle, 1); echo "<p>angle: ".ord($angle)."</p>";
					fseek($handle, $i+14); $drop = fread($handle, 1); echo "<p>??? drop ???: ".ord($drop)."</p>";
					fseek($handle, $i+32); $state = fread($handle, 1); echo "<p>state: ".ord($state)." ";
					switch(ord($state))
					{
						case 0: echo "player"; break;
						case 1: echo "civillian"; break;
						case 2: echo "guard"; break;
						case 3: echo "assassin"; break;
						case 4: echo "boss"; break;
						case 5: echo "cop"; break;
						case 6: echo "gang"; break;
						case 7: echo "doorman"; break;
						case 8: echo "bodyguard"; break;
						case 9: echo "driver"; break;
						case 10: echo "bomb disposer"; break;
						case 11: echo "biker"; break;
						case 12: echo "fight test"; break;
						case 13: echo "bully"; break;
						case 14: echo "cop driver"; break;
						case 15: echo "dead"; break;
						case 16: echo "flee player"; break;
						case 17: echo "kill colour"; break;
						case 18: echo "MIB"; break;
						case 19: echo "bane"; break;
						case 20: echo "hypochondria"; break;
						case 21: echo "shoot dead"; break;
						case 22: echo "lazy"; break;
						case 23: echo "diligent"; break;
						case 24: echo "gang"; break;
						case 25: echo "fight back"; break;
						case 26: echo "kill just the player"; break;
						case 27: echo "robotic"; break;
						case 28: echo "restricted"; break;
						case 29: echo "player-kill"; break;
						case 30: echo "NULL"; break;
						case 31: echo "still"; break;
					}
					echo "</p>";
					fseek($handle, $i+24); $action = fread($handle, 1); echo "<p>action: ".ord($action)."</p>";
					fseek($handle, $i+34); $aggro = fread($handle, 1); echo "<p>aggresion level: ".ord($aggro)."</p>";
					fseek($handle, $i+12); $model = fread($handle, 1); echo "<p>model: ".ord($model)."</p>";
					$color = imageColorAllocate($im, 0, 0, 255); //blue
					$npc++;
					break;
				case 4:
					echo "<p>type: 4 (0x4) vehicle </p>";
					fseek($handle, $i+4); $angle = fread($handle, 1); echo "<p>angle: ".ord($angle)."</p>";
					fseek($handle, $i+12); $model = fread($handle, 1); echo "<p>model: ".ord($model)."</p>";
					fseek($handle, $i+5); $color = fread($handle, 1); echo "<p>color: ".ord($color)."</p>";
					$color = imageColorAllocate($im, 0, 255, 0); //green
					$vehicle++;
					break;
				case 5:
					echo "<p>type: 5 (0x5) item </p>";
					fseek($handle, $i+12); $model = fread($handle, 1); echo "<p>model: ".ord($model)." ";
					switch(ord($model))
					{
						case 1: echo "keycard (leftover)"; break;
						case 2: echo "pistol"; break;
						case 3: echo "first aid"; break;
						case 4: echo "shotgun"; break;
						case 5: echo "knife"; break;
						case 6: echo "m16"; break;
						case 7: echo "mine"; break;
						case 8: echo "baseball bat"; break;
						case 9: echo "???"; break;
						case 10: echo "pistol"; break;
						case 11: echo "something ammo"; break;
						case 12: echo "something ammo"; break;
						case 13: echo "map"; break;
						case 14: echo "briefcase"; break;
						case 15: echo "diskette"; break;
						case 16: echo "m16"; break;
						case 17: echo "keycard (leftover)"; break;
						case 18: echo "keycard (leftover)"; break;
						case 19: echo "video"; break;
						case 20: echo "???"; break;
						case 21: echo "weed away (leftover)"; break;
						case 22: echo "grenades"; break;
						case 23: echo "explosives"; break;
						case 24: echo "keycard (leftover)"; break;
						case 25: echo "game ending explosion"; break;
						case 26: echo "keycard (leftover)"; break;
						case 27: echo "keycard (leftover)"; break;
					}
					echo "</p>";
					$color = imageColorAllocate($im, 255, 0, 255); //purple
					$items++;
					break;
				case 6:
					echo "<p>type: 6 (0x6) boss </p>";
					fseek($handle, $i+4); $angle = fread($handle, 1); echo "<p>angle: ".ord($angle)."</p>";
					fseek($handle, $i+12); $model = fread($handle, 1); echo "<p>model: ".ord($model)." ";
					switch(ord($model))
					{
						case 2: echo "imp/bat/gargoyle"; break;
						case 3: echo "baalrog"; break;
						case 4: echo "bane"; break;
					}
					echo "</p>";
					$color = imageColorAllocate($im, 0, 0, 128); //dark blue
					$boss++;
					break;
				case 7:
					echo "<p>type: 7 (0x7) ??? camera control ???</p>";
					$color = imageColorAllocate($im, 166, 75, 0); //brown
					$camera++;
					break;
				case 16:
					echo "<p>type: 16 (0x10) motorbike (leftover) </p>";
					$color = imageColorAllocate($im, 255, 0, 0); //red
					$motorbike++;
					break;
				case 18:
					echo "<p>type: 18 (0x12) ??? timer ??? </p>";
					$color = imageColorAllocate($im, 255, 115, 115); //light red
					$timer++;
					break;
				case 27:
					echo "<p>type: 27 (0x1B) map marker </p>";
					$color = imageColorAllocate($im, 255, 115, 0); //orange
					$marker++;
					break;
				case 31:
					echo "<p>type: 31 (0x1F) ??? bonus spawn ??? </p>";
					$color = imageColorAllocate($im, 115, 0, 155); //dark purple
					$bonus++;
					break;
				case 47:
					echo "<p>type: 47 (0x2F) fog </p>";
					$color = imageColorAllocate($im, 0, 153, 153); //windows green
					$fog++;
					break;
				default:
					echo "<p>type: ".ord($type)." (0x".dechex(ord($type)).") other </p>";
					$color = imageColorAllocate($im, 255, 0, 0); //red
					$other++;
					$_other = 1;
					break;
			}
			fseek($handle, $i+2); $dependency = fread($handle, 1); 
				echo "<p>dependency: ".ord($dependency)."</p>";
				
			fseek($handle, $i+56); $XX = fread($handle, 2); $x = dechex(ord($XX[1])).dechex(ord($XX[0]));
				echo "<p>X: 0x".$x." (".hexdec($x).")</p>";
			fseek($handle, $i+58); $XX2 = fread($handle, 2); $x2 = dechex(ord($XX2[1])).dechex(ord($XX2[0]));
				echo "<p>X2: 0x".$x2." (".hexdec($x2).")</p>";
			
			fseek($handle, $i+64); $YY = fread($handle, 2); $y = dechex(ord($YY[1])).dechex(ord($YY[0]));
				echo "<p>Y: 0x".$y." (".hexdec($y).")</p>";
			fseek($handle, $i+66); $YY2 = fread($handle, 2); $y2 = dechex(ord($YY2[1])).dechex(ord($YY2[0]));
				echo "<p>Y2: 0x".$y2." (".hexdec($y2).")</p>";
			
			fseek($handle, $i+60); $ZZ = fread($handle, 2); $z = dechex(ord($ZZ[1])).dechex(ord($ZZ[0]));
				echo "<p>Z: 0x".$z." (".hexdec($z).")</p>";
			fseek($handle, $i+62); $ZZ2 = fread($handle, 2); $z2 = dechex(ord($ZZ2[1])).dechex(ord($ZZ2[0]));
				echo "<p>Z2: 0x".$z2." (".hexdec($z2).")</p>";
					
			
			fseek($handle, $i+68); $id = fread($handle, 3); $_id = ord($id[0])." ".ord($id[1])." ".ord($id[2]);
				echo "<p>id: ".$_id."</p>";
				
			$mX = floor(hexdec($x) / 32767 * 511);
			$mY = floor(hexdec($y) / 32767 * 511);
			
			
			//echo $mX." ".$mY;
			
			if(($_other == 1) && ((hexdec($x2) != 0) || (hexdec($y2) != 0)))
			{
				$mX2 = floor(hexdec($x2) / 32767 * 511);
				$mY2 = floor(hexdec($y2) / 32767 * 511);
				imageline($im, $mX, $mY, $mX2, $mY2, $color);
			}
			else
			{
				imagesetpixel($im, $mX, $mY, $color);
			}
			
			//imagePNG($im, "_".$iam);
			//Xblue = Xred/0x7fff*127; 
			echo "<hr />";
		}
	}
	//imagesetpixel($im, 2, 2, imageColorAllocate($im, 0, 255, 0));
	imagePNG($im, "_".$iam);
	
	fclose($handle);
	
	echo "player (light blue): ".$player.
	"<br />npc (blue): ".$npc.
	"<br />vehicle (green): ".$vehicle.
	"<br />boss (dark blue): ".$boss.
	"<br />item (purple): ".$items.
	"<br />camera? (brown): ".$camera.
	"<br />motorbike (leftover)(red): ".$motorbike.
	"<br />timer? (light red): ".$timer.
	"<br />marker (orange): ".$marker.
	"<br />bonus (dark purple): ".$bonus.
	"<br />fog (win98 green): ".$fog.
	"<br />other (red): ".$other;
	
?>