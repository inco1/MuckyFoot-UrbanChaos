<?php
function fReadInt2($h,$no = 1) //Little Endian - pc
	{
	if($no == 1)
		{
		$buf = fRead($h,2);
		return (ord($buf[0])+(ord($buf[1])<<8));
		}
	else
		{
		$out = array();
		for($i = 0; $i != $no; ++$i)
			{
			$buf = fRead($h,2);
			$out[] = (ord($buf[0])+(ord($buf[1])<<8));
			}
		return $out;
		}
	}
function fReadInt2BE($h,$no = 1) //Big Endian - psx
	{
	if($no == 1)
		{
		$buf = fRead($h,2);
		return (ord($buf[0])+(ord($buf[1])<<8));
		}
	else
		{
		$out = array();
		for($i = 0; $i != $no; ++$i)
			{
			$buf = fRead($h,2);
			$out[] = (ord($buf[1])+(ord($buf[0])<<8));
			}
		return $out;
		}
	}
function int2toByteArray($in)
	{
	if(is_array($in))
		{
		$d = sizeOf($in);
		for($i = 0; $i != $d; ++$i)
			{
			$out[] = $in[$i]>>8;
			$out[] = $in[$i]&0xff;
			}
		return $out;
		}
	else
		return array($in>>8, $in&0xff);
	}

$fName = 'wstores1';
//$fName = 'frontend';
$start = time();
$pos = 0x4104;
$hIn = fOpen('./clumps/'.$fName.'.txc','rb');
fSeek($hIn,$pos);

@mkDir('./out');
@mkDir('./out/'.$fName);

$hOut = fOpen('./out/'.$fName.'.log','wb');
fWrite($hOut,'file: '.$fName."\n");

