<?php
require('dirs.php');
if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
	include('utils/'.$_POST['util'].'.php'); //dzieci, nie róbcie tego w domu
	return;
	}
$txcOpts = array
	(
	'noFFFF' => 'omit extracting indexed images (fast dump)',
	'noDump' => 'omit extracting all images',
	'noGroups' => 'omit generating image groups'
	);
?>
<!doctype html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8">
		<title>UC Toolz</title>
		<link rel="stylesheet" type="text/css" href="./interface/style.css">
		<script type="text/javascript" src="./interface/main.js"></script>
	</head>
	<body>
		<h1>UC Toolz v1.1</h1>
		<form>
			<ul id="tabList">
				<li id="txcDump">.txc dump
				<li id="txcBruteDump">.txc brute dump
				<li id="tmaDump">.tma dump
				<li id="results">Results
			</ul>
			<div id="tab_txcDump">
				<fieldset>
					<legend>File list</legend>
					<ul class="fileList">
						<?php
						$files = scanDir($txcDir);
						unset($files[array_search('.',$files)]);
						unset($files[array_search('..',$files)]);
						forEach($files as $v)
							echo '<li class="txc"><label><input type="checkbox" name="',$v,'" value="1">',$v,'</label>';
						?>
					</ul>
				</fieldset>
				<fieldset>
					<legend>Options</legend>
					<ul class="optList">
						<?php
						forEach($txcOpts as $k => $v)
							echo '<li><label><input type="checkbox" name="opt_txc_',$k,'" value="1">',$v,'</label>';
						?>
					</ul>
				</fieldset>
			</div>
			<div id="tab_txcBruteDump">
				<fieldset>
					<legend>File list</legend>
					<ul class="fileList">
						<?php
						$files = scanDir($txcDir);
						unset($files[array_search('.',$files)]);
						unset($files[array_search('..',$files)]);
						forEach($files as $v)
							echo '<li class="txcBrute"><label><input type="checkbox" name="',$v,'" value="1">',$v,'</label>';
						?>
					</ul>
				</fieldset>
			</div>
			<div id="tab_tmaDump">
				dupa
			</div>
			<div id="tab_results">
				<fieldset>
					<legend>Controls</legend>
					<input type="submit" name="go" value="GO">
					<input type="reset" value="reset">
				</fieldset>
				<fieldset>
					<legend>Log</legend>
					<ul id="log">
					</ul>
				</fieldset>
				<iframe name="tmp" id="tmp"></iframe>
			</div>
		</form>
		<form name="tmpF" action="index.php" method="POST" target="tmp">
			<input type="hidden" name="util">
			<input type="hidden" name="file">
			<input type="submit" name="go">
			<?php
			forEach($txcOpts as $k => $v)
				echo '<input type="hidden" name="opt_txc_',$k,'">';
			?>
		</form>
	</body>
</html>

