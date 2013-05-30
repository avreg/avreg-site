/**
 * @file online/view.js
 * @brief JS скрипт страницы наблюдения в реальном времени
 * Содержит функции и глобальные переменные, обеспечивающие соответствующий функционал,
 * осуществляет инициализацию страницы
 */


/* global variables */
///Элемент, в который выводятся раскладка камер
var CANVAS;
///Ширина эл-та CANVAS
var CANVAS_W = -1;
///Высота эл-та CANVAS
var CANVAS_H = -1;

///Элемент раскладки камер
var WIN_DIVS;

///значение left-позиции в раскладке развернутой ячейки
var WIN_DIV_LEFT;
///значение top-позиции в раскладке развернутой ячейки
var WIN_DIV_TOP;
///значение ширины в раскладке развернутой ячейки
var WIN_DIV_W;
///значение высоты в раскладке развернутой ячейки
var WIN_DIV_H;
///значение ширины в раскладке элемента отображения потокового видео
var IMG_IN_DIV_W;
///значение высоты в раскладке элемента отображения потокового видео
var IMG_IN_DIV_H;
///текущий режим отображения : полноэкранный/в ячейке раскладки  
var FS_WIN_DIV;

///Высота элемента в кот. выводится название камеры
var NAME_DIV_H = PrintCamNames?25:0;
var CUR_WIN_HEADER_H;

///корректировка размеров контейнера для плеера
var CORRECT_H = 4; var CORRECT_W = 4; 

/// Статусы камер
var CAM_STATUSES;
///XMLHttpRequest object
var CAM_STATUS_REQ;
/// подписка на отслеживание статусов камер
var SUBSCRIBE = true;

var imgs = new Array();

$(document).ready( function() {
	if(MSIE){
		CORRECT_H = CORRECT_W = 0;
	}
	//Кнопки свернуть/развернуть
	imgs['fs'] = new Image();
	imgs['fs'].src =  "../img/fs.png";
	imgs['tc'] = new Image();
	imgs['tc'].src =  "../img/tc.png";
	//Кнопка включения/выключения тулбар
	imgs['controlsOnOff_on'] = new Image();
	imgs['controlsOnOff_on'].src =  "../img/slide1.png";
	imgs['controlsOnOff_off'] = new Image();
	imgs['controlsOnOff_off'].src =  "../img/slide2.png";
	//плэй
	imgs['pl_start'] = new Image();
	imgs['pl_start'].src =  "../img/StepForwardNormalBlue.png";
	//стоп
	imgs['pl_stop'] = new Image();
	imgs['pl_stop'].src =  "../img/Stop1NormalBlue.png";
	//масштаб - +/-
	imgs['pl_plus'] = new Image();
	imgs['pl_plus'].src =  "../img/ZoomIn.png";
	imgs['pl_minus'] = new Image();
	imgs['pl_minus'].src =  "../img/ZoomOut.png";

	imgs['original_size'] = new Image();
	imgs['original_size'].src =  "../img/1to1.png";
	
	imgs['normal_size'] = new Image();
	imgs['normal_size'].src =  "../img/expand.png";
	
	//Соединение с сервером потеряно
	imgs['connection_fail'] = new Image();
	imgs['connection_fail'].src =  "../img/ConnectionFail.jpg";
	
	
	//Запуск сценария	   
	fill_canvas();
	
	if(MSIE){
		$('body').css({
			'background-image': "url('../img/BG.png')",
			'background-repeat': 'repeat',
			'background-size': 'cover'
			});
	};
});



/**
 * Обработчик события mouseover для элементов раскладки камер.
 * Обеспечивает формирование и вывод tooltip
 * @param cell - элемент раскладки
 * @param win_nr - номер элемента раскладки
 */
function img_mouseover(cell, win_nr) {

	if(!conf_debug)return;
   if ( WINS_DEF[win_nr] == undefined ) return;

   var img_jq = $('.pl_cont',cell);

   var cam_nr = WINS_DEF[win_nr].cam.nr;
   var orig_w = WINS_DEF[win_nr].cam.orig_w;
   var orig_h = WINS_DEF[win_nr].cam.orig_h;
//   var url = WINS_DEF[win_nr].cam.url;
   var val = ((WINS_DEF[win_nr].cam.url).split('?'))[0];


   hint = '<table style="font-weight:bold;" cellspacing="0" border="0" cellpadding="1"><tbody><tr>\n' +
      '<td align="right">Камера:<\/td>\n' +
      '<td>#'+cam_nr+' ' +  WINS_DEF[win_nr].cam.name + '<\/td>\n' +
      '<\/tr><tr>\n' +
      '<td align="left">URL:<\/td>\n' +
      '<td>'+val+'<\/td>\n' +
      '<\/tr><tr>\n' +
      '<td align="right">Размер:<\/td>\n' +
      '<td>'+orig_w+'x'+orig_h+' (исходный), ' + img_jq.width()+'x'+img_jq.height()+' (на экране)<\/td>\n' +
      '<\/tr><\/tbody><\/table>\n';

   ddrivetip();
}

/**
 * Обработчик события click по элементу раскладки камер.
 * Если включен режим раскладки разворачивает контекстный елемент в полноэкранный режим.
 * Если включен полноэкранном режим - востанавливает режим раскладки.
 * @param clicked_div - элемент раскладки камер, по кот. осуществлен клик
 */

