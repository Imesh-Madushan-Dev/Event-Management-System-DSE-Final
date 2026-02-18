<?php
session_start();
require_once 'backend/db.php';

// ── Auth guard ──────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: login.html');
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// ── Query parameters ────────────────────────────
$payment_success     = isset($_GET['payment']) && $_GET['payment'] === 'success';
$success_ticket_code = $_GET['ticket_code'] ?? '';
$active_tab          = $_GET['tab'] ?? 'events';

// ── Fetch user tickets ──────────────────────────
$tickets_query = "SELECT t.*, e.name AS event_name, e.description, e.img_url, e.branch
                  FROM Tickets t
                  JOIN Events e ON t.event_id = e.event_id
                  WHERE t.user_id = $user_id
                  ORDER BY t.purchase_date DESC";
$tickets_result = mysqli_query($conn, $tickets_query);

// ── Fetch all events with engagement counts ─────
$events_query = "SELECT e.*,
                    (SELECT COUNT(*) FROM Event_Likes el WHERE el.event_id = e.event_id) AS like_count,
                    (SELECT COUNT(*) FROM Event_Attendance ea WHERE ea.event_id = e.event_id) AS attendance_count,
                    (SELECT COUNT(*) FROM Event_Likes el WHERE el.event_id = e.event_id AND el.user_id = $user_id) AS user_liked,
                    (SELECT COUNT(*) FROM Event_Attendance ea WHERE ea.event_id = e.event_id AND ea.user_id = $user_id) AS user_attending
                 FROM Events e
                 ORDER BY e.created_date DESC";
