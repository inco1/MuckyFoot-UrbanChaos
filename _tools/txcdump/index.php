<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<title>Proto .txc dumper</title>
		<style type="text/css">
		#fileList LI
			{
			display: inline-block;
			margin: 5px;
			width: 150px;
			font-variant: small-caps;
			}
		</style>
		<script type="text/javascript">
			var crunchQueue = [];
			window.onload = function()
				{
				var f = document.forms.fMain;
				f.crunch.php = 'fileDumper';
				f.head.php = 'headAnalyzer';
				
				f.crunch.onclick = f.head.onclick = function()
					{
					var fl = document.forms.fMain.fileList;
					var i;
					for(i = fl.length; i--;)
						{
						if(fl[i].checked)
							crunchQueue.push(i);
						}
					var ifr = document.createElement('iframe');
					ifr.onload = function()
						{
						if(crunchQueue.length)
							ifr.src = this.btn.php+'.php?f='+crunchQueue.pop();
						else
							{
							this.btn.value = this.btn.oldVal;
							this.btn.disabled = false;
							this.parentNode.removeChild(this);
							}
						};
					ifr.btn = this;
					document.getElementById('tmp').appendChild(ifr);
					this.oldVal = this.value;
					this.value = 'WERKING';
					this.disabled = true;
					}
				}
		</script>
	</head>
	<body>
		<h1>Proto .txc dumper</h1>
		<form name="fMain">
			<ul id="fileList">
			<?php
			$files = scanDir('./clumps');
			unset($files[array_search('.',$files)]);
			unset($files[array_search('..',$files)]);
			sort($files,SORT_FLAG_CASE|SORT_NATURAL);
			
			forEach($files as $k => $v)
				echo '<li><input type="checkbox" name="fileList" value="',$k,'">',$v;

			?>
			</ul>
			<input type="button" name="crunch" value="dump">
			<input type="button" name="head" value="analyze head">
			<div id="tmp">
			</div>
		</form>
	</body>
</html>