jQuery(document).ready(function () {

    jQuery(".niceCheck").mousedown(
        /* при клике на чекбоксе меняем его вид и значение */
        function () {

            changeCheck(jQuery(this));

        });

    jQuery(".niceCheck").each(
        /* при загрузке страницы нужно проверить какое значение имеет чекбокс и в соответствии с ним выставить вид */
        function () {

            changeCheckStart(jQuery(this));

        });

});

/**
 * функция смены вида и значения чекбокса
 * el - span контейнер дял обычного чекбокса
 * input - чекбокс
 * @param el
 * @returns {boolean}
 */
function changeCheck(el) {
    var $input = el.find("input").eq(0);

    if (!$input.is(":checked")) {
        el.css("background-position", "0 -14px");
        $input.prop('checked', true)
    } else {
        el.css("background-position", "0 0");
        $input.prop('checked', false)
    }
    return true;
}

/**
 * если установлен атрибут checked, меняем вид чекбокса
 * @param el
 * @returns {boolean}
 */
function changeCheckStart(el) {
    var $input = el.find("input").eq(0);

    if ($input.is(":checked")) {
        el.css("background-position", "0 -14px");
    }
    return true;
}
