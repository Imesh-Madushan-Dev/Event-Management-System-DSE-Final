<?php
session_start();
require_once 'backend/db.php';

// ── Auth guard ──────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.html');
    exit();
}

$admin_id   = $_SESSION['user_id'];
$admin_name = $_SESSION['user_name'];

// ── Statistics ──────────────────────────────────
$stats_query = "SELECT
                    (SELECT COUNT(*) FROM Events)  AS total_events,
                    (SELECT COUNT(*) FROM Users)    AS total_users,
                    (SELECT COUNT(*) FROM Tickets)  AS total_tickets,
                    (SELECT SUM(price) FROM Tickets) AS total_revenue";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// ── All events ──────────────────────────────────
$events_query = "SELECT e.*,
                    (SELECT COUNT(*) FROM Event_Likes el WHERE el.event_id = e.event_id)      AS like_count,
                    (SELECT COUNT(*) FROM Event_Attendance ea WHERE ea.event_id = e.event_id)  AS attendance_count,
                    (SELECT COUNT(*) FROM Tickets t WHERE t.event_id = e.event_id)             AS ticket_count
                 FROM Events e
                 ORDER BY e.created_date DESC";
$events_result = mysqli_query($conn, $events_query);
$event_count   = mysqli_num_rows($events_result);