function img_click(clicked_div) {
	var pl_cont = $('.pl_cont',clicked_div);
	
   var tmp_div;
   var clicked_div_jq = $(clicked_div);
   var win_geo;
   var i;
   //номер ячейки
   var win_nr = parseInt(($(clicked_div).attr('id')).match(/\d+/gi));

   //устанавливаемый src
   var current_src=null;
   //если номер камеры не определен
   if(win_nr == null ){ return;}
   if ( FS_WIN_DIV ) {
      // current - fullscreen
	      //меняем на источник для ячейки
	      if (active_cams_srcs[win_nr]['type']!='avregd'){
	    	  if(active_cams_srcs[win_nr]['cell']!=null && active_cams_srcs[win_nr]['cell']!='')
	    		  current_src = active_cams_srcs[win_nr]['cell']; // get_cam_alt_url(active_cams_srcs[win_nr]['cell'], win_nr, true) ;
	      }
	   

      if ( WIN_DIV_W == undefined ) {
    	  //в режиме FS был ресайз CANVAS'a
         change_wins_geo();
         
		   //если в режиме просмотра одной камеры происходил ресайз окна браузера
//         if ( MSIE ){
//        	 if(current_src!=null) $('.pl_cont',clicked_div_jq).aplayerSetMjpegSrc( current_src ) ;
//          }else{
//        	  if(current_src!=null) $('.pl_cont',clicked_div_jq).aplayerSetMjpegSrc( current_src );
//          }
          //Переустанвливаем плеер для алтернативного источника
          if(current_src!=null){
              $('.pl_cont',clicked_div_jq).addPlayer({
                  'src': current_src ,
                  'controls': false,
                  'scale':'on',
                  'mediaType' : 'mjpeg',
                  'autostart':'on',
                  'aplayer_rtsp_php':'../lib/js/aplayer_rtsp.php',
                  'crossorigin' : (WEBKIT)? true:false,
                  'amc_onclick' : function(player_nr){
                      img_click(document.getElementById('win'+win_nr));
                  }
              });
          }


         
      } else {
    	  //востанавливаем исходные размеры отображения камеры
     	 var border_w = clicked_div.offsetWidth - clicked_div.clientWidth;
          var border_h = clicked_div.offsetHeight - clicked_div.clientHeight;
          $(clicked_div)
          .width(WIN_DIV_W + border_w )
          .height(WIN_DIV_H + border_h);
          
          $('.pl_cont',clicked_div_jq)
 	      .width(IMG_IN_DIV_W)
 	      .height(IMG_IN_DIV_H);

          if ( MSIE ){
      		$('.pl_cont',clicked_div_jq)
      		.aplayerSetSize({'height':IMG_IN_DIV_H, 'width': IMG_IN_DIV_W});
//      		if(current_src!=null)  $('.pl_cont',clicked_div_jq).aplayerSetMjpegSrc( current_src ) ;
          }else{
         	 $('.pl_cont',clicked_div_jq)
         	 .aplayerResizeToParent();
//         	if(current_src!=null) $('.pl_cont',clicked_div_jq).aplayerSetMjpegSrc( current_src );
          }

          //Переустанвливаем плеер для алтернативного источника
          if(current_src!=null){
              $('.pl_cont',clicked_div_jq).addPlayer({
                  'src': current_src ,
                  'controls': false,
                  'scale':'on',
                  'mediaType' : 'mjpeg',
                  'autostart':'on',
                  'aplayer_rtsp_php':'../lib/js/aplayer_rtsp.php',
                  'crossorigin' : (WEBKIT)? true:false,
                  'amc_onclick' : function(player_nr){
                      img_click(document.getElementById('win'+win_nr));
                  }
              });
          }

          $(clicked_div).css({'left': WIN_DIV_LEFT + 'px', 'top': WIN_DIV_TOP + 'px' });
         
      }
      for (i=0;i<WIN_DIVS.length;i++) {
          tmp_div=WIN_DIVS[i];
          if ( tmp_div == clicked_div ){
         	 continue;
          }else{
         	 //отображаем остальные камеры
         	 $(tmp_div).show();
          }
       }
  	//меняем кнопку на Развернуть
      $('img.fs_tc', '#cell_header_'+win_nr)
      .height($('#cell_header_'+win_nr).height()-4)
      .attr({
      	'src': imgs['fs'].src,
      	'title':strToolbarControls['max']
      });

      set_win_header_size(clicked_div_jq, CUR_WIN_HEADER_H);
      
       FS_WIN_DIV = undefined;
      
   } else {
	 //Если включен режим - просмотра камер в раскладке
      // current - NO fullscreen
	      for (i=0;i<WIN_DIVS.length;i++) {
	          tmp_div=WIN_DIVS[i];
	          if(tmp_div == clicked_div ){
	         	 continue;
	          }else{
	         	 //прячем остальные камеры
	         	 $(tmp_div).hide();
	          }
	       }

	  WIN_DIV_H = clicked_div.clientHeight;
      WIN_DIV_W = clicked_div.clientWidth;
      WIN_DIV_LEFT=clicked_div.offsetLeft;
      WIN_DIV_TOP=clicked_div.offsetTop;
      IMG_IN_DIV_W=pl_cont.width();
      IMG_IN_DIV_H=pl_cont.height();
      //Сохраняем текущее значение высоты хидера
      CUR_WIN_HEADER_H = $('.cell_header', clicked_div_jq).height();
      if(CUR_WIN_HEADER_H==null)CUR_WIN_HEADER_H=0;

      if(NAME_DIV_H==null)NAME_DIV_H=0;
      set_win_header_size(clicked_div_jq, NAME_DIV_H);

      win_geo = new calc_win_geo(CANVAS_W, CANVAS_H, CamsAspectRatio, 1, 1, 1);
      //alert(win_geo.cam_h +'\n'+win_geo.cam_w);
      clicked_div_jq.css('top',  calc_win_top (win_geo, 0));
      clicked_div_jq.css('left', calc_win_left(win_geo, 0));
  
      $('.pl_cont',clicked_div_jq)
	      .width(win_geo.cam_w+CORRECT_W)
	      .height(win_geo.cam_h+CORRECT_H);
      //меняем на источник для ячейки
      if (active_cams_srcs[win_nr]['type']!='avregd'){
    	  if(active_cams_srcs[win_nr]['fs']!=null && active_cams_srcs[win_nr]['fs']!='')
    		  current_src = active_cams_srcs[win_nr]['fs']; //get_cam_alt_url(active_cams_srcs[win_nr]['fs'], win_nr ,true) ;
      }

    	if ( MSIE ){
    		$(clicked_div_jq).width(win_geo.win_w+CORRECT_W).height(win_geo.win_h+CORRECT_H);
    		$('.pl_cont',clicked_div_jq)
    		.aplayerSetSize({'height':win_geo.cam_h+CORRECT_H, 'width': win_geo.cam_w+CORRECT_W});
//    		if(current_src!=null) {
//    			$('.pl_cont',clicked_div_jq).aplayerSetMjpegSrc( current_src );
//    		}
    		
        }else{
        	$(clicked_div_jq).width(win_geo.win_w).height(win_geo.win_h);
        	$('.pl_cont',clicked_div_jq)
        	.aplayerResizeToParent();
//        	if(current_src!=null){
//        		$('.pl_cont',clicked_div_jq).aplayerSetMjpegSrc( current_src );
//        	}
        }

       //Переустанвливаем плеер для алтернативного источника
       if(current_src!=null){
           $('.pl_cont',clicked_div_jq).addPlayer({
               'src': current_src ,
               'controls': false,
               'scale':'on',
               'mediaType' : 'mjpeg',
               'autostart':'on',
               'aplayer_rtsp_php':'../lib/js/aplayer_rtsp.php',
               'crossorigin' : (WEBKIT)? true:false,
               'amc_onclick' : function(player_nr){
                   img_click(document.getElementById('win'+win_nr));
               }
           });
       }
    	
    	//меняем кнопку на Свернуть
        $('img.fs_tc', '#cell_header_'+win_nr)
        .height($('#cell_header_'+win_nr).height()-4)
        .attr({
        	'src': imgs['tc'].src,
        	'title':strToolbarControls['min']
        });
        
      FS_WIN_DIV = clicked_div;
   }
   
   //Устанавливаем текущий масштаб
   var aplayer_id=$('.aplayer',pl_cont).attr('id');
   
   if( controls_handlers.original_size[aplayer_id]!=null && controls_handlers.original_size[aplayer_id] ){
	   $('#'+aplayer_id).parent().aplayerMediaSetSrcSizes();
   }
   
   var scl = $.aplayer.scale[aplayer_id];
   $.aplayer.scale[aplayer_id]=0;
   if(scl>0){
	   for(i=0;i<scl;i++){
		   $.aplayer.zoomIn(aplayer_id);
	   }
   }
   else if(scl<0){
	   scl*=-1;
	   for(i=0;i<scl;i++){
		   $.aplayer.zoomOut(aplayer_id);
	   }

   }

    //проверка связи с камерами
    if(GECKO || WEBKIT)	checking_connection.init_check();
   
} // img_click()

/**
 * Изменяет высоту заголовка ячейки при смене режима полноэкранный/раскладка
 * @param clicked_div_jq
 * @param header_height
 */
function set_win_header_size(clicked_div_jq, header_height){
	if (PrintCamNames && $('.cell_header', clicked_div_jq).height()!= header_height ) {
		var header = $('.cell_header', clicked_div_jq);
		$(header).height(header_height); //Высота заголовка
		$('span', header).css({'top': (header_height/2 - 14)+'px'}).end(); //Название камеры
		var ht =header_height -6;
		$(header).find('img').height(ht); //ToolBar
	}
}



/**
 * Функция осуществляет инициализацию и установку элемента,
 * реализующего воспроизведение видеопотока с заданной камеры,
 * в соответствующую ячейку раскладки камер
 * @param win_nr - номер устанавливаемой ячейки
 * @param win_div - элемент устанавливаемой ячейки
 * @param win_geo - объект, содержащий параметры элемента(размеры, смещение и т.п.)
 */
function brout(win_nr, win_div, win_geo) {
   if ( WINS_DEF[win_nr] == undefined ) return;
   var cam_nr = WINS_DEF[win_nr].cam.nr;
//   var id='cam'+cam_nr;
//   var orig_w = WINS_DEF[win_nr].cam.orig_w;
//   var orig_h = WINS_DEF[win_nr].cam.orig_h;
   var url = WINS_DEF[win_nr].cam.url;

   //Установка плеера в элемент  // win_geo.cam_h 
   var cont = $('<div class="pl_cont" />').width(win_geo.cam_w+CORRECT_W).height(win_geo.cam_h+CORRECT_H);
   
	$(win_div).append(cont);
    $(cont).addPlayer({
		'src': url , 
		'controls': false, 
		'scale':'on', 
		'mediaType' : 'mjpeg', 
		'autostart':'on', 
		'aplayer_rtsp_php':'../lib/js/aplayer_rtsp.php',
		'crossorigin' : (WEBKIT)? true:false,
        'amc_onclick' : function(player_nr){
            img_click(document.getElementById('win'+win_nr));
         }
	});

	if ( MSIE ){
		$(win_div).width(win_geo.win_w+CORRECT_W).height(win_geo.win_h+CORRECT_H);
		$('.pl_cont',cont).aplayerSetSize({'height':win_geo.cam_h+CORRECT_H+2 , 'width': win_geo.cam_w+CORRECT_W+2});

    }else{
	   $(cont).aplayerResizeToParent();
    }
   
	//установка обработчика клика по изображению камеры
	$(win_div).click( function(e) {
	if ( typeof(e.target) == "undefined" || typeof(e.target.tagName) == "undefined" )
    	return img_click(this);
	else {
		if ( e.target.tagName != 'A')
        	return img_click(this);
        }
	});
	   
	   //установка тултипа
      $(win_div).bind('mouseover', function() { img_mouseover(this, win_nr);} );
      win_div.mouseout( function() { hideddrivetip(); } ); 
   
}

   /**
    * 
    * Функция, которая возвращает ссылку на просмотр видео с альтернативных камер камеры 
    * аналог php-функции из lib/get_cam_url.php
    * @param alt_src альтернативный источник
    * @param bool append_abenc аутентификация пользователя
    * @return string адрес видео с камеры
    */
   function get_cam_alt_url(alt_src, $cam_nr, append_abenc){
   	   var url = alt_src;
   	   if(url==null)return null;
   	   reg = /\?camera=\d*/;
   	  if(!reg.test(url)){
   		 url += "?camera="+$cam_nr;  
   	   }
   	   if (append_abenc && user_info_USER.length>0 ) {
   		url += '&ab='+___abenc;
   	   }
   	   return url;
   }


