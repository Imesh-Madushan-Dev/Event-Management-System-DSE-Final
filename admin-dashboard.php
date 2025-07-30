<?php
session_start();
require_once 'backend/db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.html');
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['user_name'];

// Get statistics
$stats_query = "SELECT 
                    (SELECT COUNT(*) FROM Events) as total_events,
                    (SELECT COUNT(*) FROM Users) as total_users,
                    (SELECT COUNT(*) FROM Tickets) as total_tickets,
                    (SELECT SUM(price) FROM Tickets) as total_revenue";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get events created by this admin
$events_query = "SELECT e.*, 
                        (SELECT COUNT(*) FROM Event_Likes el WHERE el.event_id = e.event_id) as like_count,
                        (SELECT COUNT(*) FROM Event_Attendance ea WHERE ea.event_id = e.event_id) as attendance_count,
                        (SELECT COUNT(*) FROM Tickets t WHERE t.event_id = e.event_id) as ticket_count
                 FROM Events e 
                 WHERE e.admin_id = $admin_id
                 ORDER BY e.created_date DESC";
$events_result = mysqli_query($conn, $events_query);

// Get all users
$users_query = "SELECT * FROM Users ORDER BY name ASC";
$users_result = mysqli_query($conn, $users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NIBM Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="font-inter bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-calendar-alt text-2xl text-purple-600"></i>
                    <span class="text-xl font-bold text-gray-900">NIBM Events</span>
                    <span class="bg-purple-100 text-purple-600 px-2 py-1 rounded-full text-xs font-medium">Admin</span>
                </div>
                <div class="flex items-center space-x-6">
                    <span class="text-gray-600">Welcome, <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($admin_name); ?></span></span>
                    <a href="backend/auth.php?action=logout" class="text-red-600 hover:text-red-700 font-medium">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Dashboard Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Admin Dashboard</h1>
            <p class="text-gray-600">Manage events and users across all NIBM branches</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Events</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_events']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-xl text-purple-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_users']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-xl text-blue-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Tickets Sold</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_tickets']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-ticket-alt text-xl text-green-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">LKR <?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-xl text-yellow-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Tabs -->
        <div class="mb-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button class="tab-btn active py-2 px-1 border-b-2 border-purple-500 font-medium text-sm text-purple-600" data-tab="events">
                        <i class="fas fa-calendar mr-2"></i>Manage Events
                    </button>
                    <button class="tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="users">
                        <i class="fas fa-users mr-2"></i>Manage Users
                    </button>
                </nav>
            </div>
        </div>

        <!-- Events Tab -->
        <div id="events-tab" class="tab-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Event Management</h2>
                <button onclick="openEventModal()" class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-6 py-2 rounded-lg font-medium hover:shadow-lg transition-all">
                    <i class="fas fa-plus mr-2"></i>Add Event
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stats</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php if ($event['img_url']): ?>
                                                <img class="h-10 w-10 rounded-lg object-cover" src="<?php echo htmlspecialchars($event['img_url']); ?>" alt="">
                                            <?php else: ?>
                                                <div class="h-10 w-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                                    <i class="fas fa-calendar text-purple-600"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($event['name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo date('M j, Y', strtotime($event['created_date'])); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        <?php echo htmlspecialchars($event['branch']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $event['price'] > 0 ? 'LKR ' . number_format($event['price'], 2) : 'Free'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex space-x-4">
                                        <span><i class="fas fa-heart text-red-500 mr-1"></i><?php echo $event['like_count']; ?></span>
                                        <span><i class="fas fa-users text-blue-500 mr-1"></i><?php echo $event['attendance_count']; ?></span>
                                        <span><i class="fas fa-ticket-alt text-green-500 mr-1"></i><?php echo $event['ticket_count']; ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editEvent(<?php echo $event['event_id']; ?>)" class="text-purple-600 hover:text-purple-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteEvent(<?php echo $event['event_id']; ?>)" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Users Tab -->
        <div id="users-tab" class="tab-content hidden">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">User Management</h2>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-purple-600">
                                                    <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="deleteUser(<?php echo $user['user_id']; ?>)" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash mr-1"></i>Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Modal -->
    <div id="event-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Add New Event</h3>
                <button onclick="closeEventModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="event-form" action="backend/events.php" method="POST">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="event_id" id="event_id">
                
                <div class="space-y-4">
                    <div>
                        <label for="event_name" class="block text-sm font-medium text-gray-700 mb-2">Event Name</label>
                        <input type="text" id="event_name" name="name" required 
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors">
                    </div>
                    <div>
                        <label for="event_description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="event_description" name="description" rows="3" 
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors resize-vertical"></textarea>
                    </div>
                    <div>
                        <label for="event_img_url" class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
                        <input type="url" id="event_img_url" name="img_url" 
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors">
                    </div>
                    <div>
                        <label for="event_price" class="block text-sm font-medium text-gray-700 mb-2">Price ($)</label>
                        <input type="number" id="event_price" name="price" min="0" step="0.01" 
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors">
                    </div>
                    <div>
                        <label for="event_branch" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                        <select id="event_branch" name="branch" required 
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors">
                            <option value="">Select Branch</option>
                            <option value="Colombo">Colombo</option>
                            <option value="Kandy">Kandy</option>
                            <option value="Galle">Galle</option>
                            <option value="Matara">Matara</option>
                            <option value="Kurunegala">Kurunegala</option>
                            <option value="Ratnapura">Ratnapura</option>
                            <option value="Kalutara">Kalutara</option>
                            <option value="Badulla">Badulla</option>
                        </select>
                    </div>
                </div>

                <div class="flex space-x-4 mt-6">
                    <button type="button" onclick="closeEventModal()" 
                            class="flex-1 py-3 px-4 border-2 border-gray-200 text-gray-700 rounded-lg font-medium hover:border-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 py-3 px-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg font-medium hover:shadow-lg transition-all">
                        <span id="submit-text">Create Event</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/admin-dashboard.js"></script>
</body>
</html>
