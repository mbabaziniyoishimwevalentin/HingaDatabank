<?php
require_once '../includes/auth_check.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>
<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50">
    <div class="container mx-auto px-4 py-6 flex gap-6">
        <!-- Sidebar -->
        <div class="w-full max-w-xs">
            <?php include 'admin_sidebar.php'; ?>
        </div>
        <!-- Main Content -->
        <div class="flex-1" id="main-content">
            <?php include 'dashboard_content.php'; ?>
        </div>
    </div>
</div>
<!-- Notification Container -->
<div id="notification-container" style="position:fixed;top:24px;right:24px;z-index:9999;"></div>
<script>
function showNotification(message) {
    const container = document.getElementById('notification-container');
    const notif = document.createElement('div');
    notif.className = 'bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg mb-2 animate-fade-in';
    notif.style.transition = 'opacity 0.5s';
    notif.innerText = message;
    container.appendChild(notif);
    setTimeout(() => {
        notif.style.opacity = 0;
        setTimeout(() => notif.remove(), 500);
    }, 2500);
}

function loadPage(page, push=true) {
    fetch(page)
        .then(response => response.text())
        .then(html => {
            document.getElementById('main-content').innerHTML = html;
            if (window.feather) feather.replace();
            showNotification('Loaded: ' + getPageTitle(page));
            if (push) {
                history.pushState({page: page}, '', '?page=' + encodeURIComponent(page));
            }
            // SPA: Re-initialize page-specific JS if present
            if (typeof initAddInstitutionPage === 'function' && page === 'add_institution.php') {
                initAddInstitutionPage();
            }
            if (typeof initUsersPage === 'function' && page === 'manage_users.php') {
                initUsersPage();
            }
            if (typeof initCropsPage === 'function' && page === 'crops.php') {
                initCropsPage();
            }
            if (typeof initHarvestsPage === 'function' && page === 'harvests.php') {
                initHarvestsPage();
            }
            if (typeof initLivestockPage === 'function' && page === 'livestock.php') {
                initLivestockPage();
            }
            if (typeof initLoanRequestsPage === 'function' && page === 'loan_requests.php') {
                initLoanRequestsPage();
            }
        });
}

function getPageTitle(page) {
    switch(page) {
        case 'dashboard_content.php': return 'Dashboard';
        case 'add_institution.php': return 'Add Institution';
        case 'manage_users.php': return 'Manage Users';
        case 'crops.php': return 'Crops';
        case 'harvests.php': return 'Harvests';
        case 'livestock.php': return 'Livestock';
        case 'loan_requests.php': return 'Loan Requests';
        case 'reports.php': return 'Reports';
        default: return page;
    }
}

document.querySelectorAll('.sidebar-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const page = this.getAttribute('data-page');
        loadPage(page, true);
    });
});

window.addEventListener('popstate', function(event) {
    const page = (event.state && event.state.page) ? event.state.page : 'dashboard_content.php';
    loadPage(page, false);
});

// On initial load, check for ?page=... in URL
(function() {
    const params = new URLSearchParams(window.location.search);
    const page = params.get('page');
    if (page && page !== 'dashboard_content.php') {
        loadPage(page, false);
    }
})();
</script>
<style>
@keyframes fade-in { from { opacity: 0; transform: translateY(-10px);} to { opacity: 1; transform: none; } }
.animate-fade-in { animation: fade-in 0.4s; }
</style>
<script>if(window.feather) feather.replace();</script>
<?php require_once '../includes/footer.php'; ?>