var checking_connection = {
	timer : null,
	me_list : null,
	reconnect_timeout : 0,
	reconnect : null,
	set_handlers : null,
	is_reconnect_active : true,
	
	//инициализировать проверку соединений
	init_check : function(){
		var self = this;
		var timer_callback=null;
		
		//установка обработчиков по типу браузера
		if(!WEBKIT && GECKO){
			self.reconnect = self.reconnect_gecko;
			self.set_handlers = self.set_handlers_gecko;
			timer_callback = self.check_cams_connection_gecko;
		}
        else if(WEBKIT){
        	self.reconnect = self.reconnect_webkit;
            self.set_handlers = self.set_handlers_webkit;
			timer_callback = self.check_cams_connection_webkit;
        }
        
		for(var i in layouts_list){
			if(layouts_list[i]['MON_NR']==cur_layout){
				self.reconnect_timeout = layouts_list[i].RECONNECT_TOUT * 1000;
				break;
			}
		}
		
		//если реконнект отключен
		if(self.reconnect_timeout<100){
			self.is_reconnect_active = false;
			self.reconnect_timeout = online_check_period*1000;
		}
		
		clearInterval(self.timer);
		if(self.me_list != null){
			$.each(self.me_list, function(i, val){
				$(val.me)
				.unbind('load')
				.unbind('error');
			});
		}
		delete self.me_list;
		
		self.me_list = null;
		
		$.each(WIN_DIVS, function(i, val){
			self.add_element(val);
		});
		
		//Запуск таймера
		self.timer = setInterval(timer_callback, self.reconnect_timeout);
		
	},

	//добавить элемент для проверки
	add_element : function (win){
		var self = this;
		
		var me = $('img.ElMedia', win); 
		var me_id = $(me).attr('id');
		var me_src = $(me).attr('src');
		
		if(self.me_list==undefined){
			self.me_list = new Array();
		}

        var obj = {
			'me' : me,
			'me_id':me_id,
			'src' : me_src,
			'check_val' : 0,
			'stoped' : false,
			'connection_fail' : false,
			//канвас и контекст для webkit
			'wk_canvas' : null
		};

        //Если не mjpeg останавливаем проверку
        if(!$('.pl_cont',win).aplayerIsMjpegImage()){
            obj.stoped = true;
            self.me_list.push(obj);
            return;
        }

        if(WEBKIT){
			//создаем канвас для элемента и устанавливаем cors для img
       		obj.wk_canvas = document.createElement('canvas');
		}
		
		self.me_list.push(obj);

        if(WEBKIT || GECKO){
			self.set_handlers(me);
		}

    },

	//возобновить проверку элемента
	start_check_me : function(element){
		var self = this;
		var me = $(element).hasClass('ElMedia')? element: $('img.ElMedia', element); 
		var me_id = $(me).attr('id');

		for(var i in self.me_list){
			if(self.me_list[i].me_id == me_id){
				self.me_list[i].stoped = false;
				self.me_list[i].connection_fail = false;
				self.set_handlers(me);
			}
		}
	},
	
	//не проверять элемент
	stop_check_me : function(element){
		var self = this;
		var me = $('img.ElMedia', element); 
		var me_id = $(me).attr('id');
		
		for(var i in self.me_list){
			if(self.me_list[i].me_id == me_id){
				self.me_list[i].stoped = true;
				$(self.me_list[i].me)
				.unbind('load')
				.unbind('error');
			}
		}
	},
	
    //хеширование
	crc32_table : false,
    crc32 : function(str, crc) { 
    	var table ='';
    	if(!this.crc32_table){
    		//инициализация таблицы хеширования
	 	  	table+= "00000000 77073096 EE0E612C 990951BA 076DC419 706AF48F E963A535 9E6495A3 0EDB8832 79DCB8A4 ";
	 	  	table+= "E0D5E91E 97D2D988 09B64C2B 7EB17CBD E7B82D07 90BF1D91 1DB71064 6AB020F2 F3B97148 84BE41DE ";
	 	  	table+= "1ADAD47D 6DDDE4EB F4D4B551 83D385C7 136C9856 646BA8C0 FD62F97A 8A65C9EC 14015C4F 63066CD9 ";
	 	  	table+= "FA0F3D63 8D080DF5 3B6E20C8 4C69105E D56041E4 A2677172 3C03E4D1 4B04D447 D20D85FD A50AB56B ";
	 	  	table+= "35B5A8FA 42B2986C DBBBC9D6 ACBCF940 32D86CE3 45DF5C75 DCD60DCF ABD13D59 26D930AC 51DE003A ";
	 	  	table+= "C8D75180 BFD06116 21B4F4B5 56B3C423 CFBA9599 B8BDA50F 2802B89E 5F058808 C60CD9B2 B10BE924 ";
	 	  	table+= "2F6F7C87 58684C11 C1611DAB B6662D3D 76DC4190 01DB7106 98D220BC EFD5102A 71B18589 06B6B51F ";
	 	  	table+= "9FBFE4A5 E8B8D433 7807C9A2 0F00F934 9609A88E E10E9818 7F6A0DBB 086D3D2D 91646C97 E6635C01 ";
	 	  	table+= "6B6B51F4 1C6C6162 856530D8 F262004E 6C0695ED 1B01A57B 8208F4C1 F50FC457 65B0D9C6 12B7E950 ";
	 	  	table+= "8BBEB8EA FCB9887C 62DD1DDF 15DA2D49 8CD37CF3 FBD44C65 4DB26158 3AB551CE A3BC0074 D4BB30E2 ";
	 	  	table+= "4ADFA541 3DD895D7 A4D1C46D D3D6F4FB 4369E96A 346ED9FC AD678846 DA60B8D0 44042D73 33031DE5 ";
	 	  	table+= "AA0A4C5F DD0D7CC9 5005713C 270241AA BE0B1010 C90C2086 5768B525 206F85B3 B966D409 CE61E49F ";
	 	  	table+= "5EDEF90E 29D9C998 B0D09822 C7D7A8B4 59B33D17 2EB40D81 B7BD5C3B C0BA6CAD EDB88320 9ABFB3B6 ";
	 	  	table+= "03B6E20C 74B1D29A EAD54739 9DD277AF 04DB2615 73DC1683 E3630B12 94643B84 0D6D6A3E 7A6A5AA8 ";
	 	  	table+= "E40ECF0B 9309FF9D 0A00AE27 7D079EB1 F00F9344 8708A3D2 1E01F268 6906C2FE F762575D 806567CB ";
	 	  	table+= "196C3671 6E6B06E7 FED41B76 89D32BE0 10DA7A5A 67DD4ACC F9B9DF6F 8EBEEFF9 17B7BE43 60B08ED5 ";
	 	  	table+= "D6D6A3E8 A1D1937E 38D8C2C4 4FDFF252 D1BB67F1 A6BC5767 3FB506DD 48B2364B D80D2BDA AF0A1B4C ";
	 	  	table+= "36034AF6 41047A60 DF60EFC3 A867DF55 316E8EEF 4669BE79 CB61B38C BC66831A 256FD2A0 5268E236 ";
	 	  	table+= "CC0C7795 BB0B4703 220216B9 5505262F C5BA3BBE B2BD0B28 2BB45A92 5CB36A04 C2D7FFA7 B5D0CF31 ";
	 	  	table+= "2CD99E8B 5BDEAE1D 9B64C2B0 EC63F226 756AA39C 026D930A 9C0906A9 EB0E363F 72076785 05005713 ";
	 	  	table+= "95BF4A82 E2B87A14 7BB12BAE 0CB61B38 92D28E9B E5D5BE0D 7CDCEFB7 0BDBDF21 86D3D2D4 F1D4E242 ";
	 	  	table+= "68DDB3F8 1FDA836E 81BE16CD F6B9265B 6FB077E1 18B74777 88085AE6 FF0F6A70 66063BCA 11010B5C ";
	 	  	table+= "8F659EFF F862AE69 616BFFD3 166CCF45 A00AE278 D70DD2EE 4E048354 3903B3C2 A7672661 D06016F7 ";
	 	  	table+= "4969474D 3E6E77DB AED16A4A D9D65ADC 40DF0B66 37D83BF0 A9BCAE53 DEBB9EC5 47B2CF7F 30B5FFE9 ";
	 	  	table+= "BDBDF21C CABAC28A 53B39330 24B4A3A6 BAD03605 CDD70693 54DE5729 23D967BF B3667A2E C4614AB8 ";
	 	  	table+= "5D681B02 2A6F2B94 B40BBE37 C30C8EA1 5A05DF1B 2D02EF8D";     
	 	  	this.crc32_table = table;
    	}else{
    		table = this.crc32_table;
    	}
    	
        if( crc == window.undefined ) crc = 0; 
        var n = 0; //a number between 0 and 255 
        var x = 0; //an hex number 
 
        crc = crc ^ (-1); 
        for( var i = 0, iTop = str.length; i < iTop; i++ ) { 
            n = ( crc ^ str.charCodeAt( i ) ) & 0xFF; 
            x = "0x" + table.substr( n * 9, 8 ); 
            crc = ( crc >>> 8 ) ^ x; 
        } 
        return crc ^ (-1); 
    },

	
	//>>>>>>>>>>>>>>>>>>>>>>WEBKIT<<<<<<<<<<<<<<<<<<<<<<<<<<
	
    //коллбэк таймера - проверяет соединения для WEBKIT
    check_cams_connection_webkit : function(){
    	var self = checking_connection;
        for(index = 0; index<self.me_list.length; index++){



        	if(self.me_list[index].stoped || self.me_list[index].connection_fail) continue;

            //проверяем изменилось ли изображение

            var isFail = self.is_fail_connection_webkit(index);

 			if( isFail ){
            	$(self.me_list[index].me)
				.unbind('load')
				.attr('src', imgs['connection_fail'].src);
                self.me_list[index].connection_fail = true;
                
                if(self.is_reconnect_active)self.reconnect(index);
                
	            //активируем кнопку play
	            var me_id = $(self.me_list[index].me).attr('id');
				var win_nr = parseInt($("div.[name=win]:has(#"+me_id+")").attr('id').replace('win', '') );
	            if(!isNaN(parseInt(win_nr))){
	            	controls_handlers.activate_btn_play(win_nr);
	            }
			}
		}


    },
    
 	//Возвращает битмап изображения (для WEBKIT)
	get_bitmap : function(index){
		var self = this;
		var img_id = self.me_list[index].me_id;
		var imgObj = document.getElementById(img_id);
		var canvas = self.me_list[index].wk_canvas;
		var context = canvas.getContext('2d');


		var img_h = imgObj.naturalWidth;
	    var img_w = imgObj.naturalHeight;
        //Если натуральные размеры не определены возвращаем код ошибки 0
        if(img_h==0 || img_w==0){
           return 0;
        }
        canvas.height = img_h;
        canvas.width = img_w;
        context.drawImage(imgObj, 0,0);

        var imageData = context.getImageData(0, 0, img_w, img_h);
        var data = imageData.data;

		return data;
	},
	

	//проверяет произшел ли сбой (для WEBKIT)
	is_fail_connection_webkit : function(index){
		var self = this;
		var res = false;
		var cur_bmp = self.get_bitmap(index);

		//Если получили ноль(код ошибки) - возвращаем 'сбой связи'
		if(cur_bmp==0){
			return true;
		}
		var chq_val = ""; // контрольное значение 

		//Проверяем 50 точек
		var step= Math.floor(cur_bmp.length/50);
		for(var i=0; i<(cur_bmp.length-4);){
			chq_val += cur_bmp[i++];
			chq_val += cur_bmp[i++];
			chq_val += cur_bmp[i++];
			chq_val += cur_bmp[i++];
			i+=step; 
		}
		chq_val.toString();
		chq_val = self.crc32(chq_val);
		
		//Сравниваем контрольные значения 
		if(self.me_list[index].check_val==0){
			res = false;
		}
		else{
			res = (chq_val == self.me_list[index].check_val);
		}
		//Сохраняем текущее контрольное значение 
		self.me_list[index].check_val = chq_val;
		return res;
	},
	
	//Используется только для проверки состояния связи на начальном этапе
	set_handlers_webkit : function(me){
		var self = this;
		var me_id = $(me).attr('id');
		var index = 0;
		for(index in self.me_list){
			if(self.me_list[index].me_id == me_id){
				break;
			}
		}

		//Элемент для проверки связи
		var test_con = me;
		$(test_con).bind('error', function(){
			$(me).attr('src', imgs['connection_fail'].src);
            self.me_list[index].connection_fail = false;

            //активируем кнопку play
			var win_nr = parseInt($("div.[name=win]:has(#"+me_id+")").attr('id').replace('win', '') );
            if(!isNaN(parseInt(win_nr))){
            	controls_handlers.activate_btn_play(win_nr);
            }
            
		});

		$(test_con).bind('load',function(){
			self.me_list[index].connection_fail = false;
		});

	},
	
	//попытка реконнекта
	reconnect_webkit : function(index){
		var self = this;
		var me = self.me_list[index].me;
		var me_id = $(me).attr('id');
		var im =null;
		
		if(self.me_list[index].tset_img==undefined){
			self.me_list[index].tset_img = new Image();	
		}
		
		im = self.me_list[index].tset_img;

		//Сбой переподключения
		$(im).bind('error', function(){
			//отключение обработчиков
			$(im)
			.unbind('load')
			.unbind('error');
			self.me_list[index].connection_fail = false;
		});

		//Успешное переподключение
		$(im).bind('load', function(){
			//восстановление воспроизведения
			$(me).attr('src', self.me_list[index].src);
			self.start_check_me(me);
			//отключение обработчиков
			$(im)
			.unbind('load')
			.unbind('error');
            
            self.me_list[index].connection_fail = false;
            //деактивируем кнопку play, активируем кнопку stop 
			var win_nr = parseInt($("div.[name=win]:has(#"+me_id+")").attr('id').replace('win', '') );
	        if(!isNaN(parseInt(win_nr))){
	           	controls_handlers.activate_btn_stop(win_nr);
	        }
		});
		
		var par = (self.me_list[index].src.indexOf('?')!=-1)? "&dummy=" : "?&dummy=";
		par += Math.random();
		im.src = self.me_list[index].src+par;
	},
    
	//>>>>>>>>>>>>>>>>>>>>>>GECKO<<<<<<<<<<<<<<<<<<<<<<<<<<
	
	//коллбэк таймера - проверяет соединения для GECKO
	check_cams_connection_gecko : function (){
		var self = checking_connection;
		for(var index = 0; index<self.me_list.length; index++){
            if(self.me_list[index].stoped || self.me_list[index].connection_fail) continue;
			else if( self.me_list[index].check_val == 0 ){ //нет событий onLoad -  ошибка
            	$(self.me_list[index].me)
					.unbind('load')
					.attr('src', imgs['connection_fail'].src);
                self.me_list[index].connection_fail = true;
                if(self.is_reconnect_active)self.reconnect(index);

                //активируем кнопку play
                var me_id = $(self.me_list[index].me).attr('id');
				var win_nr = parseInt($("div.[name=win]:has(#"+me_id+")").attr('id').replace('win', '') );
		        if(!isNaN(parseInt(win_nr))){
		        	controls_handlers.activate_btn_play(win_nr);
				}
			}
			else{}
				self.me_list[index].check_val = 0;
		}
	},
	
	//установка обработчиков на элемент
	set_handlers_gecko : function(me){
		var self = this;
		var me_id = $(me).attr('id');
		var index = 0;
		for(index in self.me_list){
			if(self.me_list[index].me_id == me_id){
				break;
			}
		}
		
		$(me).bind('error', function(){
			$(me)
			.unbind('load')
			.attr('src', imgs['connection_fail'].src);
            self.me_list[index].connection_fail = true;
            self.reconnect(index);

            //активируем кнопку play
			var win_nr = parseInt($("div.[name=win]:has(#"+me_id+")").attr('id').replace('win', '') );
            if(!isNaN(parseInt(win_nr))){
            	controls_handlers.activate_btn_play(win_nr);
            }

		});

		$(me).bind('load',function(){
			self.me_list[index].check_val++;
		});
		
		$(me).bind('abort', function(){
			$(me)
			.unbind('load')
			.attr('src', imgs['connection_fail'].src);
            self.me_list[index].connection_fail = true;
            // self.reconnect(index);
            //активируем кнопку play
			var win_nr = parseInt($("div.[name=win]:has(#"+me_id+")").attr('id').replace('win', '') );
            if(!isNaN(parseInt(win_nr))){
            	controls_handlers.activate_btn_play(win_nr);
            }
		});

		
	},
	
	//попытка реконнекта
	reconnect_gecko : function(index){
		var self = this;
		var me = self.me_list[index].me;
		var me_id = $(me).attr('id');
		var im =null;
		if(self.me_list[index].tset_img==undefined){
			self.me_list[index].tset_img = new Image();	
		}
		self.me_list[index].tset_img.src = '';
		im = self.me_list[index].tset_img;
		
		$(im).bind('error', function(){
			$(im)
			.unbind('load')
			.unbind('error');
			self.me_list[index].connection_fail = false;
		});

		$(im).bind('load', function(){
			$(me).attr('src', im.src);
			self.start_check_me(me);
			$(im)
			.unbind('load')
			.unbind('error');
            self.me_list[index].connection_fail = false;
            
	        //деактивируем кнопку play, активируем кнопку stop 
			var win_nr = parseInt($("div.[name=win]:has(#"+me_id+")").attr('id').replace('win', '') );
	        if(!isNaN(parseInt(win_nr))){
	           	controls_handlers.activate_btn_stop(win_nr);
	        }
		});
		
		var par = (self.me_list[index].src.indexOf('?')!=-1)? "&dummy=" : "?&dummy=";
		par += Math.random();
		im.src = self.me_list[index].src+par;
	}
};


