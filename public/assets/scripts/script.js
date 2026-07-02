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


    function loadDashboard() {
        var url = baseurl + "/ws/stat/dashboard";
        $.get(url).then(res => {
            // Update Summary
            var summary = res.summary;
            var results = res.results;

            $("#sum-success").text(summary.success);
            $("#sum-rate").text(summary.success_rate + "% dari total request");
            $("#sum-err").text(summary.error);
            $("#sum-total").text(summary.total);


            // Render Table
            var tbody = $("#table-res tbody");
            var tr = "";
            tbody.empty();
            if (summary.total == 0) {
                var text = '<p class="empty-state">Belum ada log yang dibuat. Jalankan cron terlebih dahulu untuk mengisi data.</p>';
                tr ="<tr><td colspan='5'>" + text + "</td></tr>";
            } else {
                results.forEach(row => {
                    tr += "<tr>";
                    var cls = row.success ? 'ok' : 'bad';

                    tr += "<td>" + new Date(row.timestamp).toLocaleString() + "</td>";
                    tr += "<td><span class='badge " + cls + "'>" + (row.success ? "Success" : "Error") + "</span></td>";
                    tr += "<td>" + row.status_code + "</td>";
                    tr += "<td>" + (row.success ? "-" : ("<pre>" + htmlspecialchars(row.error) + "</pre>")) + "</td>"
                    tr += "<td>" + row.response_time_ms + "</td>";
                    tr += "</tr>";
                });

            }
            tbody.append(tr);
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

    loadDashboard();
    setInterval(loadDashboard, (1000 * 60));
});