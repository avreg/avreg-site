function Enum() {
	this.allValues = [];
	this.currentValue = 0;
	for (var i=0, ilen=arguments.length; i<ilen; i++) {
		this[arguments[i]] = parseInt(i);
		this.allValues.push(arguments[i]);
	}
};
Enum.prototype.values = function() {
	return this.allValues;
};
Enum.prototype.current = function() {
	return this.currentValue;
};
Enum.prototype.next = function() {
	this.currentValue++;
	if(this.currentValue==this.allValues.length)
		this.currentValue = 0;
	return this.currentValue;
};
Enum.prototype.set = function (element) {
	if(element>=0 && element<this.allValues.length)
		this.currentValue = element;
	return this.currentValue;
};
var Base64 = {

    	// private property
    	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

    	// public method for encoding
    	encode : function (input) {
    		var output = "";
    		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
    		var i = 0;

    		input = Base64._utf8_encode(input);

    		while (i < input.length) {
    			chr1 = input.charCodeAt(i++);
    			chr2 = input.charCodeAt(i++);
    			chr3 = input.charCodeAt(i++);

    			enc1 = chr1 >> 2;
    			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
    			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
    			enc4 = chr3 & 63;

    			if (isNaN(chr2)) {
    				enc3 = enc4 = 64;
    			} else if (isNaN(chr3)) {
    				enc4 = 64;
    			}

    			output = output +
    			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
    			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
    		}
    		return output;
    	},

    	// public method for decoding
    	decode : function (input) {
    		var output = "";
    		var chr1, chr2, chr3;
    		var enc1, enc2, enc3, enc4;
    		var i = 0;

    		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

    		while (i < input.length) {

    			enc1 = this._keyStr.indexOf(input.charAt(i++));
    			enc2 = this._keyStr.indexOf(input.charAt(i++));
    			enc3 = this._keyStr.indexOf(input.charAt(i++));
    			enc4 = this._keyStr.indexOf(input.charAt(i++));

    			chr1 = (enc1 << 2) | (enc2 >> 4);
    			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
    			chr3 = ((enc3 & 3) << 6) | enc4;

    			output = output + String.fromCharCode(chr1);

    			if (enc3 != 64) {
    				output = output + String.fromCharCode(chr2);
    			}
    			if (enc4 != 64) {
    				output = output + String.fromCharCode(chr3);
    			}
    		}
    		output = Base64._utf8_decode(output);
    		return output;
    	},

    	// private method for UTF-8 encoding
    	_utf8_encode : function (string) {
    		string = string.replace(/\r\n/g,"\n");
    		var utftext = "";

    		for (var n = 0; n < string.length; n++) {

    			var c = string.charCodeAt(n);

    			if (c < 128) {
    				utftext += String.fromCharCode(c);
    			}
    			else if((c > 127) && (c < 2048)) {
    				utftext += String.fromCharCode((c >> 6) | 192);
    				utftext += String.fromCharCode((c & 63) | 128);
    			}
    			else {
    				utftext += String.fromCharCode((c >> 12) | 224);
    				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
    				utftext += String.fromCharCode((c & 63) | 128);
    			}

    		}

    		return utftext;
    	},

    	// private method for UTF-8 decoding
    	_utf8_decode : function (utftext) {
    		var string = "";
    		var i = 0;
    		var c = c1 = c2 = 0;

    		while ( i < utftext.length ) {
    			c = utftext.charCodeAt(i);

    			if (c < 128) {
    				string += String.fromCharCode(c);
    				i++;
    			}
    			else if((c > 191) && (c < 224)) {
    				c2 = utftext.charCodeAt(i+1);
    				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
    				i += 2;
    			}
    			else {
    				c2 = utftext.charCodeAt(i+1);
    				c3 = utftext.charCodeAt(i+2);
    				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
    				i += 3;
    			}
    		}
    		return string;
    	}
    };

$(function(){
// глобальные настройки аякс запроса
/**
 * Global AJAX setup
 */
$.ajaxSetup({
	type: 'POST',
	dataType: 'json',
	cache: false,
	timeout: ajax_timeout*1000, // 5000,
	complete: function (XMLHttpRequest, textStatus) {
		if (textStatus == 'timeout') {
			alert(lang.ajax_timeout);
			if (typeof( matrix.send_query ) != 'undefined' ) {
				matrix.send_query = false;
				$('#matrix_load').hide();
			}
		}
	}

});
});