// ── All users ───────────────────────────────────
$users_result = mysqli_query($conn, "SELECT * FROM Users ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - NIBM Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <script>
      tailwind.config = {
        theme: { extend: { fontFamily: { inter: ["Inter","system-ui","sans-serif"] }, borderRadius: { "4xl": "2rem" } } },
      };
    </script>
</head>
<body class="font-inter bg-gray-50/50 min-h-screen">

    <!-- ── Navigation ────────────────────────────── -->
    <nav class="bg-white/90 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white text-sm"></i>
                    </div>
                    <span class="text-lg font-bold text-gray-900">NIBM Events</span>
                    <span class="bg-purple-100 text-purple-700 px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wide uppercase">Admin</span>
                </div>
                <div class="flex items-center gap-5">
                    <span class="text-sm text-gray-500">Welcome, <span class="font-semibold text-gray-900"><?= htmlspecialchars($admin_name) ?></span></span>
                    <a href="backend/auth.php?action=logout" class="inline-flex items-center gap-1.5 text-red-600 hover:text-red-700 text-sm font-medium transition-colors">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- ── Header ────────────────────────────────── -->
        <div class="mb-8">
            <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 mb-1">Admin Dashboard</h1>
            <p class="text-gray-500 text-sm">Manage events and users across all NIBM branches</p>
        </div>

        <!-- ── Statistics Cards ──────────────────────── -->
        <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-5 mb-8">
            <!-- Total Events -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Events</p>
                        <p class="text-2xl font-extrabold text-gray-900"><?= $stats['total_events'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-lg text-purple-600"></i>
                    </div>
                </div>
            </div>
            <!-- Total Users -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Users</p>
                        <p class="text-2xl font-extrabold text-gray-900"><?= $stats['total_users'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-users text-lg text-blue-600"></i>
                    </div>
                </div>
            </div>
            <!-- Tickets Sold -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Tickets Sold</p>
                        <p class="text-2xl font-extrabold text-gray-900"><?= $stats['total_tickets'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-ticket-alt text-lg text-emerald-600"></i>
                    </div>
                </div>
            </div>
            <!-- Total Revenue -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Revenue</p>
                        <p class="text-2xl font-extrabold text-gray-900">LKR <?= number_format($stats['total_revenue'] ?? 0, 2) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-lg text-amber-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Tabs ──────────────────────────────────── -->
        <div class="mb-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex gap-8">
                    <button class="tab-btn active border-purple-500 text-purple-600 py-3 px-1 border-b-2 font-semibold text-sm hover:text-gray-700 hover:border-gray-300 transition-colors" data-tab="events">
                        <i class="fas fa-calendar mr-2"></i>Manage Events
                    </button>
                    <button class="tab-btn border-transparent text-gray-500 py-3 px-1 border-b-2 font-semibold text-sm hover:text-gray-700 hover:border-gray-300 transition-colors" data-tab="users">
                        <i class="fas fa-users mr-2"></i>Manage Users
                    </button>
                </nav>
            </div>
        </div>

        <!-- ═══════════ Events Tab ═══════════ -->
        <div id="events-tab" class="tab-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-gray-900">Event Management</h2>
                <button onclick="openEventModal()"
                        class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm hover:shadow-lg hover:shadow-purple-200 transition-all">
                    <i class="fas fa-plus mr-2"></i>Add Event
                </button>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/70">
                            <tr>
                                <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Event</th>
                                <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Branch</th>
                                <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Engagement</th>
                                <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            <?php if ($event_count > 0): ?>
                                <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <?php if ($event['img_url']): ?>
                                                <img class="h-10 w-10 rounded-xl object-cover" src="<?= htmlspecialchars($event['img_url']) ?>" alt="" />
                                            <?php else: ?>
                                                <div class="h-10 w-10 rounded-xl bg-purple-50 flex items-center justify-center">
                                                    <i class="fas fa-calendar text-purple-500 text-sm"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($event['name']) ?></p>
                                                <p class="text-xs text-gray-400"><?= date('M j, Y', strtotime($event['created_date'])) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-purple-50 text-purple-700">
                                            <?= htmlspecialchars($event['branch']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= $event['price'] > 0 ? 'LKR ' . number_format($event['price'], 2) : '<span class="text-emerald-600">Free</span>' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex gap-4 text-xs text-gray-500">
                                            <span><i class="fas fa-heart text-red-400 mr-1"></i><?= $event['like_count'] ?></span>
                                            <span><i class="fas fa-users text-blue-400 mr-1"></i><?= $event['attendance_count'] ?></span>
                                            <span><i class="fas fa-ticket-alt text-emerald-400 mr-1"></i><?= $event['ticket_count'] ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex gap-1">
                                            <button onclick="editEvent(<?= $event['event_id'] ?>)"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-purple-600 hover:bg-purple-50 transition-colors">
                                                <i class="fas fa-edit text-sm"></i>
                                            </button>
                                            <button onclick="deleteEvent(<?= $event['event_id'] ?>)"
                                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-red-500 hover:bg-red-50 transition-colors">
                                                <i class="fas fa-trash text-sm"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <i class="fas fa-calendar-alt text-2xl text-gray-300"></i>
                                        </div>
                                        <p class="text-base font-bold text-gray-900 mb-1">No events yet</p>
                                        <p class="text-sm text-gray-500 mb-4">Create your first event to get started</p>
                                        <button onclick="openEventModal()" class="bg-purple-600 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-purple-700 transition-colors">
                                            Create Event
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ═══════════ Users Tab ═══════════ -->
        <div id="users-tab" class="tab-content hidden">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900">User Management</h2>
            </div>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/70">
                            <tr>
                                <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center">
                                            <span class="text-xs font-bold text-purple-700"><?= strtoupper(substr($user['name'], 0, 2)) ?></span>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($user['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= htmlspecialchars($user['email']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="deleteUser(<?= $user['user_id'] ?>)"
                                            class="text-red-500 hover:text-red-700 text-sm font-medium transition-colors">
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

    <!-- ── Event Modal ───────────────────────────── -->
    <div id="event-modal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-900" id="modal-title">Add New Event</h3>
                <button onclick="closeEventModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="event-form" action="backend/events.php" method="POST">
                <input type="hidden" name="action" value="create" />
                <input type="hidden" name="event_id" id="event_id" />

                <div class="space-y-4">
                    <div>
                        <label for="event_name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Event Name</label>
                        <input type="text" id="event_name" name="name" required
                               class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl bg-gray-50/50 focus:border-purple-400 focus:bg-white focus:outline-none transition-all text-sm" />
                    </div>
                    <div>
                        <label for="event_description" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Description</label>
                        <textarea id="event_description" name="description" rows="3"
                                  class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl bg-gray-50/50 focus:border-purple-400 focus:bg-white focus:outline-none transition-all text-sm resize-vertical"></textarea>
                    </div>
                    <div>
                        <label for="event_img_url" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Image URL</label>
                        <input type="url" id="event_img_url" name="img_url"
                               class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl bg-gray-50/50 focus:border-purple-400 focus:bg-white focus:outline-none transition-all text-sm" />
                    </div>
                    <div>
                        <label for="event_price" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Price (LKR)</label>
                        <input type="number" id="event_price" name="price" min="0" step="0.01"
                               class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl bg-gray-50/50 focus:border-purple-400 focus:bg-white focus:outline-none transition-all text-sm" />
                    </div>
                    <div>
                        <label for="event_branch" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Branch</label>
                        <select id="event_branch" name="branch" required
                                class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl bg-gray-50/50 focus:border-purple-400 focus:bg-white focus:outline-none transition-all text-sm">
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

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeEventModal()"
                            class="flex-1 py-3 px-4 border-2 border-gray-100 text-gray-600 rounded-2xl font-semibold text-sm hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-3 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-2xl font-semibold text-sm hover:shadow-lg hover:shadow-purple-200 transition-all">
                        <span id="submit-text">Create Event</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Alert Container ───────────────────────── -->
    <div id="alert-container" class="fixed top-4 right-4 z-[60] w-80"></div>

    <script src="assets/js/admin-dashboard.js"></script>
</body>
</html>