/**
 * Функция осуществляет вычисление размеров и расположения элементов раскладки камер
 * @param _canvas_w - ширина эл-та CANVAS
 * @param _canvas_h - высота эл-та CANVAS
 * @param img_aspect_ratio - объект, содержит коэфициенты для пропорционального преобразования размеров элементов воспроизведения
 * @param _rows_nr - номер строки текущего элемента
 * @param _cols_nr - номер столбца текущего элемента
 * @param _rowspan - сколько позиций элемент занимает в раскладке
 */

function calc_win_geo(_canvas_w, _canvas_h, img_aspect_ratio, _rows_nr, _cols_nr, _rowspan) {
   var cam_w;
   var cam_h;
   if (_rowspan == undefined)
      _rowspan = 1;
   
   
   if ( MSIE ){
	   
	   if ( img_aspect_ratio == undefined || img_aspect_ratio == 'fs' ) {
			   
		      // соотношение сторон видеоизображения нас не волнует,
		      //  растягиваем окна камер и сами изображения по всему CANVAS 
		      cam_w = parseInt(_canvas_w/_cols_nr) ;
		      cam_h = parseInt(_canvas_h/_rows_nr) - NAME_DIV_H*_rowspan ;
		   } else {
		      // create wins
			   var calc_canvas_h = _canvas_h - ((NAME_DIV_H*_rowspan ) * _rows_nr);
			   
			   if ( (_canvas_w/calc_canvas_h) >= (img_aspect_ratio.num*_cols_nr)/(img_aspect_ratio.den*_rows_nr) ) {
		        cam_h = parseInt(calc_canvas_h/_rows_nr);
		         cam_h = parseInt(cam_h/img_aspect_ratio.den);
		         cam_w = cam_h*img_aspect_ratio.num;
		         cam_h *= img_aspect_ratio.den;
		         
		      } else {
		    	  
		         cam_w = parseInt(_canvas_w/_cols_nr);
		         cam_w = parseInt(cam_w/img_aspect_ratio.num);
		         cam_h = cam_w*img_aspect_ratio.den;
		         cam_w *= img_aspect_ratio.num;
		      }
		   }
	   
	   this.win_w = cam_w ;
	   this.win_h = cam_h + NAME_DIV_H*_rowspan ;

	   this.offsetX = parseInt((_canvas_w - this.win_w * _cols_nr)/2);  
	   this.offsetY = parseInt((_canvas_h - this.win_h* _rows_nr)/2);  

	   this.cam_w = cam_w; 
	   this.cam_h = cam_h;
	   
   }else{
   
   
   if ( img_aspect_ratio == undefined || 
         img_aspect_ratio == 'fs' ) {
	   
      // соотношение сторон видеоизображения нас не волнует,
      //  растягиваем окна камер и сами изображения по всему CANVAS 
      cam_w = parseInt(_canvas_w/_cols_nr) - BorderLeft - BorderRight;
      cam_h = parseInt(_canvas_h/_rows_nr) - NAME_DIV_H*_rowspan - BorderTop - BorderBottom;
   } else {
      // create wins
	   var calc_canvas_h = _canvas_h - ((NAME_DIV_H*_rowspan + BorderTop + BorderBottom) * _rows_nr);
	   
	   if ( (_canvas_w/calc_canvas_h) >= (img_aspect_ratio.num*_cols_nr)/(img_aspect_ratio.den*_rows_nr) ) {
        cam_h = parseInt(calc_canvas_h/_rows_nr);
         cam_h = parseInt(cam_h/img_aspect_ratio.den);
         cam_w = cam_h*img_aspect_ratio.num;
         cam_h *= img_aspect_ratio.den;
         
      } else {
    	  
         cam_w = parseInt(_canvas_w/_cols_nr - BorderLeft - BorderRight);
         cam_w = parseInt(cam_w/img_aspect_ratio.num);
         cam_h = cam_w*img_aspect_ratio.den;
         cam_w *= img_aspect_ratio.num;
      }
   }
   
   this.win_w = cam_w + BorderLeft + BorderRight;
   this.win_h = cam_h + NAME_DIV_H*_rowspan + BorderTop + BorderBottom;

   this.offsetX = parseInt((_canvas_w - this.win_w * _cols_nr)/2);  
   this.offsetY = parseInt((_canvas_h - this.win_h * _rows_nr)/2);  

   this.cam_w = cam_w; 
   this.cam_h = cam_h;
   }
} // calc_win_geo()


