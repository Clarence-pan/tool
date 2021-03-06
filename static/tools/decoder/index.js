(function () {
    window.onresize = adjust_input_size;
    adjust_input_size();
    var urls = [
    ];
    for (var i in urls){
        add_option_to_goto_url_select(urls[i]);
    }
    if (localStorage.urls + "" != "undefined") {
        urls = localStorage.urls.split("\n");
        for (var i in urls){
            add_option_to_goto_url_select(urls[i]);
        }
    }
    $(goto_url).val($(goto_url_select).val());
})();

function add_option_to_goto_url_select(option_url) {
    var new_opt = document.createElement('option');
    new_opt.value = option_url;
    new_opt.innerText = option_url;
    new_opt.selected = true;
    goto_url_select.add(new_opt);
}
function on_goto_url_change(){
    return;
    var url = $(goto_url).val();
    for (var opt_index in goto_url_select.options) {
        var opt = goto_url_select.options[opt_index];
        if (opt.value == url){
            return;
        }
    }
    localStorage.urls = localStorage.urls + "\n" + url;
    add_option_to_goto_url_select(url);
}
function on_goto_url_selection_change(){
    $(goto_url).val($(goto_url_select).val());
}
function copyToClipboard(text) {
    if (window.clipboardData) // IE
    {
        window.clipboardData.setData("Text", text);
    }
    else {
        unsafeWindow.netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        const clipboardHelper = Components.classes[ "@mozilla.org/widget/clipboardhelper;1"].getService(Components.interfaces.nsIClipboardHelper);
        clipboardHelper.copyString(text);
    }
}
function last_line(text){
    var lines = text.split("\n");
    return lines[lines.length - 1];
}
function do_get_and_parse(){
    var xhttp = new XMLHttpRequest();
    var uri = $(goto_url).val() + "?" + get_base64_text();
    xhttp.onreadystatechange = function(){
        if (xhttp.readyState == 4 && xhttp.status == 200){
            var input = xhttp.responseText;
            input = last_line(input);
            var result = Base64.decode(input);
            result = formatJson(result);
            output({
                convertion: "BASE64 DECODE TO JSON",
                json: result,
                base64:input
            });
        }
        $(log).prepend("<p>XHTTP: readyState: " + xhttp.readyState + " status: " + xhttp.status + " URL: " + uri + "</p>");
    };
    xhttp.open("GET", uri, true);
    xhttp.send();
}

function get_base64_text(){
    if (window.last_base64_result){
        return window.last_base64_result;
    } else {
        var input = $('#input_value').val() || "";
        if (is_json_code(input)){
            return Base64.encode(JSON.stringify(parseJson(input)));
        } else {
            return input;
        }
    }
}

function is_json_code(code){
    return code.trim()[0] == '{'
}

