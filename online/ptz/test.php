<?php
?>
<html>
<script type="text/javascript" src="/avreg/lib/js/third-party/jquery.js"></script>
<script>
$(function(){
	$('#add_youtube').click(function(){
		$('#video_container').append('<div class="youtube">'+
			'<iframe width="420" height="315" src="//www.youtube.com/embed/wZZ7oFKsKzY" frameborder="0" allowfullscreen>'+'
			'</iframe></div>'
		);

	});
	$('#add_video').click(function(){
		$('#video_container').append(
			'<div class="video"><video src="http://www.w3schools.com/html/movie.ogg" autoplay loop preload="none">'+
			'</video></div>'
		);

	});
	$('#add_websocket').click(function(){
		var ws=new WebSocket('ws://echo.websocket.org/');
		var el=$("<div><button>.</button></div>");
		$('#websocket_container').append(el);
		$('button',el).click(function(){
			ws.send('.');
		});
		ws.onmessage=function(e){
			el.append('<span>'+e.data+'</span>');
		};
	});
});
</script>
<head>
</head>
<body>
<button id="add_websocket">websocket echo</button>
<button id="add_youtube">youtube</button>
<button id="add_video">video</button>
<div id="websocket_container"></div>
<div id="video_container"></div>
</body>
</html>
