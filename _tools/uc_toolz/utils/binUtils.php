<?php
function fReadInt2($h,$no = 1) //Little Endian - pc
	{
	$out = array();
	for($i = 0; $i != $no; ++$i)
		{
		$buf = fRead($h,2);
		if($buf === false || strLen($buf) != 2)
			return false;
		$out[] = (ord($buf[0])+(ord($buf[1])<<8));
		}
	if($no == 1) //jak jeden to zwrуж sam№ wartoњж
		return $out[0];
	else //a jak kilka to tablicк
		return $out;
	}
function fReadInt4($h,$no = 1) //Little Endian - pc
	{
	$out = array();
	for($i = 0; $i != $no; ++$i)
		{
		$buf = fRead($h,4);
		if($buf === false || strLen($buf) != 4)
			return false;
		$out[] = (ord($buf[0])+(ord($buf[1])<<8)+(ord($buf[2])<<16)+(ord($buf[3])<<24));
		//var_dump($buf,$out);
		}
	if($no == 1) //jak jeden to zwrуж sam№ wartoњж
		return $out[0];
	else //a jak kilka to tablicк
		return $out;
	}
function int2toByteArray($in) //kolejnoњж zapisu bajtуw jak w pliku
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
?>