<?php
// booking.php - Travel Booking Checkout with Razorpay Integration
session_start();
include 'db_connect.php';
include 'config_razorpay.php';

$firstName = $lastName = $email = $phone = "";
$destination = $duration = "";
$price = 0.00;
$image = "hero.jpg";
$departureLocation = "Ahmedabad";

// Prefill from user session if logged in
if (isset($_SESSION['user_id'])) {
    $userId = intval($_SESSION['user_id']);
    $userStmt = $conn->prepare("SELECT firstName, lastName, email, phone FROM users WHERE user_id = ?");
    if ($userStmt) {
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $userRes = $userStmt->get_result();
        if ($user = $userRes->fetch_assoc()) {
            $firstName = $user['firstName'];
            $lastName = $user['lastName'];
            $email = $user['email'];
            $phone = isset($user['phone']) ? $user['phone'] : '';
        }
        $userStmt->close();
    }
}

// Fetch package details
$packageName = isset($_GET['package_name']) ? trim($_GET['package_name']) : '';

if (!empty($packageName)) {
    // Check itineraries table
    $itinStmt = $conn->prepare("SELECT * FROM itineraries WHERE package_name = ? LIMIT 1");
    $itinStmt->bind_param("s", $packageName);
    $itinStmt->execute();
    $itinRes = $itinStmt->get_result();
    if ($trip = $itinRes->fetch_assoc()) {
        $destination = $trip['destination'];
        $duration = $trip['duration'];
        $price = floatval($trip['budget']);
        $departureLocation = !empty($trip['departure_location']) ? $trip['departure_location'] : 'Ahmedabad';
        if (!empty($trip['image'])) $image = $trip['image'];
    }
    $itinStmt->close();

    // Check packages table for additional metadata
    $pkgStmt = $conn->prepare("SELECT * FROM packages WHERE package_name = ? LIMIT 1");
    $pkgStmt->bind_param("s", $packageName);
    $pkgStmt->execute();
    $pkgRes = $pkgStmt->get_result();
    if ($pkg = $pkgRes->fetch_assoc()) {
        if (empty($destination)) $destination = $pkg['package_name'];
        if (empty($duration)) $duration = $pkg['duration'];
        if ($price <= 0 && !empty($pkg['price'])) $price = floatval($pkg['price']);
        if (!empty($pkg['image'])) $image = $pkg['image'];
    }
    $pkgStmt->close();
}

if (empty($destination)) {
    $destination = !empty($packageName) ? $packageName : "Selected Tour";
}

// Image path resolution
$imagePath = 'uploads/' . $image;
if (!file_exists($imagePath)) {
    $imagePath = 'images/' . $image;
}
if (!file_exists($imagePath)) {
    $imagePath = 'images/packages-hero.jpg';
}

