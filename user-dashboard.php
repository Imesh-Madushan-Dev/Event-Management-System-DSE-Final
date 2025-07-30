<?php
session_start();
require_once 'backend/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: login.html');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Check for payment success
$payment_success = isset($_GET['payment']) && $_GET['payment'] === 'success';
$success_ticket_code = $_GET['ticket_code'] ?? '';
$active_tab = $_GET['tab'] ?? 'events';

// Get user's tickets
$tickets_query = "SELECT t.*, e.name as event_name, e.description, e.img_url, e.branch 
                  FROM Tickets t 
                  JOIN Events e ON t.event_id = e.event_id 
                  WHERE t.user_id = $user_id 
                  ORDER BY t.purchase_date DESC";
$tickets_result = mysqli_query($conn, $tickets_query);

// Get all events
$events_query = "SELECT e.*, 
                        (SELECT COUNT(*) FROM Event_Likes el WHERE el.event_id = e.event_id) as like_count,
                        (SELECT COUNT(*) FROM Event_Attendance ea WHERE ea.event_id = e.event_id) as attendance_count,
                        (SELECT COUNT(*) FROM Event_Likes el WHERE el.event_id = e.event_id AND el.user_id = $user_id) as user_liked,
                        (SELECT COUNT(*) FROM Event_Attendance ea WHERE ea.event_id = e.event_id AND ea.user_id = $user_id) as user_attending
                 FROM Events e 
                 ORDER BY e.created_date DESC";
