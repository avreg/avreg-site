var img_cursor=-1;
var hint=null;


function mark_row(theRowNum)
{
// _cam_nr, _evt_id, _utime1, _utime2, _ser_nr, _fsize, _frames, _s16_1, _s16_2, _ftype_str, _fduration, _fname
    if (theRowNum<0)
      return false;

    var link = document.links[theRowNum];
    var img = document.images[theRowNum];

    if (link==null || img==null)
       return false;

    if (theRowNum == img_cursor)
       return;
    var tmp = img.name;
    var img_info = tmp.split('~');
    var cam_nr = parseInt(img_info[0]);
    var evt_id = parseInt(img_info[1]);
    var utime1 = parseInt(img_info[2]);
    var utime2 = parseInt(img_info[3]);
    var ser_nr = parseInt(img_info[4]);
    var fsize = img_info[5];
    var frames = parseInt(img_info[6]);
    var s16_1 = parseInt(img_info[7]);
    var s16_2 = parseInt(img_info[8]);
    var ftype_str = img_info[9]
    var fduration = img_info[10]
    var fname = img_info[11];

    if ( img_cursor >= 0) 
    {
       ftype = document.images[img_cursor].id;
      if ( ftype == 23 )
           document.images[img_cursor].src = WwwPrefix+'/img/movie.gif';
      else if ( ftype == 12 )
          document.images[img_cursor].src = WwwPrefix+'/img/movie.gif';
      else if ( ftype == 32 )
           document.images[img_cursor].src = WwwPrefix+'/img/audio-off.gif';
      else if (ftype >= 15 || ftype <= 21)
         document.images[img_cursor].src = WwwPrefix+'/img/camera.gif';
      else 
          document.images[img_cursor].src = WwwPrefix+'/img/unknown.gif';
    }

    ftype = document.images[theRowNum].id;
    if ( ftype == 23 )
           document.images[theRowNum].src = WwwPrefix+'/img/movie.on.gif';
    else if ( ftype == 12 )
        document.images[theRowNum].src = WwwPrefix+'/img/movie.on.gif';
    else if ( ftype == 32 )
           document.images[theRowNum].src = WwwPrefix+'/img/audio.gif';
    else if (ftype >= 15 || ftype <= 21)
         document.images[theRowNum].src = WwwPrefix+'/img/camera-red.gif';
    else 
          document.images[theRowNum].src = WwwPrefix+'/img/unknown.gif';

    img_cursor = theRowNum;
    window.parent.frames['view'].show_obj(cam_nr, evt_id, utime1, utime2, ser_nr, fsize, frames, s16_1, s16_2, ftype_str, fduration, fname);
}

function first_img ()
{
    var first_link = document.links[0];
    if (!first_link) 
      return;
    mark_row(0);
}

function on_body_load ()
{
   if ( window.parent.frames['query'].CAM_NAMES )
      first_img();
}


