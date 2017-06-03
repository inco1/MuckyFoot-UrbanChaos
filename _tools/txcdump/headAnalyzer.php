<?php
 
	if(!@$_GET['f'])
		  $_GET['f'] = 0;
	$files = scanDir('./clumps');
	unset($files[array_search('.',$files)]);
	unset($files[array_search('..',$files)]);
	sort($files,SORT_FLAG_CASE|SORT_NATURAL);
	$fName = subStr($files[$_GET['f']],0,-4);
	 
	@mkDir('./hLogs');
	$hOut = fOpen('./hLogs/'.$fName.'.hlog','wb');
	$hIn = fOpen('./heads/'.$fName.'.head','rb');
	 
	$headData = array();
	$nonZero = array();
	for($i = 0; strLen($bfr = fRead($hIn,4)); ++$i)
		  {
		  if($bfr != "\0\0\0\0")
				$nonZero[] = $i;
		  $headData[] = $bfr;
		  }
	 
	fWrite($hOut,$fName.':'."\n\n");
	fWrite($hOut,'iloœæ pozycji:'.$i."\n");
	fWrite($hOut,'iloœæ niezerowych:'.sizeOf($nonZero)."\n");
	fWrite($hOut,'wartoœci niezerowych:'."\n\n");
	for($i = 0; $i != sizeOf($nonZero); ++$i)
		  {
		  fPrintF($hOut,"%5u (%5u):  ",$i,$nonZero[$i]);
		  for($j = 0; $j != 4; ++$j)
				fPrintF($hOut,'%\'02X ',ord($headData[$nonZero[$i]][$j]));
		  fWrite($hOut,"\n");
		  }
	 
	fClose($hIn);
	fClose($hOut);
?>

