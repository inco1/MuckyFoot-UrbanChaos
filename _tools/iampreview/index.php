<?php
	$dir = './edited_IAM';
	$names = scanDir($dir);
	unset($names[array_search('.',$names)]);
	unset($names[array_search('..',$names)]);
	//$names = array_slice($names,0,5);
	$w = 128;
   
	forEach($names as $nv)
			{
			set_time_limit(10);
			$name = $dir.'/'.$nv;
			$fIn = fOpen($name,'rb');
			fSeek($fIn,8);
			$ims = array();
			for($j = 0; $j != 6; ++$j)
					$ims[] = imageCreateTrueColor($w,$w);
			for($i = 0; $i != ($w*$w); ++$i)
					{
					$chunk = fRead($fIn,6);
					for($j = 0; $j != 6; ++$j)
							{
							$col = ord($chunk[$j]);
							//imageSetPixel($ims[$j],($i%$w),(($w-1)-floor($i/$w)),imageColorAllocate($ims[$j],$col,$col,$col));
							imageSetPixel($ims[$j],$w-(($w-1)-floor($i/$w)),($i%$w),imageColorAllocate($ims[$j],$col,$col,$col));
							}
					}
			echo '<table><caption>',$nv,'</caption><tr>';
			for($j = 0; $j != 6; ++$j)
					echo '<th>channel '.$j.'</th>';
			echo '</tr><tr>';
			for($j = 0; $j != 6; ++$j)
					{
					imagePNG($ims[$j],'./out/'.$nv.$j.'_rot.png');
					echo '<td><img src="./out/'.$nv.$j.'_rot.png?',mt_rand(0,65535),'"></td>';
					}
			echo '</tr></table>';
			fClose($fIn);
			}
?>
