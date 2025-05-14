<?php
$current_page = basename($_SERVER['PHP_SELF'], ".php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<style>
    /* Mobile styles */
    @media screen and (max-width: 768px) {
        aside {
            display: none;
            position: fixed;
            z-index: 50;
            height: 100vh;
        }
        
        aside.active {
            display: flex;
        }
        
        #mobile {
            background-color: #1A1A1B;
            border: 1px solid #3D3D3D;
            display: flex;
            height: fit-content;
            width: 100%;
        }
        
        #mobile div img {
            width: 40px;
        }
        
        #mobile div p {
            font-size: medium;
        }
    }
    
    /* Desktop styles */
    @media screen and (min-width: 769px) {
        aside {
            display: flex;
        }
        
        #mobile {
            display: none;
        }
    }
</style>

<!-- Mobile Navigation -->
<nav id="mobile" class="flex justify-between items-center w-full p-4 md:hidden">
    <div class="flex items-center text-xl font-bold space-x-2">
        <img class="w-4" src="../assets/icons/logo.svg" alt="Event Manager Logo">
        <p class="text-[10px]">Event Manager</p>
    </div>
    <button id="menu-toggle" class="md:hidden">
        <img src="../assets/icons/menu.svg" alt="Menu">
    </button>
</nav>

<!-- Sidebar -->
<aside class="w-64 bg-[#1A1A1B] text-white p-6 flex-col min-h-screen hidden md:flex">
    <div class="flex items-center text-xl font-bold mb-8 space-x-2 gap-4">
        <img class="w-6" src="../assets/icons/logo.svg" alt="Event Manager Logo">
        <p>Event Manager</p>    
    </div>

    <div class="mb-8">
        <h3 class="text-gray-400 text-sm font-semibold uppercase mb-4">GENERAL</h3>
        <ul>
            <li class="mb-3">
                <a href="../dashboard/index.php" class="flex items-center gap-2 text-gray-300 hover:text-white <?php echo ($current_page === 'index' && basename(dirname($_SERVER['PHP_SELF'])) === 'dashboard') ? 'font-bold text-white' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                    Dashboard
                </a>
            </li>
            <li class="mb-3">
                <a href="../events/index.php" class="flex items-center gap-2 text-gray-300 hover:text-white <?php echo ($current_page === 'list' && basename(dirname($_SERVER['PHP_SELF'])) === 'events') ? 'font-bold text-white' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Events
                </a>
            </li>
        </ul>
    </div>

    <div class="mb-8">
        <h3 class="text-gray-400 text-sm font-semibold uppercase mb-4">TOOLS</h3>
        <ul>
            <li class="mb-3">
                <a href="../setting/profile.php" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.78 1.35a2 2 0 0 0 .72 2.73l.05.03a2 2 0 0 1 1 1.74v.44a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.78-1.35a2 2 0 0 0-.72-2.73l-.05-.03a2 2 0 0 1-1-1.74V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    Account & Settings
                </a>
            </li>
            <li class="mb-3">
                <a href="#" class="flex items-center text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-3"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    Help
                </a>
            </li>
        </ul>
    </div>

    <div class="mt-auto pt-6 border-t border-gray-700">
        <div class="flex items-center text-gray-300 space-x-2 py-2">
            <img class="w-10 h-10 rounded-full mr-3" src="https://placehold.co/40x40/4a5568/ffffff?text=<?php echo $_SESSION['user_name']; ?>" alt="User Avatar">
            <div>
                <div class="font-semibold"><?php echo $_SESSION['user_name']; ?></div>
            </div>
        </div>
        <div class="mt-4 text-center w-full" id="logoutProcess">
            <a href="#" class="inline-block px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md w-full">
                Logout
            </a>
        </div>
    </div>
</aside>

<script>
    const logoutProcess = document.getElementById("logoutProcess");
    logoutProcess.addEventListener("click", function() {
        const confirmLogout = confirm("Are you sure you want to logout?");
        if (confirmLogout) {
            window.location.href = "../auth/process_logout.php";
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.querySelector('aside');
        
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                
                if (window.innerWidth <= 768) {
                    if (sidebar.classList.contains('active')) {
                        const overlay = document.createElement('div');
                        overlay.id = 'sidebar-overlay';
                        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-40';
                        overlay.addEventListener('click', function() {
                            sidebar.classList.remove('active');
                            document.body.removeChild(overlay);
                        });
                        document.body.appendChild(overlay);
                    } else {
                        const overlay = document.getElementById('sidebar-overlay');
                        if (overlay) {
                            document.body.removeChild(overlay);
                        }
                    }
                }
            });
        }
    });
</script>