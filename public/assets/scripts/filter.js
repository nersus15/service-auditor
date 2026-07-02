document.addEventListener('DOMContentLoaded', function() {
    const filterPanel = document.getElementById('filterPanel');
    const toggleBtn = document.getElementById('toggleFilter');
    const applyBtn = document.getElementById('applyFilter');
    const filterDate = document.getElementById('filterDate');
    const customDateRange = document.getElementById('customDateRange');
    let isFloating = false;
    let isMinimized = false;

    // Toggle filter minimize/expand with button
    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        isMinimized = !isMinimized;
        updateFilterState();
    });

    // Click anywhere on panel when minimized to maximize
    filterPanel.addEventListener('click', function(e) {
        if (isMinimized && e.target !== toggleBtn) {
            isMinimized = false;
            updateFilterState();
        }
    });

    function updateFilterState() {
        if (isMinimized) {
            filterPanel.classList.add('minimized');
            toggleBtn.title = 'Expand';
        } else {
            filterPanel.classList.remove('minimized');
            toggleBtn.title = 'Minimize';
        }
    }

    // Apply filter button handler
    applyBtn.addEventListener('click', function() {
        const filters = {
            date: document.getElementById('filterDate').value,
            limit: document.getElementById('filterLimit').value,
            status: document.getElementById('filterStatus').value,
            autoReload: document.getElementById('autoReload').value,
            dateFrom: document.getElementById('dateFrom').value,
            dateTo: document.getElementById('dateTo').value
        };
        
        // Dispatch custom event with filter data
        window.dispatchEvent(new CustomEvent('filterApplied', { detail: filters }));
        
        // Optional: Log or provide visual feedback
        console.log('Filters applied:', filters);
    });

    // Show custom date range when custom option is selected
    filterDate.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDateRange.style.display = 'flex';
        } else {
            customDateRange.style.display = 'none';
        }
    });

    // Handle scroll event for floating filter panel
    window.addEventListener('scroll', function() {
        const headerHeight = document.querySelector('.hero-card').offsetHeight + 50;
        const shouldFloat = window.scrollY > headerHeight;

        if (shouldFloat && !isFloating) {
            filterPanel.classList.add('floating');
            isFloating = true;
            // Reset to expanded when it becomes floating
            isMinimized = false;
            filterPanel.classList.remove('minimized');
            toggleBtn.title = 'Minimize';
        } else if (!shouldFloat && isFloating) {
            filterPanel.classList.remove('floating');
            isFloating = false;
            // Reset minimize state when returning to normal position
            isMinimized = false;
            filterPanel.classList.remove('minimized');
            toggleBtn.title = 'Minimize';
        }
    });
});
