
var fp_uicontrol_handler = {

	timer : {},
		
	setTimer : function(ElNum){
		var self = this;
		self.timer[ElNum] = setInterval( function(){
				self.playbackInProgress(ElNum);
	    	}, 333);
	},
	
	playbackInProgress : function(ElNum){
		var self = this;
		self.updateCurTime(ElNum);
		self.updateSearchSliderPosition(ElNum);
	},
	
	updateCurTime : function(ElNum){
		var self = this;
		var cur_t = $f($.aplayer.idMediaCont+ElNum ).getTime();

//        console.log( $($.aplayer.idMediaCont+ElNum) );

        console.log(($('#'+$.aplayer.idMediaCont+ElNums).length==0 ));

//        if($('#'+vlcPlayer.retIdMediaElem(numPlayer)).length==0 ){
//            clearInterval(vlcPlayer.intervalId[numPlayer]);
//            return;
//        }
//

		var tCur = Math.round(cur_t);
		var tCurStr = self.FormatTime(tCur);

        var newTime = self.FormatTimeReally(tCur, ElNum);

        $('#'+$.aplayer.idCurrentTime+ElNum).html(newTime[0]);
	},
	
	updateSearchSliderPosition : function(ElNum){
		var self = this;
		if(self.inseek[ElNum]) return;
		if($('#'+$.aplayer.idSearch+ElNum).attr('isPlaying')!= undefined) return;

		var MediaElt = $f($.aplayer.idMediaCont+ElNum );
		if(MediaElt==undefined) return;
		var cur_t =  MediaElt.getTime();
		
		$('#'+$.aplayer.idSearch+ElNum).slider('value', cur_t*30 );
	},
	
	updateDuration : function(ElNum){
		var self = this;
		var tDur = Math.round( $f($.aplayer.idMediaCont+ElNum).getClip().fullDuration );
		var tDurStr = self.FormatTime(tDur);
        var timeArr = self.FormatTimeReally(0, ElNum);
        $('#'+$.aplayer.idDuration+ElNum).html(timeArr[1]);
		$('#'+$.aplayer.idSearch+ElNum).slider({'max': (tDur-1)*30 });
	},
	
    //формирование строки времени
    FormatTime : function(Seconds){
        return  (
            Math.floor(Seconds/(60*60)))+':'
            +((Math.floor(Seconds/60)%60)<10? '0'+(Math.floor(Seconds/60)%60):(Math.floor(Seconds/60)%60))+':'
            +(Math.floor(Seconds%60)<10? '0'+Math.floor(Seconds%60):Math.floor(Seconds%60));

    },

    FormatTimeReally : function(tCur, ElNum){
        var cel_nr = parseInt($('#aplayerNo_'+ElNum).parents('.content_item').attr('id').replace('cell_', ''));
        var tempDate = '0000-00-00 00:00:00';
        if(matrix!=undefined){
            tempDate = matrix.events[cel_nr][9]
        }

  //      var tempDate = matrix.events[ElNum][9];


		var nDate = tempDate.replace(new RegExp("-",'g'),"/");
        var dateObject = new Date(nDate);
        dateObject.setTime(dateObject.getTime() + tCur * 1000);

        var NewDate = Array();
        NewDate[0] = ((!(Math.floor(dateObject.getHours() / 10))) ? ('0' + dateObject.getHours()) : dateObject.getHours()) + ':' +
            ((!(Math.floor(dateObject.getMinutes() / 10))) ? ('0' + dateObject.getMinutes()) : dateObject.getMinutes()) + ':' +
            ((!(Math.floor(dateObject.getSeconds() / 10))) ? ('0' + dateObject.getSeconds()) : dateObject.getSeconds());
        NewDate[1] = ((!(Math.floor(dateObject.getFullYear() / 10))) ? ('0' + dateObject.getFullYear()) : dateObject.getFullYear()) + '-' +
            ((!(Math.floor(dateObject.getMonth() / 10))) ? ('0' + dateObject.getMonth()) : dateObject.getMonth()) + '-' +
            ((!(Math.floor(dateObject.getDay() / 10))) ? ('0' + dateObject.getDay()) : dateObject.getDay());
        return NewDate;
    },
	
		
	//Обработчики пользовательских событий aplayera для flowplayer

    inseek : {},
    //начало поиска времени воспроизведения
	elMediaOnBeforeSeek : function(ElNum){
		var self = this;
		self.inseek[ElNum] = true;
	},
	
	//конец поиска времени воспроизведения
	elMediaOnSeek : function(ElNum){
		var self = this;
		self.inseek[ElNum] = false;
	},

	elMediaOnResume :function(ElNum){
		var self = this;
		$('#'+$.aplayer.idPlay+ElNum).hide();
		$('#'+$.aplayer.idPause+ElNum).show();
		self.setTimer(ElNum);
	},

	elMediaOnPlay:function(ElNum){
		var self = this;
		$('#'+$.aplayer.idPlay+ElNum).hide();
		$('#'+$.aplayer.idPause+ElNum).show();
		self.updateDuration(ElNum);
        self.updateCurTime(ElNum);
		self.setTimer(ElNum);
	},
			
	elMediaOnPause:function(ElNum){
		this.noPlay(ElNum);
	},
			
	elMediaOnFinish : function(ElNum){
		this.noPlay(ElNum);
	},
			
	elMediaOnStop : function(ElNum){
		this.noPlay(ElNum);

		//обнуляем слайдер и текущее время
		var tCurStr = $.aplayer.ControlBar.FormatTime(0);
		$('#'+$.aplayer.idCurrentTime+ElNum).html(tCurStr);
		$('#'+$.aplayer.idSearch+ElNum).slider('value', 0 );
	},
			
	noPlay : function(ElNum){
		var self = this;
		$('#'+$.aplayer.idPlay+ElNum).show();
		$('#'+$.aplayer.idPause+ElNum).hide();
		clearInterval(self.timer[ElNum]);
		self.updateCurTime(ElNum);
	},

	
	elMediaOnBeforeClick : function(ElNum){
		var self = this;
		var player = $f($.aplayer.idMediaCont+ElNum);		
	},
	
	//dag and drop for flowplayer
	drag_n_drop : {
		//кординаты мыши
		curX:0,
		curY:0,
		
		//смещение мыши
		dX:0,
		dY:0,
		
		//позиция флоуплеера
		fpX:0,
		fpY:0,
		
		sub_cont:{}, //субконтейнер аплеера для флоуплеера
		fp_obj:{}, //внедреный объект
		
		init : function(mouse_evt, ElNum){
			var self =this;
			var fp_obj = $('#'+$.aplayer.idMediaCont+ElNum+'_api');
			var player = $f($.aplayer.idMediaCont+ElNum);
			
			self.fp_obj = fp_obj;

			self.sub_cont = $('#'+$.aplayer.idMediaCont+ElNum);
			
			self.curX = mouse_evt.clientX;
			self.curY = mouse_evt.clientY;

			//определяем текущую позицию фп-плеера
			self.fpX = ( isNaN(parseInt($(fp_obj).css('left'))) )? 0: parseInt($(fp_obj).css('left')) ;
			self.fpY = ( isNaN(parseInt($(fp_obj).css('top')))  )? 0: parseInt($(fp_obj).css('top')) ;
			
			$(fp_obj).css({'position':'absolute' });
			
			//устанавливаем обработчик перетаскивания
	        $(self.sub_cont).bind('mousemove', function(e) {
	        	e.preventDefault();
	        	//левая кнопка мыши
				if(e.button==0){
					//обработчик перемщения мыши при перетаскивании
					self.drag(e, ElNum);
				}
        		return false;
        	});
        	
		},

		drop : function(mouse_evt, ElNum){
			var self =this;
			//отключение обработчика перемщения мыши при перетаскивании
			$(self.sub_cont).unbind('mousemove');
		},
		
		drag : function(mouse_evt, ElNum){
			var self =this;

			self.dX = self.curX - mouse_evt.clientX;
			self.dY = self.curY - mouse_evt.clientY;
			
			self.curX = mouse_evt.clientX;
			self.curY = mouse_evt.clientY;
			
			self.fpX -= self.dX;
			self.fpY -= self.dY;
			
			//проверка допустимости перетаскивания
			//размеры флоуплеера
			var fp_h = $(self.fp_obj).height();
			var fp_w = $(self.fp_obj).width();

			//размеры субконтейнера
			var sub_h = $(self.sub_cont).height();
			var sub_w = $(self.sub_cont).width();
			
			//привязка к верхней границе субконтейнера
			if(self.fpY>0) self.fpY = 0;
			//привязка к левой границе субконтейнера
			if(self.fpX>0) self.fpX = 0;
			//привязка к нижней границе субконтейнера
			if(sub_h  <= fp_w && sub_h > fp_h + self.fpY  ) {
				self.fpY = sub_h - fp_h;
			}
			if(sub_h  > fp_w ) {
				self.fpY = 0;
			}

			//привязка к правой границе субконтейнера
			if(sub_w  <= fp_w && sub_w > fp_w + self.fpX ){
				self.fpX = sub_w - fp_w;
			}
			if(sub_w  > fp_w ){
				self.fpX = 0;
			}
			
			//установка текущей позиции
			$(self.fp_obj)
				.css({
					'left':self.fpX+'px',
					'top': self.fpY+'px'
				});
			
		}
	},

	onPlayerLoaded : function(ElNum){

        var self = this;
		var player = $f($.aplayer.idMediaCont+ElNum);

		//dragg and drop
		$('#'+$.aplayer.idMediaCont+ElNum)
		.mousedown(function(e) {
        	//левая кнопка мыши
			if(e.button==0){
				self.drag_n_drop.init(e, ElNum);
			}
    	});

        //обработчик mouseup
        $(document).bind('mouseup', function(e) {
        	//левая кнопка мыши
			if(e.button==0){
				self.drag_n_drop.drop(e, ElNum);
			}
    	});
		
	},
	
	
	onClipBegin : function(ElNum){
		var self = this;
		var player = $f($.aplayer.idMediaCont+ElNum);
	},

    //Обработчики событий контролов
	
	soundOnClickHandler: function(ElNum){
		var self = this;
		var player = $f($.aplayer.idMediaCont+ElNum);
		player.unmute();

		//$('#'+$.aplayer.idElMedia+ElNum).each(function(){ this.muted=false; });
		$('#'+$.aplayer.idSoundOff+ElNum).show();
		$('#'+$.aplayer.idSoundOn+ElNum).hide();
	},

	soundOffClickHandler : function(ElNum){
		var self = this;
		var player = $f($.aplayer.idMediaCont+ElNum);
		player.mute();
		$('#'+$.aplayer.idSoundOff+ElNum).hide();
		$('#'+$.aplayer.idSoundOn+ElNum).show();
	},

	//Слайдер звука
	volumeSlideHandler : function(Elem){
		var self = this;
		var ElNum = $(Elem).attr('No');
		var player = $f($.aplayer.idMediaCont+ElNum);
		var vol = Math.round(($(Elem).slider('value')/40)*100) ;
		if (vol<0) vol =0;
		if (vol>100)vol = 100;
		player.setVolume(vol);
	},

    volumeStopHandler : function(Elem){
    	var self = this;
		var ElNum = $(Elem).attr('No');
		var player = $f($.aplayer.idMediaCont+ElNum);  	
    },
	
    //воспроизведение
    playClickHandler : function(ElNum){
	     var self = this;
	     if($f($.aplayer.idMediaCont+ElNum ).isPaused()){
	    	 $f($.aplayer.idMediaCont+ElNum ).resume();
	     }
	     else{
	    	 $f($.aplayer.idMediaCont+ElNum ).play();
	     }
    },

    //пауза
    pauseClickHandler : function(ElNum){
    	$f($.aplayer.idMediaCont+ElNum ).pause();
    },

    //стоп
	stopClickHandler : function(ElNum){
		var self = this;
		$f($.aplayer.idMediaCont+ElNum).stop();
    },

    //слайдер поиска по времени
	searchOnStartHandler : function(Elem){
		var self = this;
		var ElNum = $(Elem).attr('No');
		var player = $f($.aplayer.idMediaCont+ElNum);

		if( player.isPlaying() ){
			$(Elem).attr({'isPlaying':'true'});
			player.pause();
		}else{
			$(Elem).attr({'isPlaying':'true'});
		}
		
		//если файл не загружен - запускаем воспроизведение
		if(player.getStatus()['bufferEnd']==0 && player.getState()!=2 ){
			player.play();
		}
	},

	searchOnStopHandler : function(Elem){
		var self = this;
		var ElNum = $(Elem).attr('No');
		var player = $f($.aplayer.idMediaCont+ElNum);
		
		//если в буфере пусто
		if(player.getStatus()['bufferEnd']==0){
			//возвращаем слайдер на наачальную позицию
			$('#'+$.aplayer.idSearch+ElNum).slider('value', 0);
			return;
		}
		
		var setTime = Math.round(($(Elem).slider('value'))/30);
		
		var tCurStr = self.FormatTime(setTime);
		$('#'+$.aplayer.idCurrentTime+ElNum).html(tCurStr);
		
		player.seek(setTime);
		self.inseek[ElNum] = true;
		
		if($(Elem).attr('isPlaying')=='true'){
			self.playClickHandler(ElNum);
		}
		$(Elem).removeAttr('isPlaying');
	},

	searchOnSlideHandler : function(Elem){
		var self = this;
		var ElNum = $(Elem).attr('No');
		var player = $f($.aplayer.idMediaCont+ElNum);

		//если в буфере пусто
		if(player.getStatus()['bufferEnd']==0){
			return;
		}
		
		var setTime = Math.round(($(Elem).slider('value'))/30);
		var tCurStr = $.aplayer.ControlBar.FormatTime(setTime);
		$('#'+$.aplayer.idCurrentTime+ElNum).html(tCurStr);

		player.seek(setTime);
		self.inseek[ElNum] = true;
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
		var me = $('#'+$.aplayer.idMediaCont+plNo+'_api');
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
			'width':  me_width +'px',
			'height': me_height+'px'
		} );

		if(me_width<=width){
			$(me).css({'left':'0px'});
		}
		if(me_height<=height){
			$(me).css({'top':'0px'});
		}
	}

}