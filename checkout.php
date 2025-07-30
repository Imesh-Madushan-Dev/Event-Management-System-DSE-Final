<?php
session_start();
require_once 'backend/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: login.html');
    exit();
}

// Get ticket details from URL parameters
$ticket_code = $_GET['ticket_code'] ?? '';
$event_name = $_GET['event_name'] ?? '';
$price = floatval($_GET['price'] ?? 0);
$ticket_id = $_GET['ticket_id'] ?? '';

if (empty($ticket_code)) {
    header('Location: user-dashboard.php');
    exit();
}

// Get full ticket details from database
$ticket_query = "SELECT t.*, e.name as event_name, e.description, e.img_url, e.branch, e.created_date
                 FROM Tickets t 
                 JOIN Events e ON t.event_id = e.event_id 
                 WHERE t.ticket_code = '$ticket_code' AND t.user_id = {$_SESSION['user_id']}";
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - NIBM Events</title>
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
                    <a href="user-dashboard.php" class="text-purple-600 hover:text-purple-700 font-medium">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <?php if ($ticket['price'] > 0): ?>
        <!-- Paid Event Checkout -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Checkout</h1>
            <p class="text-gray-600">Complete your ticket purchase</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Event Details -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Event Details</h2>
                
                <div class="space-y-4">
                    <?php if ($ticket['img_url']): ?>
                    <img src="<?php echo htmlspecialchars($ticket['img_url']); ?>" alt="Event Image" class="w-full h-48 object-cover rounded-lg">
                    <?php endif; ?>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($ticket['event_name']); ?></h3>
                        <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($ticket['branch']); ?> Branch</p>
                    </div>
                    
                    <p class="text-gray-600"><?php echo htmlspecialchars($ticket['description']); ?></p>
                    
                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-900">Ticket Price:</span>
                            <span class="text-xl font-bold text-purple-600">LKR <?php echo number_format($ticket['price'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Information</h2>
                
                <form id="payment-form">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cardholder Name</label>
                            <input type="text" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors" placeholder="John Doe">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Card Number</label>
                            <input type="text" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                                <input type="text" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">CVV</label>
                                <input type="text" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:outline-none transition-colors" placeholder="123" maxlength="3">
                            </div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-lg font-semibold text-gray-900">Total Amount:</span>
                                <span class="text-2xl font-bold text-purple-600">LKR <?php echo number_format($ticket['price'], 2); ?></span>
                            </div>
                            
                            <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                                <i class="fas fa-credit-card mr-2"></i>Complete Payment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php else: ?>
        <!-- Free Event Confirmation -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-3xl text-green-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Ticket Booked Successfully!</h1>
            <p class="text-gray-600">Your free event ticket has been confirmed</p>
        </div>

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-md p-8">
                <div class="text-center mb-6">
                    <?php if ($ticket['img_url']): ?>
                    <img src="<?php echo htmlspecialchars($ticket['img_url']); ?>" alt="Event Image" class="w-full h-48 object-cover rounded-lg mb-4">
                    <?php endif; ?>
                    
                    <h2 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($ticket['event_name']); ?></h2>
                    <p class="text-purple-600 font-medium"><?php echo htmlspecialchars($ticket['branch']); ?> Branch</p>
                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium mt-2">Free Entry</span>
                </div>
                
                <div class="border-t border-b border-gray-200 py-4 mb-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Ticket Code:</span>
                            <p class="font-mono font-medium"><?php echo htmlspecialchars($ticket['ticket_code']); ?></p>
                        </div>
                        <div>
                            <span class="text-gray-500">Booked Date:</span>
                            <p class="font-medium"><?php echo date('M j, Y', strtotime($ticket['purchase_date'])); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center space-y-4">
                    <button onclick="generateQR('<?php echo $ticket['ticket_code']; ?>', '<?php echo htmlspecialchars($ticket['event_name']); ?>')" 
                            class="bg-purple-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-purple-700 transition-colors">
                        <i class="fas fa-qrcode mr-2"></i>Show QR Code
                    </button>
                    
                    <div class="flex space-x-4 justify-center">
                        <a href="user-dashboard.php?tab=tickets" class="bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700 transition-colors">
                            <i class="fas fa-ticket-alt mr-2"></i>View My Tickets
                        </a>
                        <a href="user-dashboard.php" class="text-purple-600 hover:text-purple-700 font-medium px-6 py-3">
                            <i class="fas fa-arrow-left mr-1"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
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

    <script>
        // Payment form handling
        document.getElementById('payment-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simulate payment processing
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing Payment...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                // Redirect to user dashboard with success message
                window.location.href = 'user-dashboard.php?payment=success&ticket_code=<?php echo $ticket['ticket_code']; ?>&tab=tickets';
            }, 2000);
        });

        // Alternative QR Code generation using QR Server API
        function generateQR(ticketCode, eventName) {
            const modal = document.getElementById('qr-modal');
            const qrContainer = document.getElementById('qr-code');
            const qrTitle = document.getElementById('qr-title');
            const qrText = document.getElementById('qr-code-text');

            // Clear previous QR code
            qrContainer.innerHTML = '';

            // Set title and text
            qrTitle.textContent = eventName;
            qrText.textContent = `Ticket Code: ${ticketCode}`;

            // Show loading
            qrContainer.innerHTML = '<div class="flex justify-center"><i class="fas fa-spinner fa-spin text-2xl text-purple-600"></i></div>';

            // Show modal first
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Generate QR code using QR Server API
            const qrSize = 200;
            const qrData = encodeURIComponent(ticketCode);
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${qrSize}x${qrSize}&data=${qrData}&format=png&margin=10`;

            // Create image element
            const qrImage = document.createElement('img');
            qrImage.src = qrUrl;
            qrImage.alt = 'QR Code';
            qrImage.className = 'mx-auto border-2 border-gray-200 rounded-lg';
            qrImage.style.width = `${qrSize}px`;
            qrImage.style.height = `${qrSize}px`;

            qrImage.onload = function() {
                qrContainer.innerHTML = '';
                qrContainer.appendChild(qrImage);
            };

            qrImage.onerror = function() {
                // Fallback to text-based QR if image fails
                qrContainer.innerHTML = `
                    <div class="bg-gray-100 p-8 rounded-lg text-center">
                        <i class="fas fa-qrcode text-4xl text-gray-400 mb-4"></i>
                        <p class="text-sm text-gray-600 mb-2">QR Code</p>
                        <p class="font-mono text-xs bg-white p-2 rounded border">${ticketCode}</p>
                        <p class="text-xs text-gray-500 mt-2">Show this code at the event</p>
                    </div>
                `;
            };
        }

        function closeQRModal() {
            const modal = document.getElementById('qr-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
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

        // Card number formatting
        document.querySelector('input[placeholder="1234 5678 9012 3456"]')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });

        // Expiry date formatting
        document.querySelector('input[placeholder="MM/YY"]')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>
