var __errorLog = [];
function _logJsError(g, h, j) {
    if (g.message && g.fileName && g.lineNumber) {
        h = g.fileName;
        j = g.lineNumber;
        g = g.message
    }
    console.error(g);
    if (h) {
        try {
            var n = getStackTrace()().join("\n---\n")
        } catch (s) {
            n = ""
        }
        var B = "Msg=" + escape(g + " Coming from (" + (document.referrer || "") + "), visiting (" + (document.location.href || "") + "), error log (" + __errorLog.join("; ") + ")\n Backtrace:\n" + n);
        B += "&URL=" + escape(h);
        B += "&Line=" + j;
        B += "&Platform=" + escape(navigator.platform);
        B += "&UserAgent=" +
        escape(navigator.userAgent);
        g = document.createElement("img");
        g.setAttribute("src", "/error/js/submit?" + B);
        document.body.appendChild(g)
    	console.error(B);
    }
}
function logError(g) {
    console.error(g);
    __errorLog.push(g)
}
function getStackTrace() {
    if ($.browser.msie) {
        return "No backtrace avail";
    }
    var g;
    try {
        0()
    } catch (h) {
        g = h.stack ? "Firefox" : window.opera ? "Opera" : "Other";
        switch (g) {
            case "Firefox":
                return function() {
                    return h.stack.replace(/^.*?\n/, "").replace(/(?:\n@:0)?\s+$/m, "").replace(/^\(/gm, "{anonymous}(").split("\n")
                };
            case "Opera":
                return function() {
                    var j = h.message.split("\n"), n = /Line\s+(\d+).*?in\s+(http\S+)(?:.*?in\s+function\s+(\S+))?/i, s, B, O;
                    s = 4;
                    B = 0;
                    for (O = j.length; s < O; s += 2)
                        if (n.test(j[s]))
                            j[B++] = (RegExp.$3 ? RegExp.$3 + "()@" + RegExp.$2 + RegExp.$1 : "{anonymous}" + RegExp.$2 +
                            ":" + RegExp.$1) + " -- " + j[s + 1].replace(/^\s+/, "");
                    j.splice(B, j.length - B);
                    return j
                };
            default:
                return function() {
                    for (var j = arguments.callee.caller, n = /function\s*([\w\-$]+)?\s*\(/i, s = [], B = 0, O, C, L; j; ) {
                        O = n.test(j.toString()) ? RegExp.$1 || "{anonymous}" : "{anonymous}";
                        C = s.slice.call(j.arguments);
                        for (L = C.length; L--; )
                            switch (typeof C[L]) {
                                case "string":
                                    C[L] = '"' + C[L].replace(/"/g, '\\"') + '"';
                                    break;
                                case "function":
                                    C[L] = "function"
                            }
                        s[B++] = O + "(" + C.join() + ")";
                        j = j.caller
                    }
                    return s
                }
        }
    }
}
onerror = _logJsError;
