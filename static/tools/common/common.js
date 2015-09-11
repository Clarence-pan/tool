var ToolsBasePath = /tools(\/?)$/.test(window.location.pathname) ? '.' : '..';

// load js, if needed
if (!window.$){
    (function(){
        var jQuery = includeJs(ToolsBasePath + '/../js/jquery.js');
        window.$ = function(onLoad){
            jQuery.onload = onLoad;
        };
    })();
}

$(function(){
    includeCss(ToolsBasePath + '/common/common.css');
    includeHtml(ToolsBasePath + '/common/layout.html').done(function(html){
        var $body = $('body');
        var $layout = $(html);
        var $header = $layout.find('header');
        var $footer = $layout.find('footer');
        $layout.find('a').each(function(){
            var $this = $(this);
            $this.attr('href', ToolsBasePath + '/common/' + $this.attr('href'));
        });
        if ($body.find('.content').size() > 0){
            $header.prependTo($body);
            $footer.appendTo($body);
        } else {
            $body.addClass('content');
            $header.insertBefore($body);
            $footer.insertAfter($body);
        }

        setTimeout(function(){
            if (!window.scrollY){
                window.scrollBy(0, $header.height());
                console.log('scroll %o', $header.height());
            }
        },100);
    });
});

function includeJs(src){
    var script = document.createElement('script');
    script.setAttribute('src', src);
    document.body.appendChild(script);
    return script;
}

function includeCss(src){
    var link = document.createElement('link');
    link.setAttribute('rel','stylesheet');
    link.setAttribute('href',src);
    link.setAttribute('type','text/css');
    document.body.appendChild(link);
    return link;
}

function includeHtml(src, callbacks){
    return $.get(src);
}