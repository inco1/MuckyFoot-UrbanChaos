var toCrunch = [];

window.onload = function()
	{
	var tabs = document.getElementById('tabList').children;
	var i;
	for(i = tabs.length; i--; )
		{
		tabs[i].tabs = tabs;
		tabs[i].onclick = function()
			{
			var tabs = this.tabs;
			var i;
			for(i = tabs.length; i--; )
				{
				tabs[i].className = '';
				document.getElementById('tab_'+tabs[i].id).className = 'hidden';
				}
			this.className = 'sel';
			document.getElementById('tab_'+this.id).className = '';
			}
		}
	tabs[0].click();
	var f = document.forms[0];
	f.tmp = document.getElementById('tmp');
	f.tmp.f = f;
	f.onsubmit = function()
		{
		var tmpF = document.forms.tmpF;
		this.go.prevText = this.go.value;
		this.go.value = 'WERKING';
		this.go.disabled = 1;
		document.getElementById('log').innerHTML = '';
		//zebranie plików do zmielenia
		var inp = document.querySelectorAll('.fileList INPUT[type="checkbox"]');
		var i;
		for(i = inp.length; i--;)
			if(inp[i].checked)
				toCrunch.push({file:inp[i].name, util:inp[i].parentNode.parentNode.className});
		//zebranie opcji
		inp = document.querySelectorAll('.optList INPUT[type="checkbox"]');
		for(i = inp.length; i--;)
			if(inp[i].checked)
				tmpF[inp[i].name].value = inp[i].value;
			else
				tmpF[inp[i].name].value = 0;
		document.forms.tmpF.go.click();
		return false;
		}
	f.tmp.onload = function()
		{
		var f = document.forms.tmpF; 
		document.getElementById('log').innerHTML += '<li>Crunched: '+f.file.value;
		f.go.click();
		}
	document.forms.tmpF.onsubmit = function()
		{
		if(toCrunch.length)
			{
			var crunchy = toCrunch.pop();
			this.file.value = crunchy.file;
			this.util.value = crunchy.util;
			}
		else
			{
			var f = document.forms[0];
			f.go.value = f.go.prevText;
			f.go.disabled = 0;
			return false;
			}
		}
	}