// основной объект галереи
var gallery = {
		images : new Array(),
        // Объект дерева
		treeObject: null,
        // Конфигурация
		config : {},
		hcameras : 100,
		// объект изменения ширины столбцов
		resize_column : {
			myWidth: null, // ширина
			myHeight: null, // высота
			res: false,
			// функция изменения ширины столбцов
			resize : function(pageX) {
				var self = this;

				if( typeof( window.innerWidth ) == 'number' ) {
					//Non-IE
					self.myWidth = window.innerWidth;
					self.myHeight = window.innerHeight;
				}else{
					//IE 6+ in 'standards compliant mode'
					self.myHeight = ietruebody().clientHeight;
					self.myWidth = ietruebody().clientWidth;
				}

				$('#sidebar').width(pageX + 2);
				$('#sidebar .block').width(pageX-26);

				$('#sidebar #statistics').width(pageX-66);
				$('#content').width(self.myWidth - $('#sidebar').width() ).css('margin-left', pageX + 2);
				$('#list_panel').width($('#content').width()-38);

				var hc;

				if(MSIE){
					hc = $('#content').height() - 100/*$('#win_top').height()*/ - $('#toolbar').height();
					hc -=30 ;

				}else{
					hc = $('#content').height() - gallery.hcameras - $('#toolbar').height();
					hc-=23;
				}
                /*
                alert('Вызвано в инициализации:\n' +
                            'winbot height = ' + $('#win_bot').height() +
                            '\nheight new = ' + hc +
                            '\ntoolbar height = ' + $('#toolbar').height() +
                            '\nwintop height = ' + $("#win_top").height() +
                            '\ncontent height = ' + $("#content").height());
                            //*/
				$('#win_bot').height(hc);

				if(MSIE){

				}else{
					$('#page').width($('#sidebar').width()+$('#content').width());
				}
			},
			// функция инициализации
			init: function(){

                if (MSIE)
                {
                    $("#content #win_bot, #content #win_bot_detail").css("top", 105);
                }
				var self = this;
				if( typeof( window.innerWidth ) == 'number' ) {
					//Non-IE
					self.myWidth = window.innerWidth;
					self.myHeight = window.innerHeight;
				}else{
					//IE 6+ in 'standards compliant mode'
					self.myHeight = ietruebody().clientHeight;
					self.myWidth = ietruebody().clientWidth;
				}

				$('.block','#sidebar').width($('#sidebar').width()-9);
				$('#statistics','#sidebar').width($('.block','#sidebar').width()-20);


				// обработка изменение ширины используя вертикальный разделитель
				$('#handler_vertical')
				.mousedown(function(e){
					self.res = true;
					e.preventDefault();
					$(document).mousemove(function(e){
						if (e.pageX > 300 && e.pageX< self.myWidth - 535) {
							self.resize(e.pageX);
						}
					});
				});
				$(document).mouseup(function(e){
					if (self.res){
						self.res = false;
						matrix.resize();
						gallery.cookie.set('resize_column', $('#sidebar').width()-2);
					}
					$(document).unbind('mousemove');
				});
				// востанавливаем расположения из куков
				try{
					pageX = parseInt(gallery.cookie.get('resize_column'));

				}catch (e) {
					pageX = false;
				}

				if (pageX) {
					self.resize(pageX);
				} else {
					gallery.cookie.set('resize_column', 300);
				}

                // Устанавливаю отступ блока просмотра контента от блока выбора камер
                //$("#win_bot").css("position", "absolute");
                //alert();
                if (MSIE)
                {

                }
			}
		},

		reload_events : function(){
			var count = 0;
			var cook = '';
			//если включен режим детального просмотра - включаем режим превью
			if(matrix.mode == 'detail')	matrix.preview();

			$('input[name="type_event"]').each(function(){
				if ($(this).attr('checked')) {
					count++;
					v = $(this).val();
					cook += v.substr(0,1)+',';
				}
			});
			if (count > 0){
				gallery.cookie.set('type_event', cook);
				// обновляем дерево
				gallery.tree_event.reload();

				if(MSIE){
					//Устанавливаем матрицу на начало диапазона
					matrix.num = 0;
					scroll.setposition(0);
				}

			} else {
				// не дадим пользователю снять последний чекбокс
				//$(this).attr('checked', 'checked');
				alert(lang.empty_event);
				return false;
			}
			return true;
		},
		reload_cams : function () {
			var count = 0;
			var cam_cnt = 0;
			var cook = '';
			//если включен режим детального просмотра - включаем режим превью
			if(matrix.mode == 'detail')	matrix.preview();

			$('input[name="cameras"]').each(function(){
				cam_cnt++;
				if ($(this).attr('checked')) {
					count++;
					cook += $(this).val()+',';
				}
			});
			gallery.cookie.set('cameras',cook);
			// обновляем дерево
			gallery.tree_event.reload();

			if(MSIE){
				//устанавливаем на начало диапазона
				matrix.num = 0;
				scroll.setposition(0);
			}

			if (count >0 ){
				//переключаем чекбокс всех камер в 'Отменить выбор всех камер'
				$('#cam_selector').attr('checked', true).parent().attr('style','background-position: 0px -14px');
				$('#lbl_cam_selector').html('Отменить выбор всех камер');

				if(count==cam_cnt){
					$('#select_all_cam>.new_Check').css('opacity', 1);
				}
				else{
					$('#select_all_cam>.new_Check').css('opacity', 0.5);
				}
			} else {
				//переключаем чекбокс всех камер в 'Выбрать все камеры'
				$('#cam_selector').attr('checked', false).parent().attr('style','background-position: 0px 0px');
				$('#lbl_cam_selector').html('Выбрать все камеры');
				$('#select_all_cam>.new_Check').css('opacity', '1');
			}
			return true;
		},
		cookie : {
			config: {
				"days": "30",
				"path": WwwPrefix + "offline/gallery.php",
				"name" : "gallery"
			},
			getobject : function() {
				var self = this;
				var strcook = '';

				if (ReadCookie(self.config.name)){
					strcook = Base64.decode(ReadCookie(self.config.name));
				}

				var objcook = {};
				if (strcook) {
					try{
						objcook = $.parseJSON(strcook);
					}catch (e) {
						return objcook;
					}
				}
				return objcook;
			},
			setobject : function (objcook) {
				var self = this;
				//->
				var stringify;
				if($.browser.msie){
					stringify = function (obj) {
						var t = typeof (obj);
						if (t != "object" || obj === null) {
						// simple data type
						if (t == "string") obj = '"'+obj+'"';
							return String(obj);
						}
						else {
						// recurse array or object
						var n, v, json = [], arr = (obj && obj.constructor == Array);
						for (n in obj) {
							v = obj[n]; t = typeof(v);
							if (t == "string") v = '"'+v+'"';
							else if (t == "object" && v !== null)
								v = JSON.stringify(v);
							json.push((arr ? "" : '"' + n + '":') + String(v));
						}
							return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
						}
					};

				}else{
					stringify = JSON.stringify;
				}
				//->
				var strcook = Base64.encode(stringify(objcook));

				SetCookie(self.config.name, strcook, self.config.days, self.config.path);
			},
			set : function (name, value) {
				var self = this;

				var objcook = self.getobject();
				objcook[name] = value;
				self.setobject(objcook);
			},
			get : function (name) {
				var self = this;
				var objcook = self.getobject();
				if (typeof(objcook[name]) != 'undefined' ) {
					return objcook[name];
				}
				return false;
			},
			init: function(config) {
				var self = this;
				// обновление настроек
				if (config && typeof(config) == 'object') {
				    $.extend(self.config, config);
				}
			}
		},
		// объект построения дерева событий
		tree_event : {
			holder: null,
			// функция обновления дерева
			reload : function(){
				var self = this;
				// получения настроек формирование дерева
				var variable = {};

				$('input[name="type_event"]').each(function(){
					if ($(this).attr('checked')) {
						// по типу (изображения, видео, аудио)
						var type = $(this).val();
						// какие камеры выбраны
						$('input[name="cameras"]').each(function(){
							if ($(this).attr('checked')) {
								var k = type + '_' + $(this).val();
								variable[k] = 1;
							}
						});
					}
				});
				// js нового дерева
				matrix.curent_tree_events = {
						all : {
							size : 0,
							count: 0
						}
				};
				// html код дерева
				var html = '<ul><li id="tree_all"><a href="#">'+lang.all+'</a><ul>';
				// предыдущее событие
				var old_value = false;
				// предыдущий год, месяц, день
				var o0 = false, o1 = false, o2 = false;
				var ii = 0;

				$.each(matrix.tree_events, function( i,value) {
					// временной диапазон
					var key = value.date;
					// размер временного диапазона
					var size = 0;
					// количество файлов во временном диапазоне
					var count = 0;

					//считаем размер и количество файлов в временном диапазоне в выбранных настройках
					$.each(variable, function(k, v) {
						if (typeof(value[k+'_size']) != 'undefined' ) {
							size += parseFloat(value[k+'_size']);
						}
						if (typeof(value[k+'_count']) != 'undefined' ) {
							count += parseInt(value[k+'_count']);
						}
					});

					// если не пусто, то строим дерево
					if (count > 0 && size > 0) {
						// разбиваем дату на год месяц день
						var e = key.split('_');

						var year = e[0];
						var month = (e[1]>9)?e[1]:e[1].replace('0','');
						var day = e[2];
						//час истечения диапазона
						var hour_to = parseInt((e[3]>9)?e[3]:e[3].replace('0',''))+1;

						// определяем самый первый диапазон для всего дерева
						if (ii == 0) {
							matrix.curent_tree_events['all'].from = e[3]+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0];
							ii++;
						}

						// обновляем самый последний диапазон для всего дерева
						matrix.curent_tree_events['all'].to = hour_to+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0];

						// если есть предыдущее событие
						if (old_value != false) {

							var o = old_value.split('_');

							var o_manth = (o[1]>9)?o[1]:o[1].replace('0','');

							//предыдущий час истечения диапазона
							var o_hour_to = parseInt((o[3]>9)?o[3]:o[3].replace('0',''))+1;

							// и оно не относиться к дню текущего события, то закрываем день
							if (e[0]+'_'+e[1]+'_'+e[2] != o[0]+'_'+o[1]+'_'+o[2]) {
								html += '</ul>';
								matrix.curent_tree_events[o[0]+'_'+o[1]+'_'+o[2]].to = o_hour_to+':00 ' + o[2] + ' ' + monthNames[parseInt(o_manth)]+ ' ' + o[0];
								matrix.curent_tree_events[o[0]+'_'+o[1]+'_'+o[2]].next = e[0]+'_'+e[1]+'_'+e[2];
								o2 = o[0]+'_'+o[1]+'_'+o[2];
							}
							// и оно не относиться к месяцу текущего события, то закрываем месяц
							if (e[0]+'_'+e[1] != o[0]+'_'+o[1]) {
								html += '</ul>';
								matrix.curent_tree_events[o[0]+'_'+o[1]].to = o_hour_to+':00 ' + o[2] + ' ' + monthNames[parseInt(o_manth)]+ ' ' + o[0];
								matrix.curent_tree_events[o[0]+'_'+o[1]].next = e[0]+'_'+e[1];
								o1 = o[0]+'_'+o[1];
							}
							// и оно не относиться к году текущего события, то закрываем год
							if (e[0] != o[0]) {
								html += '</ul>';
								matrix.curent_tree_events[o[0]]['to'] = o_hour_to+':00 ' + o[2] + ' ' + monthNames[parseInt(o_manth)]+ ' ' + o[0];
								matrix.curent_tree_events[o[0]].next = e[0];
								o0 = o[0];

							}
						}
						// обновляем размер и количество файлов всего дерева
						matrix.curent_tree_events['all'].size += size;
						matrix.curent_tree_events['all'].count += count;
						matrix.curent_tree_events['all'].under = e[0];

						// если в кеше нет года текущего события, то..
						if (typeof(matrix.curent_tree_events[e[0]]) == 'undefined' ) {
							//записываем новые данные в кеш
							matrix.curent_tree_events[e[0]] = {
									size : size,
									count : count,
									from : e[3]+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0],
									to : hour_to+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0],
									prev : o0,
									top: 'all',
									under: e[0]+'_'+e[1]
							};

							// строим дерево
							html += '<li id="tree_'+e[0]+'"><a href="#">'+e[0]+'</a><ul>';
						} else {
							//если есть то обновляем размер и количество и конек диапазона
							matrix.curent_tree_events[e[0]].size += size;
							matrix.curent_tree_events[e[0]].count += count;
							matrix.curent_tree_events[e[0]].to = hour_to+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0];
						}

						// если в кеше нет месяца текущего события, то..
						if (typeof(matrix.curent_tree_events[e[0]+'_'+e[1]]) == 'undefined' ) {
							//записываем новые данные в кеш
							matrix.curent_tree_events[e[0]+'_'+e[1]] = {
									size : size,
									count : count,
									from : e[3]+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0],
									to : hour_to+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0],
									prev: o1,
									top: e[0],
									under: e[0]+'_'+e[1]+'_'+e[2]
							};
							// строим дерево
							html += '<li id="tree_'+e[0]+'_'+e[1]+'"><a href="#">'+e[1]+' ('+monthNames[month]+')</a><ul>';
						} else {
							//если есть то обновляем размер и количество
							matrix.curent_tree_events[e[0]+'_'+e[1]].size += size;
							matrix.curent_tree_events[e[0]+'_'+e[1]].count += count;
							matrix.curent_tree_events[e[0]+'_'+e[1]].to = hour_to+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0];
						}

						// если нет дня текущего события, то..
						if (typeof(matrix.curent_tree_events[e[0]+'_'+e[1]+'_'+e[2]]) == 'undefined' ) {

							//записываем новые данные в кеш
							matrix.curent_tree_events[e[0]+'_'+e[1]+'_'+e[2]] = {
									size : size,
									count : count,
									from : e[3]+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0],
									to :   hour_to+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0],
									prev : o2,
									top: e[0]+'_'+e[1],
									under: e[0]+'_'+e[1]+'_'+e[2]+'_'+e[3]
							};

							// строим дерево
							html += '<li id="tree_'+e[0]+'_'+e[1]+'_'+e[2]+'"><a href="#">'+e[2]+'</a><ul>';
						}else {
							//если есть то обновляем размер и количество
							matrix.curent_tree_events[e[0]+'_'+e[1]+'_'+e[2]].size += size;
							matrix.curent_tree_events[e[0]+'_'+e[1]+'_'+e[2]].count += count;
							matrix.curent_tree_events[e[0]+'_'+e[1]+'_'+e[2]].to = hour_to+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0];
						}


						//записываем новые данные о события
						matrix.curent_tree_events[key] = {
								size : size,
								count : count,
								from : e[3]+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0],
								to : hour_to+':00 ' + e[2] + ' ' + monthNames[month]+ ' ' + e[0],
								next : false,
								prev : old_value,
								top: e[0]+'_'+e[1]+'_'+e[2]
						};
						// строим дерево
						html += '<li id="tree_'+key+'"><a href="#">'+e[3]+':00</a>';
						// записываем следующее события
						if (old_value) {
							matrix.curent_tree_events[old_value].next = key;
						}
						// сохраняем старое событие
						old_value = key;
					}
				});

				html += '</ul></ul>';
				// высчитываем новый выбранный диапазон событий если старого в новом дереве нет
				if (matrix.tree != 'all' && typeof(matrix.curent_tree_events[matrix.tree]) == 'undefined') {
					var str = matrix.tree;
					while (str != ''){
						var end = str.lastIndexOf( '_' );
						str = str.substr(0, end);
						if (typeof(matrix.curent_tree_events[str]) != 'undefined') {
							matrix.tree = str;
							break;
						}
					}
				}
				if (typeof(matrix.curent_tree_events[matrix.tree]) == 'undefined') {
					matrix.tree = 'all';
				}

				var open = '#tree_'+matrix.tree;
				var parent = $(self.holder).parent().hide();
				$("#tree_new").remove();
				parent.append('<div id="tree_new"></div>');
				// построение дерева
				$(self.holder).html(html)
					.jstree({
						"core" : {animation : 0},
						"plugins" : ["themes","html_data","ui","crrm"]
					})
					// событие возникает, если пользователь выбрал новый диапазон событий
					.bind("select_node.jstree", function (event, data) {
						// если режим детального просмотра, переходим в превью
						if (matrix.mode == 'detail') {
							matrix.preview();
						}

					var	tree = data.rslt.obj.attr("id").replace('tree_', '');

//					if(matrix.keyBoardTree != tree) {
//					matrix.keyBoardTree = tree;
//				    }

					//если диапазон не изменился - ничего не делаем
					if(matrix.keyBoardTree == tree) return;

					matrix.keyBoardTree = tree;


						var found = tree.split('_');

						var s = '';
						for(var i=0,ilen=found.length; i<ilen; i++) {
							if(s!='')
								s += '_';
							s += found[i];
							$('#tree_'+s+' > a').addClass('jstree-clicked');
						}

						if(keyBoard.boxesEnum.current()!=keyBoard.boxesEnum.TREE || typeof(data.args[2])!=='undefined') {
							// если новый диапазон, перестраиваем матрицу
							if (matrix.tree != tree) {
								matrix.tree = tree;
								matrix.keyBoardTree = tree;

								matrix.build();

								if(MSIE){
									scroll.setposition(scroll.position);
								}
							}

						}


						$.jstree._focused().set_focus("#tree_"+tree);
						$("#tree_"+tree).jstree("set_focus");

					})
					.bind("loaded.jstree", function (event, data) {
						$.jstree._focused().select_node(open);
						$.jstree._focused().open_node(open);
						$('#tree').show();
						$('ins.jstree-icon').hover(
								function(){
									$(this).attr('title',
											function(){
												if($(this).parent().hasClass('jstree-open')){
													return 'Свернуть';
												} else if ($(this).parent().hasClass('jstree-closed')) {
													return 'Развернуть';
												}
											});
								},
								function(){}
								);
					})
				.delegate("a", "click", function (event, data) { event.preventDefault();}).show();

				gallery.treeObject = $(self.holder);

				matrix.build();
			},
			// инициалзация дерева
			init: function(holder, ajax_params) {
				var self = this;
				self.holder = holder;

				if(ajax_params==null)ajax_params={'method': 'get_tree_events', 'on_dbld_evt':'inform_user'};

				// получаем данные о постройке дерева события
				$.ajax({
					  type: "POST",
					  timeout: update_tree_timeout*1000,
					  url: WwwPrefix+'/offline/gallery.php',
					  data: ajax_params,
					  success: function(data) {
							if (data.status == 'success'){
								matrix.tree_events = data.tree_events;
								matrix.cameras = data.cameras;
								gallery.tree_event.reload();
							} else if (data.status == 'error' && data.code=='0') {
								alert(lang.empty_tree);
								//$('#matrix_load').hide();
							}
							//если вовремя заполнения EVENTS_TREE были обнаружены дублированные события
					  		else if (data.status == 'error' && data.code=='1') {
					  			$('#matrix_load').hide();

						  			var header = "Ошибка";

						  			var message = "<h2 style='color: #000;'>" +"В диапазоне ["+data.range_start+" : "+data.range_end
						  			+"] в базе данных обнаружено "+data.qtty
						  			+" записей о ссылках на файлы с одинаковым временем создания и номером камеры (дубли).<br /><br />"
				  					+"<table>"
				  					+"<tr >"
						  			+"<td style='padding-left:10px; padding-right:10px; color:black; font-weight:bold;'>'Удалить'- </td>"
						  			+"<td style='color:black;'>удаление дублирующих записей. <br />"
				  					+"В случае выбора этой опции, будут удалены из базы данных только записи-дубли, "
				  					+"при этом сами записи об этих событиях будут сохранены в единственном варианте "
				  					+"и будут доступны для дальнейшего использования.<br />"
				  					//+"Для дальнейшего анализа ситуации вам будут предоставлен список удаденных записей-дублей в виде текстового файла."
				  					+"</tr>"
				  					+"<tr><td>&nbsp;</td><td>&nbsp;</td></tr>"
				  					+"<tr>"
				  					+"<td style='padding-left:10px; padding-right:10px; color:black; font-weight:bold;'>'Игнорировать'- </td>"
				  					+"<td style='color:black;'>игнорировать дублирующие записи.</h2><br />"
						  			+"В случае выбора этой опции, дублирующие записи остануться в базе данных, "
				  					+"но это никак не повлияет на дальнейшую работу, поскольку их наличие будет "
				  					+"учитываться. Однако, в случае обновления данных за период, который содержит "
				  					+"дублирующие записи, снова появится это уведомление."
						  			+"</td>"
						  			+"</tr>";
						  			//+"</table>";

						  			//+"</table>";

						  			message_box.yes_delegate = function(event){
						  				gallery.tree_event.init(holder, {'method': 'get_tree_events', 'on_dbld_evt':'clear'});
						  			};

						  			message_box.no_delegate = function(event){
						  				gallery.tree_event.init(holder, {'method': 'get_tree_events', 'on_dbld_evt':'ignore'});
						  			};

						  			message_box.buttons_name.No = "Игнорировать";
						  			message_box.buttons_name.Yes = "Удалить";

						  			message_box.show(message, header, message_box.message_type.error, message_box.button_type.YesNo);

					  		}
							//если вовремя очистки не были удалены все дубли
					  		else if (data.status == 'error' && data.code=='2') {
					  			$('#matrix_load').hide();

						  			var header = "Ошибка.";

						  			var message = "<h2 style='color: #000;'>" +"Не удалось удалить все дублирующие записи.<br />"
						  			+"Было удалено "+ data.qtty+ "записей-дублей. <br />"
						  			+" В диапазоне ["+data.range_start+" : "+data.range_end
						  			+"]  "
						  			+"остались записи о ссылках на файлы с одинаковым временем создания и номером камеры (дубли).<br /><br />"
				  					+"<table>"
				  					+"<tr >"
						  			+"<td style='padding-left:10px; padding-right:10px; color:black; font-weight:bold;'>'Удалить'- </td>"
						  			+"<td style='color:black;'>удаление дублирующих записей. <br />"
				  					+"В случае выбора этой опции, будут удалены из базы данных только записи-дубли, "
				  					+"при этом сами записи об этих событиях будут сохранены в единственном варианте "
				  					+"и будут доступны для дальнейшего использования.<br />"
				  					+"Для дальнейшего анализа ситуации вам будут предоставлен список удаденных записей-дублей в виде текстового файла."
				  					+"</tr>"
				  					+"<tr><td>&nbsp;</td><td>&nbsp;</td></tr>"
				  					+"<tr>"
				  					+"<td style='padding-left:10px; padding-right:10px; color:black; font-weight:bold;'>'Игнорировать'- </td>"
				  					+"<td style='color:black;'>игнорировать дублирующие записи.</h2><br />"
						  			+"В случае выбора этой опции, дублирующие записи остануться в базе данных, "
				  					+"но это никак не повлияет на дальнейшую работу, поскольку их наличие будет "
				  					+"учитываться. Однако, в случае обновления данных за период, который содержит "
				  					+"дублирующие записи, снова появится это уведомление."
						  			+"</td>"
						  			+"</tr>";
						  			//+"</table>";

						  			message_box.yes_delegate = function(event){
						  				gallery.tree_event.init(holder, {'method': 'get_tree_events', 'on_dbld_evt':'clear'});
						  			};

						  			message_box.no_delegate = function(event){
						  				gallery.tree_event.init(holder, {'method': 'get_tree_events', 'on_dbld_evt':'ignore'});
						  			};

						  			message_box.buttons_name.No = "Игнорировать";
						  			message_box.buttons_name.Yes = "Удалить";

						  			message_box.show(message, header, message_box.message_type.error, message_box.button_type.YesNo);

					  		}
						}
					});

			}
		},








		// объект управлением цветом камер
		cameras_color : {
			camera_id : '', // ид выбранной камеры
			camera_title : '', // заголовок выбранной камеры
			camera_collor : '', // цвет выбранной камеры
			camera_link : '', // ссылка на камеру
			// показываем окно выбора цвета
			open: function() {
				var self = this;
				keyBoard.beforeView = keyBoard.view;
				keyBoard.view = keyBoard.views.colorDialog;
				$('#cameras_color h2').html(lang.color_cameras+" &lt;"+ self.camera_title+"&gt;");

				$('#overlay').show();
				$('#cameras_color').show();
				//установка текущего элемента
				keyBoard.selectColor(0);
			},
			// закрываем окно выбора цвета
			close : function() {
				keyBoard.view = keyBoard.beforeView;
				$('#cameras_color').hide();
				$('#overlay').hide();
			},
			// выбор нового цвета камеры
			select : function() {
				var self = this;
				// читаем старый цвет камеры
				old_camera_collor = gallery.cookie.get('camera_'+self.camera_id+'_color');
				// записываем новый цвет камеры
				gallery.cookie.set('camera_'+self.camera_id+'_color',self.camera_collor);
				//удаляем старый цвет камеры
				if (old_camera_collor != '') {
					$('.camera_'+self.camera_id).removeClass(old_camera_collor);
					self.camera_link.removeClass(old_camera_collor + '_font');
				}
				// устанавливаем новый цвет
				$('.camera_'+self.camera_id).addClass(self.camera_collor);
				self.camera_link.addClass(self.camera_collor+'_font');

			},

			init:	function() {
				var self = this;
				// обработка нажатия ссылки камеры
				$('.set_camera_color').click(function(e){
					e.preventDefault();
					self.camera_id = $(this).attr('href').replace('#','');
					self.camera_title = $(this).html();
					self.camera_link = $(this);
					self.open();
					return false;
				});
				// обработка выбора цвета камеры
				$('#cameras_color .window_body li').click(function(){
					self.camera_collor = $(this).attr('class').replace(' selectColor','');
					self.select();
					self.close();
				});

				// обработка закрытия окна
				$('#cameras_color .close').click(function(){
					self.close();
				});
			}

		},
		// объект, показывающий сообщения хочет ли пользователь перейти на следующий временной диапазон
		nextwindow : {
			mode: '', // вверх или вниз по дереву
			// показ окна
			open: function(mode) {
				keyBoard.beforeView = keyBoard.view;
				keyBoard.view = keyBoard.views.chooseDialog;
				var self = this;
				self.mode = mode;
				$('#overlay').show();
				$('#nextwindow').show();
			},
			// закрытие окна
			close : function() {
				keyBoard.view = keyBoard.beforeView;
				$('#nextwindow').hide();
				$('#overlay').hide();
			},
			// если пользователь нажал да
			select : function() {
				var self = this;
				// запомнить выбор если выбрана галочка
				if ($('#checknextwindow').attr('checked')) {
					gallery.cookie.set('checknextwindow','yes');
				}

				if (self.mode == 'left') {
					// если пользователь идет вверх по дереву
					// обновляем матрицу и перемещаем текущий указатель в конец матрицы
					prev = matrix.curent_tree_events[matrix.tree].prev;
					new_num = matrix.curent_tree_events[prev].count - 1;
					sp = Math.floor(new_num / scroll.row_count) * scroll.row_count;
					matrix.num = new_num;
					scroll.position = sp;
					matrix.select_node = 'left';
					$.jstree._focused().deselect_node("#tree_"+matrix.tree);
					$("#tree_"+prev).jstree("set_focus");
					$.jstree._focused().select_node("#tree_"+prev);
					scroll.setposition(sp);

				}else if (self.mode == 'right') {
					// если пользователь идет вниз по дереву
					// обновляем матрицу и перемещаем текущий указатель в начало матрицы
					next = matrix.curent_tree_events[matrix.tree].next;
					matrix.num = 0;
					matrix.select_node = 'right';
					$.jstree._focused().deselect_node("#tree_"+matrix.tree);
					$("#tree_"+next).jstree("set_focus");
					$.jstree._focused().select_node("#tree_"+next);
				}
			},
			init:	function() {
				var self = this;
				// обработка события если пользователь нажал да
				$('#nextwindow .yes').click(function(){
					self.select();
					self.close();
				});
				// обработка события если пользователь нажал нет
				$('#nextwindow .no').click(function(){
					if ($('#checknextwindow').attr('checked')) {
						gallery.cookie.set('checknextwindow','no');
					}
					self.close();
				});
			}
		},
		// инициализация галереи
		init : function(config) {

			var self = this;
			// обновление настроек
			if (config && typeof(config) == 'object') {
			    $.extend(self.config, config);
			}

			$('#matrix_load').show();

			//инициализация месседж-бокса
			message_box.init();

			self.cookie.init({path:WwwPrefix+'/offline/gallery.php'});

			//Загрузка изображений контролов
			//Кнопки свернуть/развернуть
			gallery.images['preview'] = new Image();
			gallery.images['preview'].src =  WwwPrefix+'/offline/gallery/img/slide1.png';
			//Кнопки свернуть/развернуть
			gallery.images['detail'] = new Image();
			gallery.images['detail'].src =  WwwPrefix+'/offline/gallery/img/slide2.png';

			var wcheck = 0;
			var cams_chek = $('#cameras_selector .new_Check').each(function(i,val){
				if($(val).width()>wcheck){
					wcheck = $(val).width();
				}
			});
			$(cams_chek).width(wcheck);



			// организация увеличение размера списка камер
			if ($('#win_top').height() > 100) {
				$('#more_cam').show();
				$('#win_top').hover(
						function(){
							if (!$(this).hasClass('selectBox')) {
								$('#more_cam').hide();
								$('#win_top').height('auto');
							}
						},
						function(){
							if (!$(this).hasClass('selectBox')) {
								$('#more_cam').show();
								$('#win_top').height(100);
							}
						}
				);
			}

			// обработка выбора чекбокса камеры
			$('input[name="cameras"]').change(function(){
				var rez = gallery.reload_cams();
				if(!rez)
					$(this).attr('checked', 'checked');

				if ($(this).attr('checked')) {
					$(this).parent().attr('style','background-position: 0px -14px');
				} else {
					$(this).parent().attr('style','background-position: 0px -0px');
				}
			});

			$('#cameras_selector .niceCheck').click(function(){
				var rez = gallery.reload_cams();
				if(!rez) {
					$(this).attr('style','background-position: 0px -14px');
					$(this).children().attr('checked', 'checked');
				}
			});


			// обработка выбора чекбокса типа события
			$('#type_event input[name="type_event"]').change(function(){
				var rez = gallery.reload_events();
				if(!rez) {
					$(this).attr('checked', 'checked');

				}

				if ($(this).attr('checked')) {
					$(this).parent().attr('style','background-position: 0px -14px');
				} else {
					$(this).parent().attr('style','background-position: 0px -0px');
				}

			});
			$('#type_event .niceCheck').click(function(){
				var rez = gallery.reload_events();
				if(!rez) {
					$(this).attr('style','background-position: 0px -14px');
					$(this).children().attr('checked', 'checked');
				}
			});

			//Кнопка смены режима просмотра - детальный/миниатюры
			var btnCangeMode =	$('<div class="select_mode"> <img src="'+gallery.images['preview'].src +'" /></div>');

			$(btnCangeMode)
			.find("img")
			.width(220)
			.height(35);

			$(btnCangeMode)
			.click(function(e){
				var im = $(btnCangeMode).find('img');
					if(matrix.mode == 'detail'){
						matrix.preview();
					}else{
						matrix.detail();
					}
			});

			$("#toolbar>#toolbar_left:first-child").prepend(btnCangeMode);

			//кнопка установки оригинального размера
			var btn_orig_size = $("<div id='btn_orig_size' class='btn_img_size' style='left:45px;' ><img style='height: 30px; width: 30px;' src='"+WwwPrefix+"/img/1to1.png' title='Оригинальный размер' /> </div>")
					.click(function(event){
						var nr = matrix.num;
						var val = matrix.events[nr];
						if (typeof(val) == 'undefined') return;

						var height = val[3];
						var width = val[4];

						if(scale2.position != 0) scale2.setposition(0);

						//Изменение положения ползунка масштаба
						if($('.active .refBox').aplayerIsImage()) //Если картинка
						{
							//формирование src ресайза картинки
							var ResizedImgSrc = matrix.getResizedImageSrc(matrix.num, height, width);

							//Загрузка изображения соответствующего размера
							$('.active .refBox')
							.aplayerSetImgSrc(ResizedImgSrc)
							.aplayerResizeContanerOnlyToParent()
							.aplayerSetSizeMediaElt({'height': height, 'width': width});

							//визуализируем скролл масштаба режима просмотра
							$('#scale2').show();
							//показываем чекбокс пропорций
							$('div.propotion').show();
						}
						else if( value[7]=='audio' ) //Если внедренный объект  аудио
						{
						}
						else
						{
							if( $('.active .refBox').aplayerIsEmbededObject() )// если ембед
							{
								//Скрываем елемент управления масштабом
								$('#scale2').hide();
								//корректировка высоты с учетом панели управления ембеда
								height = parseInt(height)+25;
							}
							//установка размеров плеера в соответствии с размерами родительского элемента
							$('.active .refBox').aplayerResizeContanerOnlyToParent();
							//Изменение размеров медиа-элемента плеера
							$('.active .refBox').aplayerSetSizeMediaElt({
								'width':  parseInt(width),
								'height': parseInt(height)
							} );
						}
					})
					.hide();

			//кнопка вписать в окно
			var btn_cell_size = $("<div id='btn_cell_size' class='btn_img_size' style='left:0px;' ><img style='height: 30px; width: 30px;' src='"+WwwPrefix+"/img/expand.png' title='Вписать в окно' /> </div>")
				.click(function(event){
					scale2.setposition(0);
				})
				.hide();

			//позиционирование этих двух кнопок
			$("#btn_cell_size, #btn_orig_size" );

			//установка в панель инструментов
			$("#toolbar>#toolbar_right").prepend(btn_cell_size, btn_orig_size);

			// инициализация изменения размеров столбцов
			self.resize_column.init();

			// инициализация матрицы
			matrix.init(self.config.matrix);

			// инициализация дерева событий
			self.tree_event.init('#tree_new');

			// инициализация выбора цвета камеры
			self.cameras_color.init();

			// инициализация перехода на следующий временной диапазон
			self.nextwindow.init();

			// инициализация событий клавиатуры
			keyBoard.init();


			if(MSIE){
				var wt = $('#win_top');
				//установка высоты списка камер
				gallery.hcameras = $(wt).height()+parseInt($(wt).css('border-top-width'))+parseInt($(wt).css('border-bottom-width')) ;
			}

			//Установка начального состояния чекбокса "выбрать/отменить все камеры"
			var cntr=0;
			var col_cams = $('#cameras_selector .niceCheck').each(function(i,val){
				if($('input:checkbox', this).attr('checked')!='checked' ){
					cntr++;
				}
			});

			if(cntr==col_cams.length){
				$('#cam_selector').attr('checked', false).parent().attr('style','background-position: 0px 0px');
				$('#lbl_cam_selector').html('Выбрать все камеры');
				$('#select_all_cam>.new_Check').css('opacity', 1);
			}else{
				$('#cam_selector').attr('checked', true).parent().attr('style','background-position: 0px -14px');
				$('#lbl_cam_selector').html('Отменить выбор всех камер');
				if(cntr!=0){
					$('#select_all_cam>.new_Check').css('opacity', 0.5);
				}else{
					$('#select_all_cam>.new_Check').css('opacity', 1);
				}
			}

			//установка обработчика чекбокса "Выбрать/отменить все камеры"
			$('#select_all_cam').bind('click', function(e){
				$('#cameras_selector .niceCheck').each(function(i,val){
					if($('#cam_selector').attr('checked')=='checked' ){
						$(this).attr('style','background-position: 0px -14px');
						$(this).children().attr('checked', true);
						$('#lbl_cam_selector').html('Отменить выбор всех камер');
					}else{
						$(this).attr('style','background-position: 0px 0px');
						$(this).children().attr('checked', false);
						$('#lbl_cam_selector').html('Выбрать все камеры');
						//Устанавливаем матрицу на начало диапазона
						matrix.num = 0;
						scroll.setposition(0);
					}
				});

				gallery.reload_cams();
			})
			.find('label').bind('click', function(e){
				if($('#cam_selector').attr('checked')=='checked' ){
					$('#cam_selector')
					.attr('checked', false)
					.parent().attr('style','background-position: 0px 0px');

				}else{
					$('#cam_selector')
					.attr('checked', true)
					.parent().attr('style','background-position: 0px -14px');
				}
			});

		}
};


