<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';

// Handle search
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_results = [];

if (!empty($search_query)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR brand LIKE ? OR category LIKE ? LIMIT 12");
        $search_param = "%{$search_query}%";
        $stmt->execute([$search_param, $search_param, $search_param]);
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error_message = "Error performing search: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - NapZon</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #0f172a; /* slate-900 */
        }
        .sidebar-active-link {
            background-color: #3b82f6; /* blue-500 */
            color: #ffffff; /* white */
        }
        .card-bg {
             background-color: #1e293b; /* slate-800 */
        }
         .text-gradient-blue {
            background-image: linear-gradient(to right, #93c5fd, #e2e8f0, #93c5fd);
            color: transparent;
            -webkit-background-clip: text;
            background-clip: text;
        }
        .sidebar-logo {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-300">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-slate-800 text-slate-300 flex flex-col">
            <div class="p-6">
                 <img src="assets/NapZon_Logo.png" alt="NapZon Logo" class="sidebar-logo mb-4">
                <p class="text-sm text-slate-400 mt-1">Welcome, <?php echo htmlspecialchars($full_name); ?></p>
            </div>
            <nav class="mt-10 flex-1 px-4 space-y-2">
                <a href="buyer_dashboard.php" class="flex items-center px-4 py-3 rounded-md sidebar-active-link">
                    <i class="fas fa-home w-6 mr-3"></i>
                    <span>Home</span>
                </a>
                <a href="orders.php" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md">
                    <i class="fas fa-shopping-bag w-6 mr-3"></i>
                    <span>My Orders</span>
                </a>
                <a href="wishlist.php" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md">
                    <i class="fas fa-heart w-6 mr-3"></i>
                    <span>Wishlist</span>
                </a>
                <a href="cart.php" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md">
                    <i class="fas fa-shopping-cart w-6 mr-3"></i>
                    <span>Cart</span>
                </a>
                <a href="profile.php" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white rounded-md">
                    <i class="fas fa-user w-6 mr-3"></i>
                    <span>Profile</span>
                </a>
            </nav>
             <div class="p-4 border-t border-slate-700">
                <div class="flex items-center">
                     <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center text-white text-lg font-semibold">
                        <?php echo strtoupper(substr($full_name, 0, 1)); ?>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-semibold text-white"><?php echo htmlspecialchars($full_name); ?></p>
                        <p class="text-xs text-slate-400">Buyer</p>
                    </div>
                </div>
                 <button id="logoutButton" class="mt-4 w-full text-left text-slate-400 hover:text-white flex items-center">
                    <i class="fas fa-power-off w-6 mr-3"></i>
                    <span>Logout</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto bg-slate-900 p-8">
            <!-- Top Bar -->
            <div class="bg-slate-800/50 backdrop-filter backdrop-blur-lg rounded-md shadow-md mb-6 px-6 py-4 flex flex-col md:flex-row justify-between items-center border border-slate-700 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-white">Welcome to NapZon</h2>
                    <p class="text-slate-400 text-sm">Find your perfect pair of shoes</p>
                </div>
                <form action="" method="GET" class="w-full md:w-96">
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="<?php echo htmlspecialchars($search_query); ?>"
                               placeholder="Search for shoes, brands, or categories..." 
                               class="w-full bg-slate-700 text-white rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 border border-slate-600"
                        >
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        <?php if (!empty($search_query)): ?>
                            <a href="buyer_dashboard.php" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-white">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <?php if (!empty($search_query)): ?>
                <!-- Search Results Section -->
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-white">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h3>
                        <a href="buyer_dashboard.php" class="text-blue-400 hover:text-blue-300 text-sm">
                            <i class="fas fa-times mr-1"></i> Clear Search
                        </a>
                    </div>
                    <?php if (!empty($search_results)): ?>
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            <?php foreach ($search_results as $product): ?>
                                <div class="card-bg rounded-lg shadow-md border border-slate-700 overflow-hidden">
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">
                                    <div class="p-4">
                                        <h4 class="text-white font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p class="text-slate-400 text-sm mb-2"><?php echo htmlspecialchars($product['brand']); ?></p>
                                        <p class="text-blue-400 font-bold">₱<?php echo number_format($product['price'], 2); ?></p>
                                        <button class="mt-3 w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-search text-4xl text-slate-600 mb-4"></i>
                            <p class="text-slate-400">No products found matching your search.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Featured Products Section -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-white mb-4">Featured Products</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <?php
                        // Fetch featured products from database
                        try {
                            $stmt = $conn->prepare("SELECT * FROM products WHERE featured = 1 LIMIT 8");
                            $stmt->execute();
                            $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($featured_products as $product) {
                                ?>
                                <div class="card-bg rounded-lg shadow-md border border-slate-700 overflow-hidden">
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-48 object-cover">
                                    <div class="p-4">
                                        <h4 class="text-white font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p class="text-slate-400 text-sm mb-2"><?php echo htmlspecialchars($product['brand']); ?></p>
                                        <p class="text-blue-400 font-bold">₱<?php echo number_format($product['price'], 2); ?></p>
                                        <button class="mt-3 w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                                <?php
                            }
                        } catch(PDOException $e) {
                            echo "<p class='text-red-400'>Error loading featured products</p>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Categories Section -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-white mb-4">Shop by Category</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php
                        $categories = ['Men', 'Women', 'Kids', 'Sport'];
                        foreach ($categories as $category) {
                            ?>
                            <a href="category.php?cat=<?php echo urlencode($category); ?>" class="card-bg rounded-lg p-6 text-center hover:bg-slate-700 transition">
                                <i class="fas fa-shoe-prints text-3xl text-blue-400 mb-2"></i>
                                <h4 class="text-white font-semibold"><?php echo htmlspecialchars($category); ?></h4>
                            </a>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden">
        <div class="bg-slate-800 rounded-lg p-6 w-full max-w-sm shadow-xl border border-slate-700">
            <div class="text-center mb-4">
                <i class="fas fa-power-off text-slate-400 text-3xl mb-3"></i>
                <h3 class="text-xl font-semibold text-white">Confirm Logout</h3>
            </div>
            <p class="text-slate-300 text-center mb-6">Are you sure you want to log out?</p>
            <div class="flex justify-center space-x-4">
                <button id="cancelLogout" class="px-4 py-2 bg-slate-600 text-white rounded-md hover:bg-slate-700 transition">
                    Cancel
                </button>
                <a href="logout.php" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                    Logout
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutButton = document.getElementById('logoutButton');
            const logoutModal = document.getElementById('logoutModal');
            const cancelLogout = document.getElementById('cancelLogout');

            if(logoutButton) {
                logoutButton.addEventListener('click', function() {
                    logoutModal.classList.remove('hidden');
                });
            }

            if(cancelLogout) {
                cancelLogout.addEventListener('click', function() {
                    logoutModal.classList.add('hidden');
                });
            }

            // Close modal if clicking outside
            if(logoutModal) {
                 logoutModal.addEventListener('click', function(e) {
                    if (e.target === logoutModal) {
                        logoutModal.classList.add('hidden');
                    }
                });
            }
             // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    logoutModal.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html> 