/**
 * Вычисляет положение left для элементов раскладки
 * Вызывается при:
 * <ul>
 * <li> Установке элементов в раскладке
 * <li> При переходе в элемента в полноэкраный режим
 * <li> При ресайзе окна
 * <li>	При выходе из полноэкранного режима, если этом режиме был ресайз окна
 * </ul>
 * @param win_geo - объект, содержит параметры контекстного элемента
 * @param col - колонка раскладки по которой позиционируется элемент
 */
function calc_win_left(win_geo, col) {
   var _left = parseInt(col*win_geo.win_w + win_geo.offsetX);
   return _left;
}

/**
 * Вычисляет положение top для элементов раскладки
 * Вызывается при:
 * <ul>
 * <li> Установке элементов в раскладке
 * <li> При переходе в элемента в полноэкраный режим
 * <li> При ресайзе окна
 * <li>	При выходе из полноэкранного режима, если этом режиме был ресайз окна
 * </ul>
 * @param win_geo - объект, содержит параметры контекстного элемента
 * @param row - строка раскладки по которой позиционируется элемент
 */
function calc_win_top(win_geo, row) {
   var _top = parseInt( row*win_geo.win_h + win_geo.offsetY );
   return _top; 
}

/**
 * Вычисляет и устанавливает размеры отображаемого элемента в полноэкранном режиме при ресайзе окна
 * @param fs_win - отображаемый  в полноэкранном режиме элемент
 */
function change_fs_win_geo(fs_win) {
   var win_geo = new calc_win_geo(CANVAS_W, CANVAS_H, CamsAspectRatio, 1, 1, 1);
   var fs_win_div_jq = $(fs_win);
   
   fs_win_div_jq.css('top',  calc_win_top (win_geo, 0));
   fs_win_div_jq.css('left', calc_win_left(win_geo, 0));

   if ( GECKO || WEBKIT) {
	   $(fs_win_div_jq)
	   .width(win_geo.win_w)
	   .height(win_geo.win_h);
	   
	   $('.pl_cont',fs_win_div_jq).width(win_geo.cam_w+CORRECT_W).height(win_geo.cam_h+CORRECT_H).aplayerResizeToParent();
         // .attr('alt',win_geo.cam_w + 'x' + win_geo.cam_h);
   } else if ( MSIE ) {
     	$(fs_win_div_jq)
       	.width(win_geo.win_w+CORRECT_W)
       	.height(win_geo.win_h+CORRECT_H);
       	
       	$('.pl_cont',fs_win_div_jq)
       	.width(win_geo.cam_w+CORRECT_W)
       	.height(win_geo.cam_h+CORRECT_H)
       	.aplayerSetSize({'height':win_geo.cam_h+CORRECT_H , 'width': win_geo.cam_w+CORRECT_W}) ;   
         // .text(win_geo.cam_w + 'x' + win_geo.cam_h)
   }
} // change_fs_win_geo()


/**
 * Вычисляет и устанавливает размеры элементов раскладки после ресайза окна
 */
function change_wins_geo() {
	
   var base_win_geo = new calc_win_geo(CANVAS_W, CANVAS_H, CamsAspectRatio, ROWS_NR, COLS_NR, 1);
   
   var win_geo;
   var i,tmp_div,win_def,win_nr,win_id;
   
   for (i=WIN_DIVS.length-1; i>=0; i--) {
      win_id = WIN_DIVS[i].id;
      win_nr = parseInt(win_id.substr(3));
      if ( win_nr == NaN ) {
         alert('Error: win.id="' + WIN_DIVS[i].id + '"');
         return;
      }
      tmp_div=$(WIN_DIVS[i]);

      win_def = WINS_DEF[win_nr];
      
      if(win_def == null) return;
      
      if ( win_def.rowspan == 1 && win_def.colspan == 1 ){
         win_geo = base_win_geo;
      }
      else{
    	  win_geo = new calc_win_geo(
        	base_win_geo.win_w*win_def.colspan,
               base_win_geo.win_h*win_def.rowspan,
               CamsAspectRatio, 1, 1, win_def.rowspan);
      }

      tmp_div.css('top',  calc_win_top (base_win_geo, win_def.row));
      tmp_div.css('left', calc_win_left(base_win_geo, win_def.col));

      if ( GECKO || WEBKIT) {
          $(tmp_div)
          	.width(win_geo.win_w)
          	.height(win_geo.win_h);    	  
          $('.pl_cont',tmp_div)
    	  	.width(win_geo.cam_w+CORRECT_W)
    	  	.height(win_geo.cam_h+CORRECT_H)
    	  	.aplayerResizeToParent();
      } else if (MSIE) {
         	$(tmp_div).width(win_geo.win_w+CORRECT_W).height(win_geo.win_h+CORRECT_H);
        	$('.pl_cont',tmp_div).width(win_geo.cam_w+CORRECT_W).height(win_geo.cam_h+CORRECT_H)
        	.aplayerSetSize({'height':win_geo.cam_h+CORRECT_H , 'width': win_geo.cam_w+CORRECT_W}) ;
      } else {
         $('applet',tmp_div).width(win_geo.cam_w).height(win_geo.cam_h);
      }
   } // for(allwin)
} // change_wins_geo()

/**
 * Обработчик ресайза окна
 */