var message_box = {
		self : null,
		//тип соббщения
		message_type : {
			info:'mb_infomation',
			question:'mb_question',
			warning:'mb_warning',
			error:'mb_error'
		},
		
		//отображение кнопок
		button_type : {
			OK:'OK',
			YesNo:'YesNo'
		},
		
		buttons_name : {
			OK:'OK',
			Yes:'Да',
			No:'Нет'
		},
		
		//Заголовок
		header : 'message box HEADER',
		//текст собщения
		message : 'text of message',
		
		//изображения иконок типа сообщения
		images : {},
		
		//делегаты, срабатвающие по нажатию соотв. кнопки
		//устанавливать непосредственно перед выводом окна
		ok_delegate:function(event){},
		no_delegate:function(event){},
		yes_delegate:function(event){},
		//сброс делегатов
		reset_delegates : function(){
			message_box.ok_delegate=function(event){};
			message_box.no_delegate=function(event){};
			message_box.yes_delegate=function(event){};
			//названия кнопок востанавливаем
			message_box.buttons_name = { OK:'OK', Yes:'Да', No:'Нет' };
		},
		

		//инициализация message_box
		init : function(){
			//иконки message_box
			this.images[message_box.message_type.error] = new Image();
			this.images[message_box.message_type.error].src =  WwwPrefix+'/offline/gallery/img/mb_error.png';
			this.images[message_box.message_type.info] = new Image();
			this.images[message_box.message_type.info].src =  WwwPrefix+'/offline/gallery/img/mb_info.png';
			this.images[message_box.message_type.warning] = new Image();
			this.images[message_box.message_type.warning].src =  WwwPrefix+'/offline/gallery/img/bm_warning.png';
			this.images[message_box.message_type.question] = new Image();
			this.images[message_box.message_type.question].src =  WwwPrefix+'/offline/gallery/img/mb_question.png';
			
			$('#mb_btn_ok').live('click', function(e){
				message_box.close();
				message_box.ok_delegate(e);
				message_box.reset_delegates();
			});
			$('#mb_btn_no').live('click', function(e){
				message_box.close();
				message_box.no_delegate(e);
				message_box.reset_delegates();
			});
			$('#mb_btn_yes').live('click', function(e){
				message_box.close();
				message_box.yes_delegate(e);
				message_box.reset_delegates();
			});
		},
		
		//показать собщение
		show : function(message, header, message_type, button_type){
			
			button_type = button_type || message_box.button_type.OK;
			message_type = message_type || message_box.message_type.info;
			header = header || "Информационное собщение";
			
			var mainBox = $('<div id="message_box" class="message_box '+message_type+'" />');
			
			var mbHeader = $('<div id="mb_header" class="mb_header" ></div>')
				.append('<img src="'+message_box.images[message_type].src+'" alt="'+message_type+'" id="type_imgage" />')
				.append('<h3>'+header+'</h3>');
			
			var mbBody = $('<div id="mb_body" class="mb_body" >'+message+'</div>');

			var mbControlsBar = $('<div id="mb_controls_bar" class="mb_controls_bar" ></div>');

			var btnOK = $('<button id="mb_btn_ok" class="mb_button" value="ok" >'+message_box.buttons_name.OK+'</button>');
			var btnYes = $('<button id="mb_btn_no" class="mb_button" value="yes" >'+message_box.buttons_name.No+'</button>');
			var btnNo = $('<button id="mb_btn_yes" class="mb_button" value="no" >'+message_box.buttons_name.Yes+'</button>');
			
			switch (button_type) {
			case this.button_type.YesNo:
				$(mbControlsBar)
				.append(btnYes)
				.append(btnNo);
				break;
			case this.button_type.OK:	
			default:
				$(mbControlsBar).append(btnOK);
				break;
			}
			
			$(mainBox)
			.append(mbHeader)
			.append(mbBody)
			.append(mbControlsBar)
			.appendTo('body');
			
		},

		//закрытие окна
		close : function(){
			$('#message_box').remove();
		}


};




