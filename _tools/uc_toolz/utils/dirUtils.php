<?php
function fixDir($dir)
	{
	if(strPos($dir,'/') !== false)
		$sl = '/';
	else
		$sl = '\\';
	if($dir[strLen($dir)-1] != $sl) //brakuj�cy slash na ko�cu �cie�ki
		$dir.= $sl;
	return $dir;
	}
function mkPath($path) //pr�ba za�o�enia ca�ej �cie�ki
	{
	if(strPos($path,'/') !== false)
		$sl = '/';
	else
		$sl = '\\';
	$steps = explode($sl,$path);
	$p = '';
	forEach($steps as $v)
		{
		$p.=$v.$sl;
		@mkDir($p);
		}
	}
?>