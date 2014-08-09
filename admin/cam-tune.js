function send_param_update_xhr(cam_nr, post_data, categories) {
    var jqxhrSaveParams = $.ajax({
        type: "POST",
        url: WwwPrefix + '/admin/cam_params_replace.php',
        data: post_data,
        dataType: 'json'
    });

    jqxhrSaveParams
        .fail(function(jqXHR, textStatus, errorThrown){
            var msg;
            msg = 'SaveParams() error: (' + jqXHR.status + ') [' + errorThrown + '] ' + textStatus;
            if (jqXHR.readyState == 4 && errorThrown != 'parsererror') {
                msg += "\n\n";
                msg += jqXHR.responseText.description;
            }
            alert(msg);
            return false;
        })
        .done(function (response) {
            if (response.status == 'done') {
                if ($("#iframe-index2").size()) {
                    $("#iframe-index2")[0].contentWindow.location.reload(true);
                } else {
                    // if not an iframe
                    if (window.location.href.indexOf('?') > 0) {
                        window.location.reload(true);
                    } else {
                        window.location.replace(window.location.href +
                            '?cam_nr=' + cam_nr + '&' + 'categories=' + categories);
                    }
                }
                return true;
            } else {
                alert('error: ' + response.description);
                return false;
            }
        });
}

$( document ).ready(function() {
    $('a.update_param').click(function(evt) {
        var target, a, cam_nr, par, val, val_def, categories;
        evt = evt || window.event
        evt.preventDefault();
        target = evt.target || evt.srcElement;
        if ( target.nodeName == 'IMG' || target.nodeName == 'img') {
            target = target.parentNode;
        }
        a = target.id.split('~');
        cam_nr = a[1];
        par = a[2];
        val = a[3];
        val_def = a[4];
        categories = a[5];

        var post_data = {
            'cam_nr': cam_nr
        }
        post_data[par] = val;
        send_param_update_xhr(cam_nr, post_data, categories);
    });
});
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
