<?php
function fixDir($dir)
	{
	if(strPos($dir,'/') !== false)
		$sl = '/';
	else
		$sl = '\\';
	if($dir[strLen($dir)-1] != $sl) //brakuj¹cy slash na koñcu œcie¿ki
		$dir.= $sl;
	return $dir;
	}
function mkPath($path) //próba za³o¿enia ca³ej œcie¿ki
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