function do_copy_output() {
    copyToClipboard( $(output_value).val());
}
function do_swap() {
    var t = $(input_value).val();
    $(input_value).val($(output_value).val());
    $(output_value).val(t);
}
function goto_url_get() {
    window.open($(goto_url).val() + "?" + get_base64_text(), "_blank");
}
function goto_url_get() {
    var url = $(goto_url).val();
    var param = get_base64_text();
    $('<form method="POST" target="_blank"></form>')
        .attr('action', url)
        .append($('<input type="hidden" />').attr(name, param))
        .appendTo('body').submit();
}
function on_input_value_change() {
    window.last_base64_result = null;
//    if ($(input_value).val()[0] != '{') {
//        format();
//    } else {
//        encode64();
//    }
}
function adjust_input_size() {
    //$(input_value).height($(window).height()  - $(ta).height() - 50);
}
function report_error(msg) {
    $(err).text(msg);
    $(err).show();
    if (msg != "") {
        setTimeout(function () {
            $(err).fadeOut()
        }, 2000);
    }
}
function output(convertion, json, base64) {
    if (!json && !base64) {
        json = convertion['json'];
        base64 = convertion['base64'];
        convertion = convertion['convertion'];
    }
    if (base64){
        window.last_base64_result = base64;
    }
    var base64_p = document.createElement('p');
    base64_p.innerText = "BASE64: " + base64;
    $(log).prepend(base64_p);
    var json_title = document.createElement('p');
    json_title.innerText = "JSON:";
    var json_pre = document.createElement('pre');
    json_pre.className = "brush: js;";
    json_pre.innerHTML = json;


    var json_pre_wrapper = document.createElement("div");
    json_pre_wrapper.className = "wrapper";
    json_pre_wrapper.appendChild(json_pre);
    var obj = parseJson(json);
    var viewer = new JsonViewer({
        renderTo: $('<div></div>').insertBefore(json_pre),
        json: obj
    });
    window.viewer = viewer;
    json_pre_wrapper.onscroll = function(){
        json_pre_wrapper.scroll();
    };
    $(log).prepend(json_pre_wrapper);
    $(log).prepend(json_title);
    SyntaxHighlighter.highlight({}, json_pre);
    if ($(goto_url).val() && $(goto_url).val() != '<NULL>'){
        var goto_url_a = document.createElement("a");
        goto_url_a.innerText = goto_url_a.href = $(goto_url).val() + "?" + base64;
        goto_url_a.target = "_blank";
        $(log).prepend(goto_url_a);
    }
    var convertion_p = document.createElement('h3');
    convertion_p.innerText = convertion + '  - '+ (new Date()).toLocaleString();
    $(log).prepend(convertion_p);
    $(log).prepend(document.createElement("hr"));
    if (convertion == "BASE64 ENCODE") {
        $(output_value).val(base64);
    } else {
        $(output_value).val(json);
    }
    //window.location.replace(window.location.origin + window.location.pathname + "#log");}
    window.location.replace("#log");
}

function parseJson(code, throws){
    try{
        code = ' function json_eval_function(){ return ' + code + ';}';
        eval(code);
        return json_eval_function();
    }catch(e){
        console.log(" Parse json failed: %o", e);
        if (throws){
            throw e;
        }
        return null;
    }
}

function decode64() {
    var result = Base64.decode($('#input_value').val());
    output({
        'convertion': "BASE64 DECODE",
        'json': result,
        'base64': $('#input_value').val()
    });
}
function encode64() {
    var input = try_normalize_json($('#input_value').val());
    var result = Base64.encode(input);
    output({
        'convertion': "BASE64 ENCODE",
        'json': input,
        'base64': result
    });

}

function try_normalize_json(code){
    var obj = parseJson(code);
    if (!obj){
        return code;
    }
    return JSON.stringify(code);
}

function right_of(needle, haystack, defaultValue){
    var pos = haystack.indexOf(needle);
    if (pos >= 0){
        return haystack.substring(pos + 1);
    }
    return defaultValue !== undefined ? defaultValue : haystack;
}

function remove_http_url_prefix(rawInput) {
    if ((rawInput.indexOf('?') > 0) && rawInput.trim().match(/^http:/g)){
        return right_of('?', rawInput);
    }
    return rawInput;
}
function format() {
    var rawInput = $('#input_value').val();
    var input = remove_http_url_prefix(rawInput);
    var result = Base64.decode(input);
    result = formatJson(result);
    output({ 'convertion': "BASE64 DECODE TO JSON",
        'json': result,
        'base64': $('#input_value').val()});
}

