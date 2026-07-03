$(document).ready(function() {
    const $filterPanel = $('#filterPanel');
    const $toggleBtn = $('#toggleFilter');
    const $applyBtn = $('#applyFilter');
    const $filterDate = $('#filterDate');
    const $customDateRange = $('#customDateRange');
    
    let isFloating = false;
    let isMinimized = false;

    $toggleBtn.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        isMinimized = !isMinimized;
        updateFilterState();
    });

    $filterPanel.on('click', function(e) {
        // Checking if the target is NOT the toggle button
        if (isMinimized && !$(e.target).is($toggleBtn)) {
            isMinimized = false;
            updateFilterState();
        }
    });

    function updateFilterState() {
        $filterPanel.toggleClass('minimized', isMinimized);
        $toggleBtn.attr('title', isMinimized ? 'Expand' : 'Minimize');
    }

    $applyBtn.on('click', function() {
        const filters = {
            date: $('#filterDate').val(),
            limit: $('#filterLimit').val(),
            status: $('#filterStatus').val(),
            autoReload: $('#autoReload').val(),
            dateFrom: $('#dateFrom').val(),
            dateTo: $('#dateTo').val()
        };
        
        window.dispatchEvent(new CustomEvent('filterApplied', { detail: filters }));
        
        console.log('Filters applied:', filters);
    });

    // Show custom date range when custom option is selected
    $filterDate.on('change', function() {
        if ($(this).val() === 'custom') {
            $customDateRange.css('display', 'flex');
        } else {
            $customDateRange.hide(); // Sets display to none
        }
    });

    // Handle scroll event for floating filter panel
    $(window).on('scroll', function() {
        const $heroCard = $('.hero-card');
        if (!$heroCard.length) return; 

        const headerHeight = $heroCard.outerHeight() + 50;
        const shouldFloat = $(window).scrollTop() > headerHeight;

        if (shouldFloat && !isFloating) {
            $filterPanel.addClass('floating').removeClass('minimized');
            isFloating = true;
            isMinimized = false;
            $toggleBtn.attr('title', 'Minimize');
        } else if (!shouldFloat && isFloating) {
            $filterPanel.removeClass('floating minimized');
            isFloating = false;
            isMinimized = false;
            $toggleBtn.attr('title', 'Minimize');
        }
    });
});