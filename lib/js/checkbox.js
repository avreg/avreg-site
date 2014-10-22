dict = {
    select_all: 'Выбрать все',
    deselect_all: 'Снять выбор'
};
function countSelElem(itms, sel) {
    var cntr = 0;
    $(itms).each(function () {
        if (sel === true) {
            if ($(this).is(':checked')) {
                cntr++;
            }
        } else {
            cntr++;
        }
    });
    return cntr;
}

function chbox_itm_clk(name) {
    var itms = $('#id_' + name + ' .chbox_itm');
    var cntr = countSelElem(itms, true);

    if (cntr == 0) {
        $('#id_head_' + name).css('opacity', 1);
        $('#id_head_' + name + ' .chbox_head').text(dict.select_all);
        $('#id_' + name + '_select_all').prop('checked', false);
    } else if (cntr == $(itms).size()) {
        $('#id_head_' + name).css('opacity', 1);
        $('#id_head_' + name + ' .chbox_head').text(dict.deselect_all);
        $('#id_' + name + '_select_all').prop('checked', true);
    } else {
        $('#id_head_' + name).css('opacity', 0.7);
        $('#id_head_' + name + ' .chbox_head').text(dict.deselect_all);
        $('#id_' + name + '_select_all').prop('checked', true);
    }
};

//поведение при клике на 'Выбрать все'
function chbox_select_all(name) {
    var head = $('#id_' + name + '_select_all');
    if (!$(head).is(':checked')) {
        $('#id_' + name + ' input.chbox_itm').prop('checked', false);
        $('#id_head_' + name + ' .chbox_head').text(dict.select_all);
    } else {
        $('#id_' + name + ' input.chbox_itm').prop('checked', true);
        $('#id_head_' + name + ' .chbox_head').text(dict.deselect_all);
    }
    chbox_itm_clk(name);
};

//Установка начального состояния
function initCheckBox(name, size) {
    var itms = $('#id_' + name + ' .chbox_itm');
    var cntSel = countSelElem(itms, true);
    var cntElem = countSelElem(itms, false);
    // Для 'Выбрать Все'
    if ($('#id_' + name + ' input.chbox_itm').is(':checked')) {
        if (cntSel == 0) {
            $('#id_' + name + '_select_all').prop('checked', false);
            $('#id_head_' + name + ' .chbox_head').text(dict.select_all);
        } else if (cntElem == cntSel) {
            $('#id_' + name + '_select_all').prop('checked', true);
            $('#id_head_' + name + ' .chbox_head').text(dict.deselect_all);
        } else {
            $('#id_' + name + '_select_all').prop('checked', true);
            $('#id_head_' + name + ' .chbox_head').text(dict.deselect_all);
            $('#id_head_' + name).css('opacity', 0.7);
        }

    }
    //определение и установка высоты элемента управления
    if (size != 0) {
        var ht_cbx = 0;
        $('#id_' + name + ' .chbox_itm').each(function () {
            /// Подключение стилей
            if ($.browser.msie) {
                //для MSIE
                if ($(this).height() > ht_cbx) {
                    ht_cbx = $(this).height();
                }
            } else {
                if ($(this).height() > ht_cbx) {
                    ht_cbx = $(this).height() + 6;
                }
            }
        });
        ht_cbx *= size;
        $('#id_' + name).height(ht_cbx);
    }
}
/* vim: set expandtab smartindent tabstop=4 shiftwidth=4: */
