<?php if(session_status()===PHP_SESSION_NONE) session_start(); ?>
 <!-- Start Navigation -->
 <div class="header header-transparent">
    <div class="container">
        <nav id="navigation" class="navigation navigation-landscape">
            <div class="nav-header">
                <a class="nav-brand text-logo exchange" href="index.php">
                    <img src="<?php echo $static_url; ?>/img/logo-light.png" class="logo" alt="Multi Lists" style="height: 50px;">
                    <img src="<?php echo $static_url; ?>/img/logo-dark.png" class="fixed-logo" alt="Multi Lists" style="height: 50px;">
                </a>
                <div class="nav-toggle"></div>
                <div class="mobile_nav">
                    <ul>
                        <li>
                            <?php if (!empty($_SESSION['dev_id'])): ?>
                        <a href="developer-dashboard.php" class="text-muted">
                                <span class="svg-icon svg-icon-2hx">
                                    <svg width="35" height="35" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3" d="M16.5 9C16.5 13.125 13.125 16.5 9 16.5C4.875 16.5 1.5 13.125 1.5 9C1.5 4.875 4.875 1.5 9 1.5C13.125 1.5 16.5 4.875 16.5 9Z" fill="currentColor"/>
                                        <path d="M9 16.5C10.95 16.5 12.75 15.75 14.025 14.55C13.425 12.675 11.4 11.25 9 11.25C6.6 11.25 4.57499 12.675 3.97499 14.55C5.24999 15.75 7.05 16.5 9 16.5Z" fill="currentColor"/>
                                        <rect x="7" y="6" width="4" height="4" rx="2" fill="currentColor"/>
                                    </svg>
                                </span>
                            </a>
                    <?php else: ?>
                        <a href="log-in.php" class="text-muted">
                                <span class="svg-icon svg-icon-2hx">
                                    <svg width="35" height="35" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3" d="M16.5 9C16.5 13.125 13.125 16.5 9 16.5C4.875 16.5 1.5 13.125 1.5 9C1.5 4.875 4.875 1.5 9 1.5C13.125 1.5 16.5 4.875 16.5 9Z" fill="currentColor"/>
                                        <path d="M9 16.5C10.95 16.5 12.75 15.75 14.025 14.55C13.425 12.675 11.4 11.25 9 11.25C6.6 11.25 4.57499 12.675 3.97499 14.55C5.24999 15.75 7.05 16.5 9 16.5Z" fill="currentColor"/>
                                        <rect x="7" y="6" width="4" height="4" rx="2" fill="currentColor"/>
                                    </svg>
                                </span>
                            </a>
                    <?php endif; ?>
                        </li>
                        <li>
                            <a href="submit-property.php" class="text-primary">
                                <span class="svg-icon svg-icon-2hx">
                                    <svg width="35" height="35" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="currentColor"/>
                                        <rect x="10.8891" y="17.8033" width="12" height="2" rx="1" transform="rotate(-90 10.8891 17.8033)" fill="currentColor"/>
                                        <rect x="6.01041" y="10.9247" width="12" height="2" rx="1" fill="currentColor"/>
                                    </svg>
                                </span>	
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling" aria-controls="offcanvasScrolling">
                                <span class="svg-icon svg-icon-2hx">
                                    <svg width="22" height="22" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect y="6" width="16" height="3" rx="1.5" fill="currentColor"/>
                                        <rect opacity="0.3" y="12" width="8" height="3" rx="1.5" fill="currentColor"/>
                                        <rect opacity="0.3" width="12" height="3" rx="1.5" fill="currentColor"/>
                                    </svg>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="nav-menus-wrapper" style="transition-property: none;">
                <ul class="nav-menu">
                
                <li class="relative parent-parent-menu-item">
                        <a href="#" class="home-link">Listing<span class="submenu-indicator"></span></a>
                        <ul class="nav-dropdown nav-submenu">
                            <li><a href="/half-map.php" class="sub-menu-item">Comming Soon Listings</a></li>
                            <li><a href="/active-listings.php" class="sub-menu-item">Active-listings</a></li>
                        </ul>
                    </li>
                    </li>
                    
                    <li class="relative parent-parent-menu-item">
                        <a href="concierge.php" class="home-link">Wynston Concierge</a>
                    </li>
                    
                    <li class="relative parent-parent-menu-item">
                        <a href="blog.php" class="home-link">Articles</a>
                    </li>
                    
                    <li class="relative parent-parent-menu-item">
                        <a href="about-us.php" class="home-link">About Us</a>
                    </li>
                    
                    
                </ul>
                
                <ul class="nav-menu nav-menu-social align-to-right">
                    
                    <li style="position:relative;" class="dev-dropdown-wrap">
                    <?php
                    $__nav_logo = ''; $__nav_name = '';
                    if (!empty($_SESSION['dev_id'])) {
                        try {
                            if (isset($pdo)) {
                                $__s = $pdo->prepare("SELECT logo_path, full_name, company_name FROM developers WHERE id = ? LIMIT 1");
                                $__s->execute([$_SESSION['dev_id']]);
                                $__r = $__s->fetch(PDO::FETCH_ASSOC);
                                if ($__r) { $__nav_logo = $__r['logo_path'] ?? ''; $__nav_name = $__r['company_name'] ?: $__r['full_name']; }
                            }
                        } catch (Exception $__e) {}
                    }
                    if (!empty($_SESSION['dev_id'])): ?>
                        <a href="developer-dashboard.php" class="fw-medium text-invers dev-nav-btn">
                            <?php if (!empty($__nav_logo)): ?>
                                <img src="<?= htmlspecialchars($__nav_logo) ?>" class="dev-nav-avatar" alt="">
                            <?php else: ?>
                                <span class="dev-nav-initials"><?= strtoupper(substr($__nav_name, 0, 1)) ?></span>
                            <?php endif; ?>
                            My Dashboard <i class="fa-solid fa-chevron-down dev-caret"></i>
                        </a>
                        <div class="dev-nav-dropdown">
                            <a href="developer-dashboard.php"><i class="fa-solid fa-gauge"></i>Dashboard</a>
                            <a href="developer-profile.php"><i class="fa-solid fa-address-card"></i>My Profile</a>
                            <a href="change-password.php"><i class="fa-solid fa-lock"></i>Change Password</a>
                            <div class="dev-nav-divider"></div>
                            <a href="dev-logout.php" class="dev-nav-signout"><i class="fa-solid fa-sign-out-alt"></i>Sign Out</a>
                        </div>
                    <?php else: ?>
                        <a href="log-in.php" class="fw-medium text-invers">
                            <span class="svg-icon svg-icon-2hx me-1">
                                <svg width="22" height="22" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.3" d="M16.5 9C16.5 13.125 13.125 16.5 9 16.5C4.875 16.5 1.5 13.125 1.5 9C1.5 4.875 4.875 1.5 9 1.5C13.125 1.5 16.5 4.875 16.5 9Z" fill="currentColor"/>
                                    <path d="M9 16.5C10.95 16.5 12.75 15.75 14.025 14.55C13.425 12.675 11.4 11.25 9 11.25C6.6 11.25 4.57499 12.675 3.97499 14.55C5.24999 15.75 7.05 16.5 9 16.5Z" fill="currentColor"/>
                                    <rect x="7" y="6" width="4" height="4" rx="2" fill="currentColor"/>
                                </svg>
                            </span>
                            SignUp or SignIn
                        </a>
                    <?php endif; ?>
                    </li>
                    <li class="add-listing light">
                        <a href="submit-property.php">
                            <span class="svg-icon svg-icon-muted svg-icon-2hx me-1">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect opacity="0.3" width="12" height="2" rx="1" transform="matrix(-1 0 0 1 15.5 11)" fill="currentColor"/>
                                    <path d="M13.6313 11.6927L11.8756 10.2297C11.4054 9.83785 11.3732 9.12683 11.806 8.69401C12.1957 8.3043 12.8216 8.28591 13.2336 8.65206L16.1592 11.2526C16.6067 11.6504 16.6067 12.3496 16.1592 12.7474L13.2336 15.3479C12.8216 15.7141 12.1957 15.6957 11.806 15.306C11.3732 14.8732 11.4054 14.1621 11.8756 13.7703L13.6313 12.3073C13.8232 12.1474 13.8232 11.8526 13.6313 11.6927Z" fill="currentColor"/>
                                    <path d="M8 5V6C8 6.55228 8.44772 7 9 7C9.55228 7 10 6.55228 10 6C10 5.44772 10.4477 5 11 5H18C18.5523 5 19 5.44772 19 6V18C19 18.5523 18.5523 19 18 19H11C10.4477 19 10 18.5523 10 18C10 17.4477 9.55228 17 9 17C8.44772 17 8 17.4477 8 18V19C8 20.1046 8.89543 21 10 21H19C20.1046 21 21 20.1046 21 19V5C21 3.89543 20.1046 3 19 3H10C8.89543 3 8 3.89543 8 5Z" fill="currentColor"/>
                                </svg>
                            </span>Post Property
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-invers" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling" aria-controls="offcanvasScrolling">
                            <span class="svg-icon svg-icon-2hx">
                                <svg width="24" height="24" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect y="6" width="16" height="3" rx="1.5" fill="currentColor"/>
                                    <rect opacity="0.3" y="12" width="8" height="3" rx="1.5" fill="currentColor"/>
                                    <rect opacity="0.3" width="12" height="3" rx="1.5" fill="currentColor"/>
                                </svg>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