$events_result = mysqli_query($conn, $events_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - NIBM Events</title>
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
                </div>
                <div class="flex items-center gap-5">
                    <span class="text-sm text-gray-500">Welcome, <span class="font-semibold text-gray-900"><?= htmlspecialchars($user_name) ?></span></span>
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
            <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 mb-1">Student Dashboard</h1>
            <p class="text-gray-500 text-sm">Discover and engage with campus events</p>
        </div>

        <!-- ── Tabs ──────────────────────────────────── -->
        <div class="mb-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex gap-8">
                    <button class="tab-btn <?= $active_tab === 'events' ? 'active border-purple-500 text-purple-600' : 'border-transparent text-gray-500' ?> py-3 px-1 border-b-2 font-semibold text-sm hover:text-gray-700 hover:border-gray-300 transition-colors" data-tab="events">
                        <i class="fas fa-calendar mr-2"></i>All Events
                    </button>
                    <button class="tab-btn <?= $active_tab === 'tickets' ? 'active border-purple-500 text-purple-600' : 'border-transparent text-gray-500' ?> py-3 px-1 border-b-2 font-semibold text-sm hover:text-gray-700 hover:border-gray-300 transition-colors" data-tab="tickets">
                        <i class="fas fa-ticket-alt mr-2"></i>My Tickets
                    </button>
                </nav>
            </div>
        </div>

        <!-- ═══════════ Events Tab ═══════════ -->
        <div id="events-tab" class="tab-content <?= $active_tab === 'tickets' ? 'hidden' : '' ?>">
            <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-6">
                <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                    <!-- Image -->
                    <div class="h-48 relative overflow-hidden">
                        <?php if ($event['img_url']): ?>
                            <img src="<?= htmlspecialchars($event['img_url']) ?>" alt="Event" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-4xl text-white/50"></i>
                            </div>
                        <?php endif; ?>
                        <span class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-full text-xs font-semibold text-gray-700">
                            <?= htmlspecialchars($event['branch']) ?>
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="p-5">
                        <h3 class="text-base font-bold text-gray-900 mb-1.5 line-clamp-2"><?= htmlspecialchars($event['name']) ?></h3>
                        <p class="text-gray-500 text-sm mb-4 line-clamp-2 leading-relaxed"><?= htmlspecialchars($event['description']) ?></p>

                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3 text-xs text-gray-400 font-medium">
                                <span><i class="fas fa-heart mr-1"></i><?= $event['like_count'] ?></span>
                                <span><i class="fas fa-users mr-1"></i><?= $event['attendance_count'] ?></span>
                            </div>
                            <?php if ($event['price'] > 0): ?>
                                <span class="text-sm font-bold text-purple-600">LKR <?= number_format($event['price'], 2) ?></span>
                            <?php else: ?>
                                <span class="text-sm font-bold text-emerald-600">Free</span>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <button onclick="toggleLike(<?= $event['event_id'] ?>)"
                                    id="like-btn-<?= $event['event_id'] ?>"
                                    class="flex-1 py-2 px-3 rounded-xl font-semibold text-xs transition-all <?= $event['user_liked'] ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                                <i class="fas fa-heart mr-1"></i><?= $event['user_liked'] ? 'Liked' : 'Like' ?>
                            </button>
                            <button onclick="toggleAttendance(<?= $event['event_id'] ?>)"
                                    id="attend-btn-<?= $event['event_id'] ?>"
                                    class="flex-1 py-2 px-3 rounded-xl font-semibold text-xs transition-all <?= $event['user_attending'] ? 'bg-green-100 text-green-600' : 'bg-purple-100 text-purple-600 hover:bg-purple-200' ?>">
                                <i class="fas fa-check mr-1"></i><?= $event['user_attending'] ? 'Attending' : 'Attend' ?>
                            </button>
                            <button onclick="buyTicket(<?= $event['event_id'] ?>, <?= $event['price'] ?>)"
                                    class="py-2 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold text-xs hover:shadow-lg transition-all">
                                <i class="fas fa-ticket-alt mr-1"></i>Buy
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- ═══════════ Tickets Tab ═══════════ -->
        <div id="tickets-tab" class="tab-content <?= $active_tab === 'events' ? 'hidden' : '' ?>">
            <?php if (mysqli_num_rows($tickets_result) > 0): ?>
                <div class="grid lg:grid-cols-2 gap-6">
                    <?php while ($ticket = mysqli_fetch_assoc($tickets_result)): ?>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-base font-bold text-gray-900 mb-1"><?= htmlspecialchars($ticket['event_name']) ?></h3>
                                <p class="text-sm text-gray-500 mb-1"><?= htmlspecialchars($ticket['branch']) ?></p>
                                <p class="text-xs text-gray-400">Purchased: <?= date('M j, Y', strtotime($ticket['purchase_date'])) ?></p>
                            </div>
                            <div class="text-right">
                                <?php if ($ticket['price'] > 0): ?>
                                    <span class="text-base font-bold text-purple-600">LKR <?= number_format($ticket['price'], 2) ?></span>
                                <?php else: ?>
                                    <span class="text-base font-bold text-emerald-600">Free Entry</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-4 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 mb-0.5">Ticket Code</p>
                                <p class="text-xs text-gray-400 font-mono"><?= htmlspecialchars($ticket['ticket_code']) ?></p>
                            </div>
                            <button onclick="generateQR('<?= $ticket['ticket_code'] ?>', '<?= htmlspecialchars($ticket['event_name']) ?>')"
                                    class="bg-purple-50 text-purple-600 px-4 py-2 rounded-xl text-xs font-semibold hover:bg-purple-100 transition-colors">
                                <i class="fas fa-qrcode mr-1"></i>Show QR
                            </button>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-ticket-alt text-2xl text-gray-300"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-1">No Tickets Yet</h3>
                    <p class="text-sm text-gray-500">Purchase tickets for events to see them here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── QR Code Modal ─────────────────────────── -->
    <div id="qr-modal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <h3 class="text-lg font-bold text-gray-900 mb-4" id="qr-title">Event Ticket</h3>
                <div id="qr-code" class="mb-4 flex justify-center min-h-[200px] items-center"></div>
                <p class="text-sm text-gray-500 mb-5" id="qr-code-text"></p>
                <div class="flex gap-3">
                    <button onclick="downloadQR()" class="flex-1 bg-emerald-600 text-white px-4 py-2.5 rounded-xl font-semibold text-sm hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-download mr-1"></i>Download
                    </button>
                    <button onclick="closeQRModal()" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2.5 rounded-xl font-semibold text-sm hover:bg-gray-200 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Alert Container ───────────────────────── -->
    <div id="alert-container" class="fixed top-4 right-4 z-[60] w-80"></div>

    <script src="assets/js/user-dashboard.js"></script>
    <script>
        // ── Payment success handling ─────────────
        <?php if ($payment_success): ?>
        document.addEventListener("DOMContentLoaded", () => {
            showAlert("Payment successful! Your ticket has been confirmed.", "success");
            <?php if ($active_tab !== 'tickets'): ?>
            setTimeout(() => document.querySelector('[data-tab="tickets"]')?.click(), 1000);
            <?php endif; ?>
        });
        <?php endif; ?>

        // ── Download QR ──────────────────────────
        function downloadQR() {
            const img = document.querySelector("#qr-code img");
            if (!img) return;
            const a = document.createElement("a");
            a.href = img.src;
            a.download = "ticket-qr-code.png";
            a.click();
        }
    </script>
</body>
</html>
