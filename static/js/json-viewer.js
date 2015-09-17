/**
 * JsonViewer - a util to view JSON
 */
(function ($, window) {
    window.JsonViewer = JsonViewer;

    /**
     * make expander of something
     * @param defaultExpand
     * @returns {*}
     */
    $.fn.initExpander = function (defaultExpand) {
        return initExpander(this, defaultExpand);
    };

    /**
     * construct a JsonViewer
     * @param options
     * @returns {JsonViewer}
     * @constructor
     */
    function JsonViewer(options) {
        var defaultOptions = {
            renderTo: null, // which element to render to
            json: {} // json data
        };
        var self = this;
        self.options = $.extend(self, defaultOptions, options);
        self.$root = $('<div></div>').append(createJsonNode(self.json));
        self.$root.addClass('json-viewer').appendTo(self.renderTo.empty());
        self.$root.find('.object-end:last').text('}');
        return this;
    }

    /**
     * init expander
     * @param triggers $('dt,hX')
     * @param defaultExpand bool initial expanded
     * @events expand, shrink, toggle-expand
     */
    function initExpander(triggers, defaultExpand) {
        triggers.each(function (i, e) {
            var tagName = e.tagName;
            if (!tagName.match(/^(dt|h\d*)/i)) {
                console.error("Unsupported tag: %o of %o!", tagName, e);
                return;
            }
            e = $(e);
            var target = e.data('expander-target');
            if (!target) {
                target = e.nextUntil(tagName);
                e.data('expander-target');
            }

            var trigger = e;
            if (trigger.children('.icon-expander').size() == 0) {
                trigger.prepend('<i class="icon icon-expander"></i>');
            }
            trigger.on('expand', function () {
                target.show();
                trigger.addClass('expanded');
            });
            trigger.on('shrink', function () {
                target.hide();
                trigger.removeClass('expanded');
            })
            trigger.on('toggle-expand', function () {
                target.toggle();
                trigger.toggleClass('expanded');
            });
            trigger.on('click', function () {
                trigger.trigger('toggle-expand');
            });
            trigger.css({cursor: 'pointer'});
        });
        if (!defaultExpand) {
            triggers.trigger('shrink');
            $(triggers.toArray().slice(0, 3)).trigger('expand');
        }
    }

    function createJsonNode(obj) {
        var info = rebuild(obj);
        if (info.title) {
            if (info.type == 'array'){
                var $list = $('<div class="array"></div>');
                $('<div class="array-begin">[</div>').appendTo($list);
                $.each(obj, function(i, v){
                   $('<div class="array-item"></div>').append(createJsonNode(v)).appendTo($list);
                });
                $('<div class="array-end">],<div>').appendTo($list);
                return $list;
            } else { // object
                var $list = $('<div class="object"></div>');
                $('<div class="object-begin">{</div>').appendTo($list);
                $.each(obj, function(k, v){
                    $('<div class="object-item"></div>')
                        .append($('<div class="key"></div>').text(k).attr('title', 'type: ' + getTypeOf(v)))
                        .append('<div class="delimiter">: </div>')
                        .append($('<div class="value"></div>').append(createJsonNode(v)))
                        .appendTo($list);
                });
                $('<div class="object-end">},</div>').appendTo($list);
                return $list;
            }
        } else {
            return $("<div><div>").append($('<span></span>').text(info.body).attr('title', 'type: ' + info.type).attr('class', info.type)).append(',');
        }
    }

    function getTypeOf(something) {
        var type = $.type(something);
        if (['array', 'string', 'boolean', 'null', 'object', 'number', 'undefined'].indexOf(type) >= 0) {
            return type;
        } else {
            console.error("Unsupported type %o of %o.", type, something);
            return 'unknown';
        }
    }

    function rebuild(something) {
        var type = getTypeOf(something);
        switch (type) {
            case 'object':
                return {title: isEmptyObject(something) ? '{}' : '{...}', body: something, type: type};
            case 'array':
                return {title: isEmptyArray(something) ? '[]' : '[...]', body: something, type: type};
            case 'string':
                return {body: JSON.stringify(something), type: type};
            case 'null':
                return {body: 'null', type: type};
            case 'boolean':
                return { body: something + "", type: type};
            default:
                return { body: JSON.stringify(something), type: type};
        }
    }

    function isEmptyArray(arr){
        return arr.length == 0;
    }

    function isEmptyObject(obj){
        for (var key in obj){
            return false;
        }
        return true;
    }

})(window.jQuery, window);