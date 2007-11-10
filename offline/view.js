// document.write('[ ' + window.screenX + ',' + window.screenY + ' ' + window.innerWidth + ',' + window.innerHeight + ' ]');

var g_camname;
var g_fname;
var g_fsize;
var g_fdate1;
var g_fdate2;

function obj_loaded(e) {
   var PlayTimer = window.parent.frames['query'].PlayTimer;
   var play_direction = window.parent.frames['query'].play_direction;
   var play_tio_sel = ie?
                 window.parent.frames['query'].document.all['play_tio']:
                 window.parent.frames['query'].document.getElementById('play_tio');
   var play_tio = parseFloat(play_tio_sel.options[play_tio_sel.selectedIndex].text) * 1000;
   if ( PlayTimer == null && play_direction != 0 ) {
     if (play_direction < 0 )
        window.parent.frames['query'].PlayTimer = setTimeout("window.parent.frames['query'].do_play(-1)",
       play_tio);
     else 
        window.parent.frames['query'].PlayTimer = setTimeout("window.parent.frames['query'].do_play(1)",
       play_tio);
   }
  
   if (e!=null)
   {  
        if (e.tagName == 'IMG')
       { 
          hint = '<table cellspacing="0" border="0" cellpadding="1"><tbody><tr>\n' +
 '<td align="right">Камера:<\/td>\n' +
 '<td>'+g_camname+'<\/td>\n' +
 '<\/tr><tr>\n' +
 '<\/tr><tr>\n' +
 '<td align="right">Файл:<\/td>\n' +
 '<td>'+g_fname+'<\/td>\n' +
 '<\/tr><tr>\n' +
 '<td align="right">Размер:<\/td>\n';
 hint += '<td>'+g_fsize+'<\/td><\/tr><tr>\n' +
 '<td align="right">Создан:<\/td>\n' +
 '<td>'+g_fdate1+'<\/td>\n' +
  '<\/tr><\/tbody><\/table>\n';

       } else if (e.tagName == 'EMBED') {
          hint = '<table cellspacing="0" border="0" cellpadding="1"><tbody><tr>\n' +
 '<td align="right">Камера:<\/td>\n' +
 '<td>'+g_camname+'<\/td>\n' +
 '<\/tr><tr>\n' +
  '<td align="right">Файл:<\/td>\n' +
 '<td>'+g_fname+'<\/td>\n' +
 '<\/tr><tr>\n' +
 '<td align="right">Размер:<\/td>\n' +
 '<td>'+g_fsize+'<\/td>\n' +
 '<\/tr><tr>\n' +
 '<td align="right">Начат:<\/td><td>'+g_fdate1+'<\/td><\/tr>\n' +
'<tr><td align="right">Закрыт:<\/td><td>'+g_fdate2+'<\/td>\n' +
 '<\/tr><\/tbody><\/table>\n';
       }
   }
}


