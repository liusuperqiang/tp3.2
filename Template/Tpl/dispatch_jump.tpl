<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>跳转中...</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1">
<include file="./Template/Public/tpl/basecss.html" />

<link type="text/css" rel="stylesheet" href="__THEME__/infocustom.css" />
<js href="__JS__/jquery-1.11.3.js" />
<style type="text/css">
:focus{
outline:0;
}
body {
font:95%/1.5 "微软雅黑",verdana,tahoma,arial;
padding:0;
margin:0;
-webkit-text-size-adjust: 100%;
-ms-text-size-adjust: 100%;
text-size-adjust: 100%;
color:#686868;
background:#f5f5f5;
}
body, div,
h1, h2, h3, h4, h5, h6,
p, blockquote, pre, dl, dt, dd, ol, ul, li, hr,
fieldset, form, label, legend, th, td,
article, aside, figure, footer, header, hgroup, menu, nav, section,
summary, hgroup {
margin: 0;
padding: 0;
border: 0;
}
@-webkit-viewport { width: device-width; }
@-moz-viewport { width: device-width; }
@-ms-viewport { width: device-width; }
@-o-viewport { width: device-width; }
@viewport { width: device-width; }

*{ padding: 0; margin: 0; }
.system-message{ padding: 24px 48px; }
.system-message h1{ font-size: 300%; font-weight: normal; line-height:160%; margin-bottom: 12px; }
.system-message .jump{ padding-top: 10px;color:#686868;}
.system-message .jump a{ color: #686868;}
.system-message p.success,.system-message p.error{ line-height: 1.8em; font-size: 140%; }
.system-message .error{color:#f2092f;}
.system-message .detail{ font-size: 12px; line-height: 20px; margin-top: 12px; display:none}
.system-message .success{color:#25af05;}
</style>
</head>
<body>
<div class="system-message">
	<?php if(isset($message)) {?>
	<h1 class="success">:)</h1>
	<p class="success"><?php echo($message); ?></p>
	<?php }else{?>
	<h1 class="error">:(</h1>
	<p class="error"><?php echo($error); ?></p>
	<?php }?>
	<p class="detail"></p>
	<p class="jump">
	页面自动 <a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 中（<b id="wait"><?php echo($waitSecond); ?></b>）... 
	</p>
</div>

<script type="text/javascript">
(function(){
var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var interval = setInterval(function(){
	var time = --wait.innerHTML;
	if(time <= 0) {
		location.href = href;
		clearInterval(interval);
	};
}, 1000);
})();
</script>
</body>
</html>