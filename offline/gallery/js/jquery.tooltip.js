(function ($) {
    $(function () {

        //Плагин ToolTip использует значение атрибута "tooltip" для отображения в тултипе

        $.fn.tooltip = function () {
            $(this).each(function (i) {
                $(this)
                    .mouseover(function (e) {
                        if ($(".tooltip").length > 0) {
                            return;
                        } //ограничение кол-ва эдентичных тултипов(при отключении logo_play)
                        $('<div class="tooltip" id="tooltip_' + i + '" ><table >' + $(this).attr('tooltip') + '</table></div>')
                            .css({'left': e.pageX - 200, 'top': e.pageY + 20 })
                            .fadeIn(400)
                            .appendTo('body');
                    })
                    .mousemove(function (kmouse) {
                        $(".tooltip").css({left: kmouse.pageX - 200, top: kmouse.pageY + 20});
                    })
                    .mouseout(function () {
                        $(".tooltip").fadeOut(400);
                        $(".tooltip").remove();
                    });
            });
        };

        $.fn.tooltipOff = function () {
            $(this).unbind("mouseover").unbind("mousemove").unbind("mouseout");
        };

    });
})(jQuery);

