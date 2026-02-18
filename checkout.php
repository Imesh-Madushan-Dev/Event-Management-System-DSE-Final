<?php
session_start();
require_once 'backend/db.php';

// ── Auth guard ──────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: login.html');
    exit();
}

// ── Get ticket parameters ───────────────────────
$ticket_code = $_GET['ticket_code'] ?? '';
if (empty($ticket_code)) {
    header('Location: user-dashboard.php');
    exit();
}

// ── Fetch ticket with event details ─────────────
$ticket_query = "SELECT t.*, e.name AS event_name, e.description, e.img_url, e.branch, e.created_date
                 FROM Tickets t
                 JOIN Events e ON t.event_id = e.event_id
                 WHERE t.ticket_code = '" . mysqli_real_escape_string($conn, $ticket_code) . "'
                   AND t.user_id = " . intval($_SESSION['user_id']);
$ticket_result = mysqli_query($conn, $ticket_query);
$ticket = mysqli_fetch_assoc($ticket_result);

if (!$ticket) {
    header('Location: user-dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Checkout - NIBM Events</title>
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
                <a href="user-dashboard.php" class="inline-flex items-center gap-1.5 text-purple-600 hover:text-purple-700 text-sm font-medium transition-colors">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-6 py-10">

        <?php if ($ticket['price'] > 0): ?>
        <!-- ═══════════ Paid Event Checkout ═══════════ -->
        <div class="text-center mb-10">
            <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 mb-1">Complete Your Purchase</h1>
            <p class="text-gray-500 text-sm">Secure your spot at this event</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Event Summary Card -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <?php if ($ticket['img_url']): ?>
                    <img src="<?= htmlspecialchars($ticket['img_url']) ?>" alt="Event" class="w-full h-48 object-cover" />
                <?php else: ?>
                    <div class="w-full h-48 bg-gradient-to-br from-purple-400 to-indigo-500 flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-4xl text-white/50"></i>
                    </div>
                <?php endif; ?>
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-1"><?= htmlspecialchars($ticket['event_name']) ?></h2>
                    <p class="text-sm text-gray-500 mb-3"><?= htmlspecialchars($ticket['branch']) ?> Branch</p>
                    <p class="text-sm text-gray-500 leading-relaxed mb-5"><?= htmlspecialchars($ticket['description']) ?></p>
                    <div class="border-t border-gray-100 pt-4 flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-600">Ticket Price</span>
                        <span class="text-xl font-extrabold text-purple-600">LKR <?= number_format($ticket['price'], 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Payment Form Card -->
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-5">Payment Information</h2>

                <form id="payment-form">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Cardholder Name</label>
                            <input type="text" required placeholder="John Doe"
                                   class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl bg-gray-50/50 focus:border-purple-400 focus:bg-white focus:outline-none transition-all text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Card Number</label>
                            <input type="text" required placeholder="1234 5678 9012 3456" maxlength="19" id="card-number"
                                   class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl bg-gray-50/50 focus:border-purple-400 focus:bg-white focus:outline-none transition-all text-sm font-mono" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Expiry</label>
                                <input type="text" required placeholder="MM/YY" maxlength="5" id="card-expiry"
                                       class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl bg-gray-50/50 focus:border-purple-400 focus:bg-white focus:outline-none transition-all text-sm font-mono" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">CVV</label>
                                <input type="text" required placeholder="123" maxlength="3"
                                       class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl bg-gray-50/50 focus:border-purple-400 focus:bg-white focus:outline-none transition-all text-sm font-mono" />
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-5">
                            <div class="flex justify-between items-center mb-5">
                                <span class="text-sm font-bold text-gray-900">Total Amount</span>
                                <span class="text-2xl font-extrabold text-purple-600">LKR <?= number_format($ticket['price'], 2) ?></span>
                            </div>
                            <button type="submit"
                                    class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3.5 rounded-2xl font-semibold text-sm hover:shadow-lg hover:shadow-purple-200 transition-all">
                                <i class="fas fa-lock mr-2"></i>Complete Payment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php else: ?>
        <!-- ═══════════ Free Event Confirmation ═══════════ -->
        <div class="max-w-lg mx-auto text-center">
            <div class="w-20 h-20 bg-emerald-50 rounded-3xl flex items-center justify-center mx-auto mb-5">
                <i class="fas fa-check text-3xl text-emerald-600"></i>
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900 mb-1">Ticket Booked!</h1>
            <p class="text-gray-500 text-sm mb-8">Your free event ticket has been confirmed</p>

            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden mb-8">
                <?php if ($ticket['img_url']): ?>
                    <img src="<?= htmlspecialchars($ticket['img_url']) ?>" alt="Event" class="w-full h-48 object-cover" />
                <?php endif; ?>
                <div class="p-6 text-center">
                    <h2 class="text-xl font-bold text-gray-900 mb-1"><?= htmlspecialchars($ticket['event_name']) ?></h2>
                    <p class="text-sm text-purple-600 font-medium mb-2"><?= htmlspecialchars($ticket['branch']) ?> Branch</p>
                    <span class="inline-block bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold">Free Entry</span>

                    <div class="border-t border-gray-100 mt-5 pt-5 grid grid-cols-2 gap-4 text-left text-sm">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Ticket Code</p>
                            <p class="font-mono font-medium text-gray-800 text-xs"><?= htmlspecialchars($ticket['ticket_code']) ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">Booked</p>
                            <p class="font-medium text-gray-800 text-xs"><?= date('M j, Y', strtotime($ticket['purchase_date'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button onclick="generateQR('<?= $ticket['ticket_code'] ?>', '<?= htmlspecialchars($ticket['event_name']) ?>')"
                        class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-3 rounded-2xl font-semibold text-sm hover:shadow-lg hover:shadow-purple-200 transition-all">
                    <i class="fas fa-qrcode mr-2"></i>Show QR Code
                </button>
                <a href="user-dashboard.php?tab=tickets"
                   class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-semibold text-sm hover:bg-emerald-700 transition-colors">
                    <i class="fas fa-ticket-alt mr-2"></i>View My Tickets
                </a>
                <a href="user-dashboard.php"
                   class="text-gray-600 hover:text-gray-900 px-6 py-3 rounded-2xl font-semibold text-sm transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>Dashboard
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── QR Code Modal ─────────────────────────── -->
    <div id="qr-modal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl">
            <div class="text-center">
                <h3 class="text-lg font-bold text-gray-900 mb-4" id="qr-title">Event Ticket</h3>
                <div id="qr-code" class="mb-4 flex justify-center min-h-[200px] items-center"></div>
                <p class="text-sm text-gray-500 mb-5" id="qr-code-text"></p>
                <div class="flex gap-3">
                    <button onclick="downloadQR()"
                            class="flex-1 bg-emerald-600 text-white px-4 py-2.5 rounded-xl font-semibold text-sm hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-download mr-1"></i>Download
                    </button>
                    <button onclick="closeQRModal()"
                            class="flex-1 bg-gray-100 text-gray-700 px-4 py-2.5 rounded-xl font-semibold text-sm hover:bg-gray-200 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Alert Container ───────────────────────── -->
    <div id="alert-container" class="fixed top-4 right-4 z-[60] w-80"></div>

    <script>
    "use strict";

    // ── Card number formatting ──────────────────
    const cardInput = document.getElementById("card-number");
    if (cardInput) {
        cardInput.addEventListener("input", (e) => {
            let v = e.target.value.replace(/\D/g, "").substring(0, 16);
            e.target.value = v.replace(/(.{4})/g, "$1 ").trim();
        });
    }

    // ── Expiry formatting ───────────────────────
    const expiryInput = document.getElementById("card-expiry");
    if (expiryInput) {
        expiryInput.addEventListener("input", (e) => {
            let v = e.target.value.replace(/\D/g, "").substring(0, 4);
            if (v.length > 2) v = v.substring(0, 2) + "/" + v.substring(2);
            e.target.value = v;
        });
    }

    // ── Payment form handler ────────────────────
    document.getElementById("payment-form")?.addEventListener("submit", (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        btn.disabled = true;

        setTimeout(() => {
            window.location.href = 'user-dashboard.php?payment=success&ticket_code=<?= $ticket['ticket_code'] ?>&tab=tickets';
        }, 2000);
    });

    // ── QR Code generation ──────────────────────
    function generateQR(ticketCode, eventName) {
        const modal = document.getElementById("qr-modal");
        const container = document.getElementById("qr-code");
        document.getElementById("qr-title").textContent = eventName;
        document.getElementById("qr-code-text").textContent = "Ticket: " + ticketCode;

        container.innerHTML = '<i class="fas fa-spinner fa-spin text-2xl text-purple-600"></i>';
        modal.classList.remove("hidden");
        modal.classList.add("flex");

        const size = 200;
        const url = "https://api.qrserver.com/v1/create-qr-code/?size=" + size + "x" + size + "&data=" + encodeURIComponent(ticketCode) + "&format=png&margin=10";
        const img = new Image();
        img.src = url;
        img.alt = "QR Code";
        img.className = "mx-auto rounded-xl border-2 border-gray-100";
        img.width = size;
        img.height = size;
        img.onload = () => { container.innerHTML = ""; container.appendChild(img); };
        img.onerror = () => {
            container.innerHTML = '<div class="bg-gray-50 p-6 rounded-2xl text-center"><i class="fas fa-qrcode text-4xl text-gray-300 mb-3"></i><p class="font-mono text-xs bg-white p-2 rounded-xl border border-gray-100">' + ticketCode + '</p><p class="text-xs text-gray-400 mt-2">Show this code at the event</p></div>';
        };
    }

    function closeQRModal() {
        const modal = document.getElementById("qr-modal");
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    }

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