<!-- End Navigation -->

<style>
.dev-nav-btn { display:inline-flex; align-items:center; gap:8px; cursor:pointer; }
.dev-nav-avatar { width:28px; height:28px; border-radius:50%; object-fit:cover; border:2px solid rgba(201,168,76,.6); }
.dev-nav-initials { width:28px; height:28px; border-radius:50%; background:#002446; border:2px solid rgba(201,168,76,.6); color:#c9a84c; font-size:12px; font-weight:800; display:inline-flex; align-items:center; justify-content:center; }
.dev-caret { font-size:10px; opacity:.7; transition:transform .2s; }
.dev-dropdown-wrap:hover .dev-caret { transform:rotate(180deg); }
.dev-nav-dropdown { display:none; position:absolute; top:calc(100% + 8px); right:0; background:#fff; border-radius:10px; box-shadow:0 8px 32px rgba(0,0,0,.15); border:1px solid #e8e4dd; min-width:190px; z-index:9999; padding:6px 0; }
.dev-dropdown-wrap:hover .dev-nav-dropdown { display:block; }
.dev-nav-dropdown a { display:flex; align-items:center; gap:10px; padding:10px 16px; font-size:13px; font-weight:600; color:#002446; text-decoration:none; transition:background .15s; }
.dev-nav-dropdown a:hover { background:#f5f7ff; }
.dev-nav-dropdown a i { width:14px; font-size:12px; color:#888; }
.dev-nav-divider { height:1px; background:#f0ece6; margin:4px 0; }
.dev-nav-signout { color:#dc2626 !important; }
.dev-nav-signout i { color:#dc2626 !important; }
</style>
<script>
    const currentPath = window.location.pathname;
    console.log(currentPath);

    // Find and highlight the active submenu item
    const subMenuItems = document.querySelectorAll('.sub-menu-item');
    subMenuItems.forEach((item) => {
        if (item.getAttribute('href') === currentPath) {
            item.classList.add('active');

            // Highlight all parent menus recursively
            let parentMenu = item.closest('.parent-menu-item');
            while (parentMenu && !parentMenu.classList.contains('processed')) {
                const parentLink = parentMenu.querySelector('a');
                if (parentLink) {
                    parentLink.classList.add('active');
                }
                parentMenu.classList.add('processed'); // Mark as processed to avoid re-processing
                parentMenu = parentMenu.closest('.parent-parent-menu-item');
            }

            // Highlight the top-level parent menu
            const topLevelMenu = item.closest('.parent-parent-menu-item');
            if (topLevelMenu) {
                const topLevelLink = topLevelMenu.querySelector('.home-link');
                if (topLevelLink) {
                    topLevelLink.classList.add('active');
                }
            }
        }
    });
</script>