function canvas_growth() {
   var canvas_changed = false;
   
   var avail_h = (($.browser.msie)?ietruebody().clientHeight:window.innerHeight) - $('#toolbar').height()-5;
   var avail_w = (($.browser.msie)?ietruebody().clientWidth:window.innerWidth);
   
   if ( avail_h !=  CANVAS_H) {
      CANVAS_H = avail_h;
      CANVAS.height(CANVAS_H);
      canvas_changed = true;
   }
   if ( avail_w != CANVAS_W) {
	  CANVAS_W = avail_w;
      CANVAS.width(CANVAS_W);
      canvas_changed = true;
   }

   if (!canvas_changed)
      return;
   if ( WIN_DIVS == undefined )
      return;
      
   WIN_DIV_W = undefined;
   
   if ( FS_WIN_DIV ) {
      change_fs_win_geo(FS_WIN_DIV);
   } else {
      change_wins_geo();
   } // if ( FS_WIN_DIV )
} // canvas_growth()

   /**
    * Выводит список доступных раскладок
    * @returns {String} - html -разметка
    */
   function layouts_to_list(){
	   var html = '<div id="nav"><span>';
        $.each(layouts_list, function(i, value){
        if(cur_layout<0){
            cur_layout = layouts_list[0].MON_NR;
        }
   		html+='<div class="layout'+((cur_layout==value.MON_NR)? ' selectedLayout':'' )+'" >';

        html+='<a id="layout_'+value.MON_NR+'" class="layout_link"';
   		//html+=' onclick="change_layout('+value.MON_NR+')"  href="#">'; //динамическая смена раскладки - отключена
   		// html+=' href="?layout_id='+value.MON_NR+'">';  //нединаимическая смена раскладки без использованиz пользовательских раскладок
		
   		//нединаимическая смена раскладки c использованием пользовательских раскладок
   		html+=' onclick="user_layouts.setCookie(\'layouts\', JSON.stringify(user_layouts.client_layouts), 86400, \'/\', window.location.hostname, \'\');"  href="../online/view.php?layout_id=' + value.MON_NR + '" >';

   		html+= (value.SHORT_NAME==''? value.MON_TYPE :value.SHORT_NAME);
   		html+= (value.IS_DEFAULT==1? '(def)' :'');
   		html+='</a>&nbsp;&nbsp;&nbsp;&nbsp;</div>';
   	});
   	html+='</span></div>';
   	
   	return html;
   }
   
   
   

   /**
    * Смена раскладки
    * @param mon_nr - номер устанавливаемой раскладки (из MON_NR в WEB_MONITORS БД)
    */
   function change_layout(mon_nr){
   	//Если был включен режим - 1 камера на весь экран
   	FS_WIN_DIV = null;
   	//целевая раскладка
   	var layout=null;
   	//кол-во элементов для отображения камер в целевой раскладке
   	var wins_nr = 0;
   	//структура целевой раскладки
   	var l_defs = null;
   	//Пропорции
   	var AspectRatio;
   	//Главная ячейка раскладки
   	var main_cell  = 0;

	//обнуляем массив масштабов
   $.aplayer.scale = new Array();
   	
   	cur_layout = mon_nr;

   	//Устанавливаем целевую раскладку
   	$.each(layouts_list, function(i, value){
   		if(value['MON_NR']==mon_nr){
   			layout = value;
   			return;
   		}
   	});
   	
   	//Чистим канвас
   	$('#canvas').empty();

   	l_defs = layouts_defs[layout['MON_TYPE']];
   	
   	//Главная ячейка раскладки
   	main_cell = l_defs[4];
   	
   	//кол-во элементов для отображения камер
    wins_nr = l_defs[0];

   	//пересоздаем объект текущей раскладки
   	WINS_DEF = new MakeArray(wins_nr);
   	
   	//размеры камер
   	major_win_cam_geo = null;
   	
   	layout_wins = $.parseJSON(layout['WINS']);
   	active_cams_srcs = new Array();
   	//и перезаполняем новыми значениями
   	$.each(WINS_DEF, function(i, value){
   		if(layout_wins[i]==null || GCP_cams_params[layout_wins[i][0]]==null ) return;
   		//Параметры текущего типа раскладки
   		var l_wins = l_defs[3][i];
   		var cam_nr = layout_wins[i][0];
   		//установка url камеры
   		active_cams_srcs[i] = new Array();
   		var cam_url = '';
   		switch(layout_wins[i][1]){
   		case '0':
   		case '1': //avregd

   			cam_url = CAMS_URLS[cam_nr]['avregd'];// get_cam_http_url(conf, cam_nr,'mjpeg', true);
   	   		active_cams_srcs[i]['type']='avregd';
   	   		active_cams_srcs[i]['cell']=cam_url;
   	   		active_cams_srcs[i]['fs']=cam_url;
   	   		break;
   		case '2': //alt 1
   			cam_url = CAMS_URLS[cam_nr]['cell_url_alt_1']; //get_cam_alt_url(GCP_cams_params[layout_wins[i][0]]['cell_url_alt_1'], cam_nr, true);
   	   		active_cams_srcs[i]['type']='alt_1';
   	   		active_cams_srcs[i]['cell']=cam_url;
   	   		active_cams_srcs[i]['fs']= CAMS_URLS[cam_nr]['fs_url_alt_1']; //get_cam_alt_url(GCP_cams_params[cam_nr]['fs_url_alt_1'],cam_nr, true);
   			break;
   		case '3': //alt 2
   			cam_url = CAMS_URLS[cam_nr]['cell_url_alt_2']; //get_cam_alt_url(GCP_cams_params[layout_wins[i][0]]['cell_url_alt_2'], cam_nr, true);
   	   		active_cams_srcs[i]['type']='alt_1';
   	   		active_cams_srcs[i]['cell']=cam_url;
   	   		active_cams_srcs[i]['fs']= CAMS_URLS[cam_nr]['fs_url_alt_2']; //get_cam_alt_url(GCP_cams_params[cam_nr]['fs_url_alt_2'], cam_nr, true);
   	   		break;
   		}
   		
   		var wxh = GCP_cams_params[ layout_wins[i][0] ]['geometry'];
   		var cam_width = parseInt(wxh.slice(0, wxh.indexOf('x')));
   		var cam_height = parseInt(wxh.slice(wxh.indexOf('x')+1));
   		if(cam_width==null || cam_width==0) cam_width = 640;
   		if(cam_height==null || cam_height==0) cam_height = 480;
   		//Возможно неверно интерпретировано: if(!empty($GCP_cams_params[$cam_nr]['Hx2'])) $height*=2;
   		if(GCP_cams_params[layout_wins[i][0]]['Hx2']!=0 && GCP_cams_params[layout_wins[i][0]]['Hx2']!=null ) cam_height *=2;
   		
   		if (major_win_cam_geo == null /* || major_win_nr === win_nr */ )
   		      major_win_cam_geo = new Array(cam_width, cam_height);
   		
   		var net_cam_host=null;
   		if (operator_user && ( GCP_cams_params[layout_wins[i][0]].video_src == "rtsp" || GCP_cams_params[layout_wins[i][0]].video_src == "http" ) ){
   			      net_cam_host = GCP_cams_params[layout_wins[i][0]]['InetCam_IP'];
   		}else{
   		   net_cam_host = null;
   	    }
   			
   		//устанавливаем параметры и камеру для ячейки
   		WINS_DEF[i] = {
   				row : l_wins[0],
   			    col : l_wins[1],
   			    rowspan : l_wins[2],
   			    colspan : l_wins[3],
   			    main:  main_cell-1==i?1:0,
   			    cam: {
   			    	nr:   cam_nr,
   			        name: GCP_cams_params[layout_wins[i][0]]['text_left'] ,
   			        url:  cam_url, 
   			        orig_w: cam_width,
   			        orig_h: cam_height,
   			        netcam_host: net_cam_host
   			    }
   		};
   	});
   	
   	//Вывод в шапке элемента отображения камеры - названия камеры
   	PrintCamNames = (layout['PRINT_CAM_NAME']=='t' || layout['PRINT_CAM_NAME']==true)? true : false;

   	//Установка пропорций
   	AspectRatio = layout['PROPORTION'];
   	
   	//если растягивается на весь экран
   	if(AspectRatio=='fs' ) {
   		   CamsAspectRatio = 'fs';
   	} //если сохраняем пропорции
   	else {
   		
   		var rex = new RegExp("[0-9]+", "g");
   		if (AspectRatio=='calc'){
   		      ar = calcAspectForGeo(major_win_cam_geo[0], major_win_cam_geo[1]);
   		      CamsAspectRatio = { num : ar[0] , den : ar[1] };
   		   //Если пропропорции заданы в БД
   		   } else if ( rex.test(AspectRatio) ) {
   			   var m = AspectRatio.match(rex); 
   			   CamsAspectRatio = { 'num': m[0], 'den': m[1] };
   		   }
   		   else
   		      CamsAspectRatio = 'fs';
   		}
   	
   	WINS_NR = wins_nr;
   	ROWS_NR = l_defs[1];
   	COLS_NR = l_defs[2];
   	
   	fill_canvas();
   	
   }


   /**
    * Расчет размеров элементов отображения камер в раскладке
    * @param w - ширина изображения камеры
    * @param h - высота изображения камеры
    * @returns объект с размерами элементов отображения камер в раскладке
    */
   function calcAspectForGeo( w, h) {
   	
   	$.each(WellKnownAspects, function(i, val){
   		
   		if ( 0 == w % val[0] &&  0 == h % val[1] ) {
   			if (w/val[0] == h/val[1] )
   	            return val;
   		}
   		if ( h % val[0] &&  w % val[1] ) {
   	         if ( h/val[0]== w/val[1] )
   	            return new array(val[1],val[0]);
   	      }
   	});
   	var ar = new Array(w, h);
   	var _stop = (w > h)? h : w;
   	for (var i=1; i<=_stop; i++) 
   	{
   		if ( 0 == w % i && 0 == h % i ) {
   			ar[0] = w / i;
   			ar[1] = h / i;
   		}
   	}
   	   return ar;
   	}
   
   	//переход на страницу просмотра и управления пользовательскими раскладками 
   	function clients_layouts_list(){
		//Передаем параметры пользовательских раскладок
   		var url = '../admin/web_mon_list.php';
	   	if(user_layouts.isLocalStorageAvailable()){
           if(user_layouts.client_layouts_json==undefined){
                   user_layouts.setCookie('layouts', '', -1, '/', window.location.hostname, '');
           }
   			else if(user_layouts.client_layouts_json){
                var lay_user = JSON.stringify(user_layouts.client_layouts);
                user_layouts.setCookie('layouts', JSON.stringify(user_layouts.client_layouts), 86400, '/', document.location.hostname, '');

   			}
   			window.open(url, '_self');
   		}
   		else{
   			alert("Данная функция недоступна:\nлокальное хранилище данных недоступно");
   		}
	}   	

	//проверка доступности LocalStorage
	function isLocalStorageAvailable() {
	    try {
	        return 'localStorage' in window && window['localStorage'] !== null;
	    } catch (e) {
	        return false;
	    }
	}
	
	

   /**
    * Выводит раскладку с он-лайн камерами в канвас
    */
   function fill_canvas(){
	   
       if (ie||ns6) {
           tipobj=document.all? 
           document.all['tooltip'] :
           document.getElementById? document.getElementById('tooltip') : '';
           if (GECKO)
           document.onmousemove=positiontip;
		}
           // calc and set  CANVAS width & height
           CANVAS = $('#canvas');
           
           $("#toolbar").addClass('toolbar_style');
           //Выводим список раскладок
            $("#toolbar table tr").html('<td> <table><tr id="tb_contn"><td width="1000%">'+ layouts_to_list()+'</td></tr></table></td>');
           //добавляем кнопку на главнуюa
           if(REF_MAIN){

        	   $('#tb_contn').prepend('<td><div class="to_main"> <a href="../index.php" >На главную </a> </div> </td>');
           }
           // TODO Кнопка пользовательских раскладок
           $('#tb_contn').append('<td><div id="user_layouts" class="user_layouts" onclick="clients_layouts_list();" > <a href="#" >Раскладки</a> </div> </td>');
           
           canvas_growth();
           
           $(window).bind('resize', function() {
              canvas_growth();
              });

           $(window).bind('scroll', function(){return false;});
           
           var base_win_geo = new calc_win_geo(CANVAS_W, CANVAS_H, CamsAspectRatio, ROWS_NR, COLS_NR, 1);
           var win_geo;
           var win_nr;
           var _top=0;
           var _left=0;
           var win_div;
           var win_def;

            // Установка в канвас выбранной раскладки
           for (win_nr = 0; win_nr < WINS_NR; win_nr++ ) {
              if (  WINS_DEF[win_nr] == undefined )
                 continue;
              win_def = WINS_DEF[win_nr];

              if ( win_def.rowspan == 1 && win_def.colspan == 1 )
                 win_geo = base_win_geo;
              else{
                 win_geo = new calc_win_geo(base_win_geo.win_w*win_def.colspan,
                       base_win_geo.win_h*win_def.rowspan,
                       CamsAspectRatio, 1, 1, win_def.rowspan);
              }
              _top  = calc_win_top(base_win_geo, win_def.row);
              _left = calc_win_left(base_win_geo, win_def.col);

              win_div = $('<div id="win' + win_nr + '" name="win" class="win '+(win_def['main']==1? 'main_cell':'')+'" ' + 
                    ' style="position: absolute; '+
                    ' top:'+_top+'px;'+
                    ' left:'+_left+'px; '+
                    ' width:'+ win_geo.win_w +'px;'+
                    ' height:'+ win_geo.win_h +'px;'+
                    ' z-index: 30;' +
                    '"><\/div>');
                win_div.appendTo(CANVAS);

			if (PrintCamNames) {
                 var ipcamhost_link_begin = '';
                 var ipcamhost_link_end = '';
                 var host = '';
                 if ( GCP_cams_params[WINS_DEF[win_nr].cam.nr].video_src == "rtsp" ||
                     GCP_cams_params[WINS_DEF[win_nr].cam.nr].video_src == "http"  ) {
                     if (CAMS_URLS[WINS_DEF[win_nr].cam.nr].ipcam_interface_url){
                         host = CAMS_URLS[WINS_DEF[win_nr].cam.nr].ipcam_interface_url;
                     }else{
                         host = 'http://' + WINS_DEF[win_nr].cam.netcam_host;
                     }
                    ipcamhost_link_begin = '<a href="' + host +
                                             '" target="_blank" style="color:inherit;" title="'+strToolbarControls['to_cam_interface']+'">';
                    ipcamhost_link_end   = ' &rarr;<\/a>';
                 }
                 
                 var hdr = $('<div id="cell_header_'+win_nr+'" class="cell_header"  style="cursor:default;'+ 
                       ' padding:0px; margin:0px; overflow:hidden; border:0px; height:'+ NAME_DIV_H*win_def.rowspan +'px;">'+
                       '<span style="'+
                       'vertical-align: middle; position: absolute; top: '+(NAME_DIV_H*win_def.rowspan/2 - 14)+'px; padding-left:8px; padding-top:2px; padding-bottom:2px; padding-right:2px;'+
                       ' color:#e5e5e5; font-size:'+14+'px; font-weight: bold; width:100%; overflow:hidden;">'+
                       ipcamhost_link_begin + WINS_DEF[win_nr].cam.name + ipcamhost_link_end +
                       '<\/span><\/div>')
                       .appendTo(win_div);
                 
                 //ToolBar
                 var ht = $(hdr).height()-4;
                 var toolbar = $('<div class="tool_bar"></div>')
                 .height(ht)
                 .css({
                	 'position':'absolute',
                	 'top':'1px',
                	 'right':'1px'
                 })
                 .appendTo(hdr);
                 
                 //свернуть/развернуть
                 $('<img src='+imgs['fs'].src+' class="tool fs_tc" title="'+strToolbarControls['max']+'">')
                 .height(ht-4)
                 .appendTo(toolbar);

                 //Кнопка включить/выключить toolbar
                 $('<img src='+imgs['controlsOnOff_on'].src+' id="controlsOnOff_'+win_nr+'" class="tool controlsOnOff" title="'+strToolbarControls['on']+'" >')
                 .height(ht-4)
                 .click(function(e){
                	 e.preventDefault();
                	 e.stopPropagation();
                	 controls_handlers.controlsOnOff_click(e);
                	 return false;
                 })
                 .mouseover(function(e){
                	 e.preventDefault();
                	 e.stopPropagation();
                	 // controls_handlers.controlsOnOff_mouseover(e);
                	 return false;
                 })
                .mouseout(function(e){
                	 e.preventDefault();
                	 e.stopPropagation();
                	 // controls_handlers.controlsOnOff_mouseout(e);
                	 return false;
                 })
                 .appendTo(toolbar);
         
                 //панель контролов
                 
                 var normal_size = $('<img id="normal_size_'+win_nr+'" class="normal_size" title="'+strToolbarControls['cell_size']+'" src='+imgs['normal_size'].src+' />')
                 .height(ht-4)
                 .click(function(e){
                	 e.preventDefault();
                	 e.stopPropagation();
                	 controls_handlers.normal_size_click(e);
                	 return false;
                 });

                 var original_size = $('<img id="original_size_'+win_nr+'" class="original_size" title="'+strToolbarControls['orig_size']+'" src='+imgs['original_size'].src+' />')
                 .height(ht-4)
                 .click(function(e){
                	 e.preventDefault();
                	 e.stopPropagation();
                	 controls_handlers.original_size_click(e);
                	 return false;
                 });
                 
                 var start = $('<img id="pl_start_'+win_nr+'" class="pl_start" title="'+strToolbarControls['play']+'" src='+imgs['pl_start'].src+' />')
                 .height(ht-4)
                 .click(function(e){
                	 e.preventDefault();
                	 e.stopPropagation();
                	 controls_handlers.pl_start_click(e);
                	 return false;
                 })
                 .hide();
                 
                 var stop = '';
                 if(!WEBKIT){
	                 stop =  $('<img id="pl_stop_'+win_nr+'" class="pl_stop" title="'+strToolbarControls['stop']+'" src='+imgs['pl_stop'].src+' />')
	                 .height(ht-4)
	                 .click(function(e){
	                	 e.preventDefault();
	                	 e.stopPropagation();
	                	 controls_handlers.pl_stop_click(e);
	                	 return false;
	                 });
                 }
                 
                 
                 var plus = $('<img id="pl_plus_'+win_nr+'" class="pl_plus" title="'+strToolbarControls['zoom_in']+'" src='+imgs['pl_plus'].src+' />')
                 .click(function(e){
                	 e.preventDefault();
                	 e.stopPropagation();
                	 controls_handlers.pl_plus_click(e);
                	 return false;
                 })
                 .height(ht-4);
                 
                 var minus = $('<img id="pl_minus_'+win_nr+'" class="pl_minus" title="'+strToolbarControls['zoom_out']+'" src='+imgs['pl_minus'].src+' />')
                 .click(function(e){
                	 e.preventDefault();
                	 e.stopPropagation();
                	 controls_handlers.pl_minus_click(e);
                	 return false;
                 })
                 .height(ht-4);
                 
                 var plc = $('<div id="pl_controls_'+win_nr+'" class="pl_controls"></div>')
                 .height(ht)
                 .width($(hdr).width()-$(toolbar).width())
                 .css({
                	 'position':'absolute',
                	 'top':'1px',
                	 'left':'1px',
                	 'padding': '3 5'
                 })
                 .append(start, stop, minus, plus, normal_size, original_size)
                 .click(function(e){
                	 e.preventDefault();
                	 e.stopPropagation();
                	 return false;
                 })
                 .mouseover(function(e){
                	 e.preventDefault();
                	 e.stopPropagation();
                	 controls_handlers.controls_panel_mouseover(e);
                	 return false;
                 })
                 .mouseout(function(e){
                	 e.preventDefault();
                	 e.stopPropagation();
                	 controls_handlers.controls_panel_mouseout(e);
                	 return false;
                 })
                 .hide()
                 .prependTo(hdr);
                 
                 if(MSIE){
                	 var offset_x = 2; 
                	 $('#pl_stop_'+win_nr+', #pl_start_'+win_nr).css({'position':'absolute', 'top':'0', 'left':offset_x+'px'});
                	 offset_x += ht;
                	 
                	 $('#original_size_'+win_nr).remove(); //не работает установка оригинального размера для MSIE
                	 $('#normal_size_'+win_nr).css({'position':'absolute', 'top':'0', 'left':offset_x+'px'});
                	 offset_x += ht;
                	 $('#pl_minus_'+win_nr).css({'position':'absolute', 'top':'0', 'left':offset_x+'px'});
                	 offset_x += ht;
                	 $('#pl_plus_'+win_nr).css({'position':'absolute', 'top':'0', 'left':offset_x+'px'});
                	 offset_x += ht;
                 }
              }
              //Установка плеера
              brout(win_nr, win_div, win_geo);
           }
           
           WIN_DIVS = $('div.win');

           $('#dialog').jqm({
        	   overlay: 90
           });

    //Убрать тултип при перетаскивании
	$('.MediaCont').mousedown(function(e){ 
		e.preventDefault();
		 hideddrivetip(); 
		 $(e.currentTarget).addClass('cursorMove');
		 return false;
	});
	$('#canvas').mouseup(function(e){
		$('.MediaCont').removeClass('cursorMove');
	});
   
	//прячем кнопки масштабирования для плагинного содержимого
	$(".win").each(function(i, val){
		if($('.pl_cont', this).aplayerIsEmbedOrObject()){
			$('.pl_minus, .pl_plus, .normal_size, .original_size',this).remove();
		}
	});

	//проверка связи с камерами
	if(GECKO || WEBKIT)	checking_connection.init_check();
	
//--> Cameras' statuses    	
	
	var cams_nrs = '';
	
   	//Устанавливаем целевую раскладку
   	$.each(layouts_list, function(i, value){
   		if(value['MON_NR']==cur_layout){
   			var wins = $.parseJSON(value['WINS']);
   			var cnt = 0;
   		   	$.each(wins, function(ix, win){
   		   		if(cnt>0)cams_nrs+=',';
   		   		cams_nrs += win[0];
   		   		cnt++;
   		   	});
   			return;
   		}
   	});
   	
   	
	if(CAM_STATUS_REQ!=null){
		CAM_STATUS_REQ.abort(); 
	}
	//запуск запросов статуса камер
//	CAM_STATUS_REQ = cam_status_request({
//				'cams': cams_nrs
////				'subsytems':'capture,record,motion,client', 	
//			});
//--> Cameras' statuses
	
}//fill_canvas()

