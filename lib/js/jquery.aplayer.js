/**
 * Объект, который проверяет, можно ли воспроизести fileNameElemMedia через typeElemMedia
 * @param typeElemMedia - Тип медиаэлемента (flowplayer, embed, rtsp, vlc или еще что-то)
 * @param fileNameElemMedia - Имя воспроизводимого файла
 */
function checkType(typeElemMedia, fileNameElemMedia){
	/**
	 * Тип элемента
	 * @type {string}
	 */
	this.type = typeElemMedia.toLowerCase().trim();
	/**
	 * Имя воспроизводимого файла
	 * @type {string}
	 */
	this.fileName = fileNameElemMedia.toLowerCase();

	this.getExtension = function (){
		var sub = this.fileName.substr(this.fileName.lastIndexOf('.') + 1, this.fileName.length - this.fileName.lastIndexOf('.') + 1);
		sub += '&asdad=asdasd';
		var RegExp = /[^&]+/;
		return sub.match(RegExp)[0];
	};

	/**
	 * Расширение воспроизводимого файла
	 * @type {string}
	 */
	this.extension = this.getExtension();

	/**
	 * Типы файлов, которые может воспроизвести html5
	 * @type {{video/ogg: string, video/mp4: string, video/webm: string, audio/mp4: string}}
	 */
	this.html5Type = {
		'video/ogg' : ';codecs="theora, vorbis"',
        'video/ogv' : ';codecs="theora, vorbis"',
		'video/mp4' : ';codecs="avc1.4D401E, mp4a.40.2"',
		'video/webm' : ';codecs="vp8.0, vorbis"',
		'audio/mp4' : ';codecs="mp4a.40.5"'
	};

	/**
	 * Типы файлов, которые может воспроизвести flowplayer
	 * @type {{ogg: boolean, mp4: boolean, swf: boolean, mp3: boolean}}
	 */
	this.flowPlayerType = {
		'ogg' : true,
		'mp4' : true,
		'swf' : true,
		'mp3' : true,
        'flv' : true
	}

	/**
	 * Проверяю, подключен ли в браузере flashplugin (true - подключен, false - отсутствует)
	 * @returns {boolean}
	 */
	this.flashDetectConstruct = function (){
		var a = !1;
		var b = "";

		function c(d) {
			d = d.match(/[\d]+/g);
			d.length = 3;
			return d.join(".")
		}
		if (navigator.plugins && navigator.plugins.length) {
			var e = navigator.plugins["Shockwave Flash"];
			e && (a = !0, e.description && (b = c(e.description)));
			navigator.plugins["Shockwave Flash 2.0"] && (a = !0, b = "2.0.0.11")
		} else {
			if (navigator.mimeTypes && navigator.mimeTypes.length) {
				var f = navigator.mimeTypes["application/x-shockwave-flash"];
				(a = f && f.enabledPlugin) && (b = c(f.enabledPlugin.description))
			} else {
				try {
					var g = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7"),
						a = !0,
						b = c(g.GetVariable("$version"))
				} catch (h) {
					try {
						g = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6"), a = !0, b = "6.0.21"
					} catch (i) {
						try {
							g = new ActiveXObject("ShockwaveFlash.ShockwaveFlash"), a = !0, b = c(g.GetVariable("$version"))
						} catch (j) {}
					}
				}
			}
		}
		var k = b;
		return a;
	};

	/**
	 * Поиск поддержки vlc плагина
	 * @returns {boolean} - true - если поддерживается, false - иначе
	 */
	this.detectVLCPlugin = function() {
		var name = "VLC";
		var nameVLCPluginActiveX = "VideoLAN.VLCPlugin.2";
		if (navigator.plugins && (navigator.plugins.length > 0)) {
			for(var i=0;i<navigator.plugins.length;++i)
				if (navigator.plugins[i].name.indexOf(name) != -1)
					return true;
		}
		else {
			try {
				new ActiveXObject(nameVLCPluginActiveX);
				return true;
			} catch (err) {}
		}
		return false;

	};

	this.enableVLCPlugin = this.detectVLCPlugin();

	/**
	 * Хранит в себе данные, подключен ли flash
	 * @type {boolean}
	 */
	this.flashDetect = this.flashDetectConstruct();
	/**
	 * Проверяю, может ли плагин воспроизвести файл
	 * @type {{
     * Может ли воспроизвести средствами html5-video
     * canPlayHtml5: Function,
     * Может ли воспроизвести rtsp
     * canPlay_rtsp: Function,
      * Может ли воспроизвести flowplayer
     * canPlay_flowplayer: Function,
     * Может ли воспроизвести vlc
     * canPlay_vlc: Function,
     * Может ли воспроизвести emdeb
     * canPlay_embed: Function}}
	 */

	var _self = this;
	this.canPlayPlugIn = {
		canPlay_video : function (){
            if (_self.extension == 'ogv'){
                _self.extension = 'ogg';
            }
			var newElem = document.createElement(_self.type);
			newElem.src = _self.fileName;
			var typeElem = _self.type + '/' + _self.extension;

			if (newElem.canPlayType(typeElem +  _self.html5Type[typeElem])){
				return true;
			}

			return false;
		},

		canPlay_rtsp : function(){
			if (_self.type !== 'vlc' && (!_self.enableVLCPlugin() || $.browser.ie)){
				return false;
			}

			return true;
		},

		canPlay_flowplayer : function (){
			if (typeof _self.flowPlayerType[_self.extension] !== 'undefined' &&
				_self.flashDetect){
				return true;
			}

			return false;
		},

		canPlay_vlc : function (){
			if (_self.enableVLCPlugin){
				return true;
			}

			return false;
		},

		canPlay_embed : function (){
			return true;
		}
	};

	/**
	 * Собственно проверка на способность плагина воспроизвести медиа-файл
	 * @returns {boolean}
	 */
	this.checkCanPlayTypeFromPlugin = function (){
		if ((this.type === 'audio') && (this.type === 'video')){
			if (this.canPlayPlugIn.canPlayHtml5()){
				return true;
			}
		}else if (typeof this.canPlayPlugIn['canPlay_' + this.type] !== 'undefined'){

			if (this.canPlayPlugIn['canPlay_' + this.type]()){
				return true;
			}
		}

		return false;
	};

	/**
	 * Дефолтные настройки плагинов (в случае, если ни один вариант не подошел)
	 * @type {{avi: string, mp4: string, flv: string, ogg: string, ogv: string, webm: string, mp3: string, swf: string, other: string}}
	 */
	this.defaultPlugin = {
		'avi' : 'vlc',
		'mp4' : 'flowplayer',
		'flv' : 'flowplayer',
		'ogg' : 'video',
		'ogv' : 'video',
		'oga' : 'audio',
		'webm' : 'video',
		'mp3' : 'audio',
		'swf' : 'flowplayer',
		'rtsp' : 'vlc',
		'other' : 'embed'
	};
}
(function ($) { $(function () {
 		//Установка плеера в элемент
//		$('#win_bot_detail').addPlayer({'src':'/avreg/media/cam_02/2010-05/03/00_00_00.jpg'});
    	//Установка общих параметров
//		$.aplayer.init({'height':200, 'width':300, 'mediaType':'embed' });
		//Вывод конфигурации
//		$.aplayer.config.Show();
		//Получить информацию о браузере
//		$.browserInfo.getInfo();
		//Вывод инфо о браузере
//		$.browserInfo.Show();
//		$.aplayer.config.Show();

		//Пример глобальной пользовательской настройки
/*		$.aplayerConfiguration({
			'*':{
				'*':{
					'http' :{
						'cgi'  : 'mjpeg',
						'mjpg' : 'mjpeg',
						'mjpeg' : 'mjpeg',
						'jpg' : 'image',
						'jepg': 'image',
						'png' : 'image',
						'bmp' : 'image',
						'gif' : 'image',
						'tiff': 'image',
						'*'   : 'embed'
					},
					'rtsp' :{
						'live': 'vlc'
					}
				}
			},
			'mozilla' : {
				'9':{
					'http':{
						'ogg' :	'video',
						'ogv' :	'video',
						'oga' : 'audio',
						'webm':	'video',
					},
					'rtsp':{}
				}
			},
			'chrome' : {'17':{'http':{},'rtsp':{}}},
			'opera' : {'11':{'http':{},'rtsp':{} }},
			'safari' : { '*':{'http':{}, 'rtsp':{} } },
			'msie' : {
				'*':{
					'http':{
						'ogg' :	'vlc',
						'ogv' :	'vlc',
						'webm':	'vlc',
						'oga' : 'vlc'
					},
					'rtsp':{}
				}
			}
		});
*/

		//Начальная инициализация
		$.aplayer.init(null);

});})(jQuery);