// Extract days count from duration for arrival date calculation
preg_match('/(\d+)\s*Days?/i', $duration, $dMatches);
$durationDays = !empty($dMatches[1]) ? intval($dMatches[1]) : 5;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo htmlspecialchars($destination); ?> - TripTrove</title>
    <link rel="icon" type="image/png" href="images/logoTT.png">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Razorpay Checkout JavaScript SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        :root {
            --primary: #0056b3;
            --primary-dark: #004085;
            --secondary: #ff6600;
            --secondary-hover: #e65c00;
            --success: #10b981;
            --dark: #1e293b;
            --muted: #64748b;
            --bg-light: #f8fafc;
            --card-border: #e2e8f0;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--dark);
            margin: 0;
            padding: 0;
        }

        /* Hero Header */
        .booking-header {
            background: linear-gradient(135deg, #003875 0%, #0056b3 100%);
            color: white;
            padding: 40px 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .booking-header-inner {
            max-width: 1160px;
            margin: 0 auto;
        }
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 12px;
        }
        .breadcrumb a {
            color: #ffd166;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        .booking-header h1 {
            font-size: 32px;
            margin: 0 0 8px;
            font-weight: 700;
        }
        .booking-header p {
            margin: 0;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
        }

        /* 2-Column Booking Layout */
        .booking-layout {
            max-width: 1160px;
            margin: 35px auto 60px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            align-items: start;
        }

        /* Booking Form Card */
        .booking-form-card {
            background: white;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--card-border);
        }

        .form-section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 10px;
        }
        .form-section-title i {
            color: var(--primary);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-group label .req {
            color: #dc2626;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
            background: #ffffff;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.15);
        }
        .form-control[readonly] {
            background: #f8fafc;
            color: #64748b;
            cursor: not-allowed;
        }

        /* Razorpay Payment Info Banner */
        .razorpay-method-banner {
            background: linear-gradient(135deg, #02042b 0%, #0c2340 100%);
            color: white;
            padding: 18px 22px;
            border-radius: 12px;
            margin: 25px 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }
        .razorpay-logos {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .rzp-badge {
            background: #3399cc;
            color: white;
            font-weight: 800;
            font-size: 14px;
            padding: 4px 10px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }
        .payment-icons {
            display: flex;
            gap: 10px;
            font-size: 18px;
            color: #94a3b8;
        }

        /* Right Column (Sidebar Summary) */
        .booking-sidebar {
            position: sticky;
            top: 25px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .summary-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--card-border);
            overflow: hidden;
        }

        .summary-pkg-img {
            position: relative;
            height: 180px;
            overflow: hidden;
        }
        .summary-pkg-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .summary-pkg-tag {
            position: absolute;
            bottom: 12px;
            left: 14px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            color: white;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 500;
        }

        .summary-body {
            padding: 24px;
        }
        .summary-dest-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin: 0 0 6px;
        }
        .summary-dest-sub {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .price-breakdown {
            border-top: 1px solid #edf2f7;
            border-bottom: 1px solid #edf2f7;
            padding: 16px 0;
            margin-bottom: 20px;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            font-size: 13.5px;
            padding: 5px 0;
            color: #475569;
        }
        .price-row.total-row {
            border-top: 2px dashed #e2e8f0;
            margin-top: 10px;
            padding-top: 12px;
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
        }

        .btn-pay-rzp {
            width: 100%;
            background: linear-gradient(135deg, #0c2340 0%, #0056b3 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 6px 16px rgba(0, 86, 179, 0.3);
            transition: all 0.3s ease;
        }
        .btn-pay-rzp:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 86, 179, 0.4);
            background: linear-gradient(135deg, #091b33 0%, #004085 100%);
        }

        .btn-test-pay {
            width: 100%;
            margin-top: 10px;
            background: #f8fafc;
            color: #475569;
            border: 1px dashed #cbd5e1;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-test-pay:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: var(--dark);
        }

        .guarantee-box {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 12px;
            color: var(--muted);
            margin-top: 14px;
        }
        .guarantee-box i { color: #10b981; }

        /* Processing Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            padding: 35px 40px;
            border-radius: 16px;
            text-align: center;
            max-width: 420px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 18px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        @media (max-width: 900px) {
            .booking-layout { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
            .booking-sidebar { position: static; }
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

<!-- Header Banner -->
<div class="booking-header">
    <div class="booking-header-inner">
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <a href="itinerary.php?package_name=<?php echo urlencode($destination); ?>"><?php echo htmlspecialchars($destination); ?></a>
            <span>/</span>
            <span>Booking & Checkout</span>
        </div>
        <h1>Booking & Razorpay Checkout</h1>
        <p>Complete your passenger details and confirm your reservation with safe, instant payment.</p>
    </div>
</div>

<!-- Main Grid -->
<div class="booking-layout">
    <!-- Form Side -->
    <div class="booking-form-card">
        <form id="bookingForm">
            <!-- Hidden inputs -->
            <input type="hidden" id="basePrice" value="<?php echo $price; ?>">
            <input type="hidden" id="durationDays" value="<?php echo $durationDays; ?>">

            <!-- Section 1: Passenger Info -->
            <div class="form-section-title">
                <i class="fa-solid fa-user-check"></i>
                <span>1. Primary Traveler Details</span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="firstName">First Name <span class="req">*</span></label>
                    <input type="text" id="firstName" name="firstName" class="form-control" placeholder="Enter first name" value="<?php echo htmlspecialchars($firstName); ?>" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name <span class="req">*</span></label>
                    <input type="text" id="lastName" name="lastName" class="form-control" placeholder="Enter last name" value="<?php echo htmlspecialchars($lastName); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email Address (for Receipt) <span class="req">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="example@email.com" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Mobile / WhatsApp Number <span class="req">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="10-digit mobile number" value="<?php echo htmlspecialchars($phone); ?>" required>
                </div>
            </div>

            <!-- Section 2: Trip Configuration -->
            <div class="form-section-title" style="margin-top: 30px;">
                <i class="fa-solid fa-map-location-dot"></i>
                <span>2. Trip Schedule & Travelers</span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="destination">Destination</label>
                    <input type="text" id="destination" name="destination" class="form-control" value="<?php echo htmlspecialchars($destination); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="duration">Tour Duration</label>
                    <input type="text" id="duration" name="duration" class="form-control" value="<?php echo htmlspecialchars($duration); ?>" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="departuredate">Departure Date <span class="req">*</span></label>
                    <input type="date" id="departuredate" name="departuredate" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="arrivaldate">Return Date (Auto-calculated)</label>
                    <input type="date" id="arrivaldate" name="arrivaldate" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="travelers">Number of Travelers / Seats <span class="req">*</span></label>
                <select id="travelers" name="travelers" class="form-control" style="cursor: pointer;">
                    <option value="1" selected>1 Traveler (Solo)</option>
                    <option value="2">2 Travelers (Couple / Duo)</option>
                    <option value="3">3 Travelers</option>
                    <option value="4">4 Travelers (Family / Group)</option>
                    <option value="5">5 Travelers</option>
                    <option value="6">6 Travelers</option>
                </select>
            </div>

            <!-- Razorpay Info Banner -->
            <div class="razorpay-method-banner">
                <div class="razorpay-logos">
                    <span class="rzp-badge">Razorpay</span>
                    <span style="font-size:13px; font-weight:500;">Secured Online Gateway</span>
                </div>
                <div class="payment-icons">
                    <i class="fa-brands fa-cc-visa" title="Visa"></i>
                    <i class="fa-brands fa-cc-mastercard" title="Mastercard"></i>
                    <i class="fa-solid fa-qrcode" title="UPI"></i>
                    <i class="fa-solid fa-building-columns" title="NetBanking"></i>
                </div>
            </div>

            <p style="font-size:12px; color:var(--muted); margin:0;">By proceeding, you agree to TripTrove's terms and cancellation policy. Your booking receipt will be available immediately after payment.</p>
        </form>
    </div>

    <!-- Sidebar Summary -->
    <div class="booking-sidebar">
        <div class="summary-card">
            <div class="summary-pkg-img">
                <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($destination); ?>">
                <div class="summary-pkg-tag"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($duration); ?></div>
            </div>
            <div class="summary-body">
                <h3 class="summary-dest-title"><?php echo htmlspecialchars($destination); ?> Tour</h3>
                <div class="summary-dest-sub">
                    <i class="fa-solid fa-plane-departure" style="color:var(--primary);"></i>
                    <span>Departs from: <strong><?php echo htmlspecialchars($departureLocation); ?></strong></span>
                </div>

                <div class="price-breakdown">
                    <div class="price-row">
                        <span>Rate per person:</span>
                        <span>₹<span id="displayRate"><?php echo number_format($price, 2); ?></span></span>
                    </div>
                    <div class="price-row">
                        <span>Total Travelers:</span>
                        <span><span id="displayTravelers">1</span> Person(s)</span>
                    </div>
                    <div class="price-row">
                        <span>Package Subtotal:</span>
                        <span>₹<span id="displaySubtotal"><?php echo number_format($price, 2); ?></span></span>
                    </div>
                    <div class="price-row">
                        <span>GST & Service Taxes:</span>
                        <span style="color:#10b981; font-weight:600;">Included (₹0)</span>
                    </div>
                    <div class="price-row total-row">
                        <span>Total Payable:</span>
                        <span>₹<span id="displayTotal"><?php echo number_format($price, 2); ?></span></span>
                    </div>
                </div>

                <!-- Main Action Buttons -->
                <button type="button" class="btn-pay-rzp" id="payRazorpayBtn">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Pay with Razorpay</span>
                </button>

                <!-- Demo Simulation Button for Testing without live card -->
                <button type="button" class="btn-test-pay" id="simulatePayBtn" title="Instant test booking without live gateway">
                    <i class="fa-solid fa-flask"></i> Complete Payment (Test / Demo Mode)
                </button>

                <div class="guarantee-box">
                    <i class="fa-solid fa-lock"></i>
                    <span>100% Safe 256-bit Encrypted Checkout</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading / Processing Modal -->
<div class="modal-overlay" id="processingModal">
    <div class="modal-content">
        <div class="spinner"></div>
        <h3 style="margin:0 0 8px; color:var(--dark);">Processing Your Booking...</h3>
        <p style="margin:0; font-size:13.5px; color:var(--muted);">Please wait while we confirm your payment and generate your receipt voucher.</p>
    </div>
</div>

<footer>
    <p>&copy; 2025 TripTrove. All rights reserved.</p>
</footer>

<script>
    // Configuration
    const RAZORPAY_KEY = "<?php echo RAZORPAY_KEY_ID; ?>";
    const basePrice = <?php echo $price; ?>;
    const durationDays = <?php echo $durationDays; ?>;

    // Elements
    const travelersSelect = document.getElementById("travelers");
    const departureDateInput = document.getElementById("departuredate");
    const arrivalDateInput = document.getElementById("arrivaldate");
    const displayTravelers = document.getElementById("displayTravelers");
    const displaySubtotal = document.getElementById("displaySubtotal");
    const displayTotal = document.getElementById("displayTotal");
    const payBtn = document.getElementById("payRazorpayBtn");
    const simulateBtn = document.getElementById("simulatePayBtn");
    const modal = document.getElementById("processingModal");

    // Dynamic Price Calculation
    function updatePricing() {
        const travelers = parseInt(travelersSelect.value) || 1;
        const total = basePrice * travelers;
        displayTravelers.innerText = travelers;
        displaySubtotal.innerText = total.toLocaleString('en-IN', { minimumFractionDigits: 2 });
        displayTotal.innerText = total.toLocaleString('en-IN', { minimumFractionDigits: 2 });
    }

    travelersSelect.addEventListener("change", updatePricing);

    // Auto-calculate arrival date based on duration
    departureDateInput.addEventListener("change", function() {
        if (!this.value) return;
        const depDate = new Date(this.value);
        depDate.setDate(depDate.getDate() + durationDays);

        const yyyy = depDate.getFullYear();
        const mm = String(depDate.getMonth() + 1).padStart(2, '0');
        const dd = String(depDate.getDate()).padStart(2, '0');
        arrivalDateInput.value = `${yyyy}-${mm}-${dd}`;
    });

    // Form Validator
    function validateForm() {
        const firstName = document.getElementById("firstName").value.trim();
        const lastName = document.getElementById("lastName").value.trim();
        const email = document.getElementById("email").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const departure = departureDateInput.value;
        const arrival = arrivalDateInput.value;

        if (!firstName || !lastName || !email || !phone || !departure || !arrival) {
            alert("Please complete all required fields (Name, Email, Phone, and Travel Dates).");
            return false;
        }

        // Email basic validation
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            alert("Please enter a valid email address.");
            return false;
        }

        return {
            firstName,
            lastName,
            email,
            phone,
            destination: document.getElementById("destination").value,
            duration: document.getElementById("duration").value,
            departuredate: departure,
            arrivaldate: arrival,
            travelers: parseInt(travelersSelect.value),
            price: basePrice * parseInt(travelersSelect.value)
        };
    }

    // Submit Booking to Backend
    async function submitBooking(bookingData, paymentId, paymentStatus = 'Paid') {
        modal.style.display = 'flex';
        bookingData.payment_id = paymentId;
        bookingData.payment_status = paymentStatus;

        try {
            const res = await fetch('save_booking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(bookingData)
            });
            const data = await res.json();
            if (data.success) {
                // Redirect straight to receipt
                window.location.href = data.redirect_url;
            } else {
                modal.style.display = 'none';
                alert("Booking error: " + (data.message || "Could not save booking."));
            }
        } catch (err) {
            modal.style.display = 'none';
            console.error(err);
            alert("Network error occurred while confirming booking.");
        }
    }

    // Razorpay Checkout Trigger
    payBtn.addEventListener("click", function(e) {
        e.preventDefault();
        const formData = validateForm();
        if (!formData) return;

        const totalAmountInPaise = Math.round(formData.price * 100);

        // Check if Razorpay SDK loaded
        if (typeof Razorpay === 'undefined') {
            alert("Razorpay SDK could not be loaded. Completing with demo simulation mode.");
            submitBooking(formData, 'PAY_OFFLINE_' + Date.now());
            return;
        }

        const options = {
            "key": RAZORPAY_KEY,
            "amount": totalAmountInPaise,
            "currency": "INR",
            "name": "TripTrove Travel Services",
            "description": "Tour Booking: " + formData.destination + " (" + formData.travelers + " Traveler/s)",
            "image": "images/logoTT.png",
            "handler": function(response) {
                // Payment Succeeded!
                const paymentId = response.razorpay_payment_id || ('PAY_RZP_' + Date.now());
                submitBooking(formData, paymentId, 'Paid');
            },
            "prefill": {
                "name": formData.firstName + " " + formData.lastName,
                "email": formData.email,
                "contact": formData.phone
            },
            "theme": {
                "color": "#0056b3"
            },
            "modal": {
                "ondismiss": function() {
                    console.log("Checkout modal dismissed by user.");
                }
            }
        };

        try {
            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function(response) {
                alert("Payment Failed: " + (response.error.description || "Payment could not be processed."));
            });
            rzp.open();
        } catch (err) {
            console.error("Razorpay initiation error:", err);
            // Fallback for invalid keys during local development
            if (confirm("Razorpay Test Sandbox reported key initialization error. Would you like to complete this test booking in Demo Mode and generate your Receipt?")) {
                submitBooking(formData, 'PAY_DEMO_' + Date.now());
            }
        }
    });

    // Test / Demo Mode Trigger (for effortless testing)
    simulateBtn.addEventListener("click", function(e) {
        e.preventDefault();
        const formData = validateForm();
        if (!formData) return;

        const demoPaymentId = 'PAY_DEMO_' + Math.random().toString(36).substring(2, 10).toUpperCase();
        submitBooking(formData, demoPaymentId, 'Paid');
    });

    // Set default departure date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomY = tomorrow.getFullYear();
    const tomM = String(tomorrow.getMonth() + 1).padStart(2, '0');
    const tomD = String(tomorrow.getDate()).padStart(2, '0');
    departureDateInput.value = `${tomY}-${tomM}-${tomD}`;
    departureDateInput.dispatchEvent(new Event('change'));
</script>

</body>
</html>
