<?php
	$names = scanDir('./IAM');
	unset($names[array_search('.',$names)]);
	unset($names[array_search('..',$names)]);
	//$names = array_slice($names,0,5);
   
	forEach($names as $nv)
			{
			set_time_limit(10);
			$name = './IAM/'.$nv;
			$fIn = fOpen($name,'rb');
			fSeek($fIn,8);
			$ims = array();
			for($j = 0; $j != 6; ++$j)
					$ims[] = imageCreateTrueColor(128,128);
			for($i = 0; $i != (128*128); ++$i)
					{
					$chunk = fRead($fIn,6);
					for($j = 0; $j != 6; ++$j)
							{
							$col = ord($chunk[$j]);
							imageSetPixel($ims[$j],$i%128,floor($i/128),imageColorAllocate($ims[$j],$col,$col,$col));
							}
					}
			echo '<table><caption>',$nv,'</caption><tr>';
			for($j = 0; $j != 6; ++$j)
					echo '<th>channel '.$j.'</th>';
			echo '</tr><tr>';
			for($j = 0; $j != 6; ++$j)
					{
					imagePNG($ims[$j],'./out/'.$nv.$j.'.png');
					echo '<td><img src="./out/'.$nv.$j.'.png?',mt_rand(0,65535),'"></td>';
					}
			echo '</tr></table>';
			fClose($fIn);
			}
?>
