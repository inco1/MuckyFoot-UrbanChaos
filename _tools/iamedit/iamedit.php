    <?php
     
    $in = 'oval1.iam';
    $out = '1oval1.iam';
    $channels = array();
    for($j = 0; $j != 6; ++$j)
            $channels[] = imageCreateFromPNG($in.$j.'.png');
    copy($in,$out);
    $fOut = fOpen($out,'r+b');
    fSeek($fOut,8);
    for($i = 0; $i != (128*128); ++$i)
            {
            for($j = 0; $j != 6; ++$j)
                    {
                    $color = imageColorAt($channels[$j],$i%128,floor($i/128))&0xFF;
                    fWrite($fOut,chr($color));
                    }
            }
    fClose($fOut);
     
     
    ?>