for($fNo = 0, $end = 0; !$end; ++$fNo)
	{
	$magicAddr = fTell($hIn);
	@$magic = fReadInt2($hIn);
	if(fEof($hIn))
		{
		fWrite($hOut,"\n".'EOF');
		break;
		}
	else
		{
		if($magic != 0xffff)
			{
			//$end = 1;
			fWrite($hOut,'magicNo się nie zgadza: '.$magic.' (0x'.decHex($magic).'), adres: 0x'.dechex($magicAddr)."\n");
			$addData = int2toByteArray($magic);
			$end = 0;
			do
				{
				if(fEof($hIn))
					$end = 2;
				else
					{
					$bfr = ord(fRead($hIn,1));
					if($bfr == 0xff)
						{
						if(fEof($hIn))
							$end = 2;
						else
							{
							$prevBfr = $bfr;
							$bfr = ord(fRead($hIn,1));
							if($bfr == 0xff)
								{
								$end = 1;
								fSeek($hIn,-2,SEEK_CUR);
								}
							else
								$addData[] = $prevBfr;
							}
						}
					else
						$addData[] = $bfr;
					}
				}
				while(!$end);
			fWrite($hOut,'dodatkowych bajtów: '.sizeOf($addData)."\n");
			if($end == 2)
				fWrite($hOut,'koniec pliku po dodatkowych danych'."\n");
			else
				$end = 0;
			}
		else
			{
			fWrite($hOut,"\n".'=== IMG '.$fNo.' ==='."\n\n");
			$unk = fReadInt2($hIn);
			$width = fReadInt2($hIn);
			$height = fReadInt2($hIn);
			$cNo = fReadInt2($hIn);
			$bpp = strLen(decbin($cNo-1)); //ilość bitów potrzebna do zapisania ostatniego indeksu palety
			//ceil(log($cNo,2)) się pieprzy: dla $cNo = 1 wychodzi 0
			fWrite($hOut,'addr: '.$magicAddr.' (0x'.decHex($magicAddr).'),
magic: '.$magic.' (0x'.decHex($magic).'),
unk: '.$unk.'
width: '.$width.',
height: '.$height.',
colors: '.$cNo.' (0b'.decBin($cNo).'),
pal size: '.($cNo*2).' (0x'.decHex($cNo*2).'),
bpp: '.$bpp.',
data size: '.($width*$height*($bpp/8)).' (0x'.decHex($width*$height*($bpp/8)).')');

			$im = imageCreateTrueColor($width,$height); //potrzebne dla palety większej niż 256
			$colors = array();
			//echo '<table>';
			for($i = 0; $i != $cNo; ++$i)
				{
				//echo '<tr>';
				$rawColor = fReadInt2($hIn);
				//echo '<td>',$rawColor,'</td>';
				//echo '<td>',decBin($rawColor),'</td>';
				/*
				//A1R5G5B5
				$a = $rawColor>>15;
				$r = round(( $rawColor>>10&31)/0b11111*255);
				$g = round((($rawColor>>5)&31)/0b11111*255);
				$b = round((($rawColor>>0)&31)/0b11111*255);
				//echo '<td>',$r,'</td><td>',$g,'</td><td>',$b,'</td><td>',$a,'</td>';
				$colors[] = imageColorAllocateAlpha($im,$r,$g,$b,$a*127);
				*/
				/*
				//R5G5B5
				$r = round(( $rawColor>>10&31)/0b11111*255);
				$g = round((($rawColor>>5)&31)/0b11111*255);
				$b = round((($rawColor>>0)&31)/0b11111*255);
				//echo '<td>',$r,'</td><td>',$g,'</td><td>',$b,'</td>';
				$colors[] = imageColorAllocate($im,$r,$g,$b);
				*/
				
				//R5G6B5
				$r = round(( $rawColor>>11&31)/bindec('011111')*255);
				$g = round((($rawColor>>5)&63)/bindec('111111')*255);
				$b = round((($rawColor>>0)&31)/bindec('011111')*255);
				//echo '<td>',$r,'</td><td>',$g,'</td><td>',$b,'</td>';
				if(($colors[] = imageColorAllocate($im,$r,$g,$b)) === false)
					{
					fWrite($hOut,'ERR: r:'.$r.' g:'.$g.' b:'.$b);
					return;
					}
				//array_unshift($colors,imageColorAllocate($im,$r,$g,$b));
				//echo '</tr>';
				}
			//echo '</table>';

			//GRAYSCALE
			/*for($i = 0; $i != ($width*$height/2);++$i) //zwykła kolejność bajtów
				{
				$ci = fRead($hIn,2);
				imageSetPixel($im,($i*2)%$width,floor($i*2/$width),imageColorAllocate($im,ord($ci[0]),ord($ci[0]),ord($ci[0])));
				imageSetPixel($im,($i*2)%$width+1,floor($i*2/$width),imageColorAllocate($im,ord($ci[1]),ord($ci[1]),ord($ci[1])));
				}*/
			/*for($i = 0; $i != ($width*$height/2);++$i) //zamieniona kolejność bajtów
				{
				$ci = fRead($hIn,2);
				imageSetPixel($im,($i*2)%$width,floor($i*2/$width),imageColorAllocate($im,ord($ci[1]),ord($ci[1]),ord($ci[1])));
				imageSetPixel($im,($i*2)%$width+1,floor($i*2/$width),imageColorAllocate($im,ord($ci[0]),ord($ci[0]),ord($ci[0])));
				}*/
				
			//HI-COLOR
			$pal = array();
			$unkPal = array();
			$c = array();

			$readSize = $bpp;
			while($readSize%8 || ($readSize/8)&1)
				$readSize+=$bpp;
			$pxNo = $readSize/$bpp;
			$readSize/=8;
			$readSize/=2;
			fWrite($hOut,'read step: '.$readSize.' × int(2)
px per read: '.$pxNo);
			for($i = 0; $i != ($width*$height/$pxNo); ++$i)
				{
				//echo '<br><br>',$i,':<br>';
				$ci = int2toByteArray(fReadInt2($hIn,$readSize));
				//for($j = 0; $j != $readSize; ++$j)
				//	printf('%08b ',$ci[$j]);
				//echo '<br>';
				for($j = 0, $bfPos = 0, $blPos = $bpp-1;
						$j != $pxNo;
						++$j, $bfPos+=$bpp, $blPos+=$bpp)
					{
					//bfPos i blPos - indeks bitów początkowego i końcowego
					//nr bajtu, w którym te bity się znajdują
						$bfB = floor($bfPos/8);
						$blB = floor($blPos/8);
					//indeks bitów początkowego i końcowego względem początka bajta zawierającego
						$bfPosB = $bfPos%8;
						$blPosB = $blPos%8;
					//przesunięcia bitowe
						$bfShl = $bpp-(8-$bfPosB);
						$blShr = 8-1-$blPosB;
					//maski ANDowe
						$bfMask =     pow(2,8-$bfPosB)-1;
						$blMask = 256-pow(2,  $blShr );
					if($bfB == $blB) //mieści się na jednym bajcie
						$c[$j] = ($ci[$bfB] & ($bfMask & $blMask)) >> $blShr;
					elseif(abs($blB-$bfB) == 1) //dwa sąsiednie bajty
						$c[$j] = (($ci[$bfB]&$bfMask)<<$bfShl)+(($ci[$blB])>>$blShr);
					else //trzy sąsiednie bajty
						{
						$c[$j] = (($ci[$bfB]&$bfMask)<<$bfShl)+($ci[$bfB+1]<<(8-$blShr))+($ci[$blB]>>$blShr);
						//printf("maski: %b %b<br>",$bfMask,$blMask);
						//printf("shifty: %d %d<br>",$bfShl,$blShr);
						//printf("indeksy: %d %d<br><br>",$bfB,$blB);
						}
					@$pal[$c[$j]]+=1;
					if(isset($colors[$c[$j]]))
						imageSetPixel($im,($i*$pxNo)%$width+$j,($i*$pxNo/$width),$colors[$c[$j]]);
					else
						@$unkPal[$c[$j]]+=1;
					}
				}
			fWrite($hOut,'spodziewane kolory: '.sizeOf($pal)."\n");
			//var_dump($pal);
			fWrite($hOut,'nieznane kolory: '.sizeOf($unkPal)."\n");
			//var_dump($unkPal);
			imagePNG($im,'./out/'.$fName.'/'.$fName.'_'.$fNo.'.png');
			}
		}
	set_time_limit(15);
	}
	
	fWrite($hOut,"\n\n".'zmielone w '.(time()-$start).'s');
	echo 'zmielone w '.(time()-$start).'s';
	
	fClose($hIn);
	fClose($hOut);
?>