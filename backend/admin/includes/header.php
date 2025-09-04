<button class="mobile-menu-toggle" id="mobileMenuToggle">☰</button>
<div class="mobile-overlay" id="mobileOverlay"></div>

<header class="admin-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle">☰</button>
        <div class="breadcrumb">
            <span class="breadcrumb-item">Admin</span>
            <span class="breadcrumb-separator">→</span>
            <span class="breadcrumb-item current">
                <?php 
                $page = basename($_SERVER['PHP_SELF'], '.php');
                echo ucfirst($page);
                ?>
            </span>
        </div>
    </div>
    
    <div class="header-right">
        <div class="header-actions">
            <a href="../../front_end/" target="_blank" class="view-site-btn" title="View Portfolio">
                <span class="icon">🌐</span>
                <span class="text">View Site</span>
            </a>
            
            <div class="admin-menu">
                <button class="admin-menu-toggle" id="adminMenuToggle">
                    <span class="admin-avatar">👤</span>
                    <span class="admin-name"><?php echo $_SESSION['admin_username']; ?></span>
                    <span class="dropdown-arrow">▼</span>
                </button>
                
                <div class="admin-dropdown" id="adminDropdown">
                    <a href="profile.php" class="dropdown-item">
                        <span class="icon">⚙️</span>
                        <span class="text">Settings</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item logout">
                        <span class="icon">🚪</span>
                        <span class="text">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
// Mobile menu functionality
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const mobileOverlay = document.getElementById('mobileOverlay');
const sidebar = document.querySelector('.sidebar');

function toggleMobileMenu() {
    sidebar.classList.toggle('mobile-open');
    mobileOverlay.classList.toggle('show');
    document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
}

function closeMobileMenu() {
    sidebar.classList.remove('mobile-open');
    mobileOverlay.classList.remove('show');
    document.body.style.overflow = '';
}

mobileMenuToggle.addEventListener('click', toggleMobileMenu);
mobileOverlay.addEventListener('click', closeMobileMenu);

// Close mobile menu when clicking sidebar links
document.querySelectorAll('.sidebar-menu a').forEach(link => {
    link.addEventListener('click', closeMobileMenu);
});

// Sidebar toggle for desktop
const sidebarToggle = document.getElementById('sidebarToggle');
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        document.body.classList.toggle('sidebar-collapsed');
    });
}

// Admin menu toggle
const adminMenuToggle = document.getElementById('adminMenuToggle');
const adminDropdown = document.getElementById('adminDropdown');

if (adminMenuToggle) {
    adminMenuToggle.addEventListener('click', function() {
        adminDropdown.classList.toggle('show');
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.admin-menu')) {
        adminDropdown.classList.remove('show');
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        closeMobileMenu();
        document.body.classList.remove('sidebar-collapsed');
    }
});

// Touch swipe to close mobile menu
let startX = 0;
let startY = 0;

document.addEventListener('touchstart', function(e) {
    startX = e.touches[0].clientX;
    startY = e.touches[0].clientY;
});

document.addEventListener('touchmove', function(e) {
    if (!startX || !startY) return;
    
    const xDiff = startX - e.touches[0].clientX;
    const yDiff = startY - e.touches[0].clientY;
    
    // If swiping left and menu is open, close it
    if (Math.abs(xDiff) > Math.abs(yDiff) && xDiff > 50) {
        if (sidebar.classList.contains('mobile-open')) {
            closeMobileMenu();
        }
    }
    
    startX = 0;
    startY = 0;
});
</script>

<!-- Mobile Responsive Enhancement Script -->
<script src="js/mobile-responsive.js"></script>
