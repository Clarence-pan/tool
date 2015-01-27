
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
    }
}
function expandThisCell(e){
    $(e).toggleClass('fixed-height');
    window.scrollTo(0, $(e).offset().top + 30);
}
function toggle_very_brief(v){
    $('.line, .level, .category, .time').toggle(!v);
}
