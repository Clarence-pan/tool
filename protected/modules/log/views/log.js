
function showStackTrace(title){
    $(title).siblings('.stackTrace').toggle();
}
function getGlobal(key){
    var global = (function(){return this;})();
    if (!key){
        return global;
    }
    return global[key];
}
function setGlobal(key, value){
    var global = (function(){return this;})();
    global[key] = value;
}
function toggle_stack_trace(){
    $('.stackTrace').toggle();
}
function buildQuery(key, value){
    var query = getCurrentParams();
    if (typeof(key) == 'object'){
        for (var k in key){
            query[k] = key[k];
        }
    } else if (typeof(key) == 'undefined'){
    } else {
        query[key] = value;
    }
    delete query['clear'];
    query = buildQueryString(query);
    return query;
}
function refresh(key, value){
    var query;
    if ('clear' == key){
        query = '?clear='+value;
    } else {
        query = buildQuery(key, value);
    }
    location.replace(query);
}
function getCurrentParams(){
    var params = {};
    var searches = window.location.search.substr(1).split("&")
    for (var j in searches){
        var s = searches[j];
        var i = s.indexOf('=');
        if (i>0){
            params[s.substr(0,i)] = s.substr(i+1);
        }
    }
    return params;
}
function buildQueryString(params){
    var query = "?";
    for (var i in params){
        query = query + i + "=" + params[i] + "&";
    }
    return query;
}
function autoAppend(){
    var url = buildQuery({"seek": getGlobal('fileSize'),
        "autoAppend": 1,
        "id": getGlobal('itemId')});
    ajaxGetContent(url, true, function(content){
        var div = document.createElement('div');
        div.innerHTML = content;
        document.body.appendChild(div);
        var oldFileSize = getGlobal('fileSize');
        var trick = '<!-- MUST RUN:';
        var i = content.indexOf(trick);
        if (i > 0){
            eval(content.substr(i + trick.length));
        }
        if (oldFileSize != getGlobal('fileSize')){
            scrollToBottom();
        }
    });
    if (window.stopAutoAppend){
        return;
    }
    setTimeout("autoAppend()", 1000);
}
function ajaxGetContent(url, async, resultCallbackFunc){
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function(){
        if (xhttp.readyState == 4 && xhttp.status == 200){
            resultCallbackFunc(xhttp.responseText);
        }
    };
    xhttp.open("GET", url, async);
    xhttp.send();
}
function scrollToBottom(){
//            var bottom = document.getElementById('bottom');
//            if (!bottom){
//                bottom = document.createElement("div");
//                bottom.id = 'bottom';
//            }
//            document.body.appendChild(bottom);
//            location.replace("#bottom");
    window.scrollTo(0, 9999999);
}
function scrollToTop(){
    window.scrollTo(0, 0);
}
function showInNewWindow(e){
    if (e.innerHTML.trim()[0] == '<'){
        e.innerHTML = e.innerText;
    }

    if (e.innerHTML.split("\n").length > 3){
        var x = window.open('', '_blank');
        x.document.body.innerHTML = '<pre>'+e.innerHTML+'</pre>';
        bindExpander($(x.document.body).children('pre'));
    }
}
function bindExpander(code){
    if (!code.text().trim().match(/array/i)){
        return;
    }
//    var css = document.createElement('style');
//    css.innerHTML = '.expander-open,.expander-close{ color: blue; }';
//    code.closest('document')[0].head.prependChild(css);

	(function(){
		var lines = code.text().split('\n');
		code.empty();
		for (var i = 0; i < lines.length; i++){
			var line = lines[i];
			var intendClass = 'intend-'+findFirstNonBlankPos(line);
			var line = $('<div class="line"></div>').text(line).attr('data-intend-class', intendClass).addClass(intendClass);
			line.html(line.html()
                .replaceAll(/ /g, '&nbsp;')
                .replace('(', '<span class="expander-open">(</span>')
                .replace(')', '<span class="expander-close">)</span>')
                .replace('array', '<span class="expander-array">array</span>')
                );
			code.append(line);
		}
	})();
	var expanderCss = {
        color: 'blue',
        'padding-right': '2em',
		'margin-right': '-2em',
        'padding-left': '2em',
        'margin-left': '-2em',
        cursor: 'pointer'
    };
    code.find('.expander-open, .expander-close, .expander-array').css(expanderCss);
	code.delegate('.expander-array', 'click', function(){
		var opener = $(this).closest('.line').next().find('.expander-open:eq(0)');
		if (opener.is(':visible')){
			opener.trigger('click');
		}else{
			$(this).next().trigger('click');
		}
	});
	code.delegate('.expander-close', 'click', function(){
		var line = $(this).closest('.line');
		var opener = findNextDired(line, 'prev', '.' + line.attr('data-intend-class')).find('.expander-open:eq(0)');
		opener.trigger('click');
	});
    code.delegate('.expander-open', 'click', function(){
        var $this = $(this);
        var line = $this.closest('.line');
		var prevLine = findNextDired(line, 'prev', '.line');
        var thisClass = '.' + line.attr('data-intend-class');
        var content = line.nextUntil(thisClass);
        var endPos = findNextDired(line, 'next', thisClass);
		if (!line.data('wrapper')){
			var wrapper = $('<div class="wrapper"></div>').insertBefore(line);
			wrapper.append(line).append(content).append(endPos);
			line.data('wrapper', wrapper);
		}else{
			var wrapper = line.data('wrapper');
			prevLine = wrapper.prev();
			if (prevLine.find('.expander').size() > 0){
				return;
			}
		}
		wrapper.hide();

		var expander = $('<span class="expander">(...) <i>// </i></span>');
		expander.appendTo(prevLine);
		expander.append($('<i></i>').text(content.text().substring(0, 90)+'...'));
		expander.css(expanderCss).css({
			'white-space': 'initial'
		});
		expander.on('click', function(){
			wrapper.show();
			expander.remove();
		});
    });
	$('<div></div>').append(
			$('<a href="javascript:void(0)">Close All</a>').on('click', function(){
				code.find('.expander-open').trigger('click');
			})).append(
			$('<a href="javascript:void(0)">Open All</a>').on('click', function(){
				code.find('.expander').trigger('click');
			})
		).prependTo(code).find('a').css({
			'margin-left' : '2em'
		});;

	function findNextDired(elem, dir, selector){
		for (var next = elem[dir](); !next.is(selector); next = next[dir]()){
			if (next.size() <= 0){
				break;
			}
		}
		return next;
	}
    function findFirstNonBlankPos(str){
        for (var j = 0; j < str.length; j++){
            if (str[j] != ' '){
                return j;
            }
        }
        return false;
    }
}
function expandThisCell(e, evt){
    var top = $(e).toggleClass('fixed-height').offset().top;
    // 如果滚动得超过窗口范围了，自动滚回去
    if (window.scrollY > top ){
        window.scrollTo(0, top - evt.clientY);
    }
}
function toggle_very_brief(v){
    $('.line, .level, .category, .time').toggle(!v);
}
