<?php
require('./utils/binUtils.php');

$start = time();

@mkDir('./logs');
@mkDir('./heads');
@mkDir('./out');
@mkDir('./out/'.$fName);

$hOut = fOpen('./logs/'.$fName.'.log','wb');
$hIn = fOpen('./clumps/'.$fName.'.txc','rb');

//$pos = 0x4104;
//fSeek($hIn,$pos);
$headPosNo = fReadInt2($hIn);
$headUnk = fReadInt2($hIn);
//fSeek($hIn,$headPosNo*8,SEEK_CUR);
fWrite($hOut,'file: '.$fName."\n".'header pos no:'.$headPosNo.",\n".'header Unk:'.$headUnk.",\n\n");

$hHead = fOpen('./heads/'.$fName.'.head','wb');
for($i = 0; $i != $headPosNo; ++$i)
	{
	$bfr = fRead($hIn,8);
	fWrite($hHead,$bfr);
	}
fClose($hHead);

for($fNo = 0, $end = 0; !$end; ++$fNo)
	{
	$magicAddr = fTell($hIn);
	@$magic = fReadInt2($hIn);
	@$palType = fReadInt2($hIn);
	if(fEof($hIn))
		{
		fWrite($hOut,"\n".'EOF');
		break;
		}
	else
		{
		if($magic != 0xffff || $palType > 1)
			{
			//$end = 1;
			if($magic != 0xffff)
				{
				fWrite($hOut,'magicNo się nie zgadza: '.$magic.' (0x'.decHex($magic).'), adres: 0x'.dechex($magicAddr).' '."\n");
				fSeek($hIn,-2,SEEK_CUR); //przed $palType bo może on jest poprawnym magicNo?
				$addData = int2toByteArray($magic);
				}
			else if($palType > 1)
				{
				fWrite($hOut,'palType się nie zgadza: '.$palType.' (0x'.decHex($palType).'), adres: 0x'.dechex($magicAddr+2).' '."\n");
				$addData = int2toByteArray(array($magic,$palType));
				}
			$end = 0;
			do
				{
				if(fEof($hIn))
					$end = 2;
				else
					{
					$bfr = fReadInt2($hIn);
					if($bfr == 0xffff) //jeśli zgadza się magicNo
						{
						if(fEof($hIn))
							$end = 2;
						else
							{
							$prevBfr = $bfr;
							$bfr = fReadInt2($hIn);
							if(fEof($hIn))
								$end = 2;
							elseif($bfr < 2) //jeśli zgadza się palType
								{
								$end = 1;
								fSeek($hIn,-4,SEEK_CUR);
								}
							else
								{
								fSeek($hIn,-2,SEEK_CUR);
								//$addData = array_merge($addData,int2toByteArray($prevBfr));
								$tmp = int2toByteArray($prevBfr);
								$addData[] = $tmp[0];
								$addData[] = $tmp[1];
								}
							}
						}
					else
						{
						//$addData = array_merge($addData,int2toByteArray($bfr));
						$tmp = int2toByteArray($bfr);
						$addData[] = $tmp[0];
						$addData[] = $tmp[1];
						//fSeek($hIn,-1,SEEK_CUR);
						}
					}
				}
				while(!$end);
			fWrite($hOut,'dodatkowych bajtów: '.sizeOf($addData)."\n");
			if($end == 2)
				fWrite($hOut,'koniec pliku po dodatkowych danych'."\n");
			else
				{
				$end = 0;
				--$fNo;
				}
			}
		else
			{
			fWrite($hOut,"\n".'=== IMG '.$fNo.' ==='."\n\n");
			if($palType & 1)
				$palName = 'A4 R4 G4 B4';
			else
				$palName = 'R5 G6 B5';
			$width = fReadInt2($hIn);
			$height = fReadInt2($hIn);
			$cNo = fReadInt2($hIn);
			$bpp = strLen(decbin($cNo-1)); //ilość bitów potrzebna do zapisania ostatniego indeksu palety
			//ceil(log($cNo,2)) się pieprzy: dla $cNo = 1 wychodzi 0
			fWrite($hOut,'addr: '.$magicAddr.' (0x'.decHex($magicAddr).'),
magic: '.$magic.' (0x'.decHex($magic).'),
palType: '.$palType.' ('.$palName.'),
width: '.$width.',
height: '.$height.',
colors: '.$cNo.' (0b'.decBin($cNo).'),
pal size: '.($cNo*2).' (0x'.decHex($cNo*2).'),
bpp: '.$bpp.',
data size: '.($width*$height*($bpp/8)).' (0x'.decHex($width*$height*($bpp/8)).')'."\n");

			$im = imageCreateTrueColor($width,$height); //potrzebne dla palety większej niż 256
			imagealphablending($im, false);
			imagesavealpha($im, true);
			$colors = array();
			if($palType & 1)
				{
				$imAlpha = imageCreateTrueColor($width,$height);
				$imNoAlpha = imageCreateTrueColor($width,$height);
				$colorsAlpha = array();
				$colorsNoAlpha = array();
				for($i = 0; $i != $cNo; ++$i)
					{
					$rawColor = fReadInt2($hIn);
					//A4R4G4B4
					$a = round(127-(($rawColor>>12)&15)/15*127);
					$r = round((($rawColor>>8 )&15)/15*255);
					$g = round((($rawColor>>4 )&15)/15*255);
					$b = round((($rawColor>>0 )&15)/15*255);
					if(($colors[] = imageColorAllocateAlpha($im,$r,$g,$b,$a)) === false)
						{
						fWrite($hOut,'ERR: a:'.$a.' r:'.$r.' g:'.$g.' b:'.$b."\n");
						return;
						}
					$colorsAlpha[] = imageColorAllocate($imAlpha,$a,$a,$a);
					$colorsNoAlpha[] = imageColorAllocate($imNoAlpha,$r,$g,$b);
					}
				}
			else
				for($i = 0; $i != $cNo; ++$i)
					{
					$rawColor = fReadInt2($hIn);
					//R5G6B5
					$r = round(( $rawColor>>11&31)/31*255);
					$g = round((($rawColor>>5)&63)/63*255);
					$b = round((($rawColor>>0)&31)/31*255);
					if(($colors[] = imageColorAllocate($im,$r,$g,$b)) === false)
						{
						fWrite($hOut,'ERR: r:'.$r.' g:'.$g.' b:'.$b."\n");
						return;
						}
					}
				
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
px per read: '.$pxNo."\n");
			for($i = 0; $i != ($width*$height/$pxNo); ++$i)
				{
				$ci = int2toByteArray(fReadInt2($hIn,$readSize));
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
						$c[$j] = (($ci[$bfB]&$bfMask)<<$bfShl)+($ci[$bfB+1]<<(8-$blShr))+($ci[$blB]>>$blShr);
					@$pal[$c[$j]]+=1;
					if(isset($colors[$c[$j]]))
						{
						imageSetPixel($im,($i*$pxNo)%$width+$j,($i*$pxNo/$width),$colors[$c[$j]]);
						if($palType & 1)
							{
							imageSetPixel($imAlpha,($i*$pxNo)%$width+$j,($i*$pxNo/$width),$colorsAlpha[$c[$j]]);
							imageSetPixel($imNoAlpha,($i*$pxNo)%$width+$j,($i*$pxNo/$width),$colorsNoAlpha[$c[$j]]);
							}
						}
					else
						@$unkPal[$c[$j]]+=1;
					}
				}
			fWrite($hOut,'spodziewane kolory: '.sizeOf($pal)."\n");
			//var_dump($pal);
			fWrite($hOut,'nieznane kolory: '.sizeOf($unkPal)."\n");
			//var_dump($unkPal);
			imagePNG($im,'./out/'.$fName.'/'.$fName.'_'.$fNo.'.png');
			if($palType & 1)
				{
				imagePNG($imAlpha,'./out/'.$fName.'/'.$fName.'_'.$fNo.'_a.png');
				imagePNG($imNoAlpha,'./out/'.$fName.'/'.$fName.'_'.$fNo.'_raw.png');
				}
			}
		}
	set_time_limit(15);
	}
	
	fWrite($hOut,"\n\n".'zmielone w '.(time()-$start).'s');
	echo 'zmielone w '.(time()-$start).'s';
	
	fClose($hIn);
	fClose($hOut);
?>