$(document).ready(function () {
    var host = location.host;
    var protocol = location.protocol
    var baseurl = protocol + "//" + host

    function saveLocal(key, data, ttl = null) {
        var d = {
            data: data,
            exp: null
        };

        if (ttl) {
            d.exp = new Date().getTime() + (ttl);
        }
        localStorage.setItem(key, JSON.stringify(d));
    }

    function getLocal(key) {
        var t = localStorage.getItem(key);

        if (!t) return null;

        var now = new Date().getTime();
        var data = JSON.parse(t);
        if (data.exp && data.exp < now) {
            return null
        }

        return data.data;
    }


    function loadRuntimeLog() {
        var url = baseurl + "/ws/runtime";
        $.get(url).then(res => {
            var lines = res.data;
            // var escapedString = htmlspecialchars(lines.join('\n'));
            var preTag = $('pre');
            $("pre").text((lines.join('\n')))

            preTag.parent().scrollTop(preTag[0].scrollHeight);

        });
    }

    function htmlspecialchars(str) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return str.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    loadRuntimeLog();
    setInterval(loadRuntimeLog, (1000 * 60));
});