// основной объект матрицы
var matrix = {
	config : {
		cell_padding: 5, // паддинг ячейки
		cell_border : 0, // толщина бордера ячейки
		min_cell_width : 192, // минимальная ширина ячейки
		min_cell_height : 192, // минимальная высота ячейки
		max_cell_width: 0,  // максимальная ширина ячейки
		max_cell_height: 0,  // максимальная высота ячейки
		event_limit : 20000
	},
	keyBoardTree: 'all',
	currentOffset: null,
	tree: 'all', // текущий временной диапазон
	height: 0, // текущая высота матрицы
	width: 0, // текущая ширина матрицы
	cell_height: 0, // высота ячейки
	cell_width: 0, // ширина ячейки
	thumb_width : 0, // ширина миниатюры
	thumb_height : 0, //высота миниатюры
	cell_count: 2, // количество ячеек
	count_row : 5, //количество строк в матрице
	count_column: 5, // количество столбцов в матрице
	events : {}, // текущие евенты в матрице
	all_events : {}, // кеш евентов
	num : 0, // текущая позиция в матрице
	scroll: false, // использование скрола
	count_src : 0,
	load_src: 0,
	reference:'reference', //имя аттрибута для освобождения href ссылок
	mode : 'preview', // режим просмотра
	proportionDetail: false, //изменение режима пропорций в detail
	cur_count_item : 0, // текущее количество загруженных событий
	send_query: false, // можно ли посылать запросы к базе
	select_node : false, // можно ли выбирать другой диапазон
	isResizeMode : false, //активирован режим ресайза
	//объект для востановления матрицы при выходе из режима просмотра
	recover:{
		cell_style:null,
		refBox_style:null,
		elem_style:null
	}, 
	
	
	init: function(config) {
		
		// отменяет действие по клику
		$('#scroll_content').click(function(event) {
			event.preventDefault();
		});

		$('.matrix_mode').click(function(event) {
			event.preventDefault();
		});

		keyBoard.beforeView = keyBoard.view;
		keyBoard.view = keyBoard.views.matrix;
		if (config && typeof(config) == 'object') {
		    $.extend(matrix.config, config);
		}

		// обновление ширины и высоты ячейки
		matrix.cell_height = matrix.config.min_cell_height;
		matrix.cell_width = matrix.config.min_cell_width;

		// обработка переключение режима матрицы
		$('#scroll_content .content_item a').live('dblclick', function(e) {
			e.preventDefault();
			if(matrix.mode == 'preview') matrix.detail();
			else matrix.preview();
			return false;
		});
		$('#scroll_content .content_item').live('click', function(event) {
			event.preventDefault();
			var re = /cell_(\d+)/i;
			var id = $(this).attr('id');
			var found = id.match(re);
			if(typeof(found[1])!='undefined') {
				$('#cell_'+matrix.num).removeClass('active');
				$(this).addClass('active');
				matrix.num = parseInt(found[1]);
			}
		});

		// обработка чекбокса сохранять пропорции
		$('#proportion').click(function(){
			matrix.doProportion();

			if ($(this).attr('checked')) {
				$(this).parent().attr('style','background-position: 0px -14px');
			} else {
				$(this).parent().attr('style','background-position: 0px -0px');
			}
		});

		$('.propotion .niceCheck').click(function(){
			matrix.doProportion();
		});
		// обработка чекбокса показывать информацию
		$('#info').click(function(){
			matrix.doShowInfo();

			if ($(this).attr('checked')) {
				$(this).parent().attr('style','background-position: 0px -14px');
			} else {
				$(this).parent().attr('style','background-position: 0px -0px');
			}
		});

		$('.event_info .niceCheck').click(function(){
			matrix.doShowInfo();
		});
		// обновление матрицы
		matrix.resize();
		//инициализации элемента масштаба режима миниатюр
		scale.init();

		//инициализации элемента масштаба детального режима
		scale2.init();

		self.res = false;
		// изменить размер матрицы если было изменено размеры окна
		$(window).bind("resize", function(){
			
		clearInterval(self.res);

		self.res = setTimeout(function() {
				
		pageX = parseInt(gallery.cookie.get('resize_column'));

		if (pageX) {
				if( typeof( window.innerWidth ) == 'number' ) {
				//Non-IE
				gallery.resize_column.myWidth = window.innerWidth;
				gallery.resize_column.myHeight = window.innerHeight;
				} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
					//IE 6+ in 'standards compliant mode'
					gallery.resize_column.myWidth = document.documentElement.clientWidth;
					gallery.resize_column.myHeight = document.documentElement.clientHeight;
				}
				if (pageX < gallery.resize_column.myWidth - 535 && pageX - (gallery.resize_column.myWidth - 535) < 300 ) {
						pageX = pageX - (gallery.resize_column.myWidth - 535);
				} else {
						pageX = 300;
				}
				gallery.cookie.set('resize_column', pageX);
				gallery.resize_column.resize(pageX);
			}
			matrix.resize();
			clearInterval(self.res);
			}, 200);
		});
	},

	// обновление чекбокса пропорций
	doProportion : function() {
		//Если в режиме detail
		if(matrix.mode == 'detail')
		{
			matrix.proportionDetail = true;
			scale2.updateposition(scale2.position);
		}
		else //Если в режиме preview 
		{
			//установка ресайзеных какртинок
			scale.updateposition(scale.position);
		}
		
		if ($('#proportion').attr('checked')) {
			gallery.cookie.set('proportion', 'checked');
		} else {
			gallery.cookie.set('proportion', '');
		}
	},
	// обновление чекбокса информации
	doShowInfo : function() {
		if ($('#info').attr('checked')) {
			gallery.cookie.set('info', 'checked');
			matrix.thumb_height -= 24;
			$('.content_item .info_block').show();
		} else {
			gallery.cookie.set('info', '');
			matrix.thumb_height += 24;
			$('.content_item .info_block').hide();
		}
		//корректировка размеров тамбнейлов
		matrix.doProportion();

		$('#scroll_content .show').each(function() {
			matrix.setimagesize($('#info').attr('id').replace('cell_',''));
		});
	},
	
	
	// если включили режим детальный просмотр
	detail : function() {
		matrix.mode = 'detail';
		
		//меняем изображение кнопки смены режимов
		$('img','.select_mode').attr('src', gallery.images['detail'].src);
		
		keyBoard.view = keyBoard.views.detail;
		
		//скрываем все ячейки матрицы за исключением активной
		$(".content_item").each(function(){
			if(!$(this).hasClass("active")){
    			$(this).hide();
			}
			//сохраняем необходимые параметры активного элемента
			else{
				matrix.recover.cell_style = $(this).attr('style');
				matrix.recover.elem_style = $(this).find(".elem").attr('style');
				matrix.recover.refBox_style = $(this).find(".refBox").attr('style');
			}
		});
		
		//прячем info_block-и
		$('.info_block').addClass('not_visible');

		//фиксируем высоту tool bar		
		$('#toolbar').height($('#toolbar').height());
		//переключаем режим tool bar
		$('#toolbar .preview').css({'display':'none'});
		$('#toolbar .detail').show();
			
		//отключаем tooltip
		$(".elem").tooltipOff();
		//если есть тултип - удаляем
		$('.tooltip').remove();
			
		//скрываем скролл матрицы
		$("#scroll_v").hide();
		//расширяем панель матрицы на освободившееся место
		$("#list_panel").width($("#list_panel").width() + $("#scroll_v").width());
		
		//Установка размеров отображаемого элемента
		matrix.loaddetailsrc();
		scale2.setposition(scale2.position);
		//востанавливаем позицию изображения
		scale2.restore_content_position();
		//если флоуплеер
		$.aplayer.fplayer_ready_delegate = function(){
			scale2.setposition(scale2.position);
			//востанавливаем позицию изображения
			scale2.restore_content_position();
		};
		
		//если не внедренный объект
		if( !$('.content_item.active .refBox').aplayerIsEmbedOrObject() ){
			//Визуализируем кнопки масштабирования
			$('#btn_cell_size, #btn_orig_size').show();
		}
	},
	
	
	// если включили режим миниатюр
	preview : function() {
		//сохраняем позицию изображения текущего елемента
		scale2.save_content_position(); 
		$('.propotion').show();
		if (typeof(matrix.curent_tree_events[matrix.tree]) != 'undefined') {
			keyBoard.beforeView = keyBoard.view;
			keyBoard.view = keyBoard.views.matrix;

			matrix.mode = 'preview';
	
			//переключаем toolbar в режим миниатюрs
			$('#toolbar .detail').hide();
			$('#toolbar .preview').show();
			
			//востанавливаем размер панели матрицы 
			$("#list_panel").width($("#list_panel").width() - $("#scroll_v").width());
			//отображаем скролл матрицы
			$("#scroll_v").show();
			
			//отображаем все ячейки матрицы 
			$(".content_item").each(function(){
				if($(this).hasClass("active")){
					//востанавливаем параметры активного элемента
					if(MSIE){
						$(this).attr('style', matrix.recover.cell_style)
						.hide()
						.find('.elem').attr('style', matrix.recover.elem_style)
						.find('.refBox').attr('style', matrix.recover.refBox_style)
						.aplayerResizeToParent();
					}else{
						$(this).attr('style', matrix.recover.cell_style)
						.find('.elem').attr('style', matrix.recover.elem_style)
						.find('.refBox').attr('style', matrix.recover.refBox_style)
						.aplayerResizeToParent();
					}
				}
   				$(this).show();
			});
			
			//отображаем info_block-и
			$('.info_block').removeClass('not_visible');
			
			//позиционирование в ячейке матрицы
			$('.aplayer').each(function(){
				$.aplayer.setMediaEltPosition($(this).attr('id') , { left:'0px', top:'0px'}  );
			});
			
			//Если в DETAIL был изменен режим пропорций
			if(matrix.proportionDetail){
				matrix.proportionDetail=false;
				scale.updateposition(scale.position);
			}

			//обновляем статистику
			matrix.update_statistic();
			
			//Включаем тултип
			$(".elem").tooltip();

			//Скрываем кнопки масштабирования
			$('#btn_cell_size, #btn_orig_size').hide();
			
			//Скрываем кнопки масштабирования
			$('#btn_cell_size, #btn_orig_size').hide();

			//меняем изображение кнопки смены режимов
			$('img', '.select_mode').attr('src', gallery.images['preview'].src);
			
		}
	},

	
	// перестраиваем матрицу при изменении размеров
	resize : function() {

		// обновляем ширину колонок
		gallery.resize_column.resize($('#sidebar').width()-2);

		$('#tree').height($('#sidebar').height() - $('#type_event').height() - $('#favorite').height() - $('#statistics').height()-90);

		// высчитываем размеры табнейлов
		matrix.thumb_width = matrix.cell_width-matrix.config.cell_padding-4;
		matrix.thumb_height = matrix.cell_height-matrix.config.cell_padding*2 -3;

		if(MSIE){
			// высчитываем размеры табнейлов
			matrix.thumb_width = matrix.cell_width-matrix.config.cell_padding - 8 ;
			matrix.thumb_height = matrix.cell_height-matrix.config.cell_padding*2 - 8 ;
		}
		
		// показываем или скрываем информацию о событии
		if ($('#info').attr('checked')) {
			matrix.thumb_height -= 24;
			$('.content_item .info_block').show();
		} else {
			$('.content_item .info_block').hide();
		}
		// определяем новые размеры матрицы
		var hc;
		// обновляем размеры детального просмотра
		if(MSIE){
			hc = $('#content').height() - 100 - $('#toolbar').height();
			hc -=30 ;
			
		}else{
			hc = $('#content').height() - gallery.hcameras - $('#toolbar').height();
			hc-=23;
		}
        /*
        alert('Вызвано в matrix resize:\n' +
            'winbot height = ' + $('#win_bot').height() +
            '\nheight new = ' + hc +
            '\ntoolbar height = ' + $('#toolbar').height() +
            '\nwintop height = ' + $("#win_top").height() +
            '\ncontent height = ' + $("#content").height());
            //*/

        $('#win_bot').height(hc);
		
		$('#scroll_v').height(hc);
		
		// исправление бага с высотой!!! придумать что то лучше 
		//if($('#list_panel').height()!==0) matrix.height = $('#list_panel').height();
		matrix.height = hc;

		//проверяем высоту
		if(hc<=matrix.thumb_height ) {
			return;
		}
		
		// 	исправяем баг с длинной не видимого элемента
		var pan_height = $('#list_panel').css('height');
		var pan_width = parseInt($('#list_panel').css('width').replace('px',''));

		matrix.width = pan_width; //$('#list_panel').width();
		$('#matrix_load img').css('margin-top', $('#content').height()/2);

		// высчитываем новую высоту и ширину ячейки
		var old_width = matrix.config.max_cell_width;
		if ((matrix.height-(matrix.config.cell_padding+matrix.config.cell_border)*2) > (matrix.width/2 -(matrix.config.cell_padding+matrix.config.cell_border)*4)) {
			matrix.config.max_cell_width = (matrix.width/2 -(matrix.config.cell_padding+matrix.config.cell_border)*4);
			matrix.config.max_cell_height = (matrix.width/2 -(matrix.config.cell_padding+matrix.config.cell_border)*4);
		} else {
			matrix.config.max_cell_width = matrix.height-(matrix.config.cell_padding+matrix.config.cell_border)*2;
			matrix.config.max_cell_height = matrix.height-(matrix.config.cell_padding+matrix.config.cell_border)*2;
		}
		if (matrix.config.max_cell_width < matrix.cell_width || matrix.config.max_cell_height < matrix.cell_height) {
			matrix.cell_height = matrix.config.max_cell_height;
			matrix.cell_width = matrix.config.max_cell_width;
		}
		
		
		// обновляем элемент масштаба
		if (old_width != matrix.config.max_cell_width) {
			scale.reload(old_width);
		}

		// высчитываем количество, рядов, столбцов ячеек в матрице
		matrix.count_column = Math.floor(matrix.width /  (matrix.cell_width+(matrix.config.cell_padding+matrix.config.cell_border)*2));
		matrix.count_row = Math.floor(matrix.height /  (matrix.cell_height+(matrix.config.cell_padding+matrix.config.cell_border)*2));
		matrix.cell_count =  matrix.count_column * matrix.count_row;

		// центрируем содержимое в ячейках
		var left =  Math.floor((matrix.width-matrix.count_column*matrix.cell_width)/ matrix.count_column/2);
		var top =  Math.floor((matrix.height-matrix.count_row*matrix.cell_height)/ matrix.count_row/2);
		matrix.cell_padding = top + 'px ' + left + 'px';
		$('#scroll_content .content_item').css({'padding':matrix.cell_padding});
		
		// если элемента скрола нет, то создаем его
		if (matrix.scroll == true) {
			var prev_sp =  scroll.position;
			var sp = scroll.position;
			
			scroll.init({
				height:matrix.height-82,
				cell_count:Math.ceil(matrix.count_item/matrix.count_column), 
				row_count: matrix.count_column, 
				matrix_count: Math.ceil(matrix.cell_count/matrix.count_column)
			});
			
			//начальное определение позиции скролла
			sp = Math.floor(prev_sp/matrix.count_column)*matrix.count_column;
		
			//пытаемся избежать запроса ивентов
			if(prev_sp > sp) sp += scroll.row_count;
			//проверка условия попадания выбранной ячейки в диапазон матрицы
			while(sp > matrix.num || sp+matrix.cell_count-1 < matrix.num ){
				if(sp > matrix.num){
					sp -= scroll.row_count;
				}
				if(sp+matrix.cell_count-1 < matrix.num){
					sp += scroll.row_count;
				}
			}
			
			//проверка допустимости значения
			if(sp<0){
				sp=0;
			}
			//устанавливаем новую позицию скролла
			scroll.position = sp;
			
			//модификация матрицы
			matrix.modify(sp, prev_sp);
			
			scroll.setposition(sp);
		}

		//если в detail режиме - перезапускаем detail
		if(matrix.mode=='detail') matrix.detail();
		
	},
	
	//модификация матрицы при ресайзе окна или при изменении масштаба
	modify : function(sp, prev_sp){

		var i=0;
		//удаляем лишние ячейки
		$(".content_item").each(function(index, value){
			i = prev_sp+index;
			//если номер текущей ячейки меньше нового значения позиции скролла - удаляем ячейку
			if(i < sp ){ 
				$(this).remove();
			}
			//ресайз ячеек
			else{ 
				matrix.setimagesize(prev_sp+index); 
				if(matrix.events[i][7]=='image')
				{
					//формирование src ресайза картинки
					 NewSRC = matrix.getResizedImageSrc(i, matrix.cell_height, matrix.cell_width);
					//Загрузка изображения соответствующего размера
					$(this).find(".refBox").aplayerSetImgSrc(NewSRC);
				}
				$(this).find(".refBox").aplayerResizeToParent();
			}
		});

		//добавить ячейки в начало матрицы в обратном порядке
		if(prev_sp > sp){
			for(var i = prev_sp-1; i>=sp; i-- ){
				matrix.create_cell(i, 'prepend');
			}
		}

		//удаляем лишние ячейки
		$(".content_item").each(function(index, value){
			//если больше максимального номера в матрице - удаляем ячейку
			if(index > matrix.cell_count-1 ){
				$(this).remove();
			}
		});		
		
		//если размер матрицы больше, чем текущее кол-во ячеек - добавляем недостающее кол-во ячеек в конец матрицы
		var ix = sp+$(".content_item").length ;
		if(matrix.cell_count > $(".content_item").length){
			for( ; ix < sp+matrix.cell_count ; ix++){
				matrix.create_cell(ix, 'append');
			}
		}

		// обновляем размеры и позиционирование ячеек в матрице  
		$('#scroll_content .content_item').height(matrix.cell_height);
		$('#scroll_content .content_item').width(matrix.cell_width);
		$('#scroll_content .content_item').css({'padding' : matrix.cell_padding});
		
		//корректировка позиционирования лого-плей
		$('.logoPlay').each(function(){
			$(this).removeAttr('style').css({'position':'relative', 'top': ($(this).parent().height()- $(this).height())/2 });
		});
		
		//Включаем тултип
		$(".elem").tooltip();
		
		//установка активного элемента 
		$('.active').removeAttr('active');
		$('#cell_'+matrix.num).addClass('active');
		
	},
		
	//создание недостающих ячеек (при модификации матрицы в ходе ресайза)
	create_cell : function(el_num, att_mode) {
	
		var height, width; //размер ячейки
		var img_height, img_width;//размер изображения
		
		var reg = new RegExp('\\.\\w{3,4}\\s*', 'i'); //для получения расширения файла

		var active = '';
	
		var html = '';

		if (typeof( matrix.events[el_num]) == 'undefined') {
			matrix.get_events(scroll.position);
		}
					
	
		if (typeof( matrix.events[el_num]) != 'undefined'){
			value = matrix.events[el_num];

			camera_class = gallery.cookie.get('camera_'+value[5]+'_color');
			if (camera_class != '') {
				camera_class = ' '+camera_class;
			}

			html += '<div id="cell_'+el_num+'" class="content_item show'+' camera_'+value[5]+' '+camera_class+'" style="display:block;">';
			html += '<div class="elem" style="padding-top:0px; padding-left :0px; padding-right:0px;" >';

			html += '<div class="img_block"><a class="refBox" '+matrix.reference+'="#cell_'+el_num+'"></a></div>';

			html += '<a '+matrix.reference+'="#cell_'+el_num+'"><div class="info_block"';
			if ($('#info').attr('checked')) {
				html += ' style="display:block;"';
			} else {
				html += ' style="display:none;"';
			}
						
						
			//Получить расширение файла 
			var extension=value[2].match(reg);
			extension=extension[extension.length-1].slice(1);
					 	
			html += '>'+matrix.cameras[value[5]].text_left+'<br /> '+value[7]+': '+extension;
							
			if (value[7] == 'image') html +=' ('+ value[6]+') <br />';
			else html +=' ('+ value[8]+') <br />'; 

			html+= value[9];
			html += '<br /> </div></a>';
						
			html += '</div>';
			html += '</div>';
		}

		//добавляем элементы
		if(att_mode == 'prepend')$('#scroll_content').prepend($(html));
		else $('#scroll_content').append($(html));
			
		//Устанавливаем плеер в матрицу
		matrix.setimagesize(el_num);
		if (typeof( matrix.events[el_num]) != 'undefined'){
			var value = matrix.events[el_num];

			//формирование html ToolTip
			var ttl = '<tr><td>Камера</td> <td>'+matrix.cameras[value[5]].text_left+'</td> </tr>';
			if (value[7] == 'image'){
				ttl +='<tr><td>Файл</td>   <td>'+value[2].slice((value[2].lastIndexOf('/')+1))+'</td> </tr>';
				ttl +='<tr><td>Размер</td> <td>'+ value[6]+' ['+value[4]+'x'+value[3]+']</td> </tr>';
				ttl +='<tr><td>Создан</td> <td>'+value[1]+'</td> </tr>';
			}
			else{
				ttl += '<tr><td>Файл</td>   <td>'+value[2].slice((value[2].lastIndexOf('/')+1))+' ['+value[4]+'x'+value[3]+']</td> </tr>';
				ttl +='<tr><td>Размер</td> <td>'+value[6]+', '+ value[8]+'</td> </tr>';
				ttl +='<tr><td>Начало</td> <td>'+value[9]+'</td> </tr>';
				ttl +='<tr><td>Конец</td> <td>'+value[1]+'</td> </tr>';
			}

			var ResizedImgSrc = MediaUrlPref+ value[2];
			//Установка прямой ссылки на ресурс
			$('#cell_'+el_num+' a').attr({'href': ResizedImgSrc })
			if (value[7] == 'image') {

			//определяем размеры изображения
			height =  $('#cell_'+el_num+' .refBox').height();
			width = $('#cell_'+el_num+' .refBox').width();
							
			img_height = height;
			img_width = width;
							
			//если включен режим пропорций
			if( $('#proportion').attr('checked')=='checked' ){
				var img_height = value[3];
				var img_width = value[4];
				var origin_proportion = img_width/img_height;
			
				elem_proportion = width/height;
				
				if(origin_proportion > elem_proportion ){
					img_height = parseInt(width/origin_proportion);
					img_width = width;
				}
				else {
					img_width = parseInt( height*origin_proportion);
					img_height = height;
				}
			}
			//формирование src ресайза картинки
			var ResizedImgSrc = matrix.getResizedImageSrc(el_num, img_height, img_width);
							
			$('#cell_'+el_num).find(".elem").attr('tooltip',ttl).end()
			.find('a.refBox').empty().addPlayer({'src': ResizedImgSrc, 'useImageSize':'true' })
			.aplayerResizeToParent();
			}
			else {
				$('#cell_'+el_num).find(".elem").attr('tooltip',ttl).end()
				.find('a.refBox').empty().addPlayer({'src': ResizedImgSrc, 'logoPlay':'true' })
				.aplayerResizeToParent();
			}
		}
		
	},
	
	// задаем размер изображения в ячейке
	setimagesize : function(el) {
		if (typeof(matrix.events[el]) != 'undefined') {
			
			var thumb_width = 	matrix.thumb_width;
			var thumb_height = matrix.thumb_height;
			
			if ($('#proportion').attr('checked') && matrix.events[el][7]=='image' ) {
				// если выбран чекбокс сохранять пропорции
				var w = thumb_width;
				var h = Math.floor(matrix.events[el][3]*w/matrix.events[el][4]);

				if (h > thumb_height) {
					h = thumb_height;
					w = Math.floor(matrix.events[el][4]*h/matrix.events[el][3]);
				}
				
				thumb_width = w;
				thumb_height = h;
			}
			
			// задаем новые размеры
			if($('#cell_'+el+' a.refBox').aplayerIsImage())	{
				$('#cell_'+el+' a.refBox').css({'display':'block'})
				.height(thumb_height)
				.width(thumb_width)
				.aplayerSetSize({'height':thumb_height, 'width': thumb_width });
				
			} else 	{
				$('#cell_'+el+' a.refBox').css({'display':'block'})
				.height(thumb_height)
				.width(thumb_width)
				.aplayerResizeContanerOnlyToParent();
			}
			
		}
	},
	
	//Устанавливает размеры элементов в режиме делального просмотра
	loaddetailsrc : function() {

		if (typeof(matrix.events[matrix.num]) != 'undefined') {
			
			var value = matrix.events[matrix.num];

			//Визуализируем активную ячейку в режиме детального просмотра
			//сохраняем необходимые параметры нового активного элемента
			$(".content_item.active").each(function(){
				matrix.recover.cell_style = $(this).attr('style');
				matrix.recover.elem_style = $(this).find('.elem').attr('style');
				matrix.recover.refBox_style = $(this).find(".refBox").attr('style');
				
				//если лого-плей - переключаем в режим плеера
				$(this).find(".refBox .aplayer").each(function(){
						$(this).parent().addPlayer({'src': $(this).attr('s'), 'type':'"'+$(this).attr('t')+'"' ,'controls':'mini' }).click()
						.end().removeAttr('t').removeAttr('s').unbind('click');
				});

				//Устанавливает для элементов текущей ячейки размеры просмотра
				$(this).css({
					"padding": 0 ,
					'height':matrix.height+'px',
					'width':(matrix.width + $("#scroll_v").width())+'px'
					});

				$(".active .elem").css({
					'padding-top':'10px',
					'padding-left':'5px',
					'padding-right':'7px'
				});
				
				$(".active .refBox").css({
					"padding": 0 ,
					'height':(matrix.height-15)+'px',
					'width':(matrix.width + $("#scroll_v").width()- 8)+'px'
					});
				});
			
			// обновляем статистику события
			matrix.update_statistic();
		}
	},
	
	// загрузка изображения
	loadsrc : function(el) {
		// увеличиваем счетчик изображений
		matrix.count_src++;
		
		if (matrix.count_src > matrix.load_src) {
			// если количество загруженных изображений меньше количество всего изображений, показываем ромашку
//			$('#matrix_load').show();
		}
		// создаем объект изображения
		var img = new Image();
		img.onload = function() {
			// обновляем счетчик загруженных изображений
			matrix.load_src++;
			if (matrix.load_src == matrix.count_src) {
				// если все изображения загружены, то убираем ромашку
				$('#matrix_load').hide();
			}
			// записываем в кеш, что изображение уже загрузилось и есть в кеше браузера
			matrix.events[el].image_chache = true;
			};

		img.onerror = function() {
			//изображение не загрузилось
			// показываем картинку ошибки в ячейке
			var errorImgSrc = Protocol+Hostname+WwwPrefix+'/offline/gallery/img/error.jpg';
			//Установка плеера 
			$('#cell_'+el+' .img_block a').css({ 'display':'block', 'width':'100%', 'height':'100%', 'type':'audio'}
					).addPlayer({'src': errorImgSrc });
			// задаем новые размеры изображения
			matrix.setimagesize(el);
			// обновляем счетчик загруженных изображений
			matrix.load_src++;
			if (matrix.load_src == matrix.count_src) {
				// если все изображения загружены, то убираем ромашку
				$('#matrix_load').hide();
			}
		};
		
		var value = matrix.events[el];
		//формируем строку запроса для ресайза картинки
		var ResizedImgSrc;// = MediaUrlPref+ value[2];
		if (value[7] == 'image') {
			
			//вычисляем размеры изображения
			var width = matrix.thumb_width;
			var height = matrix.thumb_height;
			if ($('#proportion').attr('checked')) {
				// если выбран чекбокс сохранять пропорции
				var w = width;
				var h = Math.floor(matrix.events[el][3]*w/matrix.events[el][4]);
				if (h > height) {
					h = height;
					w = Math.floor(matrix.events[el][4]*h/matrix.events[el][3]);
				}
				width = w;
				height = h;
			}
			//формируем src изображения соответствующего размера
			ResizedImgSrc = matrix.getResizedImageSrc(el, height, width);
			// загружаем изображение
			img.src = ResizedImgSrc;
		}
		else
		{
			img.src = MediaUrlPref + matrix.events[el][2];
		}
	},

	// обновление матрицы
	update : function(sp) {

		$('#matrix_load').show();
		var hide_over = true;
		
		var height, width; //размер ячейки
		var img_height, img_width;//размер изображения
		
		var reg = new RegExp('\\.\\w{3,4}\\s*', 'i'); //для получения расширения файла
		//определение параметров в зависимости от режима отображения
		var display_mode = matrix.mode == "detail"? "none" : "block";
		
		var i = sp;
		var active = '';
		var get = false;
		// чистим кэш
		var aa = 0;
		$.each(matrix.events, function( i,value) {
			aa++;
		});

		var dev = aa - matrix.config.event_limit;
		if (dev > 0) {
			$.each(matrix.events, function( i,value) {
				delete matrix.events[i];
				dev--;
				if (dev <= 0) {
					 return false;
				}
			});
		}

		// происходит проверка, есть ли необходимые элементы в кеше
		
		var count_events = matrix.cell_count;
		
		if (matrix.cell_count > matrix.curent_tree_events[matrix.tree].count) {
			count_events = matrix.curent_tree_events[matrix.tree].count;
		}else if (matrix.cell_count+sp > matrix.curent_tree_events[matrix.tree].count) {
			count_events = matrix.curent_tree_events[matrix.tree].count - sp;
		}
		
		for (var i = sp; i < sp + count_events; i++) {

			if (typeof( matrix.events[i]) == 'undefined') {
				get = true;
				break;
			}
		}
		if (get) {
			// нет необходимых элементов в кеше, делаем запрос
			matrix.get_events(sp);
		} 
		else 
		{
			
			//Если кол-во элтов матрицы не соответствует кол-ву установленных плееров- Пересоздаем матрицу 
			if(matrix.cell_count != $("[id^="+$.aplayer.idContainer+"]").length)	
			{
				
				
				
				$('#scroll_content').empty();
				var html = '';
			
				// все элементы матрицы есть в кеше, строим матрицу
				var loadimage = {};

				for (var i = sp; i < sp+ matrix.cell_count; i++) {
					if (typeof( matrix.events[i]) != 'undefined'){
						
						value = matrix.events[i];

						camera_class = gallery.cookie.get('camera_'+value[5]+'_color');
						if (camera_class != '') {
							camera_class = ' '+camera_class;
						}

						html += '<div id="cell_'+i+'" class="content_item show'+' camera_'+value[5]+' '+camera_class+'" style="display:'+display_mode+';">';
						html += '<div class="elem" style="padding-top:0px; padding-left :0px; padding-right:0px;">';
						
						html += '<div class="img_block"><a class="refBox" '+matrix.reference+'="#cell_'+i+'"></a></div>';
					
						if (value[7] == 'image') {
							if (typeof( value.image_chache) != 'undefined' && value.image_chache) {
								loadimage[i] = true;
							} else {
								loadimage[i] = false;
							}
						}
						//ad hoc
						else loadimage[i] = true;

						html += '<a '+matrix.reference+'="#cell_'+i+'"><div class="info_block"';
						if ($('#info').attr('checked')) {
							html += ' style="display:block;"';
						} else {
							html += ' style="display:none;"';
						}
						
						
						//Получить расширение файла 
					 	var extension=value[2].match(reg);
					 	extension=extension[extension.length-1].slice(1);
					 	
						html += '>'+matrix.cameras[value[5]].text_left+'<br />'+value[7]+': '+extension;
							
						if (value[7] == 'image') html +=' ('+ value[6]+') <br />';
						else html +=' ('+ value[8]+') <br />';
						html+=value[9];
						html+='<br /></div></a>';
						html += '</div>';
						html += '</div>';
						
					}
				}
				$('#scroll_content').html(html);
			
			// проверяем какие изображения есть в кеше браузера, а какаие надо загрузить
			var ci = i + matrix.count_column;
			hide_over = true;
			for(i; i<=ci; i++) {
				if (typeof( matrix.events[i]) != 'undefined' && matrix.events[i][7] == 'image') {
					if (typeof( matrix.events[i].image_chache) != 'undefined' && matrix.events[i].image_chache) {
						loadimage[i] = true;
					} else {
						loadimage[i] = false;
						hide_over = false;
					}
				}
			}
			
				// загружаем изображения и меняем размеры
			$.each(loadimage, function(key, value) {
				if (value) {
					matrix.setimagesize(key);
				}else {
					matrix.loadsrc(key);
				}
			});
			// обновляем размеры и позиционирование ячеек в матрице
			$('#scroll_content .content_item').height(matrix.cell_height);
			$('#scroll_content .content_item').width(matrix.cell_width);
			$('#scroll_content .content_item').css({'padding' : matrix.cell_padding});
			
			//Если включен режим детального просмотра - создаем ячейки скрытыми
			if(matrix.mode=="detail") $('#scroll_content .content_item').hide();
			
			//Устанавливаем плеер в матрицу
			for (var i = sp; i < sp+ matrix.cell_count; i++) {
				matrix.setimagesize(i);
					if (typeof( matrix.events[i]) != 'undefined'){
						var value = matrix.events[i];

						//формирование html ToolTip
						var ttl = '<tr><td>Камера</td> <td>'+matrix.cameras[value[5]].text_left+'</td> </tr>';
						if (value[7] == 'image')
						{
							ttl +='<tr><td>Файл</td>   <td>'+value[2].slice((value[2].lastIndexOf('/')+1))+'</td> </tr>';
							ttl +='<tr><td>Размер</td> <td>'+ value[6]+' ['+value[4]+'x'+value[3]+']</td> </tr>';
							ttl +='<tr><td>Создан</td> <td>'+value[1]+'</td> </tr>';
						}
						else 
						{
							ttl += '<tr><td>Файл</td>   <td>'+value[2].slice((value[2].lastIndexOf('/')+1))+' ['+value[4]+'x'+value[3]+']</td> </tr>';
							ttl +='<tr><td>Размер</td> <td>'+value[6]+', '+ value[8]+'</td> </tr>';
							ttl +='<tr><td>Начало</td> <td>'+value[9]+'</td> </tr>';
							ttl +='<tr><td>Конец</td> <td>'+value[1]+'</td> </tr>';
						}

						var ResizedImgSrc = MediaUrlPref+ value[2];
						//Установка прямой ссылки на ресурс
						$('#cell_'+i+' a').attr({'href': ResizedImgSrc })
						if (value[7] == 'image') {

							//определяем размеры изображения
							height =  $('#cell_'+i+' .refBox').height();
							width = $('#cell_'+i+' .refBox').width();
							
							img_height = height;
							img_width = width;
							
							//если включен режим пропорций
							if( $('#proportion').attr('checked')=='checked' )
							{
								var img_height = value[3];
								var img_width = value[4];
								var origin_proportion = img_width/img_height;
			
								elem_proportion = width/height;
								
								if(origin_proportion > elem_proportion )
								{
									img_height = parseInt(width/origin_proportion);
									img_width = width;
								}
								else 
								{
									img_width = parseInt( height*origin_proportion);
									img_height = height;
								}
							}
							//формирование src ресайза картинки
							var ResizedImgSrc = matrix.getResizedImageSrc(i, img_height, img_width);
							
							$('#cell_'+i).find(".elem").attr('tooltip',ttl).end()
							.find('a.refBox').empty().addPlayer({'src': ResizedImgSrc, 'useImageSize':'true' })
							.aplayerResizeToParent();
						}
						else 
						{
							$('#cell_'+i).find(".elem").attr('tooltip',ttl).end()
							.find('a.refBox').empty().addPlayer({'src': ResizedImgSrc, 'logoPlay':'true' })
							.aplayerResizeToParent();
						}
				}
			}
		}
		else //Перезаполняем существующую матрицу
		{
			
			// все элементы матрицы есть в кеше, перезаполняем матрицу
			var loadimage = {};
			
			var cells = $('div [id ^=cell_]').each(function(i){
		
			if (typeof( matrix.events[i]) != 'undefined')
				{
					value = matrix.events[i];
					var cont = $(this).attr({'id': 'cell_'+(i)}).find('div.elem a').attr({reference: '#cell_'+(i) });
					if (value[7] == 'image') {
						if (typeof( value.image_chache) != 'undefined' && value.image_chache) loadimage[i] = true;
						else loadimage[i] = false;
					}
					//Не картинка //ad hoc
					else
					{
						loadimage[i] = true;
					}
				}
			});
			
			// проверяем какие изображения есть в кеше браузера, а какаие надо загрузить
			var ci = i + matrix.count_column;
			hide_over = true;
			for(i; i<=ci; i++) {
				if (typeof( matrix.events[i]) != 'undefined' && matrix.events[i][7] == 'image') {
					if (typeof( matrix.events[i].image_chache) != 'undefined' && matrix.events[i].image_chache) {
						loadimage[i] = true;
					} else {
						loadimage[i] = false;
						hide_over = false;
					}
				}
			}

			// загружаем изображения и меняем размеры
			$.each(loadimage, function(key, value) {
				if (value) {
					matrix.setimagesize(key);
				}else {
					matrix.loadsrc(key);
				}
			});

			
			$(cells).each(function(i){
				i=i+sp;
				if (typeof( matrix.events[i]) != 'undefined') {
					value = matrix.events[i];
					camera_class = gallery.cookie.get('camera_'+value[5]+'_color');
					if (camera_class != '') {
						camera_class = ' '+camera_class;
					}
				
					$(this).removeClass().attr({'id': 'cell_'+(i)})
					.addClass('content_item show'+ ' camera_'+value[5]+' '+ camera_class )
					.css({"display":display_mode});   
			
					var cont = $(this).find('div.elem>div.img_block>a.refBox');
					var NewSRC = MediaUrlPref+ matrix.events[i][2];

					//Установка прямой ссылки на ресурс
					$('#cell_'+(i)+' a').attr({'href': NewSRC })
					
					if (value[7] == 'image') 
					{
						//определяем размеры изображения
						height =  $('#cell_'+i+' .refBox').height();
						width = $('#cell_'+i+' .refBox').width();
						
						img_height = height;
						img_width = width;
						
						//если включен режим пропорций
						if( $('#proportion').attr('checked')=='checked' )
						{
							var img_height = value[3];
							var img_width = value[4];
							var origin_proportion = img_width/img_height;
		
							elem_proportion = width/height;
							
							if(origin_proportion > elem_proportion )
							{
								img_height = parseInt(width/origin_proportion);
								img_width = width;
							}
							else 
							{
								img_width = parseInt( height*origin_proportion);
								img_height = height;
							}
						}
						//формирование src ресайза картинки
						 NewSRC = matrix.getResizedImageSrc(i, img_height, img_width);

						 //если ранее в плеере была установлена картинка - меняем src						
						if($(cont).aplayerIsImage())
						{
							 matrix.setimagesize(i);
							//Загрузка изображения соответствующего размера
							$(cont).aplayerSetImgSrc(NewSRC);//.aplayerResizeContanerOnlyToParent().aplayerSetSrcSizes();
							
						}
						//если была не картинка - переустанавливаем плеер
						else
						{
							 matrix.setimagesize(i);
							$(cont).empty().addPlayer({'src': NewSRC, 'useImageSize':'true' }).aplayerResizeContanerOnlyToParent();	
						}
					}
					else
					{
						//Не картинка  - задаем новые размеры
						matrix.setimagesize(i);
						
						//пересоздаем плеер
						$(this).find('a.refBox').empty().addPlayer({'src': NewSRC, 'logoPlay':'true' }).aplayerResizeContanerOnlyToParent();	
					}

					//Получить расширение файла 
				 	var extension=value[2].match(reg);
				 	extension=extension[extension.length-1].slice(1);

					//формирование html ToolTip
					var ttl = '<tr><td>Камера</td> <td>'+matrix.cameras[value[5]].text_left+'</td> </tr>';
					if (value[7] == 'image')
					{
						ttl +='<tr><td>Файл</td>   <td>'+value[2].slice((value[2].lastIndexOf('/')+1))+'</td> </tr>';
						ttl +='<tr><td>Размер</td> <td>'+ value[6]+' ['+value[4]+'x'+value[3]+']</td> </tr>';
						ttl +='<tr><td>Создан</td> <td>'+value[1]+'</td> </tr>';
					}
					else 
					{
						ttl += '<tr><td>Файл</td>   <td>'+value[2].slice((value[2].lastIndexOf('/')+1))+' ['+value[4]+'x'+value[3]+']</td> </tr>';
						ttl +='<tr><td>Размер</td> <td>'+value[6]+', '+ value[8]+'</td> </tr>';
						ttl +='<tr><td>Начало</td> <td>'+value[9]+'</td> </tr>';
						ttl +='<tr><td>Конец</td> <td>'+value[1]+'</td> </tr>';
					}
					
				 	//Заполнение инфо-блока
				 	$(this)
				 	.find(".elem").attr({"tooltip":ttl}).end()		
					.find('.info_block')
					.empty()
					.html(function(){
						//формирование информационной строки
						var info_html = matrix.cameras[value[5]].text_left+'<br />'+value[7]+': '+extension;
						if (value[7] == 'image') info_html +=	 ' ('+ value[6]+') <br />';
						else info_html +=	 ' ('+ value[8]+') <br />';
						info_html +=value[9];
						info_html +='<br /></div></a>';
						return info_html;
					});
				}
				else
				{
					$(this).remove();
				}
			});
			
		}
			if (hide_over) {
				$('#matrix_load').hide();
			}
		}
		
		//корректировка позиционирования лого-плей
		$('.logoPlay').each(function(){
			$(this).removeAttr('style').css({'position':'relative', 'top': ($(this).parent().height()- $(this).height())/2 });
		});
		
		//Включаем тултип
		$(".elem").tooltip();
		if(matrix.num > sp+matrix.cell_count) matrix.num = sp;
		$('#cell_'+matrix.num).addClass('active');
		
		//Если включен режим детального просмотра - хайдим ячейки и выключаем тултип
		if(matrix.mode=="detail") {
			$(".elem").tooltipOff();
			$('#scroll_content .content_item').hide();
		}
	},
	
	// выполнения запроса новых событий
	get_events : function (sp) {
		// определяем тип событий и список камер
		var type = '', cameras = '';
		// проверяем закончился ли предыдущий запрос
		if (!matrix.send_query) {
			// устанавливаем флаг что запрос выполняеться
			matrix.send_query = true;
			var variable = [];
			var i = 0;
			$('input[name="type_event"]').each(function(){
				if ($(this).attr('checked')) {
					type += $(this).val()+',';
				}
			});
			$('input[name="cameras"]').each(function(){
				if ($(this).attr('checked')) {
					cameras += $(this).val()+',';
					variable[i] =  $(this).val();
					i++;
				}
			});

			// определяем с какой позиции загружать события
			var get_sp = sp>0?sp:sp=0; // sp не должно быть меньше нуля
			if (matrix.select_node == 'left' ) {
				if (sp - matrix.config.limit+ matrix.cell_count > 0) {
					get_sp = sp - matrix.config.limit+ matrix.cell_count;
				} else {
					get_sp = 0;
				}
			}
			
			// делаем запрос
			$.post(WwwPrefix+'/offline/gallery.php',
				{'method':'get_events', 
				'tree':matrix.tree, 
				'sp':get_sp, 
				'type': type, 
				'cameras': cameras}, 
				function(data) {
					
					var i = get_sp;
				// обновляем кеш
				$.each(data.events, function(key, value) {
					matrix.all_events[key] = value;
					matrix.events[i] = value;
					i++;
				});
				var loadimage = {};
				for (var i = sp; i < sp+ matrix.cell_count; i++) {
					if (typeof( matrix.events[i]) != 'undefined') 
					{
					value = matrix.events[i];
					
					if (value[7] == 'image') {

						if (typeof( value.image_chache) != 'undefined' && value.image_chache) {
							loadimage[i] = true;

						} else {
							loadimage[i] = false;
						}
					} 
					// ad hoc
					else loadimage[i] = true;
				};

				// проверяем какие изображения есть в кеше браузера, а какаие надо загрузить
				var ci = i + matrix.count_column;
				var hide_over = true;
				for(i; i<=ci; i++) {
					if (typeof( matrix.events[i]) != 'undefined' && matrix.events[i][7] == 'image') {
						if (typeof( matrix.events[i].image_chache) != 'undefined' && matrix.events[i].image_chache) {
							loadimage[i] = true;
						} else {
							loadimage[i] = false;
							hide_over = false;
						}
					}
				}
		}
		
		//если не режим ресайза
		if(!matrix.isResizeMode ){
			//обновляем матрицу
			matrix.update(sp);
		}

			//если режим детального просмотра - отображаем текущий элемент
			if(matrix.mode=='detail'){
					matrix.loaddetailsrc();
				 	scale2.updateposition(scale2.position);
			}
				// устанавливаем флаг, что запрос выполнился
				matrix.send_query = false;
				if (hide_over ) {
					$('#matrix_load').hide();
				}
			});
		}
	},
	
	//устанавливает статистику для текущего временого диапазона
	update_statistic : function(){
		var stat = '<span><strong>'+lang.count_files+'</strong>'+matrix.curent_tree_events[matrix.tree].count+'</span><br />\
		<span><strong>'+lang.size_files+'</strong>'+readableFileSize(matrix.curent_tree_events[matrix.tree].size)+'</span><br />\
		<span><strong>'+lang.date_from+'</strong>'+matrix.curent_tree_events[matrix.tree].from+'</span><br />\
		<span><strong>'+lang.date_to+'</strong>'+matrix.curent_tree_events[matrix.tree].to+'</span><br />';
		$('#statistics').html(stat);
	},
	
	
	
	// постройка матрицы временного диапазона
	build : function(){
	
		$('#matrix_load').show();	
		
		matrix.cur_count_item = 0;
		
		if (typeof( matrix.curent_tree_events[matrix.tree]) != 'undefined') {

			// обновляем статистику
			matrix.update_statistic();
			
			// записываем количество событий в данном временном диапазоне
			matrix.count_item = matrix.curent_tree_events[matrix.tree].count;
		}
		// критерии просмотра: тип, камеры
		var variable = [];
		var type = [];
		var i = 0;
		$('input[name="type_event"]').each(function(){
			if ($(this).attr('checked')) {
				type[i] = $(this).val();
				i++;
			}
		});
		var i = 0;
		$('input[name="cameras"]').each(function(){
			if ($(this).attr('checked')) {
				variable[i] =  $(this).val();
				i++;
			}
		});

		matrix.events = {};
		var count_events = 0;
		var all_count_events = 0;
		var me = [];
		// заполняем кеш матрицы элементами из общего кеша
		$.each(matrix.all_events, function( i,value) {
			if ($.inArray(value[7], type) != -1 && $.inArray(value[5], variable) != -1 && (matrix.tree == 'all' || matrix.tree == value[0].substr(0, matrix.tree.length))) {
				matrix.events[count_events] = value;
				me[count_events] = i;
				count_events++;
			}
			all_count_events++;
		});
		
		var dev = all_count_events - matrix.config.event_limit;
		if (dev > 0) {
			$.each(matrix.all_events, function( i,value) {
				if ($.inArray(i, me) == -1) {
					delete matrix.all_events[i];
					dev--;
					if (dev <= 0) {
						 return false;
					}
				}
			});
		}

		// если идет переход вверх по дереву, то показываем самый последние элементы в матрице нового диапазона
		if (matrix.select_node == 'left') {
			//отменяем показ последних - переходим в начало диапазона		
			sp=0;
			matrix.num = 0;
//			sp = scroll.position;
		} else {
			sp = 0;
		}

		
		
		if(count_events < matrix.cell_count && count_events < matrix.curent_tree_events[matrix.tree].count) {
			// если нет элементов, то выполняем запрос на сервер
			matrix.get_events(sp);
		} else {
			// если есть элементы, то обновляем матрицу
			matrix.update(sp);
		}
		
		//инициализируем элемент скрола
		scroll.init({
			height:matrix.height-82,
			cell_count:Math.ceil(matrix.count_item/matrix.count_column), 
			row_count: matrix.count_column, 
			matrix_count: Math.ceil(matrix.cell_count/matrix.count_column)
		});
		matrix.scroll = true;
	},
	
	//возвращает src ресайзенного изображения
	getResizedImageSrc : function(cell_num, height, width){
		//формируем строку src
		var ResizedImgSrc = '/lib/resize_img.php?url='+Protocol+WwwPrefix+MediaUrlPref+ matrix.events[cell_num][2];

		ResizedImgSrc += '&h='+height;
		ResizedImgSrc += '&w='+width;						
		ResizedImgSrc += ($('#proportion').attr('checked')=='checked')? '&prop=true' : '&prop=false';

		return ResizedImgSrc;
		}
	
};


