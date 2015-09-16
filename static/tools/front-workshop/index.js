$(function(){
    var $javascript = $('#javascript');
    var $css = $('#css');
    var $html = $('#html');
    var $result = $('#result');
    var $refreshBtn = $('#refreshBtn');
    var $download = $('#download');
    var $updateIndicator = $('.update-indicator');
    var resultTemplate = new EJS({url: 'result.ejs'});

    function refreshResult(){
        var resultHtml = resultTemplate.render({
            javascript: $javascript.val(),
            css: $css.val(),
            html: $html.val()
        });

        var resultUrl = URL.createObjectURL(new Blob([resultHtml], {type:'text/html'}));
        $download[0].href = resultUrl;
        $result[0].contentWindow.location = resultUrl;

        $updateIndicator.removeClass('dirty').addClass('updated');
    }

    var idleTimerId = 0;
    function refreshResultOnIdle(){
        $updateIndicator.removeClass('dirty');
        setTimeout(function(){
            $updateIndicator.addClass('dirty').removeClass('updated');
        });

        if (idleTimerId){
            clearTimeout(idleTimerId);
        }

        idleTimerId = setTimeout(refreshResult, 800);
    }

    $javascript.on('input', refreshResultOnIdle);
    $css.on('input', refreshResultOnIdle);
    $html.on('input', refreshResultOnIdle);
    $refreshBtn.on('click', refreshResult);

});
