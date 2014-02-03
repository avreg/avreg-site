(function ($) {
    /**
     * Based on https://gist.github.com/mekwall/1263939
     * @param forcedUpdate  do not append events, just recalculate
     * @returns {*}
     */
    $.fn.textfill = function (forcedUpdate) {
        var $this = $(this);

        var doResize = function () {
            var ourText = $this.children("span"),
                parent = ourText.parent(),
                maxWidth = parent.width(),
                maxHeight = parent.height(),
                fontSize = parseInt(ourText.css("fontSize"), 10),
                multiplierWidth = maxWidth / ourText.width(),
                multiplierHeight = maxHeight / ourText.height(),
                newSize = (fontSize * ((multiplierWidth < multiplierHeight ? multiplierWidth : multiplierHeight) - 0.1));

            ourText.css("fontSize", newSize);
        };

        if (!forcedUpdate) {
            // todo check if handler is already attached and skip if so (way to get rid of forceUpdate option)
            $(window).on('resize', function () {
                $this.each(doResize)
            })
        }

        return $this.each(doResize);
    };

    $.fn.serializeObject = function () {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function () {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    $.extend({

        debounce: function (fn, timeout, invokeAsap, ctx) {

            if (arguments.length == 3 && typeof invokeAsap != 'boolean') {
                ctx = invokeAsap;
                invokeAsap = false;
            }

            var timer;

            return function () {

                var args = arguments;
                ctx = ctx || this;

                invokeAsap && !timer && fn.apply(ctx, args);

                clearTimeout(timer);

                timer = setTimeout(function () {
                    !invokeAsap && fn.apply(ctx, args);
                    timer = null;
                }, timeout);

            };

        },

        throttle: function (fn, timeout, ctx) {

            var timer, args, needInvoke;

            return function () {

                args = arguments;
                needInvoke = true;
                ctx = ctx || this;

                if (!timer) {
                    (function () {
                        if (needInvoke) {
                            fn.apply(ctx, args);
                            needInvoke = false;
                            timer = setTimeout(arguments.callee, timeout);
                        } else {
                            timer = null;
                        }
                    })();
                }

            };

        }

    });

})(jQuery);

if (!Function.prototype.bind) {
    /**
     * Polyfill for bind,
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Function/bind.
     * @param oThis
     * @returns {Function}
     */
    Function.prototype.bind = function (oThis) {
        if (typeof this !== "function") {
            // closest thing possible to the ECMAScript 5 internal IsCallable function
            throw new TypeError("Function.prototype.bind - what is trying to be bound is not callable");
        }

        var aArgs = Array.prototype.slice.call(arguments, 1),
            fToBind = this,
            fNOP = function () {
            },
            fBound = function () {
                return fToBind.apply(this instanceof fNOP && oThis
                    ? this
                    : oThis,
                    aArgs.concat(Array.prototype.slice.call(arguments)));
            };

        fNOP.prototype = this.prototype;
        fBound.prototype = new fNOP();

        return fBound;
    };
}