// элемент скрол
var scroll = {
		id : '#scroll_v', // ид элемента скрола
		height : 100, // высота скрола
		cell_count : 100, // количество ячеек в скроле
		row_count : 10, // количество рядов
		matrix_count: 10, // размер матрицы
		position : 0, // текущая позиция в скроле
		min_height : 10, // минимальная высота ползунка
		
		init : function(config) {
			if (config && typeof(config) == 'object') {
			    $.extend(scroll, config);
			}
			if(MSIE){
                var wheight=(window.innerHeight)?window.innerHeight:((document.all)?document.body.offsetHeight:null);
				scroll.height = ietruebody().clientHeight-100-$('#toolbar').height()-
				$('.scroll_bot_v').height() - $('.scroll_top_v').height() - $('.scroll_polz_v_Top').height() - 25;
				
				//Установка изображений через img
				$('#scroll_v .scroll_polz_v_Top').html('<img src="./gallery/img/topScrolll.png" >');
				$('#scroll_v .scroll_polz_v_Bottom').html('<img src="./gallery/img/bottomScrolll.png" >');
				
				// задаем высоту скрола
				$(scroll.id + ' .scroll_body_v').height(scroll.height);
				// высчитываем высоту ползунка в зависимости от элементов в матрице и всех элементов в диапазоне
				h = Math.floor((scroll.height/(scroll.cell_count>0?scroll.cell_count:1))*scroll.matrix_count);
				
				if(h>scroll.height) h=scroll.height;
				
				scroll.polzh = 0;
				if ( h < scroll.min_height) {
					scroll.polzh = scroll.min_height - h;
					h = scroll.min_height;

				}
				// задаем параметры ползунка
				$(scroll.id + ' .scroll_polz_v').height(h);

			}else{
			
				
			// задаем высоту скрола
			$(scroll.id + ' .scroll_body_v').height(scroll.height);
			// высчитываем высоту ползунка в зависимости от элементов в матрице и всех элементов в диапазоне
			h = Math.floor((scroll.height/(scroll.cell_count>0?scroll.cell_count:1))*scroll.matrix_count);
		
			if(h>scroll.height) h=scroll.height-15;
			
			scroll.polzh = 0;
			if ( h < scroll.min_height) {
				scroll.polzh = scroll.min_height - h;
				h = scroll.min_height;
			}

			
			// задаем параметры ползунка
			$(scroll.id + ' .scroll_polz_v').height(h);
			
			}
			// обработка нажатия стрелки вверх на скроле
			$(scroll.id + ' .scroll_top_v').unbind('click');
			$(scroll.id + ' .scroll_top_v').click(function() {
				scroll.num_up();
			});
			// обработка нажатия стрелки вниз на скроле
			$(scroll.id + ' .scroll_bot_v').unbind('click');
			$(scroll.id + ' .scroll_bot_v').click(function() {
				scroll.num_down();
			});

			// обработка нажатия стрелки предыдущее
			$('#toolbar .prew').unbind('click');
			$('#toolbar .prew').click(function(e) {
				e.preventDefault();
				scroll.num_left();
				return false;
			});
			// обработка нажатия стрелки следующее
			$('#toolbar .next').unbind('click');
			$('#toolbar .next').click(function(e) {
				e.preventDefault();
				scroll.num_right();
				return false;
			});

			// обработка перемещения ползунка
			
			$('.scroll_polz_v_Top, .scroll_polz_v_Middle, .scroll_polz_v_Bottom', scroll.id)
			.mousedown(function(e){
				e.preventDefault();
			});
			
			scroll.mousemove = false;
			$(scroll.id + ' .scroll_polz_v').unbind('mousedown');
			$(scroll.id + ' .scroll_polz_v').mousedown(function(e){
				e.preventDefault();
				var start = e.pageY - $(this).offset().top;
				var start_top = $(this).offset().top - $(this).position().top;
				$(document).mousemove(function(e){
					scroll.mousemove = true;
					var top = e.pageY - start_top - start;
					if (top >= 0 && top <= $(scroll.id + ' .scroll_body_v').height()- $(scroll.id + ' .scroll_polz_v').height()) {
						$(scroll.id + ' .scroll_polz_v').css('top', top);
						var sp = Math.floor(top/((scroll.height-scroll.polzh)/scroll.cell_count)) * scroll.row_count;
						scroll.position = sp;
					}
				});
			});
			$(document).mouseup(function(e){
				if (scroll.mousemove) {
					$(document).unbind('mousemove');
					scroll.updateposition(scroll.position, true);
					scroll.mousemove = false;
					matrix.num = scroll.position;
					$('#cell_'+matrix.num).addClass('active');
				}
			});

			$("#win_bot").unbind('mousewheel');
			$("#win_bot").mousewheel(function(event, delta) {
		
				if (delta > 0) {
					scroll.num_up();
				} else {
					scroll.num_down();
				}
			});

			// обработка нажатия на область между ползунком и края скрола
			$(scroll.id + ' .scroll_body_v').unbind('mousedown');
			$(scroll.id + ' .scroll_body_v').mousedown(function(e){
				e.preventDefault();
				var y = e.pageY -$(this).offset().top;
				var sp = scroll.position;
				if (y < $(scroll.id + ' .scroll_polz_v').position().top) {
					sp = sp - scroll.matrix_count*scroll.row_count;
					if (sp < 0) {
						sp = 0;
					}
				} else if (y > $(scroll.id + ' .scroll_polz_v').position().top + $(scroll.id + ' .scroll_polz_v').height()){
					if (sp + scroll.matrix_count*scroll.row_count*2 >=  scroll.cell_count*scroll.row_count) {
						sp = scroll.cell_count*scroll.row_count - scroll.matrix_count*scroll.row_count;
					} else {
						sp = sp + scroll.matrix_count*scroll.row_count;
					}
				}
				scroll.updateposition(sp);
				scroll.setposition(sp);
				matrix.num = scroll.position;
					$('.active').removeClass('active');
					$('#cell_'+matrix.num).addClass('active');
			});

			scroll.position = 0;
			$(scroll.id).show();
		},

		// сдвиг влево
		num_left : function() {
			var new_num = matrix.num - 1;
			if (new_num >=0) {
				//если находимся в этом же диапазоне событий
				if (matrix.mode == 'preview') {
					if (!$('#cell_'+new_num).hasClass('show')){
						sp = Math.floor(new_num / scroll.row_count) * scroll.row_count;
						scroll.updateposition(sp);
						scroll.setposition(sp);
					}
					$('#cell_'+matrix.num).removeClass('active');
					$('#cell_'+new_num).addClass('active');
					matrix.num = new_num;
				} else if (matrix.mode == 'detail'){
				//сохраняем позицию изображения текущего елемента
				scale2.save_content_position(); 
				//востанавливаем матричные параметры активного элемента
				$(".content_item.active").attr('style', matrix.recover.cell_style).hide()
				.find('.elem').attr('style', matrix.recover.elem_style)
				.find('.refBox').attr('style', matrix.recover.refBox_style)
				.aplayerResizeToParent();
					if (!$('#cell_'+new_num).hasClass('show')){
						sp = Math.floor(new_num / scroll.row_count) * scroll.row_count;
						scroll.updateposition(sp);
						scroll.setposition(sp);
					}
					$('#cell_'+matrix.num).removeClass('active');
					$('#cell_'+new_num).addClass('active');
					matrix.num = new_num;
				
					//Установка размеров отображаемого элемента
					matrix.loaddetailsrc();
	 				scale2.updateposition(scale2.position);
	 				//востанавливаем позицию изображения
	 				scale2.restore_content_position();
	 				
	 				//если флоуплеер
					$.aplayer.fplayer_ready_delegate = function(){
						scale2.setposition(scale2.position);
						//востанавливаем позицию изображения
						scale2.restore_content_position();
					};
	 				
			 	}
			} else {
				// если вышли за пределы переходим на предыдущий если пользователь согласился
				if (matrix.curent_tree_events[matrix.tree].prev) {
					var checknextwindow = gallery.cookie.get('checknextwindow');
					if (checknextwindow == 'yes') {
						prev = matrix.curent_tree_events[matrix.tree].prev;
						new_num = matrix.curent_tree_events[prev].count - 1;
						sp = Math.floor(new_num / scroll.row_count) * scroll.row_count;
						matrix.num = new_num;
						scroll.position = sp;
						matrix.select_node = 'left';
						$.jstree._focused().deselect_node("#tree_"+matrix.tree);
						$("#tree_"+prev).jstree("set_focus");
						$.jstree._focused().select_node("#tree_"+prev);
						scroll.setposition(sp);
					} else if (checknextwindow != 'no'){
						gallery.nextwindow.open('left');
					}
				}
			}
		},
		// смещаемся на ряд вверх
		num_up : function() {
			var new_num = matrix.num - scroll.row_count;
			if (new_num >=0) {
				//если находимся в этом же диапазоне событий
				if (matrix.mode == 'preview') {
					if (!$('#cell_'+new_num).hasClass('show')){
						sp = Math.floor(new_num / scroll.row_count) * scroll.row_count;
						scroll.updateposition(sp);
						scroll.setposition(sp);
					}
					$('#cell_'+matrix.num).removeClass('active');
					$('#cell_'+new_num).addClass('active');
					matrix.num = new_num;
				} else if (matrix.mode == 'detail'){
				//сохраняем позицию изображения текущего елемента
				scale2.save_content_position(); 
				//востанавливаем матричные параметры активного элемента
				$(".content_item.active").attr('style', matrix.recover.cell_style).hide()
				.find('.elem').attr('style', matrix.recover.elem_style)
				.find('.refBox').attr('style', matrix.recover.refBox_style)
				.aplayerResizeToParent();

				if (!$('#cell_'+new_num).hasClass('show')){
						sp = Math.floor(new_num / scroll.row_count) * scroll.row_count;
						scroll.updateposition(sp);
						scroll.setposition(sp);
					}
					$('#cell_'+matrix.num).removeClass('active');
					$('#cell_'+new_num).addClass('active');
					matrix.num = new_num;

					//Установка размеров отображаемого элемента
					matrix.loaddetailsrc();
				 	scale2.updateposition(scale2.position);
				 	//востанавливаем позицию изображения
	 				scale2.restore_content_position();
	 				//если флоуплеер
					$.aplayer.fplayer_ready_delegate = function(){
						scale2.setposition(scale2.position);
						//востанавливаем позицию изображения
						scale2.restore_content_position();
					};
				 				
				}
			} else {
				// если вышли за пределы переходим на предыдущий если пользователь согласился
				if (matrix.curent_tree_events[matrix.tree].prev) {
					var checknextwindow = gallery.cookie.get('checknextwindow');
					if (checknextwindow == 'yes') {
						prev = matrix.curent_tree_events[matrix.tree].prev;
						new_num = matrix.curent_tree_events[prev].count - 1;
						sp = Math.floor(new_num / scroll.row_count) * scroll.row_count;
						matrix.num = new_num;
						scroll.position = sp;
						matrix.select_node = 'left';
						$.jstree._focused().deselect_node("#tree_"+matrix.tree);
						$("#tree_"+prev).jstree("set_focus");
						$.jstree._focused().select_node("#tree_"+prev);
						scroll.setposition(sp);
					} else if (checknextwindow != 'no'){
						gallery.nextwindow.open('left');
					}
				}
			}
		},
		// смещаемся вправо
		num_right : function() {
			var new_num = matrix.num + 1;
			if (new_num < scroll.cell_count*scroll.row_count && new_num < matrix.curent_tree_events[matrix.tree].count) {
				if (matrix.mode == 'preview') {
					if (!$('#cell_'+new_num).hasClass('show')){
						sp = Math.floor(new_num / scroll.row_count) * scroll.row_count;
						scroll.updateposition(sp);
						scroll.setposition(sp);
					}
					$('#cell_'+matrix.num).removeClass('active');
					$('#cell_'+new_num).addClass('active');
					matrix.num = new_num;
				} else if (matrix.mode == 'detail'){
				//сохраняем позицию изображения текущего елемента
				scale2.save_content_position(); 
				//востанавливаем матричные параметры активного элемента
				$(".content_item.active").attr('style', matrix.recover.cell_style).hide()
				.find('.elem').attr('style', matrix.recover.elem_style)
				.find('.refBox').attr('style', matrix.recover.refBox_style)
				.aplayerResizeToParent();
				
				if (!$('#cell_'+new_num).hasClass('show')){
						sp = Math.floor(new_num / scroll.row_count) * scroll.row_count;
						scroll.updateposition(sp);
						scroll.setposition(sp);
					}
					$('#cell_'+matrix.num).removeClass('active');
					$('#cell_'+new_num).addClass('active');
					matrix.num = new_num;

					//Установка размеров отображаемого элемента
					matrix.loaddetailsrc();
				 	scale2.updateposition(scale2.position);
				 	//востанавливаем позицию изображения
	 				scale2.restore_content_position();
					//если флоуплеер
					$.aplayer.fplayer_ready_delegate = function(){
					 	scale2.updateposition(scale2.position);
					 	//востанавливаем позицию изображения
		 				scale2.restore_content_position();
					};

				}

			}else {
				if (matrix.curent_tree_events[matrix.tree].next) {
					var checknextwindow = gallery.cookie.get('checknextwindow');
					if (checknextwindow == 'yes') {
						next = matrix.curent_tree_events[matrix.tree].next;
						matrix.num = 0;
						matrix.select_node = 'right';
						$.jstree._focused().deselect_node("#tree_"+matrix.tree);
						$("#tree_"+next).jstree("set_focus");
						$.jstree._focused().select_node("#tree_"+next);
					} else if (checknextwindow != 'no'){
						gallery.nextwindow.open('right');
					}
				}
			}
		},
		// смещаемся на ряд ниже
		num_down : function() {
			var new_num = matrix.num + scroll.row_count;
			if (new_num < scroll.cell_count*scroll.row_count && new_num < matrix.curent_tree_events[matrix.tree].count) {
				if (matrix.mode == 'preview') {
					if (!$('#cell_'+new_num).hasClass('show')){
						sp = Math.floor(new_num / scroll.row_count) * scroll.row_count;
						scroll.updateposition(sp);
						scroll.setposition(sp);
					}
					$('#cell_'+matrix.num).removeClass('active');
					$('#cell_'+new_num).addClass('active');
					matrix.num = new_num;
				} else if (matrix.mode == 'detail'){
				//сохраняем позицию изображения текущего елемента
				scale2.save_content_position(); 
				//востанавливаем матричные параметры активного элемента
				$(".content_item.active").attr('style', matrix.recover.cell_style).hide()
				.find('.elem').attr('style', matrix.recover.elem_style)
				.find('.refBox').attr('style', matrix.recover.refBox_style)
				.aplayerResizeToParent();

				//переходим на новую активную ячейку
				if (!$('#cell_'+new_num).hasClass('show')){
					sp = Math.floor(new_num / scroll.row_count) * scroll.row_count;
					scroll.updateposition(sp);
					scroll.setposition(sp);
				}
				$('#cell_'+matrix.num).removeClass('active');
				$('#cell_'+new_num).addClass('active');
				matrix.num = new_num;
				
				//Установка размеров отображаемого элемента
				matrix.loaddetailsrc();
			 	scale2.updateposition(scale2.position);
			 	//востанавливаем позицию изображения
 				scale2.restore_content_position();
 				//если флоуплеер
				$.aplayer.fplayer_ready_delegate = function(){
					scale2.setposition(scale2.position);
					//востанавливаем позицию изображения
					scale2.restore_content_position();
				};
				}
			}else {
				if (matrix.curent_tree_events[matrix.tree].next) {
					var checknextwindow = gallery.cookie.get('checknextwindow');
					if (checknextwindow == 'yes') {
						next = matrix.curent_tree_events[matrix.tree].next;
						matrix.num = 0;
						matrix.select_node = 'right';
						$.jstree._focused().deselect_node("#tree_"+matrix.tree);
						$("#tree_"+next).jstree("set_focus");
						$.jstree._focused().select_node("#tree_"+next);
					} else if (checknextwindow != 'no'){
						gallery.nextwindow.open('right');
					}
				}
			}
		},
		// обновляем позицию скрола и перестраиваем матрицу
		updateposition : function(sp, force) {
			if (scroll.position != sp || force == true) {
				scroll.position = sp;
				matrix.update(sp);
			}
		},
		// обновляем позицию скрола и ползунка
		setposition : function(sp) {
			scroll.position = sp;
			var t = Math.floor(sp/scroll.row_count*(scroll.height-scroll.polzh)/scroll.cell_count);
			//проверяем, чтобы ползунок не перекрывал нижнюю стрелку скрола
			if(t > $(scroll.id + ' .scroll_body_v').height()- $(scroll.id + ' .scroll_polz_v').height() ){
				t=$(scroll.id + ' .scroll_body_v').height()- $(scroll.id + ' .scroll_polz_v').height();
			}
			$(scroll.id + ' .scroll_polz_v').css({top:t});
		}
};
// элемент масштаба предварительного просмотра
var scale = {
	id : '#scale', // ид элемента
	width: 205, // ширина
	min : 0, // минимальное значение
	max : 20, // максимальное значение
	position : 0, // текущая позиция
	// уменьшение масштаба
	click_min : function() {
		var sp = scale.position - 1;
		if (sp < 0) {
			sp = 0;
		}
		scale.setposition(sp);
	},
	// увеличение масштаба
	click_max : function() {
		var sp = scale.position + 1;
		if (sp > scale.max) {
			sp = scale.max;
		}
		scale.setposition(sp);
	},
	// обновление текущей позиции ползунка
	setposition : function(sp) {
		var t = scale.width/scale.max*sp;
		$(scale.id + ' .scale_polz').css({left:t});
		scale.updateposition(sp);
	},
	// обновление текущей позиции масштаба и обновление элементов матрицы
	updateposition : function(sp) {
		scale.position = sp;
		gallery.cookie.set('scale', sp);
		matrix.cell_width = matrix.config.min_cell_width + Math.floor((matrix.config.max_cell_width - matrix.config.min_cell_width)*sp/scale.max);
		matrix.cell_height = matrix.config.min_cell_height + Math.floor((matrix.config.max_cell_height - matrix.config.min_cell_height)*sp/scale.max);

		matrix.resize();
	},
	// обновление элемента масштаба
	reload : function(width) {
		var sp = Math.floor(scale.position * width/matrix.config.max_cell_width);
		if (sp > scale.max) {
			sp=scale.max;
		}
		var t = Math.floor(scale.width/scale.max*sp);
		$(scale.id + ' .scale_polz').css({left:t});
		scale.position = sp;
	},

	init : function() {
		var self = this;
		// обработка нажатия уменьшения масштаба
		$(scale.id + ' .scale_min').unbind('click');
		$(scale.id + ' .scale_min').click(function() {
			scale.click_min();
		});
		// обработка нажатия увеличение масштаба
		$(scale.id + ' .scale_max').unbind('click');
		$(scale.id + ' .scale_max').click(function() {
			scale.click_max();
		});
		// обработка перемещения ползунка
		$(scale.id + ' .scale_polz').unbind('mousedown');
		$(scale.id + ' .scale_polz').mousedown(function(e){
			e.preventDefault();
			var start = e.pageX - $(this).offset().left;
			var start_left = $(this).offset().left - $(this).position().left;
			$(document).mousemove(function(e){
				var left = e.pageX - start_left - start;
				if (left >= 0 && left <= $(scale.id + ' .scale_body').width()- $(scale.id + ' .scale_polz').width()) {
					$(scale.id + ' .scale_polz').css('left', left);
					var sp = Math.floor(scale.max/scale.width * left);
					if (sp != scale.position) {
						scale.updateposition(sp);
					}
				}
			});
		});
		$(document).mouseup(function(e){
			$(document).unbind('mousemove');
		});

		if (gallery.cookie.get('scale')) {
			scale.setposition(gallery.cookie.get('scale'));
		}

		// обработка нажатия на область между ползунком и края
		$(self.id + ' .scale_body').unbind('click');
		$(self.id + ' .scale_body').click(function(e){
			e.preventDefault();
			var y = e.pageX -$(this).offset().left;
			if (y < $(self.id + ' .scale_polz').position().left) {
				self.click_min();
			} else if (y > $(self.id + ' .scale_polz').position().left + $(self.id + ' .scale_polz').width()){
				self.click_max();
			}
		});
	}

};

