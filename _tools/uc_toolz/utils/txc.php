	

    <?php
    require('utils/binUtils.php');
    //TXC DECOMPRESSOR v2.1
    $startTime = time();
    $logDir.= 'txc/';
    $outDir.= 'txc/';
     
    //POST
    $fName = $_POST['file'];
    $fNameNoExt = subStr($fName,0,strPos($fName,'.')); //bez rozszerzenia
    $groupsOutDir = $outDir.'_groups/'.$fNameNoExt.'/';
    $outDir.= $fNameNoExt.'/';
     
    @mkPath($logDir);
    @mkPath($outDir);
    @mkPath($groupsOutDir);
     
    if(!$_POST['opt_txc_noDump'])
            {
            $hHeadIn = fOpen($txcDir.$fName,'rb');
            $hDataIn = fOpen($txcDir.$fName,'rb'); //unikniêcie seeków ¿eby wróciæ do nag³ówka
            $hLog = fOpen($logDir.$fNameNoExt.'.log','wb');
     
            fWrite
                    ($hLog,
                    'txc dumper v2, dump date: '.date('j.m.Y')."\n".
                    $txcDir."\n\n"
                    );
     
            //HEADER
            $octPos = fReadInt2($hHeadIn);
            $unk1 = fReadInt2($hHeadIn); //zawsze 00 00? Mo¿e to int(4)?
     
            fWrite
                    ($hLog,
                    'header pos no: '.$octPos."\n".
                    'unk1:          '.$unk1  ."\n\n"
                    );
     
            $go = 0;
            $newGo = 0;
            $noGo = 0;
            $nul = 0;
     
            //KWARTETY
            for($i = 0; $i != $octPos; ++$i)
                    {
                    $outName = floor($i/64).'_U'.floor(($i%64)%8).'_V'.floor(($i%64)/8);
                    $addr = fReadInt4($hHeadIn);
                    fWrite
                            ($hLog,
                            '=== hIMG '.$i.' ==='."\n\n".
                            'addr: 0x'.decHex($addr)."\n"
                            );
                    fSeek($hDataIn,$addr); //SEEK_SET
                    if(($t = fReadInt2($hDataIn)) == 0xFFFF) //zwyk³y obrazek
                            {
                            fWrite
                                    ($hLog,
                                    'img type: 0xFFFF (indexed)'."\n".
                                    'out name: '.$outName."\n"
                                    );
                            $palType = fReadInt2($hDataIn);
                            if($palType & 1)
                                    $palName = 'A4 R4 G4 B4';
                            else
                                    $palName = 'R5 G6 B5';
                            $width = fReadInt2($hDataIn);
                            $height = fReadInt2($hDataIn);
                            $cNo = fReadInt2($hDataIn);
                            $bpp = strLen(decbin($cNo-1)); //iloœæ bitów potrzebna do zapisania ostatniego indeksu palety
                            //ceil(log($cNo,2)) siê pieprzy: dla $cNo = 1 wychodzi 0
                            fWrite
                                    ($hLog,
                                    'palType: '.$palType.' ('.$palName.'),'."\n".
                                    'width: '.$width.','."\n".
                                    'height: '.$height.','."\n".
                                    'colors no.: '.$cNo.' (0b'.decBin($cNo).'),'."\n".
                                    'pal size (bytes): '.($cNo*2).' (0x'.decHex($cNo*2).'),'."\n".
                                    'bpp: '.$bpp.','."\n".
                                    'data size: '.($width*$height*($bpp/8)).' (0x'.decHex($width*$height*($bpp/8)).')'."\n"
                                    );
                            if($_POST['opt_txc_noFFFF'])
                                    fWrite
                                            ($hLog,
                                            'image dump ommited'."\n"
                                            );
                            else
                                    {
                                    $im = imageCreateTrueColor($width,$height); //potrzebne dla palety wiêkszej ni¿ 256
                                    $colors = array();
                                    if($palType & 1) //paleta A4R4G4B4
                                            {
                                            imagealphablending($im, false);
                                            imagesavealpha($im, true);
                                            $imAlpha = imageCreateTrueColor($width,$height);
                                            $imNoAlpha = imageCreateTrueColor($width,$height);
                                            $colorsAlpha = array();
                                            $colorsNoAlpha = array();
                                            for($j = $cNo; $j--; )
                                                    {
                                                    $rawColor = fReadInt2($hDataIn);
                                                    $a = round(127-(($rawColor>>12)&15)/15*127);
                                                    $r = round(    (($rawColor>>8 )&15)/15*255);
                                                    $g = round(    (($rawColor>>4 )&15)/15*255);
                                                    $b = round(    (($rawColor>>0 )&15)/15*255);
                                                    $aGray = round((($rawColor>>12)&15)/15*255);
                                                    if(($colors[] = imageColorAllocateAlpha($im,$r,$g,$b,$a)) === false)
                                                            {
                                                            fWrite($hLog,'ERR: a:'.$a.' r:'.$r.' g:'.$g.' b:'.$b."\n");
                                                            return;
                                                            }
                                                    $colorsAlpha[] = imageColorAllocate($imAlpha,$aGray,$aGray,$aGray);
                                                    $colorsNoAlpha[] = imageColorAllocate($imNoAlpha,$r,$g,$b);
                                                    }
                                            }
                                    else //paleta R5G6B5
                                            for($j = $cNo; $j--; )
                                                    {
                                                    $rawColor = fReadInt2($hDataIn);
                                                    $r = round((($rawColor>>11)&31)/31*255);
                                                    $g = round((($rawColor>>5 )&63)/63*255);
                                                    $b = round((($rawColor>>0 )&31)/31*255);
                                                    if(($colors[] = imageColorAllocate($im,$r,$g,$b)) === false)
                                                            {
                                                            fWrite($hLog,'ERR: r:'.$r.' g:'.$g.' b:'.$b."\n");
                                                            return;
                                                            }
                                                    }
     
                                    $pal = array();
                                    $unkPal = array();
                                    $c = array();
     
                                    $readSize = $bpp;
                                    while($readSize%8 || ($readSize/8)&1)
                                            $readSize+=$bpp;
                                    $pxNo = $readSize/$bpp;
                                    $readSize/=8;
                                    $readSize/=2;
                                    fWrite
                                            ($hLog,
                                            'read step: '.$readSize.' × int(2)'."\n".
                                            'px per read: '.$pxNo."\n"
                                            );
                                    for($j = 0; $j != ($width*$height/$pxNo); ++$j)
                                            {
                                            $ci = int2toByteArray(fReadInt2($hDataIn,$readSize));
                                            for($k = 0, $bfPos = 0, $blPos = $bpp-1;
                                                            $k != $pxNo;
                                                            ++$k, $bfPos+=$bpp, $blPos+=$bpp)
                                                    {
                                                    //bfPos i blPos - indeks bitów pocz¹tkowego i koñcowego
                                                    //nr bajtu, w którym te bity siê znajduj¹
                                                            $bfB = floor($bfPos/8);
                                                            $blB = floor($blPos/8);
                                                    //indeks bitów pocz¹tkowego i koñcowego wzglêdem pocz¹tka bajta zawieraj¹cego
                                                            $bfPosB = $bfPos%8;
                                                            $blPosB = $blPos%8;
                                                    //przesuniêcia bitowe
                                                            $bfShl = $bpp-(8-$bfPosB);
                                                            $blShr = 8-1-$blPosB;
                                                    //maski ANDowe
                                                            $bfMask =     pow(2,8-$bfPosB)-1;
                                                            $blMask = 256-pow(2,  $blShr );
                                                    if($bfB == $blB) //mieœci siê na jednym bajcie
                                                            $c[$k] = ($ci[$bfB] & ($bfMask & $blMask)) >> $blShr;
                                                    elseif(abs($blB-$bfB) == 1) //dwa s¹siednie bajty
                                                            $c[$k] = (($ci[$bfB]&$bfMask)<<$bfShl)+(($ci[$blB])>>$blShr);
                                                    else //trzy s¹siednie bajty
                                                            $c[$k] = (($ci[$bfB]&$bfMask)<<$bfShl)+($ci[$bfB+1]<<(8-$blShr))+($ci[$blB]>>$blShr);
                                                    @$pal[$c[$k]]+=1;
                                                    if(isset($colors[$c[$k]]))
                                                            {
                                                            imageSetPixel($im,($j*$pxNo)%$width+$k,($j*$pxNo/$width),$colors[$c[$k]]);
                                                            if($palType & 1)
                                                                    {
                                                                    imageSetPixel($imAlpha,  ($j*$pxNo)%$width+$k,($j*$pxNo/$width),$colorsAlpha[$c[$k]]);
                                                                    imageSetPixel($imNoAlpha,($j*$pxNo)%$width+$k,($j*$pxNo/$width),$colorsNoAlpha[$c[$k]]);
                                                                    }
                                                            }
                                                    else
                                                            @$unkPal[$c[$k]]+=1;
                                                    }
                                            }
                                    fWrite
                                            ($hLog,
                                            'expected colors no: '.sizeOf($pal)."\n".
                                            'unexpected colors no: '.sizeOf($unkPal)."\n"
                                            );
                                    imagePNG($im,$outDir.$outName.'.png');
                                    imageDestroy($im);
                                    if($palType & 1)
                                            {
                                            imagePNG($imAlpha,$outDir.$outName.'_a.png');
                                            imagePNG($imNoAlpha,$outDir.$outName.'_raw.png');
                                            imageDestroy($imAlpha);
                                            imageDestroy($imNoAlpha);
                                            }
                                    }
                            ++$go;
                            }
                    elseif($t == 0) //nowy obrazek
                            {
                            fWrite
                                    ($hLog,
                                    'img type: 0x0000 (HI-COLOR)'."\n".
                                    'out name: '.$outName."\n"
                                    );
                           
                            $width = fReadInt2($hDataIn);
                            $height = fReadInt2($hDataIn);
                            fWrite
                                    ($hLog,
                                    'width: '.$width."\n".
                                    'height: '.$height."\n".
                                    'expected data size: '.($width*$height*2).'(0x'.decHex($width*$height*2).')'."\n"
                                    );
                            $im = imageCreateTrueColor($width,$height);
                            for($y = 0; $y != $height; ++$y)
                                    for($x = 0; $x != $width; ++$x)
                                            {
                                            $rawColor = fReadInt2($hDataIn);
                                            $r = round((($rawColor>>11)&31)/31*255);
                                            $g = round((($rawColor>>5 )&63)/63*255);
                                            $b = round((($rawColor>>0 )&31)/31*255);
                                            $color = imageColorAllocate($im,$r,$g,$b);
                                            imageSetPixel($im,$x,$y,$color);
                                            }
                            imagePNG($im,$outDir.$outName.'.png');
                            imageDestroy($im);
                            ++$newGo;
                            }
                    elseif($addr == 0)
                            {
                            fWrite
                                    ($hLog,
                                    'img type: null ptr (none)'."\n"
                                    );
                            copy('./interface/txc_nul.png',$outDir.$outName.'.png');
                            ++$nul;
                            }
                    else
                            {
                            fWrite
                                    ($hLog,
                                    'img type: 0x'.decHex($t).' (unk/mismatch)'."\n".
                                    'addr: '.$addr."\n"
                                    );
                            copy('./interface/txc_unk.png',$outDir.$outName.'.png');
                            ++$noGo;
                            }
                    fWrite($hLog,"\n"); //odstêp od nastêpnego wpisu
                    set_time_limit(15);
                    }
            fWrite
                    ($hLog,
                    'ffff-started imgs: '.$go   ."\n".
                    'zero-started imgs: '.$newGo."\n".
                    'address mismatch:  '.$noGo ."\n".
                    'null pointer:      '.$nul  ."\n".
                    'crunched in:       '.(time()-$startTime).'s'."\n"
                    );
     
            fClose($hHeadIn);
            fClose($hDataIn);
            fClose($hLog);
            }
    if(!$_POST['opt_txc_noGroups'])
            {
            $files = scanDir($outDir);
            unset($files[array_search('.',$files)]);
            unset($files[array_search('..',$files)]);
            unset($files[array_search('Thumbs.db',$files)]);
           
            $licznik = 0;
            forEach($files as $plik)
                    {
                    if(strlen(substr($plik, 0, -4)) > 8)
                            unset($files[$licznik]);
                    ++$licznik;
                    }
           
            $gLimit = count($files)/64;
            if(getType($gLimit) == "double")
                    $gLimit = floor($gLimit);
            for($i = 0; $i <= $gLimit; $i++)
                    {
                    $paleta = imageCreateTrueColor(2048, 2048);
                    $white = imageColorAllocate($paleta, 255, 255, 255);
                    for($j = 0; $j <= 7; $j++)
                            for($k = 0; $k <= 7; $k++)
                                    {
                                    set_time_limit(30);
                                    $name = $outDir.($i."_U".$j."_V".$k.".png");
                                    if(file_exists($name))
                                            {
                                            $x = $j*256;//+128;
                                            $y = $k*256;//+128;
                                            $srcIm = imageCreateFromPNG($name);
                                            imageCopy($paleta,$srcIm,$x,$y,0,0,imagesX($srcIm),imagesY($srcIm));
                                            imageDestroy($srcIm);
                                            }
                                    }
                    imagePNG($paleta, $groupsOutDir.$i.".png");
                    imageDestroy($paleta);
                    }
            }
    ?>

