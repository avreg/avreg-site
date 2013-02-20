/***
 * Работа с пользовательскими раскладками (храняться на клиенте)
 */

$(function(){
	user_layouts.init();
});


 var user_layouts = {

	//Локальное хранилище
	local_storage : false,
	//Название переменной хранилища
	storage_name : "CLIENT_LAYOUTS",
	//Клиентские раскладки
	client_layouts : undefined,
	//Клиентские раскладки в формате json
	client_layouts_json : false,
	
 	//инициализация
 	init : function(){
 		var self = this;
 		if(!this.isLocalStorageAvailable()){
 			return;
 //			alert("Ошибка сохранения пользовательской раскладки.\nЛокальное храниилище недоступно.");
 		}else{
 			self.local_storage = window['localStorage'];
 		}
 		
 		self.client_layouts_json = self.local_storage[self.storage_name];
 		
 		if(self.client_layouts_json!==undefined){
 			try{
 				self.client_layouts = JSON.parse(self.client_layouts_json);
 			}catch(e){
 				self.local_storage.removeItem(self.storage_name);
 				var error_text = 'Ошибка загрузки пользовательских раскладок\n\n'+ e.message;
 				alert(error_text);
 			}
 		}
 	},
 	

 	//перейти на страницу редактирования
 	to_tune_mode : function(layout_nr, redirect_url){
 		var self = this;
 		//переадресуем на страницу редактирования
 		self.redirect(redirect_url, true);
 		return false;
 	},
 	
 	//сохранить отредактированую клиентскую раскладку
 	tune_save : function(layout_nr, redirect_url){
 		var self = this;
	 	//данные раскладки
 		var layout_data = {};
 		
 		//Валидация заполнения формы
 		if(!validate()) return false;
		//Сохранить?
 		confirm("Сохранить изменения пользовательской раскладки № "+layout_nr+" ?");
 		
 		//номер раскладки
 		//layout_data.MON_NR = parseInt($('input[name=mon_nr]').attr('value'));
 		var MON_NR = parseInt(layout_nr);
 		
 		//название
 		layout_data.n = $('input[name=mon_name]').attr('value');
 		//тип
 		layout_data.t = $('input[name=mon_type]').attr('value');
 		//по умолчанию?
 		layout_data.d = false; //TODO correct the setting
 		//выводить названия камер? 		
 		layout_data.cn = $('input[name=PrintCamNames]').prop('checked');
 		//сохранять пропорции ?
 		layout_data.p = $('#AspectRatio option:selected').attr('value');
 		//таймаут реконнекта
 		layout_data.rt = $('#ReconnectTimeout option:selected').attr('value');
 		
 		//дата
 		var dt = new Date();
 		var m = dt.getMonth()+1;
 		if(m.toString().length==1)m='0'+m;
 		var d = dt.getDate();
 		if(d.toString().length==1)d='0'+d;
 		var h = dt.getHours();
 		if(h.toString().length==1)h='0'+h;
		var min = dt.getMinutes();
 		if(min.toString().length==1)min='0'+min;
 		var s = dt.getSeconds();
 		if(s.toString().length==1)s='0'+s;
 		
 		layout_data.dd = dt.getFullYear()+'-'+m+'-'+d+' '+h+':'+min+':'+s;
		//логин пользователя
 		layout_data.u = this.client_layouts[MON_NR].u;
 		//собираем данные раскладки
 		var wins_data = {};
		$('.layout').each(function(i, val){
			var id = $(val).attr('id');
 			var win_nr = parseInt(id.replace('win_',''));
			//номер камеры в ячейке
			var cam_nr = $('.mon_wins option:selected',val).attr('value');
			//тип источника
			var mon_type = $('.mon_wins_type option:selected',val).attr('value');
			//Записываем только ячейки с установлеными камерами
			if(mon_type ==undefined) return;
 			var data = [cam_nr,mon_type];
 			wins_data[win_nr] = data; 
 		});
 		layout_data.w = JSON.stringify(wins_data);
 		
		if(this.client_layouts==undefined){
			this.client_layouts=new Object();
		}

 		this.client_layouts[MON_NR] = layout_data;
 		
 		var json_str = JSON.stringify(this.client_layouts);
 		self.client_layouts_json = json_str;
 		
 		self.local_storage.setItem(self.storage_name, json_str );
 		
 		//переадресуем 
 		self.redirect(redirect_url, true);
 		return false;
 	},

 	//Удалить пользовательскую расскладку
 	remove : function(layout_nr, redirect_url){
		var self = this;

		confirm("Удалить пользовательскую раскладку № "+layout_nr+" ?");

		try{
		//Удаляем раскладку
		delete this.client_layouts[layout_nr];
		}catch(e){alert('Not delete layouts');}
		//Если нет ни одной пользовательской раскладки - удаляем переменную локального хранилища
		var objlen = 0;
		for(var key in this.client_layouts){
			objlen++;
		}
		if(objlen==0){
			self.local_storage.removeItem(self.storage_name);
			self.client_layouts_json = undefined;
            var json = undefined;
            this.setCookie('layouts', json_str, '', '/', window.location.hostname, '');
		}else{
	 		var json_str = JSON.stringify(this.client_layouts);
	 		self.client_layouts_json = json_str;
	 		self.local_storage.setItem(self.storage_name, json_str );
            this.setCookie('layouts', json_str, '', '/', window.location.hostname, '');
		}

		//переадресуем на онлайн просмотр
 		self.redirect(redirect_url, true);

 		return false;
 	},



 	//Добавить новую пользовательскую расскладку
 	add_new : function(){
		var self = this;
	 	//данные раскладки
 		var layout_data = {};

 		//Валидация заполнения формы
 		if(!validate()) return false;

 		//номер раскладки
 		//layout_data.MON_NR = parseInt($('input[name=mon_nr]').attr('value'));
 		var MON_NR = parseInt($('input[name=mon_nr]').attr('value'));

 		//название
 		layout_data.n = $('input[name=mon_name]').attr('value');
 		//тип
 		layout_data.t = $('input[name=mon_type]').attr('value');
 		//по умолчанию?
 		layout_data.d = false; //TODO correct the setting
 		//выводить названия камер?
 		layout_data.cn = $('input[name=PrintCamNames]').prop('checked');
 		//сохранять пропорции ?
 		layout_data.p = $('#AspectRatio option:selected').attr('value');
 		//таймаут реконнекта
 		layout_data.rt = $('#ReconnectTimeout option:selected').attr('value');
		//дата
 		var dt = new Date();
 		var m = dt.getMonth()+1;
 		if(m.toString().length==1)m='0'+m;
 		var d = dt.getDate();
 		if(d.toString().length==1)d='0'+d;
 		var h = dt.getHours();
 		if(h.toString().length==1)h='0'+h;
		var min = dt.getMinutes();
 		if(min.toString().length==1)min='0'+min;
 		var s = dt.getSeconds();
 		if(s.toString().length==1)s='0'+s;

 		layout_data.dd = dt.getFullYear()+'-'+m+'-'+d+' '+h+':'+min+':'+s;
		//логин пользователя
 		layout_data.u = user_login;

 		//собираем данные раскладки
 		var wins_data = {};
		$('.layout').each(function(i, val){
			var id = $(val).attr('id');
 			var win_nr = parseInt(id.replace('win_',''));
			//номер камеры в ячейке
			var cam_nr = $('.mon_wins option:selected',val).attr('value');
			//тип источника
			var mon_type = $('.mon_wins_type option:selected',val).attr('value');
			//Записываем только ячейки с установлеными камерами
			if(mon_type ==undefined) return;
 			var data = [cam_nr,mon_type];
 			wins_data[win_nr] = data;
 		});
 		layout_data.w = JSON.stringify(wins_data);

		if(this.client_layouts==undefined){
			this.client_layouts=new Object();
		}
 		this.client_layouts[MON_NR] = layout_data;

 		var json_str = JSON.stringify(this.client_layouts);
 		self.client_layouts_json = json_str;
        this.setCookie('layouts', json_str, '', '/', window.location.hostname, '');
 		self.local_storage.setItem(self.storage_name, json_str );

 		//переадресуем на онлайн просмотр
 		self.redirect(url_domen+WwwPrefix+'/online/view.php', true);

 		return false;
 	},

 	//перенаправить на страницу
 	//layouts:bool - передавать раскладки?
 	redirect : function(url, layouts){
 		layouts = layouts || false;
		//Передаем параметры пользовательских раскладок
		if(layouts && user_layouts.isLocalStorageAvailable() && user_layouts.client_layouts_json){
			if(url.indexOf('?')==-1){
            this.setCookie('layouts', JSON.stringify(user_layouts.client_layouts), '', '/', window.location.hostname, '');
         }else{
            this.setCookie('layouts', JSON.stringify(user_layouts.client_layouts), '', '/', window.location.hostname, '');
         }
		}
		top.document.location.href = url;
 	},

    setUserLayoutsDefault: function (number_of_layouts)
    {
        // Получаю все пользовательские раскладки
        //var user_layouts = JSON.parse(this.getCookie('layouts'));
        var user_lay = user_layouts.client_layouts;
        //console.log('клиентские раскладки строка : '+user_layouts.client_layouts);
        //console.log('клиентские расклдки json : '+user_layouts.client_layouts_json);
        //console.log('клиентские расекладки localstoraGE: ' + user_layouts.local_storage);
        user_layouts.local_storage.removeItem(self.storage_name);
        // Выбираю дефолтную по номеру раскладки в JSON объекте
        var json_layouts = '';
        for (var next_layouts in user_lay)
        {
            user_lay[next_layouts]['d'] = (next_layouts == number_of_layouts)?'true':'false';
        }
        json_layouts = JSON.stringify(user_lay);
        user_layouts.local_storage.setItem(user_layouts.storage_name, json_layouts);
        //console.log('клиентские раскладки local storAGE: ' + user_layouts.local_storage);
        user_layouts.client_layouts = user_lay;
        user_layouts.client_layouts_json = json_layouts;
        //console.log('КЛИЕНТСКИЕ РАСКЛАДКИ СТРОКА : ' + user_layouts.client_layouts);
        //console.log('клиентские расекладки localstoraGE: ' + user_layouts.local_storage);
        // Сохраняю новое значение в cookie
        this.setCookie('layouts', JSON.stringify(user_lay), '', '/', window.location.hostname, '');
    },


     // Фунции для работы с cookies javascript
     /**
      * Устанавливает значение cookie name в value
      * @param name - Имя cookie
      * @param value - Значение cookie
      * @param expires
      * @param path - Относительный путь, к которому относится cookie
      * @domain - домен
      * @secure
      */
    setCookie : function(name, value, expires, path, domain, secure)
     {
        var str = name + '=' + encodeURIComponent(value);

        if (expires) str += '; expires=' + expires.toGMTString();
        if (path)    str += '; path=' + path;
        if (domain)  str += '; domain=' + domain;
        if (secure)  str += '; secure';
        document.cookie = str;
    },

     /**
      * Получение значения cookie name
      * @param name - Имя cookie, которое необходимо получить
      * @return string - Значение cookie
      */
    getCookie: function(name)
    {
        //Если передано пустое значение cookie, возвращаю пустую строку
        if(name == '')
        {
            return this.client_layouts;
        }

        var endstr = document.cookie.indexOf(";", name)
        if (endstr == -1)
            endstr = document.cookie.length;
        start = document.cookie.indexOf(name);
        var str = document.cookie.substring(start + name.length + 1, endstr);
        alert(str);
        return decodeURIComponent(str);
    },

     /**
      * Очищение Значение cookie name
      * @param name - Имя cookie, которое необходимо очистить
      */
    clearCookie: function(name)
    {
        document.cookie = name + '=null';
    },

 	//проверяет доступность локального хранилища
 	isLocalStorageAvailable : function(){
	    try {
	        return window['localStorage'] !== null;
	    } catch (e) {
	        return false;
	    }
	}
 	
 	
 };