function show_obj(cam_nr, evt_id, utime1, utime2, ser_nr, fsize, frames, s16_1, s16_2, ftype_str, fduration, fname) {
   var cdiv = ie?
                 document.all['content']:
                 document.getElementById('content');
   var cams = ie?
                 window.parent.frames['query'].document.all['cams[]']:
                 window.parent.frames['query'].document.getElementById('cams[]');

   var embed_chkbox = ie?
                window.parent.frames['query'].document.all['embed_video']:
                window.parent.frames['query'].document.getElementById('embed_video');
   
   if (embed_chkbox == null )
      var embed_video = false;
   else    
      var embed_video = embed_chkbox.checked;

      
   var Date1 = new Date(utime1 * 1000);
   var Date2 = new Date(utime2 * 1000);
   g_fdate1 = Date1.toLocaleString();
   g_fdate2 = Date2.toLocaleString();
   g_fname = fname.substr(fname.lastIndexOf('/')+1);
   g_fsize = fsize;
   
   var link = location.protocol + '//' + location.host + encodeURI(fname);

   g_camname = cams.options[cam_nr-1].text;
   
   var icon_48x52 ='';
   var duration_info='';
   if (evt_id==23) {
      icon_48x52 = WwwPrefix+'/img/mpeg4.gif';
      duration_info='<tr><td align="right">Продолжительность<\/td><td>'+fduration+'<\/td><\/tr>\n';
   }
 
   hint=null;
   if ( evt_id == 23 ) 
   {
      g_fname += '  [ ' + s16_1 + 'x' + s16_2 + ' ] ';
      g_fsize += ', ' + fduration + ', ' + frames + ' кадров';
      
      clear_innerHTML(cdiv);
      var link_target='';
      if (ie && !embed_video)
         link_target='target="_blank"';
      if (embed_video) {
           var scale = ie?
                 window.parent.frames['query'].document.all['scale']:
                 window.parent.frames['query'].document.getElementById('scale');
           var scale_factor = scale.options[scale.selectedIndex].text;
           var scaletext='';
           if (scale_factor.length > 0)
              var scaletext = ' width="'+ scale_factor + '%" height="'+ scale_factor + '%"';

           var embed = ie?document.all['embed']:document.getElementById('embed');
           if (embed==null) {
              cdiv.innerHTML = '<embed id="embed" src="'+link+'" ' + scaletext + '  onload="obj_loaded(this);">'+
  '<NOEMBED>NOEMBED<\/NOEMBED>';
             // alert(cdiv.innerHTML);
           } else {
             if (scale_factor.length > 0) {
                  embed.src='';
                  embed.width = parseInt(cdiv.clientWidth*(scale_factor/100));
                  embed.height = parseInt(cdiv.clientHeight*(scale_factor/100));
                  // alert(jpeg.width + ' x ' + jpeg.height);
             } else {
                 if (embed.getAttribute('width')) {
                    embed.src='';
                    embed.removeAttribute('width');
                    embed.removeAttribute('height');
                 }
             }
             embed.src=link;
           }
    } else {
        cdiv.innerHTML = '<div align="center">\n' +
 '<br /><br />\n' + 
 '<a href="'+link+'" '+link_target+'>\n' + 
 '<img src="'+ icon_48x52 +'" width="48" height="52" border="0">' +
 '<\/a>\n' +
 '<table class="help" cellspacing="0" border="1" cellpadding="2"><tbody><tr>\n' +
 '<td align="right">Камера<\/td>\n' +
 '<td>'+g_camname+'<\/td>\n' +
 '<\/tr><tr>\n' +
 '<td align="right">Файл<\/td>\n' +
 '<td>'+g_fname+'<\/td>\n' +
 '<\/tr><tr>\n' +
 '<td align="right">Размер<\/td>\n' +
 '<td>'+g_fsize+'<\/td>\n' +
 '<\/tr><tr>\n' +
'<td align="right">Начат:<\/td><td>'+g_fdate1+'<\/td><\/tr>\n' +
'<tr><td align="right">Закрыт:<\/td><td>'+g_fdate2+'<\/td>\n' +
 '<\/tr><\/tbody><\/table>\n' + 
 '<br /><a href="'+link+'" '+link_target+'>зазгрузить файл ( ' + g_fsize +
 ' )<br />и открыть в медиа проигрывателе &gt;&gt;<\/a>\n' +
 '<\/div>';
        obj_loaded(null);
     }
   } else {
     g_fsize += '  [ ' + s16_1 + 'x' + s16_2 + ' ] ' ;
     var scale = ie?
                 window.parent.frames['query'].document.all['scale']:
                 window.parent.frames['query'].document.getElementById('scale');
     var scale_factor = scale.options[scale.selectedIndex].text;
     var scaletext='';
     if (scale_factor.length > 0)
       var scaletext = ' width="'+ scale_factor + '%" height="'+ scale_factor + '%"';
    
     var jpeg = ie?document.all['jpeg']:document.getElementById('jpeg');
     if ( jpeg == null ) {
          cdiv.innerHTML = '<img id="jpeg" src="'+link+'" border="0" ' + scaletext +
          ' onload="obj_loaded(this);">';
          jpeg = ie?document.all['jpeg']:document.getElementById('jpeg');
     } else {
        if (scale_factor.length > 0) {
          jpeg.src='';
          jpeg.width = parseInt(cdiv.clientWidth*(scale_factor/100));
          jpeg.height = parseInt(cdiv.clientHeight*(scale_factor/100));
          // alert(jpeg.width + ' x ' + jpeg.height);
        } else {
          if (jpeg.getAttribute('width')) {
            jpeg.src='';
            jpeg.removeAttribute('width');
            jpeg.removeAttribute('height');
          }
        }
        jpeg.src=link;
     }
   }
   
   return true;
}