var err_no = 0;   
cam_status_request = function(params ){

	return $.ajax({
	    url: '../lib/status.php',
	    dataType : "json",
	    data : params,
	    success: function (data, textStatus) { 
	    	CAM_STATUSES = data;
	    	if ( !MSIE ){ 
//	    		console.log(CAM_STATUSES);
	    	}
	    	if(SUBSCRIBE){
				params['subscribe']='multipart'; 
				CAM_STATUS_REQ = cam_status_request(params);
			}
	    },
	    error: function (XHR, textStatus, errorThrown) { 
			window.setTimeout(function(){
				if(err_no<5 && textStatus!='abort'){				
					err_no++;
					CAM_STATUS_REQ = cam_status_request(params);
				}
			}, 3000*err_no);
			
	    }    
	
	});
	
	
};

/**
 * Функция создает XHR-object
 * @returns  XHR-object
 */   
   
getXmlHttp = function(){
	var xmlhttp;
	try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
	try {
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	} catch (E) {
		xmlhttp = false;
	}
	}
	if (!xmlhttp /*&& typeof XMLHttpRequest!='undefined' */) {
		xmlhttp = new XMLHttpRequest();
	}
	return xmlhttp;
};
   
   

/**
 * Объект обработки событий контролов toolbar & controlbar
 */
