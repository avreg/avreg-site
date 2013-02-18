<?php

//сортирует массив размеров
function get_resolutions($strResolutions, $orderByWidth=true){
	$sizes = array();
	$sizes = explode(',', $strResolutions);
	
	$resol = array();

	foreach ($sizes as $key=>$val){
		$tmp = array();
		$tmp = explode('x', $val);
		$tmp[0] = mb_strtoupper($tmp[0]);
		if(trim($tmp[0])=='1:1' || trim($tmp[0])=='FS'){
			array_push($resol, array('w'=>trim($tmp[0]), 'h'=>trim($tmp[0]) ));
		}else{
			array_push($resol, array('w'=>trim($tmp[0]), 'h'=>trim($tmp[1]) ));
		}
	}

	array_reverse($resol);

	//сортировка 
//	if($orderByWidth) usort($resol, 'sort_by_width');
//	else usort($resol, 'sort_by_height');

 	return  $resol;
}

//предикаты сортировки
function sort_by_width($f, $s){
	return $f['w']-$s['w'];
}

function sort_by_height($f, $s){
	return $f['h']-$s['h'];
}

//Отобразить контролы выбора разрешения
function show_select_resolution($resolutions, $select ,$strName = "Resolutions" ){
	echo "{$strName}: ";
	echo '<select id="resolution" size="1" name="resolution">'."\n";
	foreach ($resolutions as $key=>$val){
		if($val['w']=='FS' || $val['w']=='1:1'){
			echo "<option ".($key==$select?' selected ':'' )." value='{$key}'>{$val['w']}</option>\n";
		}else{
			echo "<option ".($key==$select?' selected ':'' )." value='{$key}'>{$val['w']} x {$val['h']} </option>\n";
		}
	}
	echo '</select>'."\n"; 
	
}

?>



<style>
<!-- -->
#scale{
	position:relative;
}
</style>


<script type="text/javascript" >
var scale=0;
var corr_h=0;
var isFs = false;

	$(function(){
		var expr = new RegExp('scl=\\d+', 'ig');
		var isscl = expr.test(SELF_ADR);

		//определяем текущий масштаб			
		if(isscl){
			scale = SELF_ADR.match(expr)[0];
			scale = parseInt(scale.replace('scl=', ''));
		}

		if($.trim($('#resolution option:selected').html()) =='FS'){
			isFs = true;
		}
		
		if(typeof(reload)!='undefined' && reload){
			set_full_screen();
		}
		
		//Выбор новогого разрешения
		$('#resolution').change(function(e){
			var act = SELF_ADR ;
			var toReload = true;
			scale = $('#resolution option:selected').val();

			if($.trim($('#resolution option:selected').html()) =='FS'){
				isFs = true;
				set_full_screen();
			}
			
			if(isscl){
				//если значение масштаба уже установлено 
				act= act.replace(expr, "scl=" + scale );
			}
			else{
				act +=  "&scl=" + scale;
			}

			cookie.setCookie('scl', scale, 7);
			
			if(toReload)window.open(act,'_self');
		});

		$(window).resize(function(){
			reload = true;
			set_full_screen();

		});

	});

	//Установка полноэкранного режима
	set_full_screen=function(){
		if(!isFs){
			return;
		}

			var act = SELF_ADR ;
			var avail_h = parseInt(($.browser.msie)?ietruebody().clientHeight:window.innerHeight)-corr_h;
			var avail_w = parseInt(($.browser.msie)?ietruebody().clientWidth:window.innerWidth);

			avail_h -=parseInt(avail_h*1.1/100);
			avail_w -=parseInt(avail_w*1.1/100);
			
			var ea_h = new RegExp('ah=\\d+', 'ig');
			var ea_w = new RegExp('aw=\\d+', 'ig');
			var is_ea_h = ea_h.test(SELF_ADR);
			var is_ea_w = ea_w.test(SELF_ADR);
	
			if(is_ea_h){
				//если значение масштаба уже установлено 
				act= act.replace(ea_h, "ah=" +avail_h );
			}
			else{
				act +=  "&ah=" + avail_h;
			}
			if(is_ea_w){
				//если значение масштаба уже установлено 
				act= act.replace(ea_w, "aw=" +avail_w );
			}
			else{
				act +=  "&aw=" + avail_w;
			}

			if(typeof(reload)!='undefined' && reload){
//				window.open(act,'_self');
				
				var rht = new RegExp('height\\=(\\d+|1\\:1|FS)','ig');
				var rh = new RegExp('h\\=(\\d+|1\\:1|FS)','ig');
				var rwt = new RegExp('width\\=(\\d+|1\\:1|FS)','ig');
				var rw = new RegExp('w\\=(\\d+|1\\:1|FS)','ig');
					
				$('.cam_snapshot').each(function(){
					var src = $(this).attr('src');
					$(this).height(avail_h).width(avail_w);
					
					src = src.replace(rht, "height="+avail_h );
					src = src.replace(rh, "h="+avail_h );
					src = src.replace(rwt, "width="+avail_w );
					src = src.replace(rw, "w="+avail_w );
					
					$(this).attr('src', src).show();
					
				});
			}
		};

	

	var cookie = {
			getCookie : function(c_name)
			{
				var i,x,y,ARRcookies=document.cookie.split(";");
				for (i=0;i<ARRcookies.length;i++)
				  {
				  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
				  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
				  x=x.replace(/^\s+|\s+$/g,"");
				  if (x==c_name)
				    {
				    return unescape(y);
				    };
				  };
			},

			setCookie : function(c_name,value,exdays)
			{
				var exdate=new Date();
				exdate.setDate(exdate.getDate() + exdays);
				var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
				document.cookie=c_name + "=" + c_value;
			}

	};


	function ietruebody(){
		   return (document.compatMode &&
		      document.compatMode!='BackCompat')? document.documentElement : document.body;
		}
			

	
</script>