$events_result = mysqli_query($conn, $events_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NIBM Events</title>
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
                </div>
                <div class="flex items-center space-x-6">
                    <span class="text-gray-600">Welcome, <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($user_name); ?></span></span>
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
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Student Dashboard</h1>
            <p class="text-gray-600">Discover and engage with campus events</p>
        </div>

        <!-- Dashboard Tabs -->
        <div class="mb-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button class="tab-btn <?php echo $active_tab === 'events' ? 'active' : ''; ?> py-2 px-1 border-b-2 <?php echo $active_tab === 'events' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500'; ?> font-medium text-sm hover:text-gray-700 hover:border-gray-300" data-tab="events">
                        <i class="fas fa-calendar mr-2"></i>All Events
                    </button>
                    <button class="tab-btn <?php echo $active_tab === 'tickets' ? 'active' : ''; ?> py-2 px-1 border-b-2 <?php echo $active_tab === 'tickets' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500'; ?> font-medium text-sm hover:text-gray-700 hover:border-gray-300" data-tab="tickets">
                        <i class="fas fa-ticket-alt mr-2"></i>My Tickets
                    </button>
                </nav>
            </div>
        </div>

        <!-- Events Tab -->
        <div id="events-tab" class="tab-content <?php echo $active_tab === 'tickets' ? 'hidden' : ''; ?>">
            <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-6">
                <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl hover:-translate-y-2 transition-all">
                    <div class="h-48 bg-gradient-to-br from-purple-400 to-blue-500 relative">
                        <?php if ($event['img_url']): ?>
                            <img src="<?php echo htmlspecialchars($event['img_url']); ?>" alt="Event Image" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-4xl text-white opacity-50"></i>
                            </div>
                        <?php endif; ?>
                        <div class="absolute top-4 right-4">
                            <span class="bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium text-gray-700">
                                <?php echo htmlspecialchars($event['branch']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($event['name']); ?></h3>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?php echo htmlspecialchars($event['description']); ?></p>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span><i class="fas fa-heart mr-1"></i><?php echo $event['like_count']; ?></span>
                                <span><i class="fas fa-users mr-1"></i><?php echo $event['attendance_count']; ?></span>
                            </div>
                            <?php if ($event['price'] > 0): ?>
                                <span class="text-lg font-semibold text-purple-600">LKR <?php echo number_format($event['price'], 2); ?></span>
                            <?php else: ?>
                                <span class="text-lg font-semibold text-green-600">Free</span>
                            <?php endif; ?>
                        </div>

                        <div class="flex space-x-2">
                            <button onclick="toggleLike(<?php echo $event['event_id']; ?>)" 
                                    class="flex-1 py-2 px-4 rounded-lg font-medium text-sm transition-all <?php echo $event['user_liked'] ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?>"
                                    id="like-btn-<?php echo $event['event_id']; ?>">
                                <i class="fas fa-heart mr-1"></i><?php echo $event['user_liked'] ? 'Liked' : 'Like'; ?>
                            </button>
                            <button onclick="toggleAttendance(<?php echo $event['event_id']; ?>)" 
                                    class="flex-1 py-2 px-4 rounded-lg font-medium text-sm transition-all <?php echo $event['user_attending'] ? 'bg-green-100 text-green-600' : 'bg-purple-100 text-purple-600 hover:bg-purple-200'; ?>"
                                    id="attend-btn-<?php echo $event['event_id']; ?>">
                                <i class="fas fa-check mr-1"></i><?php echo $event['user_attending'] ? 'Attending' : 'Attend'; ?>
                            </button>
                            <button onclick="buyTicket(<?php echo $event['event_id']; ?>, <?php echo $event['price']; ?>)" 
                                    class="py-2 px-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg font-medium text-sm hover:shadow-lg transition-all">
                                <i class="fas fa-ticket-alt mr-1"></i>Buy
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Tickets Tab -->
        <div id="tickets-tab" class="tab-content <?php echo $active_tab === 'events' ? 'hidden' : ''; ?>">
            <?php if (mysqli_num_rows($tickets_result) > 0): ?>
                <div class="grid lg:grid-cols-2 gap-6">
                    <?php while ($ticket = mysqli_fetch_assoc($tickets_result)): ?>
                    <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($ticket['event_name']); ?></h3>
                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($ticket['branch']); ?></p>
                                <p class="text-xs text-gray-500">Purchased: <?php echo date('M j, Y', strtotime($ticket['purchase_date'])); ?></p>
                            </div>
                            <div class="text-right">
                                <?php if ($ticket['price'] > 0): ?>
                                    <span class="text-lg font-semibold text-purple-600">LKR <?php echo number_format($ticket['price'], 2); ?></span>
                                <?php else: ?>
                                    <span class="text-lg font-semibold text-green-600">Free Entry</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Ticket Code</p>
                                    <p class="text-xs text-gray-500 font-mono"><?php echo htmlspecialchars($ticket['ticket_code']); ?></p>
                                </div>
                                <button onclick="generateQR('<?php echo $ticket['ticket_code']; ?>', '<?php echo htmlspecialchars($ticket['event_name']); ?>')" 
                                        class="bg-purple-100 text-purple-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-purple-200 transition-colors">
                                    <i class="fas fa-qrcode mr-1"></i>Show QR
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-ticket-alt text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Tickets Yet</h3>
                    <p class="text-gray-600">Purchase tickets for events to see them here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div id="qr-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl p-8 max-w-sm w-full mx-4">
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" id="qr-title">Event Ticket</h3>
                <div id="qr-code" class="mb-4 flex justify-center min-h-[200px] items-center"></div>
                <p class="text-sm text-gray-600 mb-4" id="qr-code-text"></p>
                <div class="flex space-x-3">
                    <button onclick="downloadQR()" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition-colors">
                        <i class="fas fa-download mr-1"></i>Download
                    </button>
                    <button onclick="closeQRModal()" class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-purple-700 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alert-container" class="fixed top-4 right-4 z-50"></div>

    <script src="assets/js/user-dashboard.js"></script>
    <script>
        // Payment success handling
        <?php if ($payment_success): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showAlert('Payment successful! Your ticket has been confirmed.', 'success');
            // Auto-switch to tickets tab if not already there
            <?php if ($active_tab !== 'tickets'): ?>
            setTimeout(() => {
                document.querySelector('[data-tab="tickets"]').click();
            }, 1000);
            <?php endif; ?>
        });
        <?php endif; ?>

        // Enhanced showAlert function
        function showAlert(message, type) {
            const alertContainer = document.getElementById("alert-container");
            const alertElement = document.createElement("div");
            alertElement.className = `alert alert-${type} animate-slideUp mb-4`;
            alertElement.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-current opacity-70 hover:opacity-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            alertContainer.appendChild(alertElement);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertElement.parentElement) {
                    alertElement.remove();
                }
            }, 5000);
        }

        // Download QR code function
        function downloadQR() {
            const qrImage = document.querySelector('#qr-code img');
            if (qrImage) {
                const link = document.createElement('a');
                link.href = qrImage.src;
                link.download = 'ticket-qr-code.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
    </script>
</body>
</html>