// элемент масштаба детального просмотра
var scale2 = {
		id : '#scale2',
		width: 205,
		min : 0,
		max : 20,
		position : 0,
		content_position : { left:'0px', top:'0px' }, //позиция изображения в детальном режиме
		
		click_min : function() {
			var self = this;
			var sp = self.position - 1;
			if (sp < 0) {
				sp = 0;
			}
			self.setposition(sp);
		},
		click_max : function() {
			var self = this;
			var sp = self.position + 1;
			if (sp > self.max) {
				sp = self.max;
			}
			self.setposition(sp);
		},
		setposition : function(sp) {
			var self = this;
			var t = self.width/self.max*sp;
			$(self.id + ' .scale_polz').css({left:t});
			self.updateposition(sp);
		},

		updateposition : function(sp) {
			
			var self = this;
			self.position = sp;
			var value = matrix.events[matrix.num];
			var ref_box = $('.active .refBox');
			
			gallery.cookie.set('scale2', sp);

			if(matrix.mode=='detail') $('#scale2').show();

			if(value==null) return; //при инициализации

			//Изменение положения ползунка масштаба 	
			if(value[7]=='image') //Если картинка
			{
				//определяем размеры изображения
				var height =  $(".active .refBox").height();
				var width = $(".active .refBox").width();
				height += (height*2 - height)*scale2.position/scale2.max ;
				width += (width*2 - width)*scale2.position/scale2.max ;

				var img_height = height;
				var img_width = width;
				
				//если включен режим пропорций
				if( $('#proportion').attr('checked')=='checked' ){
					var img_height = matrix.events[matrix.num][3];
					var img_width = matrix.events[matrix.num][4];
					var origin_proportion = img_width/img_height;

					elem_proportion = width/height;
					
					if(origin_proportion > elem_proportion ){
						img_height = parseInt( width/origin_proportion);
						img_width = width;
					} else {
						img_width = parseInt(height*origin_proportion);
						img_height = height;
					}
				}
				
				//формирование src ресайза картинки
				var ResizedImgSrc = matrix.getResizedImageSrc(matrix.num, img_height, img_width);

				//Загрузка изображения соответствующего размера
				$('.active .refBox')
				.aplayerSetImgSrc(ResizedImgSrc)
				.aplayerResizeContanerOnlyToParent()
				.aplayerSetSizeMediaElt({'height': img_height, 'width': img_width});
				
				//визуализируем скролл масштаба режима просмотра
				$('#scale2').show();
				//показываем чекбокс пропорций
				$('div.propotion').show();
			}
			else if( $('.active .refBox').aplayerIsEmbededObject() || value[7]=='audio' ) //Если внедренный объект или  аудио
			{
				//Скрываем елемент управления масштабом
				$('#scale2').hide();
				//установка размеров плеера в соответствии с размерами родительского элемента
				$('.active .refBox').aplayerResizeToParent();

				//скрываем скролл масштаба режима просмотра
				$('#scale2').hide();
				//скрываем чекбокс пропорций
				$('div.propotion').hide();
			}
			else // HTML5-player + flowplayer
			{
				var is_fp = $(ref_box).aplayerIsFlowPlayer();
				// размер матрицы
				var width = matrix.width;
				var height = matrix.height;
				
				//поправка высоты на панель контролов и флоуплейер
				var _dh = $("div[id^=controlPanel_]", ref_box).height() || 0;
				if(is_fp){
					_dh +=10;
				}
				//расчет поправки по ширине
				var _dw = parseInt( _dh/(value[3]/value[4]));
				
				// максимальный размер увеличения
				var wm = width*2;
				var hm = height*2;
				if ($('#proportion').attr('checked') || value[7]=='video') {
					// если выбран режим сохранять пропорции
					if (value[3] < matrix.height-_dh && value[4] < matrix.width) {
						// если изображение влазит в окно просмотра, то используем оригинальные размеры
						width = value[4];
						height = value[3];
						wm = width*2;
						hm = height*2;
					} else {
						// если не влазит то используем ширину матрицы а высоту в впропорциях изменяем
						var w = matrix.width+_dw;
						var h = Math.floor(value[3]*w/value[4]);

						// если высота не влазит, то используем высоту матрицы, а ширину подгоняем в пропорциях
						if (h > matrix.height-_dh) {
							h = matrix.height-_dh;
							w = Math.floor(value[4]*h/value[3]);
						}
						width = w;
						height = h;
						wm = w*2;
						hm = h*2;
					}
				}

				if(!is_fp){
					sp+=3; //Несколько увеличиваем размер медиаэлемента 
				}
				//Изменение размеров медиа-элемента плеера 
				$(ref_box).aplayerSetSizeMediaElt({
					'width':  parseInt(width) + Math.floor((wm - width)*sp/self.max),
					'height': parseInt(height) + Math.floor((hm - height)*sp/self.max)
				} );
				//установка размеров плеера в соответствии с размерами родительского элемента
				$(ref_box).aplayerResizeContanerOnlyToParent(); 
				
				//визуализируем скролл масштаба режима просмотра
				$('#scale2').show();
				//скрываем чекбокс пропорций
				$('div.propotion').hide();
			}
			$('.active').show();

		},
		
		
		reload : function() {
			var self = this;
			self.updateposition(self.position);
		},
		init : function() {
			var self = this;
			$(self.id + ' .scale_min').unbind('click');
			$(self.id + ' .scale_min').click(function() {
				self.click_min();
			});
			$(self.id + ' .scale_max').unbind('click');
			$(self.id + ' .scale_max').click(function() {
				self.click_max();
			});

			$(self.id + ' .scale_polz').unbind('mousedown');
			$(self.id + ' .scale_polz').mousedown(function(e){
				e.preventDefault();
				var start = e.pageX - $(this).offset().left;
				var start_left = $(this).offset().left - $(this).position().left;
				$(document).mousemove(function(e){
					var left = e.pageX - start_left - start;
					if (left >= 0 && left <= $(self.id + ' .scale_body').width()- $(self.id + ' .scale_polz').width()) {
						$(self.id + ' .scale_polz').css('left', left);
						var sp = Math.floor(self.max/self.width * left);

						if (sp != self.position) {
							self.updateposition(sp);
						}
					}
				});

			});
			$(document).mouseup(function(e){
				$(document).unbind('mousemove');
			});

			if (gallery.cookie.get('scale2')) {
				self.setposition(gallery.cookie.get('scale2'));
			}

			// обработка нажатия на область между ползунком и края
			$(self.id + ' .scale_body').unbind('click');
			$(self.id + ' .scale_body').click(function(e){
				e.preventDefault();
				var y = e.pageX -$(this).offset().left;
				if (y < $(self.id + ' .scale_polz').position().left) {
					self.click_min();
				} else if (y > $(self.id + ' .scale_polz').position().left + $(self.id + ' .scale_polz').width()){
					self.click_max();
				}
			});
		},
		
		//сохранить текущую позицию элемента
		save_content_position : function(){
			var value = matrix.events[matrix.num];
			//реализуем только для картинок
			if(value[7]=='image') //Если картинка
			{
				var aplayer_id = $('.aplayer' , '#cell_'+matrix.num).attr('id');
				scale2.content_position = $.aplayer.getCurrentMediaEltPosition(aplayer_id);
			}
			else if( $('.active .refBox').aplayerIsEmbededObject() || value[7]=='audio' ) //Если внедренный объект или  аудио
			{}
			else // HTML5-player
			{}
		},
		
		//установить позицию элемента
		restore_content_position : function(){
			var value = matrix.events[matrix.num];
			//реализуем только для картинок
			if(value[7]=='image') //Если картинка
			{
				var aplayer_id = $('.aplayer' , '#cell_'+matrix.num).attr('id');
				$.aplayer.setMediaEltPosition(aplayer_id , scale2.content_position);
			}
			else if( $('.active .refBox').aplayerIsEmbededObject() || value[7]=='audio' ) //Если внедренный объект или  аудио
			{}
			else // HTML5-player
			{}
		}
		
		
	};
	
