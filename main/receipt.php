<?php
// receipt.php - Booking Confirmation & Payment Receipt
session_start();
include 'db_connect.php';

$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$bookingRef = isset($_GET['ref']) ? trim($_GET['ref']) : '';

$booking = null;

if ($bookingId > 0) {
    $stmt = $conn->prepare("SELECT * FROM booking1 WHERE id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $res = $stmt->get_result();
    $booking = $res->fetch_assoc();
    $stmt->close();
} elseif (!empty($bookingRef)) {
    $stmt = $conn->prepare("SELECT * FROM booking1 WHERE booking_reference = ?");
    $stmt->bind_param("s", $bookingRef);
    $stmt->execute();
    $res = $stmt->get_result();
    $booking = $res->fetch_assoc();
    $stmt->close();
}

if (!$booking) {
    // If no booking found, show friendly message
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Receipt Not Found - TripTrove</title>
        <link rel="stylesheet" href="styles.css">
        <style>
            .not-found-box {
                max-width: 600px;
                margin: 80px auto;
                background: white;
                padding: 40px;
                border-radius: 12px;
                text-align: center;
                box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            }
            .not-found-box h2 { color: #dc3545; margin-bottom: 15px; }
            .not-found-box a {
                display: inline-block;
                margin-top: 20px;
                background: #0056b3;
                color: white;
                padding: 10px 24px;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
            }
        </style>
    </head>
    <body>
        <div class="navbar">
            <div class="logo">TripTrove</div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="category.php">Packages</a></li>
                </ul>
            </nav>
        </div>
        <div class="not-found-box">
            <h2>Booking Receipt Not Found</h2>
            <p>We could not find a booking matching your request. Please verify your booking ID or contact support.</p>
            <a href="index.php">Return to Home</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Fetch itinerary details for more information (transport, departure location, image)
$itinerary = null;
$itinStmt = $conn->prepare("SELECT * FROM itineraries WHERE destination = ? OR package_name = ? LIMIT 1");
$itinStmt->bind_param("ss", $booking['destination'], $booking['destination']);
$itinStmt->execute();
$itinRes = $itinStmt->get_result();
if ($itinRes && $itinRes->num_rows > 0) {
    $itinerary = $itinRes->fetch_assoc();
}
$itinStmt->close();

$travelers = !empty($booking['travelers']) ? intval($booking['travelers']) : 1;
$totalPrice = floatval($booking['price']);
$perPersonRate = $travelers > 0 ? ($totalPrice / $travelers) : $totalPrice;
$bookingRef = !empty($booking['booking_reference']) ? $booking['booking_reference'] : ('TT-' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT));
$paymentId = !empty($booking['payment_id']) ? $booking['payment_id'] : 'PAY_MANUAL_' . $booking['id'];
$paymentStatus = !empty($booking['payment_status']) ? $booking['payment_status'] : 'Paid';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt #<?php echo htmlspecialchars($bookingRef); ?> - TripTrove</title>
    <link rel="icon" type="image/png" href="images/logoTT.png">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- html2pdf for direct PDF file generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --primary: #0056b3;
            --primary-dark: #003d80;
            --secondary: #ff6600;
            --success: #28a745;
            --light-bg: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-dark);
        }

        /* Success Alert Banner */
        .success-banner {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 16px 20px;
            text-align: center;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
        }
        .success-banner i { font-size: 22px; }

        /* Receipt Action Bar */
        .receipt-actions-bar {
            max-width: 900px;
            margin: 25px auto 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 0 15px;
        }

        .btn-receipt {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
        }
        .btn-primary-action {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 86, 179, 0.25);
        }
        .btn-primary-action:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        .btn-secondary-action {
            background: white;
            color: var(--text-dark);
            border: 1px solid var(--border);
        }
        .btn-secondary-action:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        /* Printable Receipt Card */
        .receipt-card {
            max-width: 900px;
            margin: 20px auto 50px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            overflow: hidden;
            position: relative;
        }

        /* Receipt Top Brand Header */
        .receipt-header {
            padding: 35px 40px;
            background: #ffffff;
            border-bottom: 2px dashed #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }

        .receipt-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .receipt-brand img {
            width: 65px;
            height: 65px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }
        .receipt-brand-text h1 {
            font-size: 26px;
            margin: 0;
            color: var(--primary);
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .receipt-brand-text p {
            margin: 4px 0 0;
            font-size: 13px;
            color: var(--text-muted);
        }

        .receipt-meta {
            text-align: right;
        }
        .receipt-badge {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 30px;
            border: 1px solid #bbf7d0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .receipt-meta .ref-number {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }
        .receipt-meta .ref-date {
            font-size: 13px;
            color: var(--text-muted);
            margin: 4px 0 0;
        }

        /* Body Sections */
        .receipt-body {
            padding: 35px 40px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
            background: var(--light-bg);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #eef2f6;
        }

        .info-col h3 {
            font-size: 13px;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.8px;
            margin: 0 0 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-item {
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        .info-item .label {
            color: var(--text-muted);
        }
        .info-item .val {
            font-weight: 600;
            color: var(--text-dark);
            text-align: right;
        }

        /* Payment Gateway Banner */
        .payment-status-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 14px 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .payment-status-strip .gateway {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #1e40af;
        }
        .payment-status-strip .txnid {
            font-family: monospace;
            font-size: 13px;
            background: white;
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid #dbeafe;
            color: #1e3a8a;
        }

        /* Itemized Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background: #f8fafc;
            text-align: left;
            padding: 12px 16px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }
        .items-table td {
            padding: 16px;
            font-size: 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }
        .items-table .text-right {
            text-align: right;
        }
        .item-title {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 4px;
        }
        .item-sub {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* Totals Area */
        .totals-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 35px;
        }
        .totals-box {
            width: 320px;
            border-top: 2px solid var(--border);
            padding-top: 15px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }
        .total-row.grand-total {
            border-top: 2px solid var(--primary);
            margin-top: 10px;
            padding-top: 12px;
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
        }

        /* Terms & Notes */
        .receipt-footer-notes {
            border-top: 1px solid var(--border);
            padding-top: 25px;
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.6;
        }
        .receipt-footer-notes strong {
            color: var(--text-dark);
        }

        /* Watermark stamp */
        .paid-stamp {
            position: absolute;
            top: 220px;
            right: 60px;
            transform: rotate(-12deg);
            border: 3px solid #16a34a;
            color: #16a34a;
            padding: 8px 24px;
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 3px;
            border-radius: 8px;
            opacity: 0.15;
            pointer-events: none;
            user-select: none;
        }

        /* Print styles */
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .navbar, .success-banner, .receipt-actions-bar, footer {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .paid-stamp {
                opacity: 0.3 !important;
            }
        }

        @media (max-width: 768px) {
            .receipt-header {
                flex-direction: column;
                text-align: left;
            }
            .receipt-meta {
                text-align: left;
            }
            .receipt-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="navbar">
            <div class="logo">
                <img src="images/logoTT.png" alt="TripTrove Logo">
                <span>TripTrove</span>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="category.php">Packages</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Top Success Alert -->
    <div class="success-banner">
        <i class="fa-solid fa-circle-check"></i>
        <span><strong>Congratulations!</strong> Your booking for <strong><?php echo htmlspecialchars($booking['destination']); ?></strong> is confirmed and paid.</span>
    </div>

    <!-- Actions Bar -->
    <div class="receipt-actions-bar">
        <a href="category.php" class="btn-receipt btn-secondary-action">
            <i class="fa-solid fa-arrow-left"></i> Explore More Packages
        </a>
        <div style="display:flex; gap:10px;">
            <button onclick="downloadAsPDF()" class="btn-receipt btn-primary-action">
                <i class="fa-solid fa-file-arrow-down"></i> Download PDF
            </button>
            <button onclick="window.print()" class="btn-receipt btn-secondary-action">
                <i class="fa-solid fa-print"></i> Print Receipt
            </button>
        </div>
    </div>

    <!-- Printable Receipt Card -->
    <div class="receipt-card" id="receipt-content">
        <!-- Visual Watermark Stamp -->
        <div class="paid-stamp">PAID</div>

        <!-- Header -->
        <div class="receipt-header">
            <div class="receipt-brand">
                <img src="images/logoTT.png" alt="TripTrove">
                <div class="receipt-brand-text">
                    <h1>TripTrove Tours & Travels</h1>
                    <p>Official Tax Invoice & Tour Confirmation Voucher</p>
                    <p style="font-size:12px; color:#94a3b8;"><i class="fa-solid fa-envelope"></i> support@triptrove.com | <i class="fa-solid fa-phone"></i> +91 98765 43210</p>
                </div>
            </div>
            <div class="receipt-meta">
                <div class="receipt-badge"><i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($paymentStatus); ?></div>
                <div class="ref-number">Ref: <?php echo htmlspecialchars($bookingRef); ?></div>
                <div class="ref-date">Issued: <?php echo date('d M Y, h:i A', strtotime($booking['created_at'])); ?></div>
            </div>
        </div>

        <div class="receipt-body">
            <!-- Payment Gateway Strip -->
            <div class="payment-status-strip">
                <div class="gateway">
                    <i class="fa-solid fa-shield-halved" style="color:#2563eb;"></i>
                    <span>Paid via <strong>Razorpay Payment Gateway</strong></span>
                </div>
                <div>
                    <span style="font-size:13px; color:#475569; margin-right:6px;">Payment ID:</span>
                    <span class="txnid"><?php echo htmlspecialchars($paymentId); ?></span>
                </div>
            </div>

            <!-- Customer & Trip Details Grid -->
            <div class="info-grid">
                <div class="info-col">
                    <h3><i class="fa-solid fa-user"></i> Primary Traveler</h3>
                    <div class="info-item">
                        <span class="label">Full Name:</span>
                        <span class="val"><?php echo htmlspecialchars($booking['firstName'] . ' ' . $booking['lastName']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Email:</span>
                        <span class="val"><?php echo htmlspecialchars($booking['email']); ?></span>
                    </div>
                    <?php if (!empty($booking['phone'])): ?>
                    <div class="info-item">
                        <span class="label">Phone:</span>
                        <span class="val"><?php echo htmlspecialchars($booking['phone']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <span class="label">Total Travelers:</span>
                        <span class="val"><?php echo $travelers; ?> Person<?php echo $travelers > 1 ? 's' : ''; ?></span>
                    </div>
                </div>

                <div class="info-col">
                    <h3><i class="fa-solid fa-map-location-dot"></i> Trip Overview</h3>
                    <div class="info-item">
                        <span class="label">Destination:</span>
                        <span class="val"><?php echo htmlspecialchars($booking['destination']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Duration:</span>
                        <span class="val"><?php echo htmlspecialchars($booking['duration']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Departure Date:</span>
                        <span class="val"><?php echo date('d M Y', strtotime($booking['departure_date'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Return Date:</span>
                        <span class="val"><?php echo date('d M Y', strtotime($booking['arrival_date'])); ?></span>
                    </div>
                    <?php if ($itinerary && !empty($itinerary['transport'])): ?>
                    <div class="info-item">
                        <span class="label">Transport:</span>
                        <span class="val"><?php echo htmlspecialchars($itinerary['transport']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Itemized Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Package Description</th>
                        <th class="text-right">Travelers</th>
                        <th class="text-right">Rate / Person</th>
                        <th class="text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="item-title"><?php echo htmlspecialchars($booking['destination']); ?> Tour Package</div>
                            <div class="item-sub">Duration: <?php echo htmlspecialchars($booking['duration']); ?> | Departure: <?php echo date('d M Y', strtotime($booking['departure_date'])); ?> to <?php echo date('d M Y', strtotime($booking['arrival_date'])); ?></div>
                            <div class="item-sub" style="color:#059669; margin-top:2px;">Includes accommodation, transfers, guided sightseeing & taxes</div>
                        </td>
                        <td class="text-right"><?php echo $travelers; ?></td>
                        <td class="text-right">₹<?php echo number_format($perPersonRate, 2); ?></td>
                        <td class="text-right" style="font-weight:600;">₹<?php echo number_format($totalPrice, 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Totals Area -->
            <div class="totals-container">
                <div class="totals-box">
                    <div class="total-row">
                        <span style="color:var(--text-muted);">Package Subtotal:</span>
                        <span style="font-weight:600;">₹<?php echo number_format($totalPrice, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span style="color:var(--text-muted);">GST / Service Tax:</span>
                        <span style="font-weight:600; color:#059669;">Included (0.00)</span>
                    </div>
                    <div class="total-row">
                        <span style="color:var(--text-muted);">Payment Method:</span>
                        <span style="font-weight:600;">Razorpay Online</span>
                    </div>
                    <div class="total-row grand-total">
                        <span>Total Paid:</span>
                        <span>₹<?php echo number_format($totalPrice, 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Notes & Guidelines -->
            <div class="receipt-footer-notes">
                <p><strong>Important Travel Guidelines:</strong></p>
                <ul style="padding-left: 20px; margin: 8px 0;">
                    <li>Please present a copy of this confirmation voucher along with a valid Government-issued Photo ID (Aadhaar / Passport / Voter ID) at check-in.</li>
                    <li>For flight-inclusive packages, reporting time at the departure airport is strictly 2.5 hours prior to domestic flight departure.</li>
                    <li>Free cancellation is applicable up to 7 days before departure. In case of queries or rescheduling, quote your reference number <strong><?php echo htmlspecialchars($bookingRef); ?></strong> to support.</li>
                </ul>
                <p style="text-align: center; margin-top: 20px; color:#94a3b8;">Thank you for booking with TripTrove! We wish you a safe and memorable journey.</p>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 TripTrove. All rights reserved.</p>
    </footer>

    <script>
        function downloadAsPDF() {
            const element = document.getElementById('receipt-content');
            const opt = {
                margin:       [10, 10, 10, 10],
                filename:     'TripTrove_Receipt_<?php echo htmlspecialchars($bookingRef); ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // Generate and save the PDF
            html2pdf().set(opt).from(element).save().catch(err => {
                console.error("html2pdf error, falling back to window.print():", err);
                window.print();
            });
        }
    </script>
</body>
</html>