//---------------------------------------------------- Player ------------------------------------
	//Метод установки пользовательских настроек
	$.aplayerConfiguration = function(globalSettings){
		$.aplayer.init(globalSettings);
	};

	//Установка плеера в заданные эл-ты html
	$.fn.addPlayer = function(settings){
				if(settings.src==null)
				{
					return this;
				}
			else $(this).each(function(){
				$.aplayer.play(this, settings);
			});
		return this;
	};

	//Закрытие плеера
	$.fn.aplayerClose=function(){
		$(this).empty();
		return this;
	};

	//Смена src изображения
	$.fn.aplayerSetImgSrc = function(imageSource){
		var pl =  $(this).children('[id ^=' + $.aplayer.idContainer + ']');
			$(pl).removeAttr('t','s').unbind('click');
		
		$(pl).children(':first-child').attr('src',imageSource);//.attr('title', 'image');
		$.aplayer.draggable(pl);
		return this;
	};

	//Смена src для motion jpeg
	$.fn.aplayerSetMjpegSrc = function(mjpegSource){

		var pl =  $(this).children('[id ^=' + $.aplayer.idContainer + ']');
		if($.browserInfo.browser == 'msie'){
			$.aplayer.activeX_arr[$('OBJECT', this).attr('id')].MediaURL = mjpegSource;
			$.aplayer.activeX_arr[$('OBJECT', this).attr('id')].Src = mjpegSource;
		}
		else{
			if($('img.'+$.aplayer.classElMedia, this).attr('src')=='#') {
				$('img.'+$.aplayer.classElMedia, this).attr({'origsrc': mjpegSource});
			}else{
				$('img.'+$.aplayer.classElMedia, this).attr({'origsrc': mjpegSource, 'src':mjpegSource });
			}
		}

		$.aplayer.draggable(pl);
		return this;
	};

	//Медиаэлемент использует размеры источника
	$.fn.aplayerMediaSetSrcSizes = function(){
		var pl =  $(this).children('[id ^=' + $.aplayer.idContainer + ']');
		
		$('.'+$.aplayer.classElMedia, this).removeAttr('height').removeAttr('width').css({'height':'', 'width':'' });
		$.aplayer.draggable(pl);
		return this;
	};

	//использовать размер источника
	$.fn.aplayerSetSrcSizes = function(){
		$(this).children('[id ^=' + $.aplayer.idContainer + ']').children(':first-child').removeAttr('height').removeAttr('width').css({'height':'', 'width':'' });
		return this;
	};
	
    //Изменение размеров медиаэлемента
    $.fn.aplayerSetSizeMediaElt = function(Sizes) {
    	Sizes.height = parseInt(Sizes.height);
    	Sizes.width = parseInt(Sizes.width);
    	var Owner = this;
	    $(this).children('[id ^=' + $.aplayer.idContainer + ']').each(function () {
    	    var Cont = $(this);
            var H = $(Cont).height();
	        var W = $(Cont).width();
       		var cp_h = $('[id ^='+$.aplayer.idControlPanel+']', Cont).height();
       		if(cp_h==null)cp_h=0;
       		H-=cp_h;

            $(Cont).children('video, audio, object, embed, img, .'+$.aplayer.classMediaCont).each(function () {
	           if(!($(this).hasClass($.aplayer.classMediaCont))) {
	            	$(this).height(Sizes.height).width(Sizes.width);

	            	var me_top = parseInt(($(this).css('top')).replace('px',''));
		       		var me_left = parseInt(($(this).css('left')).replace('px',''));
		       		if(isNaN(me_top)) me_top = 0;
		       		if(isNaN(me_left)) me_left = 0;

		       		if(H<Sizes.height &&  (Sizes.height+me_top)<H ){
		       			$(this).css({'top': (H-Sizes.height)+'px'});
		       		}
		       		if(W<Sizes.width &&  (Sizes.width+me_left)<W ){
		       			$(this).css({'left': (W-Sizes.width)+'px'});
		       		}
	           }
	           else{
	        	   //для flowplayer
	           		var pl_nr = ($(Cont).attr('id')).replace($.aplayer.idContainer, '');
	        	   var fp_obj = $('#'+$.aplayer.idMediaCont+pl_nr+'_api');
	        	   //если flowplayer
	        	   if( fp_obj.length > 0){
	        		   $(fp_obj).height(Sizes.height).width(Sizes.width);
	        		   //размеры флоуплеера
		       			var fp_h = Sizes.height;
		       			var fp_w = Sizes.width;
	        		   //flowplayer's position
		        		var fpY = parseInt(($(fp_obj).css('top')).replace('px',''));
				       	var fpX = parseInt(($(fp_obj).css('left')).replace('px',''));
				       	if(isNaN(fpY)) fpY = 0;
				       	if(isNaN(fpX)) fpX = 0;
		       			//размеры субконтейнера
		       			var sub_h = $('.'+$.aplayer.classMediaCont, Cont).height();
		       			var sub_w = $('.'+$.aplayer.classMediaCont, Cont).width();
		       			//привязка к верхней границе субконтейнера
		       			if(fpY>0) fpY = 0;
		       			//привязка к левой границе субконтейнера
		       			if(fpX>0) fpX = 0;
		       			//привязка к нижней границе субконтейнера
		       			if( sub_h <= fp_h && sub_h > fp_h + fpY ) fpY = sub_h - fp_h;
		       			//привязка к правой границе субконтейнера
		       			if(sub_w  <= fp_w && sub_w > fp_w + fpX  ) fpX = sub_w - fp_w;

		    			//установка текущей позиции
		    			$(fp_obj)
		    				.css({
		    					'left':fpX+'px',
		    					'top': fpY+'px'
		    				});
			       		return;
	        	   }
	           }
		       $(this).children('video, audio, embed, img').each(function () {
		       		$(this).height(Sizes.height).width(Sizes.width);

		       		var me_top = parseInt(($(this).css('top')).replace('px',''));
		       		var me_left = parseInt(($(this).css('left')).replace('px',''));
		       		if(isNaN(me_top)) me_top = 0;
		       		if(isNaN(me_left)) me_left = 0;

		       		
		       		if(H<Sizes.height &&  (Sizes.height+me_top)<H ){
		       			$(this).css({'top': (H-Sizes.height)+'px'});
		       		}
		       		if(W<Sizes.width &&  (Sizes.width+me_left)<W ){
		       			$(this).css({'left': (W-Sizes.width)+'px'});
		       		}
		       		
		       });
		
            });
            $.aplayer.draggable(Cont);
        });
        return this;
    };

    //Установка размеров плеера
    $.fn.aplayerSetSize = function(Sizes) {
    	
	    $(this).children('div[id ^=' + $.aplayer.idContainer + ']').each(function () {
	    $(this).height(Sizes.height);
	    $(this).width(Sizes.width);
	    var Cont = $(this);

	    $(Cont).children('object, embed, img').each(function () {
	        $(this).height(Sizes.height).width(Sizes.width);
	        $(this).children('embed').each(function () {
	        	$(this).height(Sizes.height).width(Sizes.width);
	        });
        }).end().children('.'+$.aplayer.classMediaCont).children('video, audio, img').each(function () { //Установка размера суб-элемента и вложенного медиа-элемента НЕ ПРОВЕРЯЛАСЬ!!!
	        $(this).height(Sizes.height - $(this).next('div[id ^=' + $.aplayer.idControlPanel + ']').height() - 7).width(Sizes.width - 6);
	        });
        });
        if($.browser.msie){
            $('object[id^=activX]').css({'height':'100%', 'width':'100%'})
        }
        return this;
    };

    //Установка размеров внутренних контейнеров плеера(без медиа элемента) в соответствии с размерами его контейнера
    $.fn.aplayerResizeContanerOnlyToParent = function () {
	    $(this).children('div[id ^=' + $.aplayer.idContainer + ']').each(function () {
	    $(this).height($(this).parent().height() - 2);
	    $(this).width($(this).parent().width() - 2);
	    var Cont = $(this);
		    $(Cont).children('.'+$.aplayer.classMediaCont).height(function(){
	        	return $(Cont).height()-$(this).next('div[id ^=' + $.aplayer.idControlPanel + ']').height();
	        });
	        $(Cont).find('.logoPlay').removeAttr('style').css({'position':'relative', 'top': (($(Cont).height()- $(Cont).find('.logoPlay').height())/2)+'px' });
        });
    	return this;
    };

    //Установка размеров плеера в соответствии с размерами его контейнера
    $.fn.aplayerResizeToParent = function () {
	    $(this).children('div[id ^=' + $.aplayer.idContainer + ']').each(function () {
	    	var h_cont = $(this).parent().height() - 2;
	    	var w_cont = $(this).parent().width() - 2;
	    $(this).height(h_cont);
	    $(this).width(w_cont );
	    var Cont = $(this);

	    $(Cont).find('object, embed, img').each(function () {
	    	if($(this).hasClass('cntr_img')) {} //чтобы избежать ресайза изображений контролов
            else{
	        $(this).height($(Cont).height() - 1).width($(Cont).width() - 1);
	        $(this).children('embed').each(function () {
	        	$(this).height($(Cont).height() - 1).width($(Cont).width() - 1);
	        });
	        //отображение лого-плей
	        $(Cont).find('.logoPlay').removeAttr('style').css({'position':'relative', 'top': (($(Cont).find('.logoPlay').parent().height()- $(Cont).find('.logoPlay').height())/2)+'px' });
            }
        })
        .end()
        .children('.'+$.aplayer.classMediaCont)
        .height(function(){
        	return $(Cont).height()-$(this).next('div[id ^=' + $.aplayer.idControlPanel + ']').height();
        })
        .end()
        .find('.MediaCont')
        .children('video, audio, img, object, embed')
        .each(function () { //Установка размера суб-элемента и вложенного медиа-элемента
	        $(this).height($(Cont).height() - $(this).parent().next('div[id ^=' + $.aplayer.idControlPanel + ']').height() - 0).width($(Cont).width() - 0);
	        });
        });
	
        return this;
    };

    //Является ли медиа-элемент картинкой?
    $.fn.aplayerIsImage = function(){
  		var el = $(this).children('div').children(':first-child');
  		if($(el).attr('name')=='img' && $(this).children('div').children('div').attr('t')== undefined) return true;
  		else return false;
    };

    //Является ли медиа-элемент картинкой (для online)?
    $.fn.aplayerIsMjpegImage = function(){
        var el = $(this).children('div').children('div').children(':first-child');
        if($(el).prop('tagName')=='IMG' && $(this).children('div').attr('t')== undefined) return true;
        else return false;
    };

    //Является ли медиа-элемент html5_Video ?
    $.fn.aplayerIsHTML5_Video = function(){
  		if($(this).find('video').size()>0) return true;
  		else return false;
    };

    //Является ли медиа-элемент html5_Audio ?
    $.fn.aplayerIsHTML5_Audio = function(){
  		if($(this).find('audio').size()>0) return true;
  		else return false;
    };

    //Является ли медиа-элемент embed ?
    $.fn.aplayerIsEmbededObject = function(){
  		if($(this).find('embed').size()>0) return true;
  		else return false;
    };

    //Является ли медиа-элемент embed || object?
    $.fn.aplayerIsEmbedOrObject = function(){
  		if($(this).find('embed, object').size()>0) return true;
  		else return false;
    };

    //Является ли внедренный объект vlc
    $.fn.aplayerIsVlc = function (){
        if ($(this).find('[type^="application/x-vlc-plugin"]').size() > 0 ||
            $(this).find('[classid^="clsid:E23FE9C6-778E-49D4-B537-38FCDE4887D8"]').size() > 0){
            return true;
        }else{
            return false;
        }
    }

    //Является ли медиа-элемент flowplayer?
    $.fn.aplayerIsFlowPlayer = function(){
  		if($(this).find('.flowplayer').size()>0) return true;
  		else return false;
    };

    //Скрыть плеер
    $.fn.aplayerHide = function(){
  		$(this).children('[id^='+$.aplayer.idContainer+']').hide();
    };

    //Отобразить плеер
    $.fn.aplayerShow = function(){
  		$(this).children('[id^='+$.aplayer.idContainer+']').show();
    };

	//Объект, инкапсулирующий свойства и методы плеера
	$.aplayer = {
		scale : new Array(),
			
		stopPlay : function(aplayerID){
			var pl_nr = aplayerID.replace($.aplayer.idContainer, '');

			//для flowplayer
			if( $('#'+aplayerID).aplayerIsFlowPlayer() ){
				$f($.aplayer.idContainer+pl_nr ).pause();
				return;
			}
			//для html5 video/audio
			if( $('#'+aplayerID).aplayerIsHTML5_Video() || $('#'+aplayerID).aplayerIsHTML5_Audio() ){
                $('#'+$.aplayer.idElMedia+pl_nr).each(function(){
					this.pause();
				});
				return;
			}
			
			if($('#'+'activX_'+pl_nr).attr('id')!=null ){
				//MSIE
			javascript:document['activX_'+pl_nr].stop();
				return;
			}
			
			if($('#'+$.aplayer.embedObj_idname+pl_nr).attr('id')!=null ){
			
			//если объект - прячем его, а вместо показываем постер "остановлено"
			$('<img class="pl_poster" src="'+$.aplayer.ControlBar.controlsImg+'Stopped.jpg'+'">')
				.height($('#'+aplayerID).height())
				.width($('#'+aplayerID).width())
				.appendTo('#'+aplayerID);
			
			var murl = $('#'+$.aplayer.embedObj_idname+pl_nr).attr('src');
			$('#'+$.aplayer.embedObj_idname+pl_nr).attr({
				'target': '#',
				'src':	'#',
				'murl':murl
			});
			
			$('#'+$.aplayer.embedObj_idname+pl_nr).hide();
				return;
			}
			try{
				$.aplayer.stopMjpegPlay($( 'img.'+$.aplayer.classElMedia,'#'+aplayerID));
			}catch(error){}
			
		},

		startPlay : function(aplayerID){
			var pl_nr = aplayerID.replace($.aplayer.idContainer, '');

			//для flowplayer
			if( $('#'+aplayerID).aplayerIsFlowPlayer() ){
				var fp = $f($.aplayer.idContainer+pl_nr);
			     if(fp.isPaused()){
			    	 fp.resume();
			     }
			     else{
			    	 if(!fp.isPlaying() ){
			    		 fp.play();
			    	 }
			     }
				return;
			}
			//для html5 video/audio
			if( $('#'+aplayerID).aplayerIsHTML5_Video() || $('#'+aplayerID).aplayerIsHTML5_Audio() ){

				$('#'+$.aplayer.idElMedia+pl_nr).each(function(){
					this.play();
				});
				return;
			}

			
			if($('#'+'activX_'+pl_nr).attr('id')!=null ){
				//MSIE
				javascript:document['activX_'+pl_nr].play();
				return;
			}
			
			if($('#'+$.aplayer.embedObj_idname+pl_nr).attr('id')!=null ){
				//если объект - отображаем, постер "остановлено" - удаляем
				$('.pl_poster','#'+aplayerID).remove();
				
				var murl = $('#'+$.aplayer.embedObj_idname+pl_nr).attr('murl');
				$('#'+$.aplayer.embedObj_idname+pl_nr).attr({
					'target': murl,
					'src':	murl,
					'murl': ''
				});
				
				$('#'+$.aplayer.embedObj_idname+pl_nr).show();
				//стартуем воспроизведение
				return;
			}
			
			try{
				$.aplayer.startMjpegPlay($( 'img.'+$.aplayer.classElMedia,'#'+aplayerID));
			}catch(error){}
		},
		
		// Реализовано только для avreg
		pausePlay : function(aplayerID, banner_url){
			banner_url = banner_url || $.aplayer.ControlBar.controlsImg+'Stopped.jpg';

			var pl_nr = aplayerID.replace($.aplayer.idContainer, '');

            //для flowplayer
			if( $('#'+aplayerID).aplayerIsFlowPlayer() ){
				$f($.aplayer.idContainer+pl_nr ).pause();
				return;
			}
			//для html5 video/audio
			if( $('#'+aplayerID).aplayerIsHTML5_Video() || $('#'+aplayerID).aplayerIsHTML5_Audio() ){
				$('#'+$.aplayer.idElMedia+pl_nr).each(function(){
					this.pause();
				});
				return;
			}
			
			if($('#'+'activX_'+pl_nr).attr('id')!=null ){
				//MSIE
			javascript:document['activX_'+pl_nr].stop();
				return;
			}
			
			if($('#'+$.aplayer.embedObj_idname+pl_nr).attr('id')!=null ){
			
			//если объект - прячем его, а вместо показываем постер "остановлено"
			$('<img class="pl_poster" src="'+banner_url+'">')
				.height($('#'+aplayerID).height())
				.width($('#'+aplayerID).width())
				.appendTo('#'+aplayerID);
			
			var murl = $('#'+$.aplayer.embedObj_idname+pl_nr).attr('src');
			$('#'+$.aplayer.embedObj_idname+pl_nr).attr({
				'target': '#',
				'src':	'#',
				'murl':murl
			});
			
			$('#'+$.aplayer.embedObj_idname+pl_nr).hide();
				return;
			}
			try{
				$.aplayer.pauseMjpegPlay($( 'img.'+$.aplayer.classElMedia,'#'+aplayerID), banner_url);
			}catch(error){}
		},
		
		
		scaleFactor : 0.1, //коэффициент масштабирования
		
		zoomIn : function(aplayerID){
			
        	//коэффициент увеличения размерa
			//плеер
			var cur_player = $('#'+aplayerID);
			var width = $(cur_player).width();
			var height = $(cur_player).height();

			//медиа элемент
			var me = $('.'+$.aplayer.classElMedia, cur_player );
			var me_width = $(me).width();
			var me_height = $(me).height();

			// шаг увеличения размерa
			var wm = width*$.aplayer.scaleFactor;
			var hm = height*$.aplayer.scaleFactor;

			me_width+=wm;
			me_height+=hm;
			
			//Изменение размеров медиа-элемента плеера
			$(cur_player).parent().aplayerSetSizeMediaElt({
				'width':  me_width,
				'height': me_height
			} );
			
			if(me_width<=width){
				$(me).css({'left':'0px'});
			}
			if(me_height<=height){
				$(me).css({'top':'0px'});
			}
			$.aplayer.scale[aplayerID]++;
		},
		
		zoomOut : function(aplayerID){
			//плеер
			var cur_player = $('#'+aplayerID);
			var width = $(cur_player).width();
			var height = $(cur_player).height();

			//медиа элемент
			var me = $('.'+$.aplayer.classElMedia, cur_player );
			var me_width = $(me).width();
			var me_height = $(me).height();
			
			// шаг увеличения размерa
			var wm = width*$.aplayer.scaleFactor;
			var hm = height*$.aplayer.scaleFactor;
			me_width-=wm;
			me_height-=hm;
			
			//Изменение размеров медиа-элемента плеера
			$(cur_player).parent().aplayerSetSizeMediaElt({
				'width':  me_width,
				'height': me_height
			} );
			
			if(me_width<=width){
				$(me).css({'left':'0px'});
			}
			if(me_height<=height){
				$(me).css({'top':'0px'});
			}
			$.aplayer.scale[aplayerID]--;
		},

		//проверяет и устанавливает перетаскиваемость медиаэлемента
		draggable : function(Container){
			
			if($(Container).aplayerIsFlowPlayer()){
				return;
			}
			
			var H = $(Container).height();
	        var W = $(Container).width();

	        var cur_plr_id = $(Container).attr('id');

	        var cp_h = $('[id ^='+$.aplayer.idControlPanel+']', Container).height();
       		if(cp_h==null)cp_h=0;
	
	        var matrix ={
	        		'width' : $(Container).width(),
	        		'height': $(Container).height()
	        };
	
            $(Container).children('video, audio, object, embed, img, .'+$.aplayer.classMediaCont).each(function () {
	           if(!( $(this).hasClass($.aplayer.classMediaCont))) {
		       		//Проверяем на перетаскиваемость
					if($(this).height() > $(this).parent().height()-cp_h || $(this).width() > $(this).parent().width())
					{
						$(this).draggable({
							drag: function(event, ui){
								var imgWidth = $(this).width();
								var imgHeight = $(this).height();
								if(imgWidth>matrix.width) {
									if(ui.position.left>0){
										ui.position.left = 0;
								}
								if(W-ui.position.left>imgWidth)
									ui.position.left = W - imgWidth;
								} else {
									ui.position.left = 0;
								}
	
								if(imgHeight>H-cp_h) {
									if(ui.position.top>0)
										ui.position.top = 0;
									if(H-ui.position.top-cp_h>imgHeight)
										ui.position.top = H -cp_h - imgHeight;
								} else {
									ui.position.top = 0;
								}
							}
						}).addClass('ui-corner-all ui-widget');
					}
					else
					{
						$(this).draggable('destroy').css({'position':'relative', 'top':'0'});
					}
	           }
		       $(this).children('video, audio, embed, img').each(function () {
		       		//Проверяем на перетаскиваемость
					if($(this).height() > $(this).parent().height()-cp_h || $(this).width() > $(this).parent().width())
					{
						$(this).draggable({
								drag: function(event, ui){
								
									var imgWidth = $(this).width();
									var imgHeight = $(this).height();
									if(imgWidth>matrix.width) {
										if(ui.position.left>0){
											ui.position.left = 0;
									}
									if(W-ui.position.left>imgWidth)
										ui.position.left = W - imgWidth;
									} else {
										ui.position.left = 0;
									}
		
									if(imgHeight>H-cp_h) {
										if(ui.position.top>0)
											ui.position.top = 0;
										if(H-ui.position.top - cp_h>imgHeight)
											ui.position.top = H -cp_h- imgHeight;
									} else {
										ui.position.top = 0;
									}
								}
							}).addClass('ui-corner-all ui-widget');
					}
					else{
						$(this).draggable('destroy').css({'position':'relative', 'top':'0'});
					}
			   });

			});
          	
           if($(Container)!=null && $(Container).height()!=null && $(Container).width()!=null ) $(Container).height(H).width(W).css({'overflow':'hidden'});
    	},
		
		//Установка базовых настроек плеера для популярных браузеров
		baseSettings : {
			'*':{
				'*':{
					'http' :{
						'cgi'  : 'mjpeg',
						'mjpg' : 'mjpeg',
						'mjpeg' : 'mjpeg',
						'jpg' : 'image',
						'jepg': 'image',
						'png' : 'image',
						'bmp' : 'image',
						'gif' : 'image',
						'tiff': 'image',
						'*'   : 'video,flowplayer,vlc,embed'
					},
					'rtsp' :{
						'live': 'vlc'
					}
    			
				}
			},
			
			'mozilla' : {
				'9':{
					'rtsp':{
                        'live': 'embed'
                    }
				}
			}
		},
		
		//Была ли произведена конфигурация?
		IsConfigurate : false,
		// Является ли медиаэлемент html5-video
        IsHtml5Video : false,
		//начальная инициализация
		init : function(globalSettings){

			if($.aplayer.IsConfigurate && globalSettings==null ) return;
			$.aplayer.IsConfigurate = true;
			if(globalSettings==null) globalSettings = {};
			
			//сбор информации о браузере
			$.browserInfo.getInfo();

			//Вывод инфо о браузере
            //$.browserInfo.Show();
			
			//объект для формирования настроек текущего браузера
			var curSets = {};
			//временный объект для прохода по всем установленным значениям для текущего браузера
			var totalObj = $.extend({},
				$.aplayer.baseSettings['*']==null?{}:$.aplayer.baseSettings['*'], //базовые настройки плеера для всех браузеров
				$.aplayer.baseSettings==null?{}:$.aplayer.baseSettings[$.browserInfo.browser], //базовые настройки плеера текущего типа браузера
				globalSettings==null?{}:globalSettings['*'] , //внешние конфигурационные настройки для всех браузеров
				globalSettings==null?{}:globalSettings[$.browserInfo.browser]
            ); //внешние конфигурационные настройки текущего типа браузера

            //Дополняем значения
            var baseObj = $.aplayer.baseSettings[$.browserInfo.browser];
            for(var i in baseObj){
                if(totalObj[i]==undefined){
                    totalObj[i]={};
                }
                for(var j in baseObj[i]){
                    if(totalObj[i][j]==undefined){
                        totalObj[i][j]={};
                    }
                    for(var k in baseObj[i][j]){
                        if(totalObj[i][j][k]==undefined){
                            totalObj[i][j][k]= baseObj[i][j][k];
                        }
                    }
                }
            }

            baseObj = $.aplayer.baseSettings['*'];

            for(var i in baseObj){
                if(totalObj['*']==undefined){
                    totalObj['*']={};
                }
                for(var j in baseObj[i]){
                    if(totalObj[i][j]==undefined){
                        totalObj[i][j]={};
                    }
                    for(var k in baseObj[i][j]){
                        if(totalObj[i][j][k]==undefined){
                            totalObj[i][j][k]= baseObj[i][j][k];
                        }
                    }
                }
            }

			//создаем массив версий, включая и версию браузера
			var arrVer = [$.browserInfo.version];
			for(var ver in totalObj)	arrVer.push(ver);
			//сортируем массив по возрастанию
			arrVer.sort($.aplayer.versionPredicate);

			var curSets = {};
			//отбираем валидные для даной версии значения
			for(var i in arrVer){

				if(typeof(totalObj[arrVer[i]])=='undefined' && arrVer[i] == $.browserInfo.version ) break;
				if(typeof(totalObj[arrVer[i]])=='undefined') continue;
				
				$.each(totalObj[arrVer[i]], function(tp, ext){
					curSets[tp] = $.extend({}, curSets[tp], ext );
				});
				
				if(arrVer[i] == $.browserInfo.version ){
					break;
				}
			}
			
			//проверка допустимых значений протокола передачи
			$.each(curSets, function(protocol, data){
				if(protocol!='http' && protocol!='rtsp' ){
					alert("В конфигурации установленно недопустимое значение протокола передачи: "+ protocol );
				}

				//проверка допустимых значений типа воспроизведения
				$.each(data, function(i, value){
                    value = value.trim();
					var typeObj = new Array();
					if (value.search(',')){
						typeObj = value.split(',');
						for (var i in typeObj){
                            typeObj[i] = typeObj[i].trim();
							typeObj[i] = typeObj[i].toLowerCase();
						}
					}else{
						typeObj[0] = value.toLowerCase();
                        console.log(typeObj[0]);
					}
					for (var i in typeObj){
						if(typeObj[i]!='embed' &&
							typeObj[i]!='video' &&
							typeObj[i]!='audio' &&
							typeObj[i]!='image' &&
							typeObj[i]!='mjpeg' &&
							typeObj[i]!='pseudo' &&
							typeObj[i]!='vlc' &&
							typeObj[i]!='flowplayer'
							){
							alert("Установленно недопустимое значение конфигурации: \n"+ i +' : '+typeObj[i] + ' в ' + value );
							curSets[i]='embed';
						}
					}
				});
			});

			$.extend($.aplayer.config, curSets);
			//Вывод конфигурации
		    // $.aplayer.config.Show();
		},

		
		
		//предикат сравнения версий
		versionPredicate : function(f,s){

			if(f=="*") return -1;
			if(s=="*") return 1;
			
			//выражение парсинга версии
			var reg = new RegExp('\\d+', 'ig');
			//парсим версии
			var F = f.match(reg);
			var S = s.match(reg);
			//учитываем кол-во значений (если больше значений - большим считаем исходноее значение, пр прочих равных)
			var p = 0;
			//выравнивание размеров версий
			if(F.length > S.length){
				for(var i = S.length; i<F.length; i++)S[i]=0;
				p = 1;
			}
			else if	(F.length < S.length){
				for(var i = F.length; i<S.length; i++)F[i]=0;
				p = -1;	
			}
			//Производим сравнение
			for(var i = 0; i<F.length; i++){
				if(parseInt(F[i]) > parseInt(S[i]))return 1;
				else if(parseInt(F[i]) < parseInt(S[i]))return -1;
			}
			return p;
		},
		
		
			//Общая для всех плееров конфигурация
			config : {
				height:'Inherit',
				width:'Inherit',
				
				isFPLoadingProgress : false,
				isFlowPlayerLoaded : false,
				//path_prefix : 'lib/js/',
				path_prefix : '../lib/js/',
				
				dictionery:{
					stop:'Stop',
					pause:'Pause',
					play: 'Play',
					soundOn:'Sound on',
					soundOff: 'Sound off',
					volume:'Volume',
					search:'Search',
					currentTime:'Current Time',
					duration:'Duration',
					scale:'Scale'
				},
				
				//Отображает содержимое объекта config
				Show : function(){
					var str='';
					for(var p in $.aplayer.config){
						if ($.aplayer.config[p].toString().indexOf('function')==-1){
							str += p +" = " +$.aplayer.config[p]+'\n';
							
							if($.aplayer.config[p].toString().indexOf('object')!=-1){
								str+='{\n';
								for(var tip in $.aplayer.config[p]){
									str +='\t'+ tip +" = " +$.aplayer.config[p][tip]+'\n';	

									if($.aplayer.config[p][tip].toString().indexOf('object')!=-1){
										str+='\t{\n';
										for(var tipIn in $.aplayer.config[p][tip]){
											str +='\t\t'+ tipIn +" = " +$.aplayer.config[p][tip][tipIn]+'\n';	
										}
										str+='\t}\n';
									}
								}
								str+='}\n';
							}
						}
						else str += p +" = " + 'function(){}'+'\n';
					}
					alert(str);
				}
			},

			//типы файлов для автоопределения
			extTypes:{
				mjpeg:['mjpeg'],
				image:['png', 'jpg','gif', 'bmp', 'jpeg', 'tiff'],
				video:['mp4', 'ogg', 'ogv', 'webm'],
				audio:['oga','mp3', 'm4a', 'wav'],
				application:['avi']
			},

			//Расширения и соответствующие MIME types
			MIMEtypes:{
                mjpeg	:'multipart/x-mixed-replace',
                mjpg	:'multipart/x-mixed-replace',
                tiff	:'image/bmp',
				bmp		:'image/bmp',
				png		:'image/png',
				jpeg	:'image/jpeg',
				jpg		:'image/jpeg',
				gif		:'image/gif',
				swf		:'application/x-shockwave-flash',
				flv		:'video/x-flv',
				aif 	:'audio/x-aiff',
				aifc 	:'audio/x-aiff',
				aiff 	:'audio/x-aiff',
				au 		:'audio/basic',
				avi 	:'video/x-msvideo',
				dv 		:'video/x-dv',
				m3u 	:'audio/x-mpegurl',
				m4a 	:'audio/mp4a-latm',
				m4b 	:'audio/mp4a-latm',
				m4p 	:'audio/mp4a-latm',
				m4u 	:'video/vnd.mpegurl',
				m4v 	:'video/x-m4v',
				mid 	:'audio/midi',
				midi 	:'audio/midi',
				mov 	:'video/quicktime',
				movie 	:'video/x-sgi-movie',
				mp2 	:'audio/mpeg',
				mp3 	:'audio/mpeg',
				mp4 	:'video/mp4',
				mpe 	:'video/mpeg',
				mpeg 	:'video/mpeg',
				mpg 	:'video/mpeg',
				mpga 	:'audio/mpeg',
				mxu 	:'video/vnd.mpegurl',
				snd 	:'audio/basic',
				wav 	:'audio/x-wav',
				wmv		:'video/x-ms-wmv',
				ogv		:'video/ogg',
				oga		:'audio/ogg',
				ogg 	:'application/ogg',
				webm	:'video/webm'
			},

			//метод определения mime type для воспроизведения файла
			setApplicationType : function(extension, elementMediaType, settings){
				if(extension==null){
					var reg = new RegExp('\\.\\w{3,5}\\s*', 'gi');
					 extension=settings.src.match(reg);
					 extension=extension[extension.length-1].slice(1);
				}
				if($.aplayer.MIMEtypes[extension]!=null)
				{
					elementMediaType=$.aplayer.MIMEtypes[extension];
				}
				else elementMediaType='application/'+extension;

				return elementMediaType;
			},

			//определение протокола передачи // если протокол не определен, предполагается HTTP
			defineTransmitProtocol : function(src){
				var tp  = $.trim(src).toLowerCase().match(/\w+:\/\//);
				if(tp!=null && tp[0]!=null) return tp[0].replace('://','');
				else return 'http';
			},

			//Определение и установка типа
			setType : function(settings){

                var ext=null;
				var elementMediaType = null;
				
				settings.src = $.trim(settings.src);
				var src = settings.src.toLowerCase();
				
				//Устанавливаем протокол передачи
				$.extend(settings, {protocol : $.aplayer.defineTransmitProtocol(src) });
				
				//получение расширения и майм-типа
				if(settings.extension==null){
					var reg = new RegExp('\\.\\w{3,5}(\\s*)', 'ig'); //для получения расширения файла
					var extArr = src.match(reg);
					if(extArr!=null && extArr.length>0){
						settings.extension = extArr[extArr.length-1].slice(1);
						ext = settings.extension;
						elementMediaType = $.aplayer.MIMEtypes[settings.extension];
					}
				}
				
				
				var playbackType = undefined;
				//если тип воспроизведения задан пользователем
				if(settings.playbackType!=undefined || settings.mediaType!=undefined){
					playbackType= (settings.playbackType!=undefined)? settings.playbackType : settings.mediaType;
				}
				
				
				if(settings.type!=null)
					{
						if(settings.type.indexOf('image')!=-1)return $.extend(settings, {mediaType : 'image' });
						if(settings.type.indexOf('application')!=-1 )return $.extend(settings, {mediaType : 'embed' });
					}
				
				if(settings.protocol == 'rtsp'){
					//если используется протокол rtsp

					settings.extension = 'live';
					settings.mediaType = $.aplayer.config[settings.protocol][settings.extension];
					return settings;
				}
				else if(settings.protocol == 'http'){
					//если используется протокол http
					
					//если задан в конфигурации способ воспроизведения - используем его
					if($.aplayer.config[settings.protocol][settings.extension]!=null)
					{

						if($.aplayer.config[settings.protocol][settings.extension]=='vlc'){
							elementMediaType = "application/x-vlc-plugin";
							mediaType = "vlc";
							settings.type="application/x-vlc-plugin";
						}
						else if(elementMediaType==null){
							if($.aplayer.config[settings.protocol][settings.extension]=='embed') elementMediaType = 'application/'+settings.extension;
							else elementMediaType = $.aplayer.config[settings.protocol][settings.extension]+'/'+settings.extension;
						}
						$.extend(settings, {mediaType : $.aplayer.config[settings.protocol][settings.extension], 'type' :elementMediaType });
						//если способ воспроизведения задан пользователем
						if(playbackType!=undefined){
							settings.mediaType = playbackType;
						}
						return settings;
					}
					
					//если в конфигурации задан способ воспроизведения для "всех остальных файлов"(т.е. - "*") - используем его
					if($.aplayer.config[settings.protocol]['*']!=null)
					{
						if(elementMediaType==null){
							if($.aplayer.config[settings.protocol]['*']=='embed')elementMediaType = 'application/'+ext;
							else elementMediaType = $.aplayer.config[settings.protocol]['*']+'/'+ext;
						}
						$.extend(settings, {mediaType : $.aplayer.config[settings.protocol]['*'], 'type' :elementMediaType });
						//если способ воспроизведения задан пользователем
						if(playbackType!=undefined){
							settings.mediaType = playbackType;
						}
						return settings;
					}
				}
				
				//автоматическое определение способа воспроизведения				
				$.each($.aplayer.extTypes, function(i, type){
					$.each(type, function(index, value){
						if(src.indexOf(value)!=-1){
							elementMediaType = i;
							ext = value;
						}
					});
				});
				
				if(elementMediaType==null){
                    $.aplayer.IsHtml5Video = false;
					elementMediaType = $.aplayer.setApplicationType(ext, elementMediaType,settings);
					$.extend(settings, {mediaType : 'embed' });
				}
				else if(elementMediaType.indexOf('pseudo')!=-1){
                    $.aplayer.IsHtml5Video = false;
					elementMediaType=elementMediaType+'/'+ext;
					$.extend(settings, {mediaType : 'pseudo'});	
				}
				else if(elementMediaType.indexOf('mjpeg')!=-1){
                    $.aplayer.IsHtml5Video = false;
					elementMediaType=elementMediaType+'/'+ext;
					$.extend(settings, {mediaType : 'mjpeg'});	
				}
				else if(elementMediaType.indexOf('image')!=-1){
                    $.aplayer.IsHtml5Video = false;
					elementMediaType=elementMediaType+'/'+ext;
					$.extend(settings, {mediaType : 'image'});	
				}
				else if(elementMediaType.indexOf('video')!=-1 && $.browserInfo.HTML5_Video)
				{
					if(
						((ext == 'ogg' ||ext == 'ogv') && $.browserInfo.video_ogg=='probably')||
						(ext == 'mp4' && $.browserInfo.video_mp4=='probably')||
						(ext == 'webm' && $.browserInfo.video_webm=='probably')
							){
								elementMediaType=elementMediaType+'/'+ext;
								$.extend(settings, {mediaType : 'video' });
							}
					else {
						if(
						((ext == 'ogg' ||ext == 'ogv') && $.browserInfo.video_ogg=='maybe')||
						(ext == 'mp4' && $.browserInfo.video_mp4=='maybe')||
						(ext == 'webm' && $.browserInfo.video_webm=='maybe')
							){
								elementMediaType=elementMediaType+'/'+ext;
								$.extend(settings, {mediaType : 'video' });	
							}
						else{
								elementMediaType = $.aplayer.setApplicationType(ext, elementMediaType, settings);
								$.extend(settings, {mediaType : 'embed' });		
						}
					}
				}
				else if(elementMediaType.indexOf('audio')!=-1 && $.browserInfo.HTML5_Audio)
				{
                    $.aplayer.IsHtml5Video = false;
					if(ext == 'mp3'|| ext == 'mp2' || ext == 'mpga')
					{	if($.browserInfo.audio_mpeg=='probably'){
							elementMediaType = 'audio/mpeg';
							$.extend(settings, {mediaType : 'audio' });
						}
						else if($.browserInfo.audio_mpeg=='maybe'){
							elementMediaType = 'audio/mpeg';
							$.extend(settings, {mediaType : 'audio' });
						}
						else {
							elementMediaType = 'audio/mpeg';
							$.extend(settings, {mediaType : 'embed' });
						}
					}
					if((ext == 'ogg' ||ext == 'oga'))
					{	if($.browserInfo.audio_ogg=='probably'){
							elementMediaType ='audio/ogg';
							$.extend(settings, {mediaType : 'audio' });
						}
						else if($.browserInfo.audio_ogg=='maybe'){
							elementMediaType ='audio/ogg';
							$.extend(settings, {mediaType : 'audio' });	
						}
						else{
							elementMediaType = 'audio/ogg';
							$.extend(settings, {mediaType : 'embed' });
						}
					}
					if(ext == 'm4a')
					{	if($.browserInfo.audio_x_m4a=='probably'){
							elementMediaType ='audio/x-m4a';
							$.extend(settings, {mediaType : 'audio' });
						}
						else if($.browserInfo.audio_x_m4a=='maybe'){
							elementMediaType ='audio/x-m4a';
							$.extend(settings, {mediaType : 'audio' });	
						}
						else{
							elementMediaType = 'audio/x-m4a';
							$.extend(settings, {mediaType : 'embed' });
						}
					}
					if(ext == 'wav')
					{	if($.browserInfo.audio_wav=='probably'){
							elementMediaType = 'audio/wav';
							$.extend(settings, {mediaType : 'audio' });
						}
						else if($.browserInfo.audio_wav=='maybe'){
							elementMediaType = 'audio/wav';
							$.extend(settings, {mediaType : 'audio' });
						}
						else{
							elementMediaType = 'audio/wav';
							$.extend(settings, {mediaType : 'embed' });
						}
					}
				}
				else
				{
                    $.aplayer.IsHtml5Video = false;
					elementMediaType = $.aplayer.setApplicationType(ext, elementMediaType, settings);
					$.extend(settings, {mediaType : 'embed' });
				}

				$.extend(settings, {'type':elementMediaType});
				
				
				
				return settings;
			},
			
			//устанавливает для элемента заданную позицию
            setMediaEltPosition : function(aplayer_id, top_left){
                var player = $('#'+aplayer_id);
				$('.ui-draggable, .'+$.aplayer.classElMedia, player)
				.css({
					'top':top_left.top,
					'left':top_left.left
				});
			},
			
			//возвращает текущую позицию медиаэлемента относительно контейнера
			getCurrentMediaEltPosition : function(aplayer_id){

				var top = $('.ui-draggable', '#'+aplayer_id).css('top');
				var left = $('.ui-draggable', '#'+aplayer_id).css('left');
				return {
							top: typeof(top)!='undefined'? top: '0px' ,
							left: typeof(left)!='undefined'? left: '0px'
						};
			},

			// Метод установки плеера
			play : function(element, settings){
				//Установка параметров переданных через $.fn.add()
				var sets = $.extend({}, $.aplayer.config, settings);

				//Определение и установка типа
				sets = $.aplayer.setType(sets);

				//Установка размеров плеера ('Inherit' - установка размеров родительского эл-та)
				try{
					if(sets.height.indexOf('Inherit')!=-1)sets.height = $(element).height();
				}catch(err){};
				try{
					if(sets.width.indexOf('Inherit')!=-1)sets.width = $(element).width();
				}catch(err){};

				//Создание контейнера для плеера;
				if(sets.nr==undefined){
					$.aplayer.aplayerNo++;
					sets.nr = $.aplayer.aplayerNo;
				}

				var container = $('<div style="overflow:hiden; " ></div>');
				$(container).attr('id', $.aplayer.idContainer+sets.nr);
				//Установка дополнительного класса
				$(container).addClass(settings['class']).addClass('aplayer');
				$(element).html(container);
                sets.mediaType = sets.mediaType.toLowerCase();

				/**
				 * Установка содержимого контейнера aplayer
				 */
				// установка лого_плей
				if(sets.logoPlay != undefined && (sets.logoPlay.indexOf('true')!=-1 ))
				{

					var setLogoPlay = $.extend({}, sets, {'src': $.aplayer.ControlBar.controlsImg +$.aplayer.logo_play });
					//создаем субконтейнер для логотипа плей
					var lgp = $('<div />').height($(container).height()).width($($(container).width()));

					//Вызов метода вывода изображения
					var im = $('<img class="logoPlay" src="'+$.aplayer.ControlBar.controlsImg +$.aplayer.logo_play+'">')
						.appendTo(container);

					$(container)
						.css({'text-align':'center', 'height': sets.height+'px' })
						.find('.logoPlay').css({'top': (($(container).height()- $(container).find('.logoPlay').height())/2)+'px' });

					var t = sets.mediaType;
					var s = sets.src;

                    $(container)
						.attr({'t':t,'s':s})
						.click(function(){
							$(this).parent().addPlayer({'src': $(this).attr('s'), 'type':'"'+$(this).attr('t')+'"' ,
								'controls':'mini', 'nr' : sets.nr }).click()
								.end().removeAttr('t').removeAttr('s').unbind('click');
						});
				}else{
					$.aplayer.checkCreateElem(container, sets);
				}

				$('[id^=cntr]',container).css($.aplayer.ControlBar.ControlsStyle);

				$.aplayer.scale[$.aplayer.idContainer+sets.nr]=0;
			},

			/**
			 * Создает необходимый контейнер $.aplayer'a для медиаэлемента
			 * @param container - Контейнер, куда будет помещен медиа элемент
			 * @param sets - Параметры медиаэлемента
			 */
			checkCreateElem : function (container, sets){
				if(sets.mediaType.toLowerCase() == 'vlc' && sets.protocol=='rtsp' ){
					$.aplayer.showVLC(container, sets);
				}
				else if (sets.mediaType == 'pseudo'){
					//Вызов метода установки motion jpeg
					$.aplayer.showPseudo(container, sets);
				}
				else if (sets.mediaType == 'mjpeg'){
					//Вызов метода установки motion jpeg

					$.aplayer.showMjpeg(container, sets);
				}
				else if(sets.mediaType == 'image'){
					//Вызов метода вывода изображения

					$.aplayer.showImage(container, sets);
				}
				//Если задано значение application - воспроизводить как внедренный объект
				else if(sets.mediaType == 'embed'){

					//Вызов метода для использования плагина
					$.aplayer.showObject(container, sets);
				}
				else if(sets.mediaType.toLowerCase() == 'vlc' && sets.protocol=='http'){
					$.aplayer.showVLC(container, sets);
				}
				else if(sets.mediaType == 'video'){
					$.aplayer.IsHtml5Video = true;
					//Вызов метода для использования HTML5 video
					$.aplayer.showVideo(container, sets);
				}
				else if(sets.mediaType == 'audio'){
					//Вызов метода для использования HTML5 audio
					$.aplayer.showAudio(container, sets);
				}
				else if(sets.mediaType == 'flowplayer'){

					//Вызов метода для установки flowplayer
					$.aplayer.embedFlowPlayer(container, sets);
					$.aplayer.draggable(container);
				}
				else{
					var typeObj = new Array();
					var typeElem = sets.mediaType.trim();
					if (typeElem.search(',')){
						typeObj = typeElem.split(',');
						$.aplayer.createEmbeddedPlayers(sets, typeObj, container);
					}else{
						alert('Error: undefined mediaType: '+sets.mediaType);
					}
				}

			},
			/**
			 * Выбор плеера в зависимости от установленных вариантов
			 * @param sets - Настройки aplayer
			 * @param typeObj - Массив, содержащий в себе необходимые объекты воспроизведения очереди
			 */
			createEmbeddedPlayers: function(sets, typeObj, container){
				var cont = container;
				var PlugInFind = false;
				for (var i in typeObj){
					var checkNewType = new checkType(typeObj[i], sets.src);
					if (checkNewType.checkCanPlayTypeFromPlugin()){
						sets.mediaType = typeObj[i].trim();
						PlugInFind = true;
						break;
					}
				}

				if (!PlugInFind){
					sets.mediaType = 'embed';
				}
				$.aplayer.checkCreateElem(container, sets);
			},

			//Метод вывода изображения
			showImage : function(container, settings){

                if(settings.useImageSize!=null && settings.useImageSize=='true')
				{
					//  title="'+settings.type+'"
					$('<img id="'+$.aplayer.idElMedia + $.aplayer.aplayerNo+'" src="'+settings.src+'" class="'+$.aplayer.classElMedia+'" name="img"/>').appendTo(container);
					return;
				}
				
				var size = 'style="width:'+settings.width+'px; height:'+settings.height+'px; "';
				// title="'+settings.type+'"
				var im = $('<img id="'+$.aplayer.idElMedia + $.aplayer.aplayerNo+'" class="'+$.aplayer.classElMedia+'" src="'+settings.src+'" '+size+' name="img"/>').attr({'height':settings.height, 'width':settings.width });
				$(im).appendTo(container);
			},
			
            // Create object vlc
			showVLC : function(container, settings){
                var toolbarShow = true;
                if (settings.src.search('://') === -1){
                    toolbarShow = false;
                }
                var html='';
            	var MediaURL = settings.src;
                settings.height = settings.height - $.aplayer.ControlBar.height - 10;
            	
            	var Autostart = (settings.autostart == "on" || settings.autostart == "true" || settings.autostart == true)? true:false;

            	if($.browser.msie){
            		//clsid:9BE31822-FDAD-461B-AD51-BE1D1C159921 //альтернативвный класс
                    html = '<OBJECT classid="clsid:E23FE9C6-778E-49D4-B537-38FCDE4887D8" codebase="http://downloads.videolan.org/pub/videolan/vlc/last/win32/axvlc.cab" ';
                    html+='width="'+settings.width+'" height="'+settings.height+'" ';
                    html+='class="'+$.aplayer.classElMedia+'"';
                    html+='id="'+'activX_'+settings.nr+'" events="True">';
                    html+='<param name="Src" value="'+ MediaURL +'" />';
                    html+='<param name="target" value="'+ MediaURL +'" />';
                    html+='<param name="ShowDisplay" value="True" />';
                    html+='<param name="AutoLoop" value="True" />';
                    html+='<param name="AutoPlay" value="'+Autostart+'" />';
                    html+='<param name="toolbar" value="' + toolbarShow + '" />';
            		html+='</OBJECT>';
            		$(container).html(html);
                  $.aplayer.activeX_arr['activX_'+$.aplayer.aplayerNo]=$('#activX_'+$.aplayer.aplayerNo ,container);
    			  		$('#activX_'+$.aplayer.aplayerNo ,container)
            		.addClass($.aplayer.classElMedia)
            		.css({
            			'width':settings.width,
            			'height':settings.height
            		});
            		return;
            		
            	}else{
            		html+='<embed type="application/x-vlc-plugin" name="vlc" autoplay="'+Autostart+'" loop="yes" \
            			progid="VideoLAN.VLCPlugin.2" pluginspage="http://www.videolan.org" \
				        id="'+$.aplayer.embedObj_idname+settings.nr+'" \
            			width="'+settings.width+'" height="'+settings.height+'" \
				        target="'+MediaURL+'" src="'+MediaURL+'" volume="100" toolbar="' + toolbarShow + '"></embed>';
            	
            		$(container).html(html);
            		
            		$('#activX_'+$.aplayer.aplayerNo ,container)
            		.addClass($.aplayer.classElMedia)
            		.css({
            			'width':settings.width,
            			'height':settings.height
            		});
            	}

                if (settings.src.search('://') === -1){
                    $.aplayer.setControls(container, settings);
                }
            },

			//Массив объектов
			activeX_arr:{},
			//Установка motion jpeg
			showMjpeg : function(container, settings){
				var messageError = $('<div></div>', {
					'class' : 'messageError'
				});
				var messageErrorText = $('<div></div>', {
					'class' : 'textError'
				});
				$(messageError).append(messageErrorText);
				if($.browserInfo.browser == 'msie'){
					
					var amc = document.createElement('object');
				      amc.id = 'activX_'+$.aplayer.aplayerNo;
				      amc.alt = 'Microsoft Internet Explorer on Windows system found. Try ActiveX viewer.';
				      amc.border = 0;
				      amc.hspace = 0;
				      amc.vspace = 0;
				      amc.width = settings.width;
				      amc.height = settings.height;
				      $(container).append(amc);

				      //amc.codeBase = "AMC.cab#Version=5,6,2,5";
				      amc.codeBase = "AMC.cab#Version=6,0,6,4";
				      //amc.classid = "clsid:745395C8-D0E1-4227-8586-624CA9A10A8D";
				      amc.classid = "clsid:DE625294-70E6-45ED-B895-CFFA13AEB044";

				      amc.UIMode = "none";
				      amc.ShowToolbar = (settings.controls == false)? false:true;
				      amc.ShowStatusBar = false;
				      amc.StretchToFit = true;
				      amc.Popups = 6;
				      amc.EnableReconnect = "True";
				      amc.EnableContextMenu = 1;
				      amc.MediaType = "mjpeg";
				      amc.MediaURL = settings.src;
				      amc.AutoStart = true;
				      $.aplayer.activeX_arr[amc.id]=amc;

                     amc.attachEvent('onclick', function(){
                         if(settings.amc_onclick!==undefined){
                             settings.amc_onclick(settings.nr);
                         }
                     });
				     $(amc).addClass($.aplayer.classElMedia);
				}else{
				//если HE ИнтернетЭксплорер
					
				var mim;
				var meHeight;
				
				var CORS = settings.crossorigin? 'crossorigin':'';
				
				//установка изображения
				if(settings.useImageSize!=null && settings.useImageSize=='true'){
					
					mim = $('<img  src="" name="mjpeg" '+CORS+' />');
				}
				else {
					var size = 'style="width:'+settings.width+'px; height:'+settings.height+'px; "';
					mim = $('<img src="" '+size+' name="mjpeg" '+CORS+' />').attr({'height':settings.height, 'width':settings.width });
				}
				$(mim)
				.attr({'id':$.aplayer.idElMedia+$.aplayer.aplayerNo, 'class':$.aplayer.classElMedia, 'origsrc':settings.src })
				.appendTo(container);
				
				//Если не устанавливать пенель контролов
				if(settings.controls==false || settings.controls=='off' ){
					$(mim).attr({'src':settings.src});
		            meHeight =  $('#'+$.aplayer.idContainer+ $.aplayer.aplayerNo).parent().height();
					$(ControlBar).css({
						'width': ($(ControlBar).parent().width())+'px'
					});
	
		            //Создаем субконтейнер для медиа-элемента, вставлем в него медиа элемент и помещаем в контейнер плеера
					var SubCont = $('<div></div>').addClass($.aplayer.classMediaCont).css({'overflow':'hidden'}).height(meHeight);
					$('#'+$.aplayer.idElMedia + $.aplayer.aplayerNo).height(meHeight).appendTo($(SubCont));

					$(SubCont).css({'position' : 'relative'});
					$(SubCont).append(messageError);
					//Устанавливаем субконтейнер медиа-элемента и панель контролов плеер
		            $('#'+$.aplayer.idContainer + $.aplayer.aplayerNo)
		            .append($(SubCont));
					
				}
				else{
					$(mim).css({'visibility':'hidden'});
						
					var Stop = $($.aplayer.ControlBar.stop)
						.css($.aplayer.ControlBar.ControlsContainers)
						.css({'padding':'5px'})
						.attr({ id:$.aplayer.idStop+$.aplayer.aplayerNo})
						.click(function(e){
							e.preventDefault();
							 $.aplayer.stopMjpegPlay(mim);
							 return false;
						})
						.children('img').attr({'src': $.aplayer.ControlBar.controlsImg+'Stop.png'})
						.end()
						.attr({'title':$.aplayer.config.dictionery.stop,'alt': $.aplayer.config.dictionery.stop });
	
					var Play = $($.aplayer.ControlBar.play)
						.css($.aplayer.ControlBar.ControlsContainers)
						.css({'padding':'5px'})
						.attr({ id: $.aplayer.idPlay+$.aplayer.aplayerNo })
						.click(function(e){
							e.preventDefault();
							 $.aplayer.startMjpegPlay(mim);
							 return false;
						})
						.children('img')
						.attr({'src': $.aplayer.ControlBar.controlsImg+'Play.png'})
						.end()
						.attr({'title':$.aplayer.config.dictionery.play,'alt': $.aplayer.config.dictionery.play });
	
					//Создаем панель контролов
		            var ControlBar = $($.aplayer.ControlBar.panel).height($.aplayer.ControlBar.height); //.css({'padding':'3px'});
		
		            $('#'+$.aplayer.idContainer+ $.aplayer.aplayerNo).height($('#'+$.aplayer.idContainer+ $.aplayer.aplayerNo).parent().parent().height());

                    meHeight =  $('#'+$.aplayer.idContainer+ $.aplayer.aplayerNo).parent().height()-$(ControlBar).height()-10;

						$(ControlBar).css({
							'width': ($(ControlBar).parent().width())+'px'
						});
						
						//вставляем в панель управления элементы управления
						$(ControlBar).attr('id',$.aplayer.idControlPanel+$.aplayer.aplayerNo)
						.append(Play)
						.append(Stop);
		            //Создаем субконтейнер для медиа-элемента, вставлем в него медиа элемент и помещаем в контейнер плеера
					var SubCont = $('<div></div>').addClass($.aplayer.classMediaCont).css({'overflow':'hidden'}).height(meHeight);
					$('#'+$.aplayer.idElMedia + $.aplayer.aplayerNo).height(meHeight).appendTo($(SubCont));

                    //Устанавливаем субконтейнер медиа-элемента и панель контролов плеер
		            $('#'+$.aplayer.idContainer + $.aplayer.aplayerNo)
		            .append($(SubCont))
		            .append($(ControlBar));

                    $(ControlBar).css({'border':'2px solid #333333', 'background-color':'#333333'});
		
		            //добавляем элемент масштаба
		            if(settings.scale=='on' || settings.scale=='true' || settings.scale==true){
		            	
		            	var Scale = $($.aplayer.ControlBar.scale)
							.css($.aplayer.ControlBar.ControlsContainers)
							.css({'padding':'5px', 'float':'right' })
							.attr({ id: $.aplayer.idScale+$.aplayer.aplayerNo })
							.attr({'title':$.aplayer.config.dictionery.scale,'alt': $.aplayer.config.dictionery.scale });
		            	
		            	$('.scbtn', Scale).css($.aplayer.ControlBar.Scale_btn)
						.click(function(e){
							e.preventDefault();
							$.aplayer.ControlBar.scaleClickHandler(e);
							return false;
						});
		            	$(ControlBar).append(Scale);
		            }
		
		            //если установлен автостарт
		            if(settings.autostart=='on' || settings.autostart=='true' || settings.autostart==true){
		            	$.aplayer.startMjpegPlay(mim);
		            }
				}//else

				//добавляем скролл-масштаб
	            if(settings.scale=='on' || settings.scale=='true' || settings.scale==true){
	            	try{
	            		// требуется подключение jquery.mousewheel.min.js
						$('.'+$.aplayer.classMediaCont , container).mousewheel(function(event, delta) {
							event.preventDefault();
							event.stopPropagation();
							$.aplayer.ControlBar.scaleWheelHandler(event, delta);
							return false;
						});
	            	}catch(error){}
	            }
				
				}//not IE
			},
			
			//начать воспроизведение Mjpeg
			startMjpegPlay : function(mim){
				$(mim).attr({'src': $(mim).attr('origsrc')+'&dummy='+Math.random() }).css({'visibility':'visible'});
			},

			//остановить воспроизведение Mjpeg
			stopMjpegPlay : function(mim){
				$(mim).attr({'src': $.aplayer.ControlBar.controlsImg+'Stopped.jpg'});
			},
			
			pauseMjpegPlay : function(mim, banner_url){
				banner_url = banner_url || $.aplayer.ControlBar.controlsImg+'Stopped.jpg';
				$(mim).attr({'src': banner_url});
			},
			
			
			//Установка motion jpeg
			showPseudo : function(container, settings){
				var freq = 130; //частота смены кадров в миллисекундах
				var mim;
				
				if(settings.freq!=null){
					freq = settings.freq;
				}
				
				//установка изображения
				if(settings.useImageSize!=null && settings.useImageSize=='true'){
					mim = $('<img  src="'+settings.src+'" name="img"/>');
				}
				else {
					var size = 'style="width:'+settings.width+'px; height:'+settings.height+'px; "';
					mim = $('<img  src="'+settings.src+'" '+size+' name="pseudo"/>').attr({'height':settings.height, 'width':settings.width });
				}
				
				$(mim)
				.attr({'id':$.aplayer.idElMedia+$.aplayer.aplayerNo, 'class':$.aplayer.classElMedia, 'origsrc':settings.src })
				.appendTo(container);
			
				//установка контролов
				if(settings.controls=='none' || settings.controls=='false' || settings.controls==false || settings.controls=='off' ){}
				else{
				var Stop = $($.aplayer.ControlBar.stop)
					.css($.aplayer.ControlBar.ControlsContainers)
					.css({'padding':'5px'})
					.attr({ id:$.aplayer.idStop+$.aplayer.aplayerNo})
					.click(function(e){
						e.preventDefault();
						 $.aplayer.stopPseudoPlay(mim);
						 return false;
					})
					.children('img').attr({'src': $.aplayer.ControlBar.controlsImg+'Stop.png'})
					.end()
					.attr({'title':$.aplayer.config.dictionery.stop,'alt': $.aplayer.config.dictionery.stop });

				var Play = $($.aplayer.ControlBar.play)
					.css($.aplayer.ControlBar.ControlsContainers)
					.css({'padding':'5px'})
					.attr({ id: $.aplayer.idPlay+$.aplayer.aplayerNo })
					.click(function(e){
						e.preventDefault();
						 $.aplayer.startPseudoPlay(mim, freq);
						 return false;
					})
					.children('img')
					.attr({'src': $.aplayer.ControlBar.controlsImg+'Play.png'})
					.end()
					.attr({'title':$.aplayer.config.dictionery.play,'alt': $.aplayer.config.dictionery.play });

				//Создаем панель контролов
	            var ControlBar = $($.aplayer.ControlBar.panel).height($.aplayer.ControlBar.height); //.css({'padding':'3px'});
	
	            $('#'+$.aplayer.idContainer+ $.aplayer.aplayerNo).height($('#'+$.aplayer.idContainer+ $.aplayer.aplayerNo).parent().parent().height());

	            var meHeight =  $('#'+$.aplayer.idContainer+ $.aplayer.aplayerNo).parent().height()-$(ControlBar).height()-10;
	
				//автоматическая подгонка панели контролов под размеры плеера = {controls:'auto'}
				if(settings.controls==null || settings.controls=='auto'){
					$(ControlBar).css({ 'width':'99.5%' });
					
					//вставляем в панель управления элементы управления
					$(ControlBar).attr('id',$.aplayer.idControlPanel+$.aplayer.aplayerNo)
					.append(Play)
					.append(Stop);
				}

                    //автоматическая подгонка панели контролов под размеры плеера = {controls:'auto'}
				if(settings.controls=='mini'){
					$(ControlBar).css({ 'width':'99.3%'  });
					
					//вставляем в панель управления элементы управления
					$(ControlBar).attr('id',$.aplayer.idControlPanel+$.aplayer.aplayerNo).css({ 'text-align':'center' })
					.append(Search).append(Play).append(Stop).append(soundOff).append(soundOn);
				}
	
	            //Создаем субконтейнер для медиа-элемента, вставлем в него медиа элемент и помещаем в контейнер плеера
				var SubCont = $('<div></div>').addClass($.aplayer.classMediaCont).css({'overflow':'hidden'}).height(meHeight);

				$('#'+$.aplayer.idElMedia + $.aplayer.aplayerNo).height(meHeight).appendTo($(SubCont));

				//Устанавливаем субконтейнер медиа-элемента и панель контролов плеер
	            $('#'+$.aplayer.idContainer + $.aplayer.aplayerNo)
	            .append($(SubCont))
	            .append($(ControlBar));

	            $(ControlBar).css({'border':'2px solid #333333', 'background-color':'#333333'});
			}
	            $.aplayer.startPseudoPlay(mim, freq);
			},
			
			//начать воспроизведение pseudo
			startPseudoPlay : function(mim, freq){
				var tid = setInterval(function(){
						var orig = $(mim).attr('origsrc');
						var pr = orig+'?'+ Math.random();
						$(mim).attr({'src': pr});
					}, freq);
				$(mim).attr({'tid':tid});
			},

			//остановить воспроизведение pseudo
			stopPseudoPlay : function(mim){
				clearInterval($(mim).attr('tid'));
			},

            onDraggable: function(container, settings){

                container.id;
                $.aplayer.applyHeight(container.height());
                $.aplayer.applyWidth(container.width());

            },

			//Метод для использования плагина
			showObject:function(container, settings){
             //Создаем object
             var obj;

             	if(settings.protocol=='rtsp' ){
             		$.aplayer.createObj_RTSP(settings, container);
             		return;
             	}
             	else  if(settings.type.indexOf($.aplayer.MIMEtypes.mp4)!=-1 || settings.type.indexOf($.aplayer.MIMEtypes.mov)!=-1) {
             		obj = $.aplayer.createObj_QuickTime(settings);
             	}
               //SWF
               else if(settings.type.indexOf($.aplayer.MIMEtypes.swf)!=-1 || settings.type.indexOf($.aplayer.MIMEtypes.flv)!=-1){
            	   obj = $.aplayer.createObj_SWF(settings);
               }
               //wmv - в IE глюки
               else if(settings.type.indexOf($.aplayer.MIMEtypes.wmv)!=-1){
            	   obj = $.aplayer.createObj_WMV(settings);
               }
               // audio/mp3
               else if(settings.type.indexOf($.aplayer.MIMEtypes.mp3)!=-1) {
            	   obj = $.aplayer.createObj_MP3(settings);
               }
               // audio/wav
               else if(settings.type.indexOf($.aplayer.MIMEtypes.wav)!=-1){
            	   obj = $.aplayer.createObj_WAV(settings);
               }
               // video AVI
               else if(settings.type.indexOf($.aplayer.MIMEtypes.avi)!=-1){
            	
            	   obj = $.aplayer.createObj_AVI(settings);
               }
               // audio wav
               else if(settings.type.indexOf($.aplayer.MIMEtypes.wav)!=-1) {
            	   obj = $.aplayer.createObj_WAV(settings);
               }
               else {
            	   obj = $.aplayer.create_Embed(settings);
               }
               $(container).height(settings.height);

               $(container).html(obj);
        	},

            //create EMBED
            create_Embed:function(settings){
            	var Autostart = (settings.autostart == "on" || settings.autostart == "true" || settings.autostart == true)? true:false;
                return $('<embed type="'+settings.type+'"autostart="'+Autostart+'" auto="'+Autostart+'" autoplay="'+Autostart+'" allowfullscreen="true" allowScriptAccess="always"  />'
                    ).attr({'src':settings.src , wmode:"transparent" })
                    .width(settings.width)
                    .height(settings.height);
           },

           //Create video AVI
           createObj_AVI: function(settings){
       	   var Autostart = (settings.autostart == "on" || settings.autostart == "true" || settings.autostart == true)? true:false;
			var obj = $('<object type="video/avi" data="'+settings.src+'" autoplay="false"\
					classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95" codebase="http://activex.microsoft.com/activex/controls/ \
					mplayer/en/nsmp2inf.cab#Version=6,4,7,1112" type="application/x-oleobject"> </object>')
				.append('<param name="src" value="'+settings.src+'" />')
				.append('<param name="controller" value="true" />')
				.append('<param name="autoplay" value="'+Autostart+'" />')
				.append('<param name="autostart" value="'+Autostart+'" />')
				.append('<param name="wmode" value="transparent" >');
//				.append('<param name="play" value="true" >');
				$(obj).width(settings.width).height(settings.height);
            $(obj).append( $($.aplayer.create_Embed(settings)));
            return obj;
           },

           //Create audio/wav
           createObj_WAV: function(settings){

               var obj;

               if($.browser.msie){
                    obj = $('<object data="'+settings.src+'" classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab"> </object>');
                }
                else{
					obj = $('<object type="audio/x-wav" data="'+settings.src+'" autoplay="false"> </object>');
                }
               var Autostart = (settings.autostart == "on" || settings.autostart == "true" || settings.autostart == true)? true:false;
				$(obj).append('<param name="src" value="'+settings.src+'" />')
				.append('<param name="controller" value="true" />')
				.append('<param name="autoplay" value="'+Autostart+'" />')
				.append('<param name="autostart" value="'+Autostart+'" />');
				$(obj).append('<param name="wmode" value="transparent" >')
				.append('<param name="play" value="'+Autostart+'" >');
				$(obj).width(settings.width).height(settings.height);
			$(obj).append($($.aplayer.create_Embed(settings)));
            return obj;
           },

           // Create object audio/mp3
           createObj_MP3:function(settings){

               var Autostart = (settings.autostart == "on" || settings.autostart == "true" || settings.autostart == true)? true:false;
			var obj = $('<object type="audio/x-mpeg" data="'+settings.src+'" autoplay="false"> </object>')
				.append('<param name="src" value="'+settings.src+'" />')
				.append('<param name="controller" value="true" />')
				.append('<param name="autoplay" value="'+Autostart+'" />')
				.append('<param name="autostart" value="0" />');
				$(obj).append('<param name="wmode" value="transparent" >')
				.append('<param name="play" value="'+Autostart+'" >');
				$(obj).width(settings.width).height(settings.height);
			$(obj).append($($.aplayer.create_Embed(settings)));
            return obj;
           },

            // Create object QuickTime
            createObj_QuickTime:function(settings){

                var Autostart = (typeof(settings.autostart)=='undefined' || settings.autostart == "on" || settings.autostart == "true" || settings.autostart == true)? true:false;
            	var Controls = ( settings.controls=='none' || settings.controls=='false' || settings.controls==false || settings.controls!='off')? false:true ;
            	if(typeof(settings.controls)=='undefined')Controls = true;
            	if($.browser.msie){
            		return $('<embed controller="'+Controls+'" play="'+Autostart+'" wmode="transparent" src="'+settings.src+'" style="width:'+settings.width+'px; height:'+settings.height+'px;" autostart="'+Autostart+'" showcontrols="true" type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/"></embed>');
            	}
                var obj = $('<object id="'+$.aplayer.embedObj_idname+$.aplayer.aplayerNo+'" classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab"></object>');
               $(obj).append('<param name="controller" value="'+Controls+'" />')
               	.append('<param name="src" value="'+settings.src+'" />');
               $(obj).append('<param name="wmode" value="transparent" >')
				.append('<param name="play" value="'+Autostart+'" >');
				$(obj).width(settings.width).height(settings.height);
				$(obj).append($($.aplayer.create_Embed(settings))
						.attr({ "TYPE":"image/x-macpaint",
							'Height' : settings.height,
							'name' : $.aplayer.embedObj_idname+$.aplayer.aplayerNo }));
               return obj;
            },

             // Create object SWF
            createObj_SWF:function(settings){

                var obj;

               if($.browser.msie){
                    obj = $('<object type="application/x-shockwave-flash data="'+settings.src+'" ></object>');
                }
                else{
                   obj = $('<object type="application/x-shockwave-flash" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"></object>');
                }
               var Autostart = (settings.autostart == "on" || settings.autostart == "true" || settings.autostart == true)? true:false;
               $(obj).append('<param name="movie" value="'+settings.src+'" />');
               $(obj).append('<param name="quality" value="high" >');
               $(obj).append('<param name="play" value="'+Autostart+'" >');
               $(obj).append('<param name="loop" value="0" >');
               $(obj).append('<param name="wmode" value="transparent" >');
               $(obj).append('<param name="scale" value="showall" >');
               $(obj).append('<param name="menu" value="1" >');
				$(obj).width(settings.width)
                .height(settings.height);
               $(obj).append($($.aplayer.create_Embed(settings)).removeAttr('type') );
               return obj;
            },

            // Create object WMV
            createObj_WMV:function(settings){

                var Autostart = (settings.autostart == "on" || settings.autostart == "true" || settings.autostart == true)? true:false;
               var obj = $('<object  type="video/x-ms-asf" classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6"  url="'+settings.src+'" data="'+settings.src+'" ></object>');
               $(obj).append('<param name="url" value="'+settings.src+'" />')
               .append('<param name="filename" value="'+settings.src+'" />')
               .append('<param name="autostart" value="'+Autostart+'">')
               .append('<param name="uiMode" value="full" />')
               .append('<param name="autosize" value="1">')
               .append('<param name="playcount" value="1">');
			 $(obj).append('<param name="wmode" value="transparent" >')
				.append('<param name="play" value="false" >');
				$(obj).width(settings.width)
                .height(settings.height);
			   $(obj).append('<embed type="audio/wav" play="'+Autostart+'" wmode="transparent" PLUGINSPAGE="http://www.microsoft.com/windows/windowsmedia/download/" src="'+settings.src+'" style="width:'+settings.width+'px; height:'+settings.height+'px;" autostart="'+Autostart+'" showcontrols="true"></embed>');
               return obj;
            },

            fplayersToSet : [],

            fplayer_ready_delegate : false,

            //внедрение FlowPlayer
            embedFlowPlayer : function(container, settings){

                if(!$.aplayer.config.isFlowPlayerLoaded){
                	if($.aplayer.config.isFPLoadingProgress){
                		//если flowplayer в процессе загрузки
                	}
                	else{
                		//если не в процессе загрузки - загружаем flowplayer
                		$.aplayer.loadFlowPlayer();
                	}

                	//помещаем параметры для установки плееров в очередь ожидания окончания загрузки
                	$.aplayer.fplayersToSet.push({ 'container':container,'settings':settings });

            	}else{
            		$.aplayer.setFlowPlayer(container, settings);            		
            	}

            	
            },

            loadFlowPlayer : function(){
            	if(!$.aplayer.config.isFlowPlayerLoaded && !$.aplayer.config.isFPLoadingProgress){
            		$.aplayer.config.isFPLoadingProgress = true;
            		
            		
            		$.getScript($.aplayer.config.path_prefix+'flowplayer/aplayerfncs.js' , function(data){

            			$.getScript(flowplayer_conf, function(data){
        	            	//тестовая задержка
        	            	//alert('load');
        	            		$.aplayer.config.isFlowPlayerLoaded = true;
        	            		$.aplayer.config.isFPLoadingProgress = false;
        	            		//Устанавливаем ожидающие загрузки плееры
        	            		$.each($.aplayer.fplayersToSet, function(i, val){
        	            			container = val.container;
        	            			settings = val.settings;
        	            			$.aplayer.setFlowPlayer(container, settings);
        	            		});
        	            		delete $.aplayer.fplayersToSet;
       	            	});            			
            		});
               	}
            },

            //Конфигурирует и устанавливает FlowPlayer
            setFlowPlayer : function(container, settings){
            	var id = $(container).attr('id');
            	var Autostart = (settings.autostart == undefined || settings.autostart == "on" || settings.autostart == "true" || settings.autostart == true)? true:false;
                var Controls =(typeof(settings.controls)=='undefined' || settings.controls=='embed')? {}:null;
                	//(settings.controls=='none' || settings.controls=='false' || settings.controls==false || settings.controls!='off')? null:{};

                var pl_nr = settings.nr;

                //Создаем субконтейнер для медиа-элемента, вставлем в него медиа элемент и помещаем в контейнер плеера
           	    var sub_cont_id = $.aplayer.idMediaCont+pl_nr;

				var con_h = settings.height;
           	    if(Controls==null){
           	    	con_h -= $.aplayer.ControlBar.height;
           	    }
               	var SubCont = $('<div style="overflow:hidden; position:relative;"></div>')
               		.attr({'id': sub_cont_id})
               		.addClass($.aplayer.classMediaCont)
               		.height(con_h);

            	//устанавливаем размеры плеера
            	$(container)
            	.height(settings.height)
            	.width(settings.width)
            	.append(SubCont);
            	
            	//устанавливаем flowplayer
            	var player = $f(sub_cont_id,
            		{
	            	    src: flowplayer_swf,
	            	    wmode: "opaque"
            	    },
            	    {
            	    clip: {
                	    url: settings.src,
            	    	provider: 'pseudo',
            	    	extension: settings.extension,
            	    	play: false,
            	    	autoPlay: Autostart,
            	    	autoBuffering: false,
            	    	onBegin : function(){
            	    	fp_uicontrol_handler.onClipBegin(settings.nr);
            	    	}
            	    },
            	
            	    // this will enable pseudostreaming support
            	    plugins: {
            	        pseudo: {
            	            //url: $.aplayer.config.path_prefix+"flowplayer/flowplayer.pseudostreaming-3.2.11.swf"
                            url: flowplayer_pseaudio
            	        },
            	        controls: Controls
            	    },

            	    onLoad : function(){

                        $('#'+sub_cont_id+'_api')
            	    	.addClass($.aplayer.classElMedia+' flowplayer')
						.css({
							'position':'absolute',
							'left':0,
							'top':0
							});

            	    	//установка пользовательских контролов
            	    	if(Controls==null){
                            if($('#controlPanel_'+settings.nr, container).length==0 ){
            	    		$.aplayer.setControls(container, settings);
                            }
            	    	}
            	    	
            	    	fp_uicontrol_handler.onPlayerLoaded(settings.nr);
            	    	
            	    	player.unmute();
            	    	player.setVolume(75);
            	    	
            	    	//отработка невыполненых функций во время загрузки скриптов
            	    	if($.aplayer.fplayer_ready_delegate){
            	    		$.aplayer.fplayer_ready_delegate();
            	    		$.aplayer.fplayer_ready_delegate = false;
            	    	}
            	    }
            	});
            },

            // Create object RTSP
            createObj_RTSP : function(settings, container){

                $.aplayer.showVLC(container, settings);
            	return;
            },

			//Метод для использования HTML5 video
			showVideo:function(container, settings){
				var vid = document.createElement("video");
				$(vid).attr({
                    width:[settings.width],
					height:[settings.height],
					src:[settings.src],
					type:[settings.type],
					id:[$.aplayer.idElMedia + settings.nr],
					'class':$.aplayer.classElMedia,
                    'preload': 'auto'
				}).appendTo(container);
				$.aplayer.setControls(container, settings);
			},

			//Mетод для использования HTML5 audio
			showAudio:function(container, settings){
				var audio = document.createElement("audio");
				$(audio).attr(
					{ width:[settings.width],
				//	height:[settings.height], //для аудио высоту не задаем
					src:[settings.src],
					type:[settings.type],
					id:[$.aplayer.idElMedia+$.aplayer.aplayerNo],
					'class':$.aplayer.classElMedia
				}).appendTo(container);
				$.aplayer.setControls(container, settings);
			},

			//Установка контролов

            //Номер текущего при установке элемента
			aplayerNo:-1,
            //Базовая часть идентификатора эл-та (без номера)
			idElMedia:'elMedia_',
			classElMedia:'ElMedia',
			classMediaCont:'MediaCont',
			idMediaCont:'MediaCont_',
			idContainer:'aplayerNo_',
			idControlPanel:'controlPanel_',
			idPlay: 'cntrlPlay_',
			idPause: 'cntrlPause_',
			idSearch: 'cntrSearch_',
			idDuration: 'cntrDuration_',
			idCurrentTime:'cntrCurrentTime_',
			idStop:'cntrStop_',
			idSoundOff:'cntrSoundOff_',
			idSoundOn:'cntrSoundOn_',
			idVolume:'cntrVolume_',
			idScale:'cntrScale_',
			embedObj_idname : 'objId_',

			logo_error: 'error.jpg',
			logo_play:'logo_play.png',
			
        ControlBar : {
			//Control's images location
        	// controlsImg:'aplayerControls/',
        	//controlsImg:'gallery/img/aplayerControls/',
        	controlsImg:'../img/aplayerControls/',
        	//controlsImg:'img/aplayerControls/',
        	
        	height : 42, // 37,
        	
			//Базовая разметка контролов
            panel:'<div style="cursor:default;"></div>',

			play: '<div > <img style="height:100%;" />  </div>',
            pause:'<div style="display:none;"> <img style="height:100%;" />  </div>',
			stop: '<div > <img style="height:100%;" /> </div>',

            search:'<div style="padding:1px;" ><div /></div>',

			duration:'<div>0:00:00</div>',
			currentTime:'<div>0:00:00</div>',
			soundOff:'<div><img style="height:100%;" /></div>',
			soundOn:'<div style="display:none;"><img style="height:100%; " /></div>',

			volume:'<div> <div class="divSlider" /> </div>',

			scale:'<div class="divScale"> <div class="scbtn reduce">-</div> <div class="scbtn increase">+</div> </div>',

			//Стиль для контролов
			ControlsStyle: {
				'cursor':'pointer'
			},

			/*Стиль элементов вывода времени и продолжительности */
			TimeFontStyle: {
				'height':'100%',
				'color':'silver',
				'font-family':'Verdana',
				'font-size':'16px',
				'font-weight':'bold',
				'text-align':'right',
                'margin-top' : '5px',
				'overflow':'hidden',
                'margin-left' : '5px',
                'float' : 'left'
	        },

 			/*Стили субКонтейнеров для элементов*/
			ControlsContainers: {
				'float':'left',
				'height':'25px'
	        },

	    	/*Стили кнопок масштаба*/
			Scale_btn: {
				'height':'22px',
				'width' :'22px',
				'float':'left',
				'text-align':'center',
				'color':'silver',
				'font-family':'Verdana',
				'font-size':'16px',
				'font-weight':'bold',
				'margin':'0 1 0 0'
				
	        },

            /*Стили слайдеров*/
	        Search_handle: {
	            'top': '-2px',
	            'margin-left': '-1px',
                'z-index': '2',
	            'width': '2px',
    	        'height': '12px', //'7px',
                'background-color': 'Darkred',
	            'cursor': 'default',
	            'border': '1px solid Darkred'
	        },
            Search_line: {
                'height':'10px',  // '5px',
                'padding-left':'5px',
				'width':'99.25%',
	            'border': '1px solid #555555',
	            'background-color': '#aaaaaa'
	        },

	         Volume_handle: {
                'top': '0px',
	            'margin-left': '-1px',
                'z-index': '2',
	            'width': '2px',
    	        'height': '8px',
                'background-color': 'Darkred',
	            'cursor': 'default',
	            'border': '1px solid Darkred'

	        },
            Volume_line: {
            	'top':'5px',
            	'width':'60px',
                'height':'10px',
                'padding-left':'5px',
	            'border': '1px solid Blue',
	            'background-color': 'Lightblue',
	            'left':'2px'
	        },

			//Обработчики событий медиа элемента
			elMediaOnTimeUpdate:function(ElNum){
                if($('#'+$.aplayer.idSearch+ElNum).attr('isPlaying')!= undefined) return;
                var MediaElt = $('#'+$.aplayer.idElMedia+ElNum)[0];
                if(MediaElt==undefined) return;
				var tDur = MediaElt.currentTime;

				if( tDur >= $('#'+$.aplayer.idElMedia+ElNum)[0].duration)
				{
					$('#'+$.aplayer.idElMedia+ElNum).each(function(){
						this.pause();
						this.currentTime = 0;
						$.aplayer.ControlBar.elMediaOnCanPlay(ElNum);
					});
					return;
				}
				$('#'+$.aplayer.idSearch+ElNum).slider('value', tDur*10);
				Math.round(tDur);
				var tStr = $.aplayer.ControlBar.FormatTime(tDur);
                if ($('#'+ $.aplayer.idContainer + ElNum).aplayerIsHTML5_Video() ||
                    $('#'+ $.aplayer.idContainer + ElNum).aplayerIsHTML5_Audio()){
                    var newTime = $.aplayer.ControlBar.FormatTimeReally(tDur, ElNum);
                }else{
                    var newTime = new Array(tStr, tDur);
                }

                $('#'+$.aplayer.idCurrentTime+ElNum).html(newTime[0]);
                $('#'+$.aplayer.idDuration+ElNum).html(newTime[1]);
			},

			elMediaOnCanPlay:function(ElNum){
				var MediaElt = $('#'+$.aplayer.idElMedia+ElNum)[0];

				if(MediaElt==undefined) return;
				var tDur = Math.round(MediaElt.duration);

				$('#'+$.aplayer.idSearch+ElNum).slider({'max': tDur*10 });
				tDur = Math.round($('#'+$.aplayer.idElMedia+ElNum)[0].currentTime);
				tStr = $.aplayer.ControlBar.FormatTime(tDur);

                if ($('#'+ $.aplayer.idContainer + ElNum).aplayerIsHTML5_Video() ||
                    $('#'+ $.aplayer.idContainer + ElNum).aplayerIsHTML5_Audio()){

                    var newTime = $.aplayer.ControlBar.FormatTimeReally(tDur, ElNum);
                }else{
                    var newTime = new Array(tStr, tDur);
                }
                $('#'+$.aplayer.idCurrentTime+ElNum).html(newTime[0]);
                $('#'+$.aplayer.idDuration+ElNum).html(newTime[1]);
			},

			elMediaOnPlay: function(ElNum){
                var me = $('#'+$.aplayer.idElMedia+ElNum);
                me.volume = $('#'+$.aplayer.idVolume+ElNum).slider('value')/40;

				$('#'+$.aplayer.idPlay+ElNum).hide();
				$('#'+$.aplayer.idPause+ElNum).show();
			},
			elMediaOnPause: function(ElNum){
                if($('#'+$.aplayer.idSearch+ElNum).attr('isPlaying')!=undefined) return;
				$('#'+$.aplayer.idPlay+ElNum).show();
				$('#'+$.aplayer.idPause+ElNum).hide();
			},
			elMediaOnEnded:function(ElNum){
                $('#'+$.aplayer.idPlay+ElNum).show();
				$('#'+$.aplayer.idPause+ElNum).hide();
			},
			elDurationChanged:function(ElNum){
                var tDur = Math.round($('#'+$.aplayer.idElMedia+ElNum)[0].duration);
				var tStr = $.aplayer.ControlBar.FormatTime(tDur);
                if ($.aplayer.IsHtml5Video){
                    var newTime = $.aplayer.ControlBar.FormatTimeReally(tDur, ElNum);
                    tStr = newTime[0];
                    tDur = newTime[1];
                }else{
                    var newTime = new Array(tStr, tDur);
                }
			},

            //Обработчики событий контролов
			soundOnClickHandler: function(ElNum){
               $('#'+$.aplayer.idElMedia+ElNum).each(function(){ this.muted=false; });
			   $('#'+$.aplayer.idSoundOff+ElNum).show();
			   $('#'+$.aplayer.idSoundOn+ElNum).hide();
            },

			soundOffClickHandler : function(ElNum){
               $('#'+$.aplayer.idElMedia+ElNum).each(function(){ this.muted=true; });
			   $('#'+$.aplayer.idSoundOff+ElNum).hide();
			   $('#'+$.aplayer.idSoundOn+ElNum).show();
            },

            playClickHandler : function(ElNum){
            	$('#'+$.aplayer.idElMedia+ElNum).each(function(){ this.play(); });
            },

            pauseClickHandler : function(ElNum){
               $('#'+$.aplayer.idElMedia+ElNum).each(function(){ this.pause(); });
            },

			stopClickHandler : function(ElNum){
               $('#'+$.aplayer.idElMedia+ElNum).each(function(){
					this.pause();
					try{
					this.currentTime = 0;
					}catch(error){
//						console.log(error.message);
					}

					$.aplayer.ControlBar.elMediaOnCanPlay(ElNum);
			   });
            },

            //Обработчик клика кнопок масштаба
            scaleClickHandler : function(e){
            	
            	var btn = $(e.target);
				//плеер
				var plNo = $(e.target).parent().attr('id').replace($.aplayer.idScale, '');
				var cur_player = $('#'+$.aplayer.idContainer+plNo);
				var width = $(cur_player).width();
				var height = $(cur_player).height();
				//медиа элемент
				var me = $('#'+$.aplayer.idElMedia+plNo );
				var me_width = $(me).width();
				var me_height = $(me).height();
				
				// шаг увеличения размерa
				var wm = width*$.aplayer.scaleFactor;
				var hm = height*$.aplayer.scaleFactor;

				if($(btn).hasClass('increase')){
			 		//если +
					me_width+=wm;
					me_height+=hm;
			 	}else{
			 		//если -
					me_width-=wm;
					me_height-=hm;
			 	}
				
				//Изменение размеров медиа-элемента плеера
				$(cur_player).parent().aplayerSetSizeMediaElt({
					'width':  me_width,
					'height': me_height
				} );
				
				if(me_width<=width){
					$(me).css({'left':'0px'});
				}
				if(me_height<=height){
					$(me).css({'top':'0px'});
				}
            },

            //Обработчик колеса мыши - изменение масштаба
            scaleWheelHandler : function(e, delta){
				//плеер
				var plNo = $(e.currentTarget).parent().attr('id').replace($.aplayer.idContainer, '');
				var cur_player = $('#'+$.aplayer.idContainer+plNo);
				var width = $(cur_player).width();
				var height = $(cur_player).height();
				
				//медиа элемент
				var me = $('#'+$.aplayer.idElMedia+plNo );
				var me_width = $(me).width();
				var me_height = $(me).height();
				
				// шаг увеличения размерa
				var wm = width*$.aplayer.scaleFactor;
				var hm = height*$.aplayer.scaleFactor;
				
				if (delta > 0) {
			 		//если +
					me_width+=wm;
					me_height+=hm;
					$.aplayer.scale[$.aplayer.idContainer+plNo]++;
				}else{
			 		//если -
					me_width-=wm;
					me_height-=hm;
					$.aplayer.scale[$.aplayer.idContainer+plNo]--;
			 	}
			 					
				//Изменение размеров медиа-элемента плеера
				$(cur_player).parent().aplayerSetSizeMediaElt({
					'width':  me_width,
					'height': me_height
				} );
				
				if(me_width<=width){
					$(me).css({'left':'0px'});
				}
				if(me_height<=height){
					$(me).css({'top':'0px'});
				}
            },

			searchOnStartHandler : function(Elem){
                var me = $('#'+$.aplayer.idElMedia+$(Elem).attr('No'))[0];
                if(!me.paused) $(Elem).attr({'isPlaying':'true'});
			    else $(Elem).attr({'isPlaying':'false'});
				me.pause();
            },

            getDurationElement : function (Elem){
                var MediaElt = $('#'+$.aplayer.idElMedia+Elem)[0];
                if(MediaElt==undefined) return '00:00:00';
                return $.aplayer.ControlBar.FormatTime(Math.round(MediaElt.duration));

            },

			searchOnStopHandler : function(Elem){
                var me = $('#'+$.aplayer.idElMedia+$(Elem).attr('No'))[0];
                var numElMedia = me.id.substr($.aplayer.idElMedia.length);
				$(me).each(function(){
					var setTime = ($(Elem).slider('value'))/10;
					this.currentTime = setTime;
					var tStr = $.aplayer.ControlBar.FormatTime(setTime);

                    if ($.aplayer.IsHtml5Video){
                        var newTime = $.aplayer.ControlBar.FormatTimeReally(setTime, numElMedia);
                    }else{
                        var newTime = new Array(tStr, $.aplayer.getDurationElement(numElMedia));
                    }

                    $('#'+$.aplayer.idCurrentTime+$(Elem).attr('No')).html(newTime[0]);
                    $('#'+$.aplayer.idDuration+Elem).html(newTime[1]);
			   });
               $(me).each(function(){
					if($(Elem).attr('isPlaying')=='true')me.play();
					$(Elem).removeAttr('isPlaying');
			   });
            },

			searchOnSlideHandler : function(Elem){
                var me = $('#'+$.aplayer.idElMedia+$(Elem).attr('No'))[0];
                var numElMedia = me.id.substr($.aplayer.idElMedia.length);
				$(me).each(function(){
					var setTime = ($(Elem).slider('value'))/10;
					this.currentTime = setTime;
					var tStr = $.aplayer.ControlBar.FormatTime(setTime);
                    if ($.aplayer.IsHtml5Video){
                        var newTime = $.aplayer.ControlBar.FormatTimeReally(setTime, numElMedia);
                    }else{
                        var newTime = new Array(tStr, $.aplayer.getDurationElement(numElMedia));
                    }

                    $('#'+$.aplayer.idCurrentTime+$(Elem).attr('No')).html(newTime[0]);
                    $('#'+$.aplayer.idDuration+Elem).html(newTime[1]);
			   });
            },

            //формирование строки времени
            FormatTime : function(Seconds){
                return  (Math.floor(Seconds/(60*60)))+':'
                		+((Math.floor(Seconds/60)%60)<10? '0'+(Math.floor(Seconds/60)%60):(Math.floor(Seconds/60)%60))+':'
                		+(Math.floor(Seconds%60)<10? '0'+Math.floor(Seconds%60):Math.floor(Seconds%60));
            },

            FormatTimeReally : function(tCur, ElNum){
                var tempDate = matrix.events[ElNum][9];
                var nDate = tempDate.replace(new RegExp("-",'g'),"/").split(' ');
				return [nDate[1], nDate[0]];

            },

			volumeSlideHandler : function(Elem){
                var me = $('#'+$.aplayer.idElMedia+$(Elem).attr('No'))[0];
                me.volume = $(Elem).slider('value')/40;
            },

            volumeStopHandler : function(Elem){
                var me = $('#'+$.aplayer.idElMedia+$(Elem).attr('No'))[0];
                me.volume = $(Elem).slider('value')/40;
                $(Elem).hide();
            }
        },

		//Установка контролов
		setControls : function(container, settings)
		{
            var time = $.aplayer.ControlBar.FormatTimeReally(0, settings.nr);
			if(typeof(settings.controls)!='undefined' && (settings.controls=='none' || settings.controls=='false' || settings.controls==false || settings.controls=='off')){
				return;
			}
			//Использовать контролы браузера
			else if(settings.controls=='browser'){
				$(container).find('.'+$.aplayer.classElMedia).attr('controls', 'controls');
				return;
			}

			// определение обработчиков событий обычные/flowplayer
			var evt_handler = {};
			var str_evt_handler = '';
			if(settings.mediaType =='flowplayer' && typeof(fp_uicontrol_handler)!='undefined' ){
				evt_handler = fp_uicontrol_handler;
				str_evt_handler = 'fp_uicontrol_handler';
			}else if (settings.mediaType.toLowerCase() === 'vlc' && typeof(vlcPlayer) !== 'undefined'){
                evt_handler = vlcPlayer;
                str_evt_handler = 'vlcPlayer';
            }else{
				evt_handler = $.aplayer.ControlBar;
				str_evt_handler = '$.aplayer.ControlBar';
			}
			var Volume = $($.aplayer.ControlBar.volume)
			.css($.aplayer.ControlBar.ControlsContainers)
			.attr({'title':$.aplayer.config.dictionery.volume});
			
			$(Volume).find('.divSlider').slider({
            	range: "min",
                min : 0,
                max : 40,
			    value: 30,
                orientation: 'horizontal',
                slide:function(event, ui){evt_handler.volumeSlideHandler(this);},
                change:function(event, ui){evt_handler.volumeSlideHandler(this);}
			}).attr({
                'id': $.aplayer.idVolume+settings.nr,
                'No':settings.nr
                })
                .find('.ui-slider-range')
                .addClass('ui-corner-all')
                .attr({'left':'2px'});

            //Установка стилей ползунка
            $(Volume).find('.ui-slider-handle').css($.aplayer.ControlBar.Volume_handle).removeClass('ui-state-default');
            //Установка стилей полосы поиска
            $(Volume).find('.ui-slider').css($.aplayer.ControlBar.Volume_line);

			var soundOn = $($.aplayer.ControlBar.soundOn)
				.css($.aplayer.ControlBar.ControlsContainers)
				.attr({
					onclick: str_evt_handler+'.soundOnClickHandler("'+settings.nr+'")',
					id:$.aplayer.idSoundOn+settings.nr
				})
				.children('img')
				.attr({'src': $.aplayer.ControlBar.controlsImg+'SndOn.png'})
				.end()
				.attr({'title':$.aplayer.config.dictionery.soundOn,'alt': $.aplayer.config.dictionery.soundOn});

			var soundOff = $($.aplayer.ControlBar.soundOff)
				.css($.aplayer.ControlBar.ControlsContainers)
				.attr({
					onclick: str_evt_handler+'.soundOffClickHandler("'+settings.nr+'")',
					id:$.aplayer.idSoundOff+settings.nr
				})
				.children('img')
				.attr({'src': $.aplayer.ControlBar.controlsImg+'SndOff.png'})
				.end()
				.attr({'title':$.aplayer.config.dictionery.soundOff,'alt': $.aplayer.config.dictionery.soundOff });
			var CurrentTime = $('<div>' + time[0] + '</div>')
				.attr({'id':$.aplayer.idCurrentTime+settings.nr, 'title':$.aplayer.config.dictionery.currentTime})
				.css($.aplayer.ControlBar.TimeFontStyle);

			var Duration = $('<div>' + time[1] + '</div>')
				.attr({'id':$.aplayer.idDuration+settings.nr, 'title':$.aplayer.config.dictionery.duration})
				.css($.aplayer.ControlBar.TimeFontStyle);

            var Times = $('<div />').append(Duration).append(CurrentTime);

            if (typeof matrix.mode !== 'undefined' && matrix.mode == 'preview'){
                $(Duration).hide()
            }else{
                $(Duration).show();
            }
			var Search = $($.aplayer.ControlBar.search).attr({'title':$.aplayer.config.dictionery.search});
			$(Search).find(':first-child').slider({
			    range: "min",
			    value: 0,
			    start:function(event, ui){ evt_handler.searchOnStartHandler(this);},
                stop:function(event, ui){ evt_handler.searchOnStopHandler(this);},
                slide:function(event, ui){ evt_handler.searchOnSlideHandler(this);}
			}).attr({
                'id': $.aplayer.idSearch+settings.nr,
                'No':settings.nr
                })
                .find('.ui-slider-range')
                .addClass('ui-corner-all')
                .attr({'left':'2px', 'width':'99.5%'});

            //Установка стилей ползунка
            $(Search).find('.ui-slider-handle')
                .css($.aplayer.ControlBar.Search_handle)
                .removeClass('ui-state-default');

            //Установка стилей полосы поиска
            $(Search)
                .find('.ui-slider')
                .css($.aplayer.ControlBar.Search_line);

			var Stop = $($.aplayer.ControlBar.stop)
				.css($.aplayer.ControlBar.ControlsContainers)
				.attr({
					//onclick: '$.aplayer.ControlBar.stopClickHandler("'+settings.nr+'")',
					onclick: str_evt_handler+'.stopClickHandler("'+settings.nr+'")',
					id:$.aplayer.idStop+settings.nr
				})
				.children('img')
				.attr({'src': $.aplayer.ControlBar.controlsImg+'Stop.png'})
				.end()
				.attr({'title':$.aplayer.config.dictionery.stop,'alt': $.aplayer.config.dictionery.stop });

			var Play = $($.aplayer.ControlBar.play)
				.css($.aplayer.ControlBar.ControlsContainers)
				.attr({
						//onclick: '$.aplayer.ControlBar.playClickHandler("'+settings.nr+'")',
						onclick: str_evt_handler+'.playClickHandler("'+settings.nr+'")',
						id: $.aplayer.idPlay+settings.nr
					})
					.children('img')
					.attr({'src': $.aplayer.ControlBar.controlsImg+'Play.png'})
					.end()
					.attr({'title':$.aplayer.config.dictionery.play,'alt': $.aplayer.config.dictionery.play });

			var Pause = $($.aplayer.ControlBar.pause)
				.css($.aplayer.ControlBar.ControlsContainers)
				.attr({
						//onclick: '$.aplayer.ControlBar.pauseClickHandler("'+settings.nr+'")',
					onclick: str_evt_handler+'.pauseClickHandler("'+settings.nr+'")',
					id:$.aplayer.idPause+settings.nr
				})
				.children('img')
				.attr({'src': $.aplayer.ControlBar.controlsImg+'Pause.png'})
				.end()
				.attr({'title':$.aplayer.config.dictionery.pause,'alt': $.aplayer.config.dictionery.pause });
			
			//Создаем панель контролов
            var ControlBar = $($.aplayer.ControlBar.panel).height($.aplayer.ControlBar.height);

            $('#'+$.aplayer.idContainer+ settings.nr).height($('#'+$.aplayer.idContainer+ settings.nr).parent().height());

            var meHeight =  $('#'+$.aplayer.idContainer+ settings.nr).parent().height()-$(ControlBar).height()-10;
			//автоматическая подгонка панели контролов под размеры плеера = {controls:'auto'}
			if(settings.controls==null || settings.controls=='auto'){
				$(ControlBar).css({
					'width': ($(ControlBar).parent().width())+'px'
				});
				
				//вставляем в панель управления элементы управления
				$(ControlBar)
				.attr('id',$.aplayer.idControlPanel+settings.nr)
				.append(Search)
				.append(Play)
				.append(Pause)
				.append(Stop)
				.append(Times)
				.append(soundOff)
				.append(soundOn)
				.append(Volume);
			}

			//автоматическая подгонка панели контролов под размеры плеера = {controls:'mini'}
			if(settings.controls=='mini'){
				$(ControlBar).css({
					'width': ($(ControlBar).parent().width())+'px'
				});
				
				//вставляем в панель управления элементы управления
				$(ControlBar)
				.attr('id',$.aplayer.idControlPanel+settings.nr)
				.css({ 'text-align':'center' })
				.append(Search)
				.append(Play)
				.append(Pause)
				.append(Stop)
				.append(soundOff)
				.append(soundOn)
                .append(Times);
			}

			$('img', ControlBar).addClass('cntr_img').css({'float':'left'});
				
			if(settings.mediaType =='flowplayer' && typeof(fp_uicontrol_handler)!='undefined' ){
				//Устанавливаем панель контролов в медиа плеер
	            $('#'+$.aplayer.idContainer + settings.nr ).append($(ControlBar));

			}else{
	            //если не flowplayer Создаем субконтейнер для медиа-элемента, вставлем в него медиа элемент и помещаем в контейнер плеера
 				var SubCont = $('<div style="overflow:hidden; position:relative;"></div>')
					.addClass($.aplayer.classMediaCont)
					.css({'overflow':'hidden'})
					.height(meHeight);

                if ($('#'+$.aplayer.idElMedia + settings.nr).attr('id') !== undefined){
                    $('#'+$.aplayer.idElMedia + settings.nr)
                        .height(meHeight)
                        .appendTo($(SubCont));
                }else{//VLC
                    $('#' + $.aplayer.embedObj_idname + settings.nr)
                        .height(meHeight)
                        .appendTo($(SubCont));
                }
				//Устанавливаем субконтейнер медиа-элемента и панель контролов в медиа плеер HTML5
	            $('#'+$.aplayer.idContainer + settings.nr ).append($(SubCont)).append($(ControlBar));
			}
			
			
			$(ControlBar).css({'border':'2px solid #333333', 'background-color':'#333333'});
			

            //добавляем элемент масштаба
            try{
				if(settings.scale=='on' || settings.scale=='true' || settings.scale==true){
							
	            	var Scale = $($.aplayer.ControlBar.scale)
						.css($.aplayer.ControlBar.ControlsContainers)
						.css({'padding':'5px', 'float':'right' })
						.attr({ id: $.aplayer.idScale+settings.nr })
						.attr({'title':$.aplayer.config.dictionery.scale,'alt': $.aplayer.config.dictionery.scale });
	            	
	            	$('.scbtn', Scale).css($.aplayer.ControlBar.Scale_btn)
					.click(function(e){
						e.preventDefault();
						evt_handler.scaleClickHandler(e);
						return false;
					});
	            	$(ControlBar).append(Scale);
	            }
            }catch(e){
//            	console.log(e);
            }

			//Устанавливаем на медиа эл-т обработчики событий
			$('#'+$.aplayer.idElMedia + settings.nr).attr({
				ontimeupdate:'$.aplayer.ControlBar.elMediaOnTimeUpdate('+settings.nr+')',
				oncanplay: '$.aplayer.ControlBar.elMediaOnCanPlay('+settings.nr+')',
				onplay: '$.aplayer.ControlBar.elMediaOnPlay('+settings.nr+')',
				onpause:'$.aplayer.ControlBar.elMediaOnPause('+settings.nr+')',
				onended:'$.aplayer.ControlBar.elMediaOnEnded('+settings.nr+')',
				ondurationchanged:'$.aplayer.ControlBar.elDurationChanged('+settings.nr+')'
			});

			//set handlers on flowplayer
			if($('#'+$.aplayer.idContainer+settings.nr).aplayerIsFlowPlayer()){
				$f($.aplayer.idMediaCont+settings.nr)
					.onStart(function(){ fp_uicontrol_handler.elMediaOnPlay(settings.nr); })
					.onStop(function(){ fp_uicontrol_handler.elMediaOnStop(settings.nr); })
					.onPause(function(){ fp_uicontrol_handler.elMediaOnPause(settings.nr); })
					.onResume(function(){ fp_uicontrol_handler.elMediaOnResume(settings.nr); })
					.onFinish(function(){ fp_uicontrol_handler.elMediaOnFinish(settings.nr); })
					.onSeek(function(){ fp_uicontrol_handler.elMediaOnSeek(settings.nr); })
					.onBeforeSeek(function(){ fp_uicontrol_handler.elMediaOnBeforeSeek(settings.nr); })
			}
		}
    };

	

//GetBrowserInfo
	//Инкапсулирует данные о браузере
	$.browserInfo = {
		
		browser:'undefined',
		version:'undefined',
		
		HTML5_Audio:false,
		HTML5_Video:false,

		video_mp4:false,
		video_ogg:false,
		video_webm:false,

		audio_ogg:false,
		audio_mpeg:false,
		audio_wav:false,
		audio_x_m4a:false,

		isDefined:false,

		//Выводит значения свойств browserInfo
		Show:function(){
			var str='';
			for(var p in $.browserInfo){
				if ($.browserInfo[p].toString().indexOf('function')==-1)
					str += p +" = " +$.browserInfo[p]+'\n';
			}
			alert(str);
		},

		//Сформировать данные о браузере
		getInfo:function(){
			if(!$.browserInfo.isDefined){
			$.browserInfo.defineBrowserType();
			$.browserInfo.support_HTML5_Audio();
			$.browserInfo.support_HTML5_Video();
			$.browserInfo.isDefined = true;
			}
		},

		//Определение типа и версии браузера
		defineBrowserType : function()
		{
			var userAgent = navigator.userAgent.toLowerCase();
			$.browser.chrome = /chrome/.test(userAgent);
			if($.browser.chrome) {
				$.browserInfo.browser = 'chrome';
				userAgent = userAgent.substring(userAgent.indexOf('chrome/') +7);
			  	userAgent = userAgent.substring(0,userAgent.indexOf('.'));
				$.browser.version = userAgent;
				 // If it is chrome then jQuery thinks it's safari so we have to tell it it isn't
				$.browser.safari = false;
			}
			else if($.browser.safari){
				$.browserInfo.browser = 'safari';
				userAgent = userAgent.substring(userAgent.indexOf('safari/') +7);
 				userAgent = userAgent.substring(0,userAgent.indexOf('.'));
  				$.browser.version = userAgent;
			}
			else if($.browser.opera)$.browserInfo.browser = 'opera';
    		else if($.browser.msie)$.browserInfo.browser = 'msie';
    		else if($.browser.mozilla)$.browserInfo.browser = 'mozilla';
    		$.browserInfo.version = $.browser.version;
		},
		
		//Check HTML5_Audio
		support_HTML5_Audio:function(){
			try{
			var audio = document.createElement("audio");
			$.browserInfo.HTML5_Audio = audio instanceof HTMLAudioElement;
			}catch(e){};
			if($.browserInfo.HTML5_Audio)
			{
				$.browserInfo.support_audio_ogg(audio);
				$.browserInfo.support_audio_mpeg(audio);
				$.browserInfo.support_audio_wav(audio);
				$.browserInfo.support_audio_x_m4a(audio);
			}
		},

		//Check HTML5_Video
		support_HTML5_Video:function(src){
			var video = document.createElement("video");
            if (src){
                video.src = src;
            }
			try{
				$.browserInfo.HTML5_Video = video instanceof HTMLVideoElement;
			}catch(e){};
			if($.browserInfo.HTML5_Video)
			{
				//Вызов методов определения поддерживаемых типов
				$.browserInfo.support_video_mp4(video);
				$.browserInfo.support_video_ogg(video);
				$.browserInfo.support_video_webm(video);
			}
		},

		//Check type video/mp4
		support_video_mp4 :function(video){
			$.browserInfo.video_mp4 = video.canPlayType('video/mp4; codecs="avc1.42E01E"');
			if($.browserInfo.video_mp4=='')
				$.browserInfo.video_mp4 = video.canPlayType('video/mp4; codecs="avc1.42E01E, mp4a.40.2"');
			if($.browserInfo.video_mp4=='')$.browserInfo.video_mp4 = video.canPlayType('video/mp4');
			if($.browserInfo.video_mp4=='')$.browserInfo.video_mp4=false;
		},

		//Check type video/ogg
		support_video_ogg :function(video){
			$.browserInfo.video_ogg = video.canPlayType('video/ogg; codecs="theora"');
			if($.browserInfo.video_ogg=='')$.browserInfo.video_ogg = video.canPlayType('video/ogg;');
			if($.browserInfo.video_ogg=='')$.browserInfo.video_ogg=false;
		},

		//Check type video/webm
		support_video_webm:function(video){
			$.browserInfo.video_webm = video.canPlayType('video/webm; codecs="vp8, vorbis"');
			if($.browserInfo.video_webm=='')$.browserInfo.video_webm = video.canPlayType('video/webm;');
			if($.browserInfo.video_webm=='')$.browserInfo.video_webm=false;
		},

		support_audio_ogg:function(audio){
			$.browserInfo.audio_ogg = audio.canPlayType('audio/ogg; codecs="vorbis"');
			if($.browserInfo.audio_ogg=='')$.browserInfo.audio_ogg = audio.canPlayType('audio/ogg');
			if($.browserInfo.audio_ogg=='')$.browserInfo.audio_ogg=false;
		},
		support_audio_mpeg:function(audio){
			$.browserInfo.audio_mpeg = audio.canPlayType('audio/mpeg;');
			if($.browserInfo.audio_mpeg=='')$.browserInfo.audio_mpeg = audio.canPlayType('audio/mpeg;');
			if($.browserInfo.audio_mpeg=='')$.browserInfo.audio_mpeg=false;
		},
		support_audio_wav:function(audio){
			$.browserInfo.audio_wav = audio.canPlayType('audio/wav; codecs="1"');
			if($.browserInfo.audio_wav=='')$.browserInfo.audio_wav = audio.canPlayType('audio/wav');
			if($.browserInfo.audio_wav=='')$.browserInfo.audio_wav=false;
		},
		support_audio_x_m4a:function(audio){
			$.browserInfo.audio_x_m4a = audio.canPlayType('audio/x-m4a;');
			if($.browserInfo.audio_x_m4a=='')$.browserInfo.audio_x_m4a = audio.canPlayType('audio/aac;');
			if($.browserInfo.audio_x_m4a=='')$.browserInfo.audio_x_m4a=false;
		}

	};

	/*
	 * jQuery UI Draggable 1.8.16
	 *
	 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
	 * Dual licensed under the MIT or GPL Version 2 licenses.
	 * http://jquery.org/license
	 *
	 * http://docs.jquery.com/UI/Draggables
	 *
	 * Depends:
	 *	jquery.ui.core.js
	 *	jquery.ui.mouse.js
	 *	jquery.ui.widget.js
	 */
	(function(d){d.widget("ui.draggable",d.ui.mouse,{widgetEventPrefix:"drag",options:{addClasses:true,appendTo:"parent",axis:false,connectToSortable:false,containment:false,cursor:"auto",cursorAt:false,grid:false,handle:false,helper:"original",iframeFix:false,opacity:false,refreshPositions:false,revert:false,revertDuration:500,scope:"default",scroll:true,scrollSensitivity:20,scrollSpeed:20,snap:false,snapMode:"both",snapTolerance:20,stack:false,zIndex:false},_create:function(){if(this.options.helper==
	"original"&&!/^(?:r|a|f)/.test(this.element.css("position")))this.element[0].style.position="relative";this.options.addClasses&&this.element.addClass("ui-draggable");this.options.disabled&&this.element.addClass("ui-draggable-disabled");this._mouseInit()},destroy:function(){if(this.element.data("draggable")){this.element.removeData("draggable").unbind(".draggable").removeClass("ui-draggable ui-draggable-dragging ui-draggable-disabled");this._mouseDestroy();return this}},_mouseCapture:function(a){var b=
	this.options;if(this.helper||b.disabled||d(a.target).is(".ui-resizable-handle"))return false;this.handle=this._getHandle(a);if(!this.handle)return false;if(b.iframeFix)d(b.iframeFix===true?"iframe":b.iframeFix).each(function(){d('<div class="ui-draggable-iframeFix" style="background: #fff;"></div>').css({width:this.offsetWidth+"px",height:this.offsetHeight+"px",position:"absolute",opacity:"0.001",zIndex:1E3}).css(d(this).offset()).appendTo("body")});return true},_mouseStart:function(a){var b=this.options;
	this.helper=this._createHelper(a);this._cacheHelperProportions();if(d.ui.ddmanager)d.ui.ddmanager.current=this;this._cacheMargins();this.cssPosition=this.helper.css("position");this.scrollParent=this.helper.scrollParent();this.offset=this.positionAbs=this.element.offset();this.offset={top:this.offset.top-this.margins.top,left:this.offset.left-this.margins.left};d.extend(this.offset,{click:{left:a.pageX-this.offset.left,top:a.pageY-this.offset.top},parent:this._getParentOffset(),relative:this._getRelativeOffset()});
	this.originalPosition=this.position=this._generatePosition(a);this.originalPageX=a.pageX;this.originalPageY=a.pageY;b.cursorAt&&this._adjustOffsetFromHelper(b.cursorAt);b.containment&&this._setContainment();if(this._trigger("start",a)===false){this._clear();return false}this._cacheHelperProportions();d.ui.ddmanager&&!b.dropBehaviour&&d.ui.ddmanager.prepareOffsets(this,a);this.helper.addClass("ui-draggable-dragging");this._mouseDrag(a,true);d.ui.ddmanager&&d.ui.ddmanager.dragStart(this,a);return true},
	_mouseDrag:function(a,b){this.position=this._generatePosition(a);this.positionAbs=this._convertPositionTo("absolute");if(!b){b=this._uiHash();if(this._trigger("drag",a,b)===false){this._mouseUp({});return false}this.position=b.position}if(!this.options.axis||this.options.axis!="y")this.helper[0].style.left=this.position.left+"px";if(!this.options.axis||this.options.axis!="x")this.helper[0].style.top=this.position.top+"px";d.ui.ddmanager&&d.ui.ddmanager.drag(this,a);return false},_mouseStop:function(a){var b=
	false;if(d.ui.ddmanager&&!this.options.dropBehaviour)b=d.ui.ddmanager.drop(this,a);if(this.dropped){b=this.dropped;this.dropped=false}if((!this.element[0]||!this.element[0].parentNode)&&this.options.helper=="original")return false;if(this.options.revert=="invalid"&&!b||this.options.revert=="valid"&&b||this.options.revert===true||d.isFunction(this.options.revert)&&this.options.revert.call(this.element,b)){var c=this;d(this.helper).animate(this.originalPosition,parseInt(this.options.revertDuration,
	10),function(){c._trigger("stop",a)!==false&&c._clear()})}else this._trigger("stop",a)!==false&&this._clear();return false},_mouseUp:function(a){this.options.iframeFix===true&&d("div.ui-draggable-iframeFix").each(function(){this.parentNode.removeChild(this)});d.ui.ddmanager&&d.ui.ddmanager.dragStop(this,a);return d.ui.mouse.prototype._mouseUp.call(this,a)},cancel:function(){this.helper.is(".ui-draggable-dragging")?this._mouseUp({}):this._clear();return this},_getHandle:function(a){var b=!this.options.handle||
	!d(this.options.handle,this.element).length?true:false;d(this.options.handle,this.element).find("*").andSelf().each(function(){if(this==a.target)b=true});return b},_createHelper:function(a){var b=this.options;a=d.isFunction(b.helper)?d(b.helper.apply(this.element[0],[a])):b.helper=="clone"?this.element.clone().removeAttr("id"):this.element;a.parents("body").length||a.appendTo(b.appendTo=="parent"?this.element[0].parentNode:b.appendTo);a[0]!=this.element[0]&&!/(fixed|absolute)/.test(a.css("position"))&&
	a.css("position","absolute");return a},_adjustOffsetFromHelper:function(a){if(typeof a=="string")a=a.split(" ");if(d.isArray(a))a={left:+a[0],top:+a[1]||0};if("left"in a)this.offset.click.left=a.left+this.margins.left;if("right"in a)this.offset.click.left=this.helperProportions.width-a.right+this.margins.left;if("top"in a)this.offset.click.top=a.top+this.margins.top;if("bottom"in a)this.offset.click.top=this.helperProportions.height-a.bottom+this.margins.top},_getParentOffset:function(){this.offsetParent=
	this.helper.offsetParent();var a=this.offsetParent.offset();if(this.cssPosition=="absolute"&&this.scrollParent[0]!=document&&d.ui.contains(this.scrollParent[0],this.offsetParent[0])){a.left+=this.scrollParent.scrollLeft();a.top+=this.scrollParent.scrollTop()}if(this.offsetParent[0]==document.body||this.offsetParent[0].tagName&&this.offsetParent[0].tagName.toLowerCase()=="html"&&d.browser.msie)a={top:0,left:0};return{top:a.top+(parseInt(this.offsetParent.css("borderTopWidth"),10)||0),left:a.left+(parseInt(this.offsetParent.css("borderLeftWidth"),
	10)||0)}},_getRelativeOffset:function(){if(this.cssPosition=="relative"){var a=this.element.position();return{top:a.top-(parseInt(this.helper.css("top"),10)||0)+this.scrollParent.scrollTop(),left:a.left-(parseInt(this.helper.css("left"),10)||0)+this.scrollParent.scrollLeft()}}else return{top:0,left:0}},_cacheMargins:function(){this.margins={left:parseInt(this.element.css("marginLeft"),10)||0,top:parseInt(this.element.css("marginTop"),10)||0,right:parseInt(this.element.css("marginRight"),10)||0,bottom:parseInt(this.element.css("marginBottom"),
	10)||0}},_cacheHelperProportions:function(){this.helperProportions={width:this.helper.outerWidth(),height:this.helper.outerHeight()}},_setContainment:function(){var a=this.options;if(a.containment=="parent")a.containment=this.helper[0].parentNode;if(a.containment=="document"||a.containment=="window")this.containment=[a.containment=="document"?0:d(window).scrollLeft()-this.offset.relative.left-this.offset.parent.left,a.containment=="document"?0:d(window).scrollTop()-this.offset.relative.top-this.offset.parent.top,
	(a.containment=="document"?0:d(window).scrollLeft())+d(a.containment=="document"?document:window).width()-this.helperProportions.width-this.margins.left,(a.containment=="document"?0:d(window).scrollTop())+(d(a.containment=="document"?document:window).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-this.margins.top];if(!/^(document|window|parent)$/.test(a.containment)&&a.containment.constructor!=Array){a=d(a.containment);var b=a[0];if(b){a.offset();var c=d(b).css("overflow")!=
	"hidden";this.containment=[(parseInt(d(b).css("borderLeftWidth"),10)||0)+(parseInt(d(b).css("paddingLeft"),10)||0),(parseInt(d(b).css("borderTopWidth"),10)||0)+(parseInt(d(b).css("paddingTop"),10)||0),(c?Math.max(b.scrollWidth,b.offsetWidth):b.offsetWidth)-(parseInt(d(b).css("borderLeftWidth"),10)||0)-(parseInt(d(b).css("paddingRight"),10)||0)-this.helperProportions.width-this.margins.left-this.margins.right,(c?Math.max(b.scrollHeight,b.offsetHeight):b.offsetHeight)-(parseInt(d(b).css("borderTopWidth"),
	10)||0)-(parseInt(d(b).css("paddingBottom"),10)||0)-this.helperProportions.height-this.margins.top-this.margins.bottom];this.relative_container=a}}else if(a.containment.constructor==Array)this.containment=a.containment},_convertPositionTo:function(a,b){if(!b)b=this.position;a=a=="absolute"?1:-1;var c=this.cssPosition=="absolute"&&!(this.scrollParent[0]!=document&&d.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,f=/(html|body)/i.test(c[0].tagName);return{top:b.top+
	this.offset.relative.top*a+this.offset.parent.top*a-(d.browser.safari&&d.browser.version<526&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollTop():f?0:c.scrollTop())*a),left:b.left+this.offset.relative.left*a+this.offset.parent.left*a-(d.browser.safari&&d.browser.version<526&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():f?0:c.scrollLeft())*a)}},_generatePosition:function(a){var b=this.options,c=this.cssPosition=="absolute"&&
	!(this.scrollParent[0]!=document&&d.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,f=/(html|body)/i.test(c[0].tagName),e=a.pageX,h=a.pageY;if(this.originalPosition){var g;if(this.containment){if(this.relative_container){g=this.relative_container.offset();g=[this.containment[0]+g.left,this.containment[1]+g.top,this.containment[2]+g.left,this.containment[3]+g.top]}else g=this.containment;if(a.pageX-this.offset.click.left<g[0])e=g[0]+this.offset.click.left;
	if(a.pageY-this.offset.click.top<g[1])h=g[1]+this.offset.click.top;if(a.pageX-this.offset.click.left>g[2])e=g[2]+this.offset.click.left;if(a.pageY-this.offset.click.top>g[3])h=g[3]+this.offset.click.top}if(b.grid){h=b.grid[1]?this.originalPageY+Math.round((h-this.originalPageY)/b.grid[1])*b.grid[1]:this.originalPageY;h=g?!(h-this.offset.click.top<g[1]||h-this.offset.click.top>g[3])?h:!(h-this.offset.click.top<g[1])?h-b.grid[1]:h+b.grid[1]:h;e=b.grid[0]?this.originalPageX+Math.round((e-this.originalPageX)/
	b.grid[0])*b.grid[0]:this.originalPageX;e=g?!(e-this.offset.click.left<g[0]||e-this.offset.click.left>g[2])?e:!(e-this.offset.click.left<g[0])?e-b.grid[0]:e+b.grid[0]:e}}return{top:h-this.offset.click.top-this.offset.relative.top-this.offset.parent.top+(d.browser.safari&&d.browser.version<526&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollTop():f?0:c.scrollTop()),left:e-this.offset.click.left-this.offset.relative.left-this.offset.parent.left+(d.browser.safari&&d.browser.version<
	526&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():f?0:c.scrollLeft())}},_clear:function(){this.helper.removeClass("ui-draggable-dragging");this.helper[0]!=this.element[0]&&!this.cancelHelperRemoval&&this.helper.remove();this.helper=null;this.cancelHelperRemoval=false},_trigger:function(a,b,c){c=c||this._uiHash();d.ui.plugin.call(this,a,[b,c]);if(a=="drag")this.positionAbs=this._convertPositionTo("absolute");return d.Widget.prototype._trigger.call(this,a,b,
	c)},plugins:{},_uiHash:function(){return{helper:this.helper,position:this.position,originalPosition:this.originalPosition,offset:this.positionAbs}}});d.extend(d.ui.draggable,{version:"1.8.16"});d.ui.plugin.add("draggable","connectToSortable",{start:function(a,b){var c=d(this).data("draggable"),f=c.options,e=d.extend({},b,{item:c.element});c.sortables=[];d(f.connectToSortable).each(function(){var h=d.data(this,"sortable");if(h&&!h.options.disabled){c.sortables.push({instance:h,shouldRevert:h.options.revert});
	h.refreshPositions();h._trigger("activate",a,e)}})},stop:function(a,b){var c=d(this).data("draggable"),f=d.extend({},b,{item:c.element});d.each(c.sortables,function(){if(this.instance.isOver){this.instance.isOver=0;c.cancelHelperRemoval=true;this.instance.cancelHelperRemoval=false;if(this.shouldRevert)this.instance.options.revert=true;this.instance._mouseStop(a);this.instance.options.helper=this.instance.options._helper;c.options.helper=="original"&&this.instance.currentItem.css({top:"auto",left:"auto"})}else{this.instance.cancelHelperRemoval=
	false;this.instance._trigger("deactivate",a,f)}})},drag:function(a,b){var c=d(this).data("draggable"),f=this;d.each(c.sortables,function(){this.instance.positionAbs=c.positionAbs;this.instance.helperProportions=c.helperProportions;this.instance.offset.click=c.offset.click;if(this.instance._intersectsWith(this.instance.containerCache)){if(!this.instance.isOver){this.instance.isOver=1;this.instance.currentItem=d(f).clone().removeAttr("id").appendTo(this.instance.element).data("sortable-item",true);
	this.instance.options._helper=this.instance.options.helper;this.instance.options.helper=function(){return b.helper[0]};a.target=this.instance.currentItem[0];this.instance._mouseCapture(a,true);this.instance._mouseStart(a,true,true);this.instance.offset.click.top=c.offset.click.top;this.instance.offset.click.left=c.offset.click.left;this.instance.offset.parent.left-=c.offset.parent.left-this.instance.offset.parent.left;this.instance.offset.parent.top-=c.offset.parent.top-this.instance.offset.parent.top;
	c._trigger("toSortable",a);c.dropped=this.instance.element;c.currentItem=c.element;this.instance.fromOutside=c}this.instance.currentItem&&this.instance._mouseDrag(a)}else if(this.instance.isOver){this.instance.isOver=0;this.instance.cancelHelperRemoval=true;this.instance.options.revert=false;this.instance._trigger("out",a,this.instance._uiHash(this.instance));this.instance._mouseStop(a,true);this.instance.options.helper=this.instance.options._helper;this.instance.currentItem.remove();this.instance.placeholder&&
	this.instance.placeholder.remove();c._trigger("fromSortable",a);c.dropped=false}})}});d.ui.plugin.add("draggable","cursor",{start:function(){var a=d("body"),b=d(this).data("draggable").options;if(a.css("cursor"))b._cursor=a.css("cursor");a.css("cursor",b.cursor)},stop:function(){var a=d(this).data("draggable").options;a._cursor&&d("body").css("cursor",a._cursor)}});d.ui.plugin.add("draggable","opacity",{start:function(a,b){a=d(b.helper);b=d(this).data("draggable").options;if(a.css("opacity"))b._opacity=
	a.css("opacity");a.css("opacity",b.opacity)},stop:function(a,b){a=d(this).data("draggable").options;a._opacity&&d(b.helper).css("opacity",a._opacity)}});d.ui.plugin.add("draggable","scroll",{start:function(){var a=d(this).data("draggable");if(a.scrollParent[0]!=document&&a.scrollParent[0].tagName!="HTML")a.overflowOffset=a.scrollParent.offset()},drag:function(a){var b=d(this).data("draggable"),c=b.options,f=false;if(b.scrollParent[0]!=document&&b.scrollParent[0].tagName!="HTML"){if(!c.axis||c.axis!=
	"x")if(b.overflowOffset.top+b.scrollParent[0].offsetHeight-a.pageY<c.scrollSensitivity)b.scrollParent[0].scrollTop=f=b.scrollParent[0].scrollTop+c.scrollSpeed;else if(a.pageY-b.overflowOffset.top<c.scrollSensitivity)b.scrollParent[0].scrollTop=f=b.scrollParent[0].scrollTop-c.scrollSpeed;if(!c.axis||c.axis!="y")if(b.overflowOffset.left+b.scrollParent[0].offsetWidth-a.pageX<c.scrollSensitivity)b.scrollParent[0].scrollLeft=f=b.scrollParent[0].scrollLeft+c.scrollSpeed;else if(a.pageX-b.overflowOffset.left<
	c.scrollSensitivity)b.scrollParent[0].scrollLeft=f=b.scrollParent[0].scrollLeft-c.scrollSpeed}else{if(!c.axis||c.axis!="x")if(a.pageY-d(document).scrollTop()<c.scrollSensitivity)f=d(document).scrollTop(d(document).scrollTop()-c.scrollSpeed);else if(d(window).height()-(a.pageY-d(document).scrollTop())<c.scrollSensitivity)f=d(document).scrollTop(d(document).scrollTop()+c.scrollSpeed);if(!c.axis||c.axis!="y")if(a.pageX-d(document).scrollLeft()<c.scrollSensitivity)f=d(document).scrollLeft(d(document).scrollLeft()-
	c.scrollSpeed);else if(d(window).width()-(a.pageX-d(document).scrollLeft())<c.scrollSensitivity)f=d(document).scrollLeft(d(document).scrollLeft()+c.scrollSpeed)}f!==false&&d.ui.ddmanager&&!c.dropBehaviour&&d.ui.ddmanager.prepareOffsets(b,a)}});d.ui.plugin.add("draggable","snap",{start:function(){var a=d(this).data("draggable"),b=a.options;a.snapElements=[];d(b.snap.constructor!=String?b.snap.items||":data(draggable)":b.snap).each(function(){var c=d(this),f=c.offset();this!=a.element[0]&&a.snapElements.push({item:this,
	width:c.outerWidth(),height:c.outerHeight(),top:f.top,left:f.left})})},drag:function(a,b){for(var c=d(this).data("draggable"),f=c.options,e=f.snapTolerance,h=b.offset.left,g=h+c.helperProportions.width,n=b.offset.top,o=n+c.helperProportions.height,i=c.snapElements.length-1;i>=0;i--){var j=c.snapElements[i].left,l=j+c.snapElements[i].width,k=c.snapElements[i].top,m=k+c.snapElements[i].height;if(j-e<h&&h<l+e&&k-e<n&&n<m+e||j-e<h&&h<l+e&&k-e<o&&o<m+e||j-e<g&&g<l+e&&k-e<n&&n<m+e||j-e<g&&g<l+e&&k-e<o&&
	o<m+e){if(f.snapMode!="inner"){var p=Math.abs(k-o)<=e,q=Math.abs(m-n)<=e,r=Math.abs(j-g)<=e,s=Math.abs(l-h)<=e;if(p)b.position.top=c._convertPositionTo("relative",{top:k-c.helperProportions.height,left:0}).top-c.margins.top;if(q)b.position.top=c._convertPositionTo("relative",{top:m,left:0}).top-c.margins.top;if(r)b.position.left=c._convertPositionTo("relative",{top:0,left:j-c.helperProportions.width}).left-c.margins.left;if(s)b.position.left=c._convertPositionTo("relative",{top:0,left:l}).left-c.margins.left}var t=
	p||q||r||s;if(f.snapMode!="outer"){p=Math.abs(k-n)<=e;q=Math.abs(m-o)<=e;r=Math.abs(j-h)<=e;s=Math.abs(l-g)<=e;if(p)b.position.top=c._convertPositionTo("relative",{top:k,left:0}).top-c.margins.top;if(q)b.position.top=c._convertPositionTo("relative",{top:m-c.helperProportions.height,left:0}).top-c.margins.top;if(r)b.position.left=c._convertPositionTo("relative",{top:0,left:j}).left-c.margins.left;if(s)b.position.left=c._convertPositionTo("relative",{top:0,left:l-c.helperProportions.width}).left-c.margins.left}if(!c.snapElements[i].snapping&&
	(p||q||r||s||t))c.options.snap.snap&&c.options.snap.snap.call(c.element,a,d.extend(c._uiHash(),{snapItem:c.snapElements[i].item}));c.snapElements[i].snapping=p||q||r||s||t}else{c.snapElements[i].snapping&&c.options.snap.release&&c.options.snap.release.call(c.element,a,d.extend(c._uiHash(),{snapItem:c.snapElements[i].item}));c.snapElements[i].snapping=false}}}});d.ui.plugin.add("draggable","stack",{start:function(){var a=d(this).data("draggable").options;a=d.makeArray(d(a.stack)).sort(function(c,f){return(parseInt(d(c).css("zIndex"),
	10)||0)-(parseInt(d(f).css("zIndex"),10)||0)});if(a.length){var b=parseInt(a[0].style.zIndex)||0;d(a).each(function(c){this.style.zIndex=b+c});this[0].style.zIndex=b+a.length}}});d.ui.plugin.add("draggable","zIndex",{start:function(a,b){a=d(b.helper);b=d(this).data("draggable").options;if(a.css("zIndex"))b._zIndex=a.css("zIndex");a.css("zIndex",b.zIndex)},stop:function(a,b){a=d(this).data("draggable").options;a._zIndex&&d(b.helper).css("zIndex",a._zIndex)}})})(jQuery);
