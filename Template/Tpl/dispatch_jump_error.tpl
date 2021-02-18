<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>跳转中...</title>
<js href="__JS__/jquery-1.11.3.js" />
<style type="text/css">
:focus{
outline:0;
}
body {
font:14px/1.5 "微软雅黑",verdana,tahoma,arial;
padding:0;
margin:0;
-webkit-text-size-adjust: 100%;
-ms-text-size-adjust: 100%;
text-size-adjust: 100%;
color:#686868;
background:#fff;
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
a:link {
color: #686868; 
text-decoration: none;
outline: 0;
}
a:visited {
color: #686868; 
text-decoration: none;
outline: 0;
}
a:hover {
color: #424242; 
text-decoration: none;
outline: 0;
}
a:active {
color: #424242; 
text-decoration: none;
outline: 0;
}
.box{
border:1px #a94442 solid;
width:500px;
margin:100px auto 0 auto;
border-radius:10px;
box-shadow:0 0 20px rgba(169,68,66,0.5);
padding:15px;
}
.box > h1{
font-weight:normal;
text-align:center;
font-size:22px;
line-height:40px;
border-bottom:1px #843534 solid;
}
.box > .error{
min-height:150px;
color:#f00;
font-size:16px;
text-align:center;
padding:20px 0;
}
.box > .jump{
text-align:right;
font-size:12px;
color:#686868;
}
</style>
</head>
<body>
<div class="box">
	<h1>错误信息</h1>
	<?php if(isset($message)) {?>
	<p class="success"><?php echo($message); ?></p>
	<?php }else{?>
	<p class="error"><?php echo($error); ?></p>
	<?php }?>
	<p class="detail"></p>
	<p class="jump">
	请稍后，正在<a id="href" href="<?php echo($jumpUrl); ?>">跳转</a> 中（<b id="wait"><?php echo($waitSecond); ?></b>）... 
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