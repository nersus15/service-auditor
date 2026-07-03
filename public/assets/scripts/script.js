$(document).ready(function () {
    var host = location.host;
    var protocol = location.protocol
    var baseurl = protocol + "//" + host
    var interval = null;
    var selectedUrl = null;
    var filters = {
        "date": 'all',
        "limit": -1,
        "status": 'all',
        "autoreload": 'off',
    }

    // load settings
    loadSettings();

    // URL selector handler
    $("#urlSelector").on("change", function() {
        selectedUrl = $(this).val();
        $("#currentUrl").text(selectedUrl);
        saveLocal("selectedUrl", selectedUrl);
        loadDashboard();
    });

    // Load saved URL from localStorage
    var savedUrl = localStorage.getItem("selectedUrl");
    var currentUrl = $("#urlSelector").val();

    if (savedUrl) {
        selectedUrl = savedUrl;
        $("#urlSelector").val(savedUrl);
        $("#currentUrl").text(savedUrl);
    }else if(currentUrl != ""){
        saveLocal("selectedUrl", currentUrl);
    }


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
        var params = {};
        if (selectedUrl) {
            params.url = selectedUrl; 
        }

        for (var key in filters) {
            params[key.toLowerCase()] = filters[key];
        }

        var queryString = $.param(params);
        if (queryString) {
            url += "?" + queryString;
        }

        
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
                tr ="<tr><td colspan='4'>" + text + "</td></tr>";
            } else {
                results.forEach(row => {
                    tr += "<tr data-result='" + encodeURIComponent(JSON.stringify(row)) + "'>";
                    var cls = row.success ? 'ok' : 'bad';

                    tr += "<td>" + new Date(row.timestamp).toLocaleString() + "</td>";
                    tr += "<td><span class='badge " + cls + "'>" + (row.success ? "Success" : "Error") + "</span></td>";
                    tr += "<td>" + row.status_code + "</td>";
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

    const modal = $('#detailModal');
    const closeModal = $('#closeModal');

    function openModal(result) {
        $('#detailTime').text(new Date(result.timestamp).toLocaleString());
        $('#detailUrl').text(result.url || '-');
        $('#detailStatus').text(result.success ? 'Success' : 'Error');
        $('#detailCode').text(result.status_code);
        $('#detailLatency').text(result.response_time_ms + ' ms');
        $('#detailError').text(result.error || '-');
        $('#detailBody').text(result.body_excerpt || '-');
        modal.addClass('open');
    }

    function closeModalWindow() {
        modal.removeClass('open');
    }

    closeModal.on('click', closeModalWindow);
    modal.on('click', function(event) {
        if (event.target === this) {
            closeModalWindow();
        }
    });

    $(document).on('click', '#table-res tbody tr', function() {
        const encoded = $(this).attr('data-result');
        if (!encoded) {
            return;
        }

        try {
            const result = JSON.parse(decodeURIComponent(encoded));
            openModal(result);
        } catch (err) {
            console.error('Unable to parse row detail:', err);
        }
    });

    $("#applyFilter").click(function(){
        var fDate = $("#filterDate").val();
        var fLimit = $("#filterLimit").val();
        var fStatus = $("#filterStatus").val();
        var fReload = $("#autoReload").val();

        if(fDate == "custom"){
            filters.date = $("#dateFrom").val() + " - " + $("#dateTo").val();
        }else{
            filters.date = fDate;
        }

        if (fLimit == "unlimited"){
            filters.limit = -1;
        }else{
            filters.limit = parseInt(fLimit);
        }

        filters.status = fStatus
        filters.autoreload = fReload;


        saveLocal("settings", filters);

        loadDashboard();

        if(interval){
            clearInterval(interval);
        }

        if(filters.autoreload && filters.autoreload != 'off'){
            var m = filters.autoreload.replace("m", "");
            interval = setInterval(loadDashboard, (1000 * 60 * parseInt(m)));
        }
    });

    function loadSettings(){
        var t = getLocal("settings");
        var fDate = $("#filterDate");
        var fLimit = $("#filterLimit");
        var fStatus = $("#filterStatus");
        var fReload = $("#autoReload");

        if(t != null) {
            filters = t;
        }

        fReload.find("option[value='"+ filters.autoreload +"']").prop('selected', true).parent().trigger('change');

        if (filters.date){
            var arr = filters.date.split(" - ");
        }

        if(arr && arr.length > 1){
            fDate.find("option[value='custom']").prop('selected', true);
            fDate.trigger('change');

            $("#dateFrom").val(arr[0]).trigger('change');
            $("#dateTo").val(arr[1]).trigger('change');
        }else{
            fDate.find("option[value='"+ filters.date +"']").prop('selected', true).parent().trigger('change');
        }

        if(filters.limit == -1){
            fLimit.find("option[value='unlimited']").prop('selected', true).parent().trigger('change');
        }else{
            fLimit.find("option[value='"+ filters.limit +"']").prop('selected', true).parent().trigger('change');
        }

        fStatus.find("option[value='"+ filters.status +"']").prop('selected', true).parent().trigger('change');
    }


    loadDashboard();

    if(filters.autoreload && filters.autoreload != 'off'){
        var m = filters.autoreload.replace("m", "");
        interval = setInterval(loadDashboard, (1000 * 60 * parseInt(m)));
    }
});