var keyBoard = {
	boxesEnum : {},
	keys : {
		i : 73,
		a : 65,
		v : 86,
		p : 80,
		s : 83,
		minus : 189,
		plus : 107,
		minus2: 109,
		plus2: 61,
		tab : 9,
		enter : 13,
		space : 32,
		backspace : 8,
		left : 37,
		up : 38,
		right : 39,
		down : 40,
		pageUp : 33,
		pageDown : 34,
		esc : 27,
		home: 36,
		end: 35
	},

	views : {
		matrix: 0,
		detail: 1,
		colorDialog: 2,
		chooseDialog: 3
	},
	/*selectedBox: 3,// 0 - Зона дерева событий. 1 - Зона списка камер. 2 - диалог. 3 - диалог 2*/
	view : 0,
	beforeView : 0,
	currentBoxSelector : [],
	currentSelector : null,
	currentSelectorChild : 0,
	colorSelector : null,
	colorSelectorNumber : 0,
	selectColor : function(next) {
		$(keyBoard.colorSelector[keyBoard.colorSelectorNumber]).removeClass('selectColor');
		keyBoard.colorSelectorNumber = next;
		if(keyBoard.colorSelectorNumber>=keyBoard.colorSelector.length) {
			keyBoard.selectColor(keyBoard.colorSelectorNumber-keyBoard.colorSelector.length);
			return;
		}
		if(keyBoard.colorSelectorNumber<=-1) {
			//keyBoard.colorSelectorNumber = keyBoard.colorSelector.length-1;
			keyBoard.selectColor(keyBoard.colorSelectorNumber+keyBoard.colorSelector.length);
			return;
		}
		$(keyBoard.colorSelector[keyBoard.colorSelectorNumber]).addClass('selectColor');
	},
	getColor : function() {
		return $(keyBoard.colorSelector[keyBoard.colorSelectorNumber]);
	},
	selectBox : function(){
		keyBoard.selectElem();
		for(var i=0,ilen=keyBoard.currentBoxSelector.length; i<ilen;i++){
			keyBoard.currentBoxSelector[i].removeClass('selectBox');
		}
		keyBoard.currentBoxSelector = [];
		for(var elem=0,elemlen=arguments.length; elem<elemlen; elem++)
			keyBoard.currentBoxSelector.push(arguments[elem]);
		for(var j=0,jlen=keyBoard.currentBoxSelector.length; j<jlen;j++){
			keyBoard.currentBoxSelector[j].addClass('selectBox');
		}
	},
	selectElem : function(next){
		$(keyBoard.currentSelector[keyBoard.currentSelectorChild]).removeClass('selectElement');
		keyBoard.currentSelectorChild = next;
		if(keyBoard.currentSelectorChild>=keyBoard.currentSelector.length)
			keyBoard.currentSelectorChild = 0;
		if(keyBoard.currentSelectorChild<=-1)
			keyBoard.currentSelectorChild = keyBoard.currentSelector.length-1;
		$(keyBoard.currentSelector[keyBoard.currentSelectorChild]).addClass('selectElement');
	},
	getCam : function (){
		return $(keyBoard.currentSelector[keyBoard.currentSelectorChild]);
	},
	checkSelecBox: function () {
	

		
		if(MSIE){
//			$('#win_top').addClass('selectBox');
			
			if(keyBoard.boxesEnum.current()==keyBoard.boxesEnum.INSIDE) {
				//keyBoard.selectBox($('#scroll_content'));
				keyBoard.selectBox($('#win_bot'));
				$('#win_top').height(gallery.hcameras);
				if ($('#win_top').height() > 100) {
					$('#more_cam').show();
				}
			}
			else if(keyBoard.boxesEnum.current()==keyBoard.boxesEnum.TREE) {
				keyBoard.selectBox($('#tree'));

				$('#win_top').height(gallery.hcameras);
				if ($('#win_top').height() > 100) {
					$('#more_cam').show();
				}
			} 
			else if(keyBoard.boxesEnum.current()==keyBoard.boxesEnum.CAMS) {
				//keyBoard.selectBox($('#cameras_selector'));
				keyBoard.selectBox($('#win_top'));
				keyBoard.selectElem(0);

				$('#more_cam').hide();
				$('#win_top').height('auto');
			}
			return;
		}
		
		if(keyBoard.boxesEnum.current()==keyBoard.boxesEnum.INSIDE) {
			//keyBoard.selectBox($('#scroll_content'));
			keyBoard.selectBox($('#win_bot'));
			$('#win_top').height(gallery.hcameras);
			if ($('#win_top').height() > 100) {
				$('#more_cam').show();
			}
		} else if(keyBoard.boxesEnum.current()==keyBoard.boxesEnum.TREE) {
			keyBoard.selectBox($('#tree'));

			$('#win_top').height(gallery.hcameras);
			if ($('#win_top').height() > 100) {
				$('#more_cam').show();
			}
		} else if(keyBoard.boxesEnum.current()==keyBoard.boxesEnum.CAMS) {
			//keyBoard.selectBox($('#cameras_selector'));
			keyBoard.selectBox($('#win_top'));
			keyBoard.selectElem(0);

			$('#more_cam').hide();
			$('#win_top').height('auto');
		}
	},
	init : function() {
		keyBoard.currentSelector = $('#cameras_selector .options').children();
		keyBoard.colorSelector = $('#cameras_color ul').children('li');
		keyBoard.boxesEnum = new Enum('INSIDE','TREE','CAMS');

		keyBoard.chooseDialogTab = new Enum('check','yes','no');

		keyBoard.selectBox($('#scroll_content'));

		$('#list_panel').click(function(){
			keyBoard.boxesEnum.set(keyBoard.boxesEnum.INSIDE);
			keyBoard.checkSelecBox();
		});
		$('#win_top').click(function(){
			keyBoard.boxesEnum.set(keyBoard.boxesEnum.CAMS);
			keyBoard.checkSelecBox();
		});
		$('#tree').click(function(){
			keyBoard.boxesEnum.set(keyBoard.boxesEnum.TREE);
			keyBoard.checkSelecBox();
		});

		// обработка нажатий клавиатуры
		//$(document).unbind('keydown');
		$(document).keydown(function (e) {
			e.preventDefault();

			// work any where
			if(e.which == keyBoard.keys.tab){
				if(keyBoard.view!==keyBoard.views.colorDialog && keyBoard.view!==keyBoard.views.chooseDialog) {
					keyBoard.boxesEnum.next();
					//'INSIDE','TREE','CAMS'
					keyBoard.checkSelecBox();
				}
			} else if(e.which == keyBoard.keys.i){ //i
				$('#image_type').attr('checked', !$('#image_type').attr('checked'));
				var r1 = gallery.reload_events();
				if(!r1) {
					$('#image_type').attr('checked', 'checked');
				}

				if ($('#image_type').attr('checked')) {
					$('#image_type').parent().attr('style','background-position: 0px -14px');
				} else {
					$('#image_type').parent().attr('style','background-position: 0px -0px');
				}

			} else if(e.which == keyBoard.keys.a){ //a
				$('#audio_type').attr('checked', !$('#audio_type').attr('checked'));
				var r3 = gallery.reload_events();
				if(!r3) {
					$('#audio_type').attr('checked', 'checked');
				}

				if ($('#audio_type').attr('checked')) {
					$('#audio_type').parent().attr('style','background-position: 0px -14px');
				} else {
					$('#audio_type').parent().attr('style','background-position: 0px -0px');
				}

			} else if(e.which == keyBoard.keys.v) { //v
				$('#video_type').attr('checked', !$('#video_type').attr('checked'));
				var r2 = gallery.reload_events();
				if(!r2) {
					$('#video_type').attr('checked', 'checked');
				}
				if ($('#video_type').attr('checked')) {
					$('#video_type').parent().attr('style','background-position: 0px -14px');
				} else {
					$('#video_type').parent().attr('style','background-position: 0px -0px');
				}
			} else if(e.which == keyBoard.keys.p) {
				$('#proportion').attr('checked', !$('#proportion').attr('checked'));
				matrix.doProportion();

				if ($('#proportion').attr('checked')) {
					$('#proportion').parent().attr('style','background-position: 0px -14px');
				} else {
					$('#proportion').parent().attr('style','background-position: 0px -0px');
				}

			} else if(e.which == keyBoard.keys.s) {
				$('#info').attr('checked', !$('#info').attr('checked'));
				matrix.doShowInfo();

				if ($('#info').attr('checked')) {
					$('#info').parent().attr('style','background-position: 0px -14px');
				} else {
					$('#info').parent().attr('style','background-position: 0px -0px');
				}
			}


			//work in views
			if(keyBoard.view==keyBoard.views.detail) { // DETAIL
				
				if(e.which == keyBoard.keys.minus || e.which == keyBoard.keys.minus2) {
					scale2.click_min();
				} else if(e.which == keyBoard.keys.plus || e.which == keyBoard.keys.plus2) {
					scale2.click_max();
				}
				if(keyBoard.boxesEnum.current()==keyBoard.boxesEnum.INSIDE) {
					if(e.which == keyBoard.keys.space) {
						scroll.num_right();
					} else if(e.which == keyBoard.keys.backspace) {
						scroll.num_left();
					} else if(e.which == keyBoard.keys.enter) {
						matrix.preview();
					} else if(e.which == keyBoard.keys.down) {
						//DETAIL: Нажатие стрелки вниз
						scroll.num_down();
					} else if(e.which == keyBoard.keys.up) {
						//DETAIL: Нажатие стрелки вверх	
						scroll.num_up();
					} else if(e.which == keyBoard.keys.right) {
						//DETAIL: Нажатие стрелки вправо
						scroll.num_right();
					} else if(e.which == keyBoard.keys.left) {
						//DETAIL: Нажатие стрелки влево
						scroll.num_left();
					}
				}

			} else if(keyBoard.view==keyBoard.views.matrix) { //MATRIX
				if(e.which == keyBoard.keys.minus || e.which == keyBoard.keys.minus2) {
					scale.click_min();
				} else if(e.which == keyBoard.keys.plus || e.which == keyBoard.keys.plus2) {
					scale.click_max();
				}

				
				if(keyBoard.boxesEnum.current()==keyBoard.boxesEnum.INSIDE) {
					if (e.which == keyBoard.keys.left) { 
						//PREVIEW: стрелка влево
						scroll.num_left();
					} else if (e.which == keyBoard.keys.home) {
						$('#cell_'+matrix.num).removeClass('active');
						matrix.num = scroll.position;
						$('#cell_'+matrix.num).addClass('active');
					} else if (e.which == keyBoard.keys.end) {

						$('#cell_'+matrix.num).removeClass('active');
						matrix.num = scroll.position+(scroll.matrix_count-1)*scroll.row_count;
						$('#cell_'+matrix.num).addClass('active');

					} else if (e.which == keyBoard.keys.up) { 
						//PREVIEW: стрелка вверх
						scroll.num_up();
					} else if (e.which == keyBoard.keys.right) {
					//PREVIEW: стрелка вправо
						scroll.num_right();
					} else if (e.which == keyBoard.keys.down) {
					//PREVIEW: стрелка вниз
						scroll.num_down();
					} else if (e.which == keyBoard.keys.pageUp) {
						var sp = scroll.position;
						sp = sp - scroll.matrix_count*scroll.row_count;
						if (sp < 0) {
							sp = 0;
						}
						matrix.num = matrix.num - scroll.matrix_count*scroll.row_count;
						if (matrix.num < 0) {
							// если вышли за пределы переходим на предыдущий если пользователь согласился
							if (matrix.curent_tree_events[matrix.tree].prev) {
								var checknextwindow = gallery.cookie.get('checknextwindow');
								if (checknextwindow == 'yes') {
									prev = matrix.curent_tree_events[matrix.tree].prev;
									new_num = matrix.curent_tree_events[prev].count - 1;
									sp = Math.floor(new_num / scroll.row_count) * scroll.row_count;
									matrix.num = new_num;
									scroll.position = sp;
									matrix.select_node = 'left';
									$.jstree._focused().deselect_node("#tree_"+matrix.tree);
									$("#tree_"+prev).jstree("set_focus");
									$.jstree._focused().select_node("#tree_"+prev);
									scroll.setposition(sp);
								} else if (checknextwindow != 'no'){
									gallery.nextwindow.open('left');
								}
							}
						} else {
							scroll.updateposition(sp);
							scroll.setposition(sp);
						}
					} else if (e.which == keyBoard.keys.pageDown) {
						var sp = scroll.position;
						if (sp + scroll.matrix_count*scroll.row_count*2 >  scroll.cell_count*scroll.row_count) {
							if (matrix.curent_tree_events[matrix.tree].next) {
								var checknextwindow = gallery.cookie.get('checknextwindow');
								if (checknextwindow == 'yes') {
									next = matrix.curent_tree_events[matrix.tree].next;
									matrix.num = 0;
									matrix.select_node = 'right';
									$.jstree._focused().deselect_node("#tree_"+matrix.tree);
									$("#tree_"+next).jstree("set_focus");
									$.jstree._focused().select_node("#tree_"+next);
								} else if (checknextwindow != 'no'){
									gallery.nextwindow.open('right');
								}
								return false;
							}
							sp = scroll.cell_count*scroll.row_count - scroll.matrix_count*scroll.row_count;
							matrix.num =scroll.cell_count*scroll.row_count - scroll.matrix_count*scroll.row_count;
						} else {
							sp =  sp + scroll.matrix_count*scroll.row_count;
							matrix.num = matrix.num + scroll.matrix_count*scroll.row_count;
						}
						scroll.updateposition(sp);
						scroll.setposition(sp);
					} else if(e.which == keyBoard.keys.enter) {
						matrix.detail();
					}
				}
			} else if(keyBoard.view==keyBoard.views.colorDialog) {
				if (e.which == keyBoard.keys.esc) {
					gallery.cameras_color.close();
				} else if (e.which == keyBoard.keys.left) {
					keyBoard.selectColor(keyBoard.colorSelectorNumber-1);
				} else if (e.which == keyBoard.keys.up) {
					keyBoard.selectColor(keyBoard.colorSelectorNumber-4);
				} else if (e.which == keyBoard.keys.right) {
					keyBoard.selectColor(keyBoard.colorSelectorNumber+1);
				} else if (e.which == keyBoard.keys.down) {
					keyBoard.selectColor(keyBoard.colorSelectorNumber+4);
				} else if (e.which == keyBoard.keys.enter) {
					keyBoard.getColor().removeClass('selectColor');
					gallery.cameras_color.camera_collor = keyBoard.getColor().attr('class');
					gallery.cameras_color.select();
					gallery.cameras_color.close();
					return;
				}
			} else if(keyBoard.view==keyBoard.views.chooseDialog) {
				if (e.which == keyBoard.keys.esc) {
					gallery.nextwindow.close();
				} else if (e.which == keyBoard.keys.enter) {
					if (keyBoard.chooseDialogTab.current() == keyBoard.chooseDialogTab.yes) {
						gallery.nextwindow.select();
					}
					gallery.nextwindow.close();
				} else if (e.which == keyBoard.keys.space) {
					$('#checknextwindow').attr('checked', !$('#checknextwindow').attr('checked'));
					if ($('#checknextwindow').attr('checked')) {
						$('#checknextwindow').parent().attr('style','background-position: 0px -14px');
					} else {
						$('#checknextwindow').parent().attr('style','background-position: 0px -0px');
					}
				} else if (e.which == keyBoard.keys.tab){
					$('#checknextwindow').parent().removeClass('select');
					$('#nextwindow .yes').removeClass('select');
					$('#nextwindow .no').removeClass('select');

					keyBoard.boxesEnum.current()==keyBoard.boxesEnum.INSIDE
					keyBoard.chooseDialogTab.next();
					if(keyBoard.chooseDialogTab.current() == keyBoard.chooseDialogTab.check) {
						$('#checknextwindow').parent().addClass('select');
					} else if(keyBoard.chooseDialogTab.current() == keyBoard.chooseDialogTab.yes) {
						$('#nextwindow .yes').addClass('select');
					} else if(keyBoard.chooseDialogTab.current() == keyBoard.chooseDialogTab.no) {
						$('#nextwindow .no').addClass('select');

					}
				}
			}
			if (keyBoard.view == 0) {
				if(keyBoard.boxesEnum.current()==keyBoard.boxesEnum.CAMS) {
					if (e.which == keyBoard.keys.left) {
						keyBoard.selectElem(keyBoard.currentSelectorChild-1);
					} else if (e.which == keyBoard.keys.up) {
						keyBoard.selectElem(keyBoard.currentSelectorChild-1);
					} else if (e.which == keyBoard.keys.right) {
						keyBoard.selectElem(keyBoard.currentSelectorChild+1);
					} else if (e.which == keyBoard.keys.down) {
						keyBoard.selectElem(keyBoard.currentSelectorChild+1);
					} else if (e.which == keyBoard.keys.space) {
						var camId = keyBoard.getCam().find('input').attr('id');
						$('#'+camId).attr('checked', !$('#'+camId).attr('checked'));
						var rez = gallery.reload_cams();
						if(!rez)
							$('#'+camId).attr('checked', 'checked');

						if ($('#'+camId).attr('checked')) {
							$('#'+camId).parent().attr('style','background-position: 0px -14px');
						} else {
							$('#'+camId).parent().attr('style','background-position: 0px -0px');
						}
					} else if (e.which == keyBoard.keys.enter) {
						gallery.cameras_color.camera_id = keyBoard.getCam().find('label').children('a').attr('href').replace('#','');
						gallery.cameras_color.camera_title = keyBoard.getCam().find('label').children('a').html();
						gallery.cameras_color.camera_link = keyBoard.getCam().find('label').children('a');
						gallery.cameras_color.open();
					} else if (e.which == keyBoard.keys.esc) {
						keyBoard.boxesEnum.set(keyBoard.boxesEnum.INSIDE);
						keyBoard.checkSelecBox();
					}
				} else if(keyBoard.boxesEnum.current()==keyBoard.boxesEnum.TREE) {
					if (e.which == keyBoard.keys.left) {
						var top = matrix.curent_tree_events[matrix.keyBoardTree].top;

						if(top!=false && typeof(top)!='undefined') {
							$.jstree._focused().deselect_node('#tree_'+matrix.keyBoardTree);

							$.jstree._focused().select_node('#tree_'+top);
							$.jstree._focused().toggle_node('#tree_'+top);
							$('#tree').scrollTo( $('#tree_'+top));
						}
					} else if (e.which == keyBoard.keys.home) {
						var top = matrix.curent_tree_events[matrix.keyBoardTree].top;
						if(top!=false && typeof(top)!='undefined') {
							var under = matrix.curent_tree_events[top].under;
							if(under!=false && typeof(under)!='undefined') {
								$.jstree._focused().deselect_node('#tree_'+matrix.keyBoardTree);
								$.jstree._focused().select_node('#tree_'+under);
								$('#tree').scrollTo( $('#tree_'+under));
							}
						}
					} else if (e.which == keyBoard.keys.end) {
						var top = matrix.curent_tree_events[matrix.keyBoardTree].top;
						if(top!=false && typeof(top)!='undefined') {
							$.jstree._focused().deselect_node('#tree_'+matrix.keyBoardTree);
							$.jstree._focused().select_node('#tree_'+top+' > ul > .jstree-last');
							$('#tree').scrollTo( $('#tree_'+top+' > ul > .jstree-last'));
						}
					} else if (e.which == keyBoard.keys.up) {
						var prev = matrix.curent_tree_events[matrix.keyBoardTree].prev;
						if(prev!=false && typeof(prev)!='undefined' &&  matrix.curent_tree_events[matrix.keyBoardTree].top ==  matrix.curent_tree_events[prev].top ) {
							$.jstree._focused().deselect_node('#tree_'+matrix.keyBoardTree);
							$.jstree._focused().select_node('#tree_'+prev);
							$('#tree').scrollTo( $('#tree_'+prev));
						}
					} else if (e.which == keyBoard.keys.right) {
						var under = matrix.curent_tree_events[matrix.keyBoardTree].under;
						if(under!=false && typeof(under)!='undefined') {
							$.jstree._focused().deselect_node('#tree_'+matrix.keyBoardTree);
							$.jstree._focused().select_node('#tree_'+under);
							$('#tree').scrollTo( $('#tree_'+under));
						}
					} else if (e.which == keyBoard.keys.down) {
						var next = matrix.curent_tree_events[matrix.keyBoardTree].next;
						if(next!=false && typeof(next)!='undefined' &&  matrix.curent_tree_events[matrix.keyBoardTree].top ==  matrix.curent_tree_events[next].top ) {
							$.jstree._focused().deselect_node('#tree_'+matrix.keyBoardTree);
							$.jstree._focused().select_node('#tree_'+next);
							$('#tree').scrollTo( $('#tree_'+next));

						}
					} else if (e.which == keyBoard.keys.enter) {
						var tree = matrix.keyBoardTree;
						if (matrix.tree != tree) {
							matrix.tree = tree;
							matrix.keyBoardTree = tree;
							matrix.build();
						}
						// если режим детального просмотра, обновляем картинку
						if (matrix.mode == 'detail') {
							matrix.preview();
						}

					} else if (e.which == keyBoard.keys.space) {
						if($.jstree._focused().is_open()) {
							$.jstree._focused().close_node('#tree_'+matrix.keyBoardTree);
						} else {
							$.jstree._focused().open_node('#tree_'+matrix.keyBoardTree);
						}
					}
				}
			}

		});
	}
};
