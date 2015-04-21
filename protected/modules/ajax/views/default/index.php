<?php
/**
 * @var $this DefaultController
 * @var $groups array
 */

?>
<style type="text/css">
    html, body{
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }
    #leftFrame, #rightFrame{
        border: none;
        margin: 0;
        padding: 0;
        height: 100vh;
        width: 100vw;
    }
    #leftFrame{
        position: absolute;
        width: 10em;
        z-index: 2;
    }
    #rightFrame{
        position: absolute;
        left: 10em;
        margin-left: 2px;
        z-index: 1;
    }
    #verticalSeperator{
        display: block;
        width: 2px;
        position: absolute;
        left: 10em;
        height: 100vh;
        background-color: #ccc;
        padding: 0;
        border: none;
        overflow: hidden;
        cursor: col-resize;
        z-index: 5;
    }
</style>
<iframe name="leftFrame" id="leftFrame" src="/ajax/default/menu"></iframe>
<div id="verticalSeperator">&nbsp;</div>
<iframe name="rightFrame" id="rightFrame" src="/ajax/default/home"></iframe>
<script type="application/javascript" src="/static/js/jquery.js" ></script>
<link rel="stylesheet" href="/static/js/jui/jquery-ui.css" />
<script type="application/javascript" src="/static/js/jui/jquery-ui.js" ></script>
<script type="application/javascript">
    /**
     * 初始化垂直分割器
     * @param $seperator
     * @param $left
     * @param $right
     * @param options
     */
    function initVerticalSeperator($seperator, $left, $right, options){
        options = $.extend({}, {
            leftWidth: '14em',
            seperatorWidth: '5px'
        }, options);

        updateStyle(options);

        $seperator.on({
            mousedown: updateStyleOnDrag('start'),
            mousemove:  updateStyleOnDrag('drag'),
            mouseup:  updateStyleOnDrag('stop')
        });
        $('body').on({
            mousemove:  updateStyleOnDrag('drag'),
            mouseup:  updateStyleOnDrag('stop')
        });
        // iframe的鼠标移动事件无法被捕获，因此采用一个蒙板来捕获这个鼠标移动事件
        var mask = $("<div style='position: absolute; left: 0; top: 0; width: 100vw; height: 100vh; background-color: #9bff9b; opacity: 0.1; z-index: 3; '></div>").appendTo('body').on({
            mousemove:  updateStyleOnDrag('drag'),
            mouseup:  updateStyleOnDrag('stop')
        }).hide();

        // 更新样式
        function updateStyle(options){
            $left.css({
                width: options.leftWidth
            });
            $seperator.css({
                left: options.leftWidth,
                width: options.seperatorWidth
            });
            $right.css({
                left: 0,
                'padding-left': options.leftWidth,
                'margin-left': options.seperatorWidth
            });
        }

        // 当鼠标拖动时更新样式
        var dragging = false;
        function updateStyleOnDrag(eventType){
            return function(event, drag){
                if (eventType == 'start'){
                    dragging = true;
                    mask.show();
                    $seperator.css('z-index', 4);
                } else if (eventType == 'stop'){
                    dragging = false;
                    mask.hide();
                }
                if (!dragging){
                    return;
                }
                updateStyle($.extend(options, { leftWidth: event.clientX}));
                return false;
            }
        }
    }

    $(function(){
        initVerticalSeperator($('#verticalSeperator'), $('#leftFrame'), $('#rightFrame'));

        $("#leftFrame")[0].contentWindow.onload = function(){
            var leftDocument = $("#leftFrame")[0].contentDocument;
            $('a', leftDocument).on('click', function(){
                $(this).attr('target', 'rightFrame');
            });
        }
    })
</script>