function mk_obj_hint(cam_nr, evt_id, utime1, utime2, ser_nr, fsize, frames, s16_1, s16_2, ftype_str, fduration, fname)
{
   var Date1 = new Date(utime1 * 1000);
   var Date2 = new Date(utime2 * 1000);
   
   var fn = fname.substr(fname.lastIndexOf('/')+1);
   var link = 'http://' + location.host;
   if  (location.port !="" )
      link +=':'+location.port;
   link += fname;
 
   var camname = window.parent.frames['query'].CAM_NAMES[cam_nr];
  
   var dt_info='';
   var fs =  fsize;

   if (evt_id == 23) 
   {
      fn += ' [ ' + s16_1 + 'x' + s16_2 + ' ] ';
      icon_48x52 = WwwPrefix+'/img/mpeg4.gif';
      fs += ', ' + fduration + ', ' + frames + ' кадров';
      dt_info='<tr><td align="right">Начиная с:<\/td><td>'+Date2.toLocaleString()+'<\/td><\/tr><tr><td align="right">по:<\/td><td>'+Date1.toLocaleString()+'<\/td><\/tr>\n';
  } else if ( evt_id == 32 ) {
      fn += ' [ ' + s16_1 + ' канал ]';
      icon_48x52 = WwwPrefix+'/img/audio.gif';
      fs += ', битрейт ' + frames/1000 + 'kbps'; // + ', cэмплрейт ' + s16_2;
      dt_info='<tr><td align="right">Начиная с:<\/td><td>'+Date2.toLocaleString()+'<\/td><\/tr><tr><td align="right">по:<\/td><td>'+Date1.toLocaleString()+'<\/td><\/tr>\n';     
  } else if ( evt_id >= 15 || evt_id <= 21 ) {
      /* jpeg */
      fs += '  [ ' + s16_1 + 'x' + s16_2 + ' ] ' ;
      dt_info='<tr><td align="right">Создан:<\/td><td>'+Date1.toLocaleString()+'<\/td><\/tr>\n';
   } else {
      alert('unnown evt_id '+evt_id);
   }

   hint = '<table cellspacing="0" border="0" cellpadding="1"><tbody><tr>\n' +
 '<td align="right">Камера:<\/td>\n' +
 '<td>'+camname+'<\/td>\n' +
 '<\/tr><tr>\n' +
 '<td align="right">Файл:<\/td>\n' +
 '<td>'+fn+'<\/td>\n' +
 '<\/tr><tr>\n' +
 '<td align="right">Размер:<\/td>\n' +
 '<td>'+fs+'<\/td>\n' +
 '<\/tr><tr>\n' +
 dt_info +
 '<\/tbody><\/table>\n';
   
  return true;
}

function mouse_img(theRowNum) {
    if (typeof theRowNum == 'undefined') {
      hideddrivetip();
      return;
    }
    if (theRowNum<0)
      return false;

    var link = document.links[theRowNum];
    var img = document.images[theRowNum];
    
    if (link==null || img==null)
       return false;
  
    var tmp = img.name;
    var img_info = tmp.split('~');
    var cam_nr = parseInt(img_info[0]);
    var evt_id = parseInt(img_info[1]);
    var utime1 = parseInt(img_info[2]);
    var utime2 = parseInt(img_info[3]);
    var ser_nr = parseInt(img_info[4]);
    var fsize = img_info[5];
    var frames = parseInt(img_info[6]);
    var s16_1 = parseInt(img_info[7]);
    var s16_2 = parseInt(img_info[8]);
    var ftype_str = img_info[9]
    var fduration = img_info[10]
    var fname = img_info[11];
    
 
    mk_obj_hint(cam_nr, evt_id, utime1, utime2, ser_nr, fsize, frames, s16_1, s16_2, ftype_str, fduration, fname);
    ddrivetip();
}


function absTop(e) {
   var rp = e.offsetParent;
   if (rp == null)
      return null;
   var ptop = e.offsetTop;
   while (true) {
      ptop += rp.offsetTop;
      rp = rp.offsetParent;
      if (rp == null)
         break;
   }
   return ptop;
}

function onBody(e) {
  var ptop = absTop(e);
  if (ptop == null)
     return;
  var pbottom = ptop + e.clientHeight;
  var oConvas = ietruebody();
  if (pbottom >= oConvas.scrollTop + oConvas.clientHeight) {
    /*alert(e.tagName + ' bottom = ' + pbottom + 
    "\n"+ 'ниже чем' +"\n"+
    oConvas.tagName + " clientHeight,scrollHeight = " + oConvas.clientHeight + ', ' + oConvas.scrollHeight);
    */
    scrollTo(0, ptop-3);
  } else if (ptop <= oConvas.scrollTop ) {
  /*
    alert(e.tagName + ' ptop = ' + ptop + 
    "\n"+ 'выше чем' +"\n"+
    oConvas.tagName +
    "\nscrollTop=" + oConvas.scrollTop + 
    "\nclientHeight=" + oConvas.clientHeight + 
    "\nscrollHeight=" + oConvas.scrollHeight);
  */
    scrollTo(0, pbottom - oConvas.clientHeight );
  }
  // if (ptop<)
  
}

/*

document.onmousemove = function(e) {
   e = e || event;
   var o = e.target || e.srcElement;
   window.parent.document.title = o.tagName;
}
*/