function repeat(s, count) {
    return new Array(count + 1).join(s);
}
function formatJson(json) {
    if ($.type(json) == 'string'){
        json = parseJson(json);
    }
    return JSON.stringify(json, null, "   ");
}
/* Base64加密解密 */
var Nibbler = function (options) {
    var construct,

    // options
        pad, dataBits, codeBits, keyString, arrayData,

    // private instance variables
        mask, group, max,

    // private methods
        gcd, translate,

    // public methods
        encode, decode,

        utf16to8, utf8to16;

    // pseudo-constructor
    construct = function () {
        var i, mag, prev;

        // options
        pad = options.pad || '';
        dataBits = options.dataBits;
        codeBits = options.codeBits;
        keyString = options.keyString;
        arrayData = options.arrayData;

        // bitmasks
        mag = Math.max(dataBits, codeBits);
        prev = 0;
        mask = [];
        for (i = 0; i < mag; i += 1) {
            mask.push(prev);
            prev += prev + 1;
        }
        max = prev;

        // ouput code characters in multiples of this number
        group = dataBits / gcd(dataBits, codeBits);
    };

    // greatest common divisor
    gcd = function (a, b) {
        var t;
        while (b !== 0) {
            t = b;
            b = a % b;
            a = t;
        }
        return a;
    };

    // the re-coder
    translate = function (input, bitsIn, bitsOut, decoding) {
        var i, len, chr, byteIn,
            buffer, size, output,
            write;

        // append a byte to the output
        write = function (n) {
            if (!decoding) {
                output.push(keyString.charAt(n));
            } else if (arrayData) {
                output.push(n);
            } else {
                output.push(String.fromCharCode(n));
            }
        };

        buffer = 0;
        size = 0;
        output = [];

        len = input.length;
        for (i = 0; i < len; i += 1) {
            // the new size the buffer will be after adding these bits
            size += bitsIn;

            // read a character
            if (decoding) {
                // decode it
                chr = input.charAt(i);
                byteIn = keyString.indexOf(chr);
                if (chr === pad) {
                    break;
                } else if (byteIn < 0) {
                    throw 'the character "' + chr + '" is not a member of ' + keyString;
                }
            } else {
                if (arrayData) {
                    byteIn = input[i];
                } else {
                    byteIn = input.charCodeAt(i);
                }
                if ((byteIn | max) !== max) {
                    throw byteIn + " is outside the range 0-" + max;
                }
            }

            // shift the buffer to the left and add the new bits
            buffer = (buffer << bitsIn) | byteIn;

            // as long as there's enough in the buffer for another output...
            while (size >= bitsOut) {
                // the new size the buffer will be after an output
                size -= bitsOut;

                // output the part that lies to the left of that number of bits
                // by shifting the them to the right
                write(buffer >> size);

                // remove the bits we wrote from the buffer
                // by applying a mask with the new size
                buffer &= mask[size];
            }
        }

        // If we're encoding and there's input left over, pad the output.
        // Otherwise, leave the extra bits off, 'cause they themselves are padding
        if (!decoding && size > 0) {

            // flush the buffer
            write(buffer << (bitsOut - size));

            // add padding keyString for the remainder of the group
            len = output.length % group;
            for (i = 0; i < len; i += 1) {
                output.push(pad);
            }
        }

        // string!
        return (arrayData && decoding) ? output : output.join('');
    };

    /**
     * Encode.  Input and output are strings.
     */
    encode = function (str) {
        //return translate(input, dataBits, codeBits, false);
        str = utf16to8(str);
        var out = "", i = 0, len = str.length, c1, c2, c3, base64EncodeChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
        while (i < len) {
            c1 = str.charCodeAt(i++) & 0xff;
            if (i == len) {
                out += base64EncodeChars.charAt(c1 >> 2);
                out += base64EncodeChars.charAt((c1 & 0x3) << 4);
                out += "==";
                break;
            }
            c2 = str.charCodeAt(i++);
            if (i == len) {
                out += base64EncodeChars.charAt(c1 >> 2);
                out += base64EncodeChars.charAt(((c1 & 0x3) << 4)
                    | ((c2 & 0xF0) >> 4));
                out += base64EncodeChars.charAt((c2 & 0xF) << 2);
                out += "=";
                break;
            }
            c3 = str.charCodeAt(i++);
            out += base64EncodeChars.charAt(c1 >> 2);
            out += base64EncodeChars.charAt(((c1 & 0x3) << 4)
                | ((c2 & 0xF0) >> 4));
            out += base64EncodeChars.charAt(((c2 & 0xF) << 2)
                | ((c3 & 0xC0) >> 6));
            out += base64EncodeChars.charAt(c3 & 0x3F);
        }
        return out;
    };

    /**
     * Decode.  Input and output are strings.
     */
    decode = function (str) {
        //return translate(input, codeBits, dataBits, true);
        var c1, c2, c3, c4;
        var i, len, out;
        var base64DecodeChars = new Array(-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1, -1, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1, -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1);
        len = str.length;
        i = 0;
        out = "";
        while (i < len) {
            do {
                c1 = base64DecodeChars[str.charCodeAt(i++) & 0xff];
            }
            while (i < len && c1 == -1);
            if (c1 == -1) break;
            do {
                c2 = base64DecodeChars[str.charCodeAt(i++) & 0xff];
            }
            while (i < len && c2 == -1);
            if (c2 == -1) break;
            out += String.fromCharCode((c1 << 2) | ((c2 & 0x30) >> 4));
            do {
                c3 = str.charCodeAt(i++) & 0xff;
                if (c3 == 61) {
                    out = utf8to16(out);
                    return out;
                }
                c3 = base64DecodeChars[c3];
            }
            while (i < len && c3 == -1);
            if (c3 == -1) break;
            out += String.fromCharCode(((c2 & 0XF) << 4) | ((c3 & 0x3C) >> 2));
            do {
                c4 = str.charCodeAt(i++) & 0xff;
                if (c4 == 61) {
                    out = utf8to16(out);
                    return out;
                }
                c4 = base64DecodeChars[c4];
            }
            while (i < len && c4 == -1);
            if (c4 == -1) break;
            out += String.fromCharCode(((c3 & 0x03) << 6) | c4);
        }
        out = utf8to16(out);
        return out;
    };

    utf16to8 = function (str) {
        var out, i, len, c;
        out = "";
        len = str.length;
        for (i = 0; i < len; i++) {
            c = str.charCodeAt(i);
            if ((c >= 0x0001) && (c <= 0x007F)) {
                out += str.charAt(i);
            } else if (c > 0x07FF) {
                out += String
                    .fromCharCode(0xE0 | ((c >> 12) & 0x0F));
                out += String
                    .fromCharCode(0x80 | ((c >> 6) & 0x3F));
                out += String
                    .fromCharCode(0x80 | ((c >> 0) & 0x3F));
            } else {
                out += String
                    .fromCharCode(0xC0 | ((c >> 6) & 0x1F));
                out += String
                    .fromCharCode(0x80 | ((c >> 0) & 0x3F));
            }
        }
        return out;
    };

    utf8to16 = function (str) {
        var out, i, len, c;
        var char2, char3;
        out = "";
        len = str.length;
        i = 0;
        while (i < len) {
            c = str.charCodeAt(i++);
            switch (c >> 4) {
                case 0:
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                case 7:
                    out += str.charAt(i - 1);
                    break;
                case 12:
                case 13:
                    char2 = str.charCodeAt(i++);
                    out += String.fromCharCode(((c & 0x1F) << 6) | (char2 & 0x3F));
                    break;
                case 14:
                    char2 = str.charCodeAt(i++);
                    char3 = str.charCodeAt(i++);
                    out += String.fromCharCode(((c & 0x0F) << 12) | ((char2 & 0x3F) << 6) | ((char3 & 0x3F) << 0));
                    break;
            }
        }
        return out;
    }
    this.encode = encode;
    this.decode = decode;
    construct();
};

window.Base64 = new Nibbler({
    dataBits: 8,
    codeBits: 6,
    keyString: 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/',
    pad: '='
});


SyntaxHighlighter.all();