var controls_handlers = {
	timers : new Array(),
	original_size : new Array(),
	
	//устанавливает активной кнопку play
	activate_btn_play : function(win_nr){
		$('#pl_stop_'+win_nr).hide();
		$('#pl_start_'+win_nr).show();		
	},
	//устанавливает активной кнопку stop
	activate_btn_stop : function(win_nr){
		$('#pl_stop_'+win_nr).show();
		$('#pl_start_'+win_nr).hide();		
	},
	
	original_size_click : function(e){
		var size = $(e.currentTarget);
		var cell_nr = parseInt(($(size).attr('id')).replace('original_size_',''));
		var aplayer_id = $('.aplayer', '#win'+cell_nr).attr('id');
		//установить нулевую позицию элемента
		$.aplayer.setMediaEltPosition(aplayer_id , { left:'0px', top:'0px' } );
		
		$('#'+aplayer_id).parent().aplayerMediaSetSrcSizes();
		//обнуляем масштаб
	   $.aplayer.scale[aplayer_id]=0;
	   controls_handlers.original_size[aplayer_id]=true;
	   
	},
	
	normal_size_click : function(e){
		var size = $(e.currentTarget);
		var cell_nr = parseInt(($(size).attr('id')).replace('normal_size_',''));
		var aplayer_id = $('.aplayer', '#win'+cell_nr).attr('id');
		
		//установить нулевую позицию элемента
		$.aplayer.setMediaEltPosition(aplayer_id , { left:'0px', top:'0px' } );
		$.aplayer.setMediaEltPosition(aplayer_id , { left:'0px', top:'0px' } );
		
		$('#'+aplayer_id).parent().aplayerResizeToParent();

		//обнуляем масштаб
	   $.aplayer.scale[aplayer_id]=0;
	   controls_handlers.original_size[aplayer_id]=false;
	},
	
	clear_timer : function(cell_nr){
		window.clearTimeout(this.timers[cell_nr]);
	},
	
	
	controls_panel_mouseover : function(e){
		var cp = $(e.currentTarget);
		var cell_nr = parseInt(($(cp).attr('id')).replace('pl_controls_',''));
		this.clear_timer(cell_nr);
	},

	
	
	controls_panel_mouseout : function(e){
		var cp = $(e.currentTarget);
		var cell_nr = parseInt(($(cp).attr('id')).replace('pl_controls_',''));
		if($("#cell_header_"+cell_nr).hasClass('control')){}
		else{
			this.timers[cell_nr] = window.setTimeout(function(){
				controls_handlers.hide_cntrolpanel(e);
			}, 100);
		}
	},
	
	controlsOnOff_click : function(e){
		
		var gw = $(e.currentTarget);
		var cell_nr = parseInt(($(gw).attr('id')).replace('controlsOnOff_',''));
		
		if($("#cell_header_"+cell_nr).hasClass('control')){
			$("#cell_header_"+cell_nr).removeClass('control');
		}
		else{
			$("#cell_header_"+cell_nr).addClass('control');
		}
		if($("#cell_header_"+cell_nr).hasClass('fixed_cntr')){
			$("#cell_header_"+cell_nr).removeClass('fixed_cntr');
		}
		else{
			$("#cell_header_"+cell_nr).addClass('fixed_cntr');
		}
		
		if($("#cell_header_"+cell_nr).hasClass('fixed_cntr')){
			$(gw).attr({'title':strToolbarControls['off']});
		}
		else{
			$(gw).attr({'title':strToolbarControls['on']});
		}
		
		if($("#cell_header_"+cell_nr).hasClass('control')){
			$(gw).unbind('mouseout');
			if(!$("span", "#cell_header_"+cell_nr).hasClass('hidden')){
				this.show_cntrolpanel(e);
			}
		}
		else{
			this.hide_cntrolpanel(e);
			$(gw).mouseout(function(e){
           	 e.preventDefault();
        	 e.stopPropagation();
        	 controls_handlers.controlsOnOff_mouseout(e);
        	 return false;
			});
		}
	},
	
	controlsOnOff_mouseover : function(e){
		this.show_cntrolpanel(e);
	},
	
	controlsOnOff_mouseout : function(e){
		var gw = $(e.currentTarget);
		var cell_nr = parseInt(($(gw).attr('id')).replace('controlsOnOff_',''));
		
		if($("span", "#cell_header_"+cell_nr).hasClass('hidden')){
				this.timers[cell_nr] = window.setTimeout(function(){
					controls_handlers.hide_cntrolpanel(e);
			}, 2000);
		}
	},

	show_cntrolpanel : function(e){
		var gw = $(e.currentTarget);
		var cell_nr = parseInt(($(gw).attr('id')).replace('controlsOnOff_',''));

		//переключаем кнопку тулбара
		$(gw).attr({
			'src':imgs['controlsOnOff_off'].src
		});
		
		$('span',"#cell_header_"+cell_nr).addClass('hidden');
		$('span',"#cell_header_"+cell_nr).fadeOut(200, function(){

			$(".pl_controls", "#win"+cell_nr).fadeIn(200);
		});
		
	},
	
	hide_cntrolpanel : function(e){
		var gw = $(e.currentTarget);
		var cell_nr = parseInt(($(gw).attr('id')).replace('controlsOnOff_',''));

		if(isNaN(cell_nr)){
			cell_nr = parseInt(($(gw).attr('id')).replace('pl_controls_',''));
		}
		
		//переключаем кнопку тулбара
		$(gw).attr({
			'src':imgs['controlsOnOff_on'].src
		});

		$('span',"#cell_header_"+cell_nr).removeClass('hidden');
		$(".pl_controls", "#win"+cell_nr).fadeOut(200, function(){
			$('span',"#cell_header_"+cell_nr).fadeIn(200);
		});

	},
	
	pl_start_click : function(e){
		var start = $(e.currentTarget);
		var cell_nr = parseInt(($(start).attr('id')).replace('pl_start_',''));
		var aplayer_id = $('.aplayer', '#win'+cell_nr).attr('id');
		
		$('#pl_stop_'+cell_nr).show();
		$(start).hide();
		$.aplayer.startPlay(aplayer_id);
		
		checking_connection.start_check_me($("#"+aplayer_id));
	},

	pl_stop_click : function(e){
		var stop = $(e.currentTarget);
		var cell_nr = parseInt(($(stop).attr('id')).replace('pl_stop_',''));
		var aplayer_id = $('.aplayer', '#win'+cell_nr).attr('id');
		var stop_baner_url = WINS_DEF[cell_nr].cam.stop_url;
		
		checking_connection.stop_check_me($("#"+aplayer_id));
		
		$('#pl_start_'+cell_nr).show();
		$(stop).hide();
		
		if(stop_baner_url){
			var par = (stop_baner_url.indexOf('?')!=-1)? "&dummy=" : "?&dummy=";
			par += Math.random();
			stop_baner_url+=par;
		}
		
		$.aplayer.pausePlay(aplayer_id, stop_baner_url);
	},
	
	pl_plus_click : function(e){
		var plus = $(e.currentTarget);
		var cell_nr = parseInt(($(plus).attr('id')).replace('pl_plus_',''));
		var aplayer_id = $('.aplayer', '#win'+cell_nr).attr('id');
		$.aplayer.zoomIn(aplayer_id);
	},
	
	pl_minus_click : function(e){
		var minus = $(e.currentTarget);
		var cell_nr = parseInt(($(minus).attr('id')).replace('pl_minus_',''));
		var aplayer_id = $('.aplayer', '#win'+cell_nr).attr('id');
		$.aplayer.zoomOut(aplayer_id);
	}
	
   
};