<?php
// itinerary.php - Enhanced Luxury Travel Itinerary Page
session_start();
include 'db_connect.php';

$packageName = isset($_GET['package_name']) ? trim($_GET['package_name']) : '';

if (empty($packageName)) {
    // If not provided, redirect to category page
    header("Location: category.php");
    exit;
}

// Fetch itinerary details
$stmt = $conn->prepare("SELECT * FROM itineraries WHERE package_name = ? LIMIT 1");
$stmt->bind_param("s", $packageName);
$stmt->execute();
$itinResult = $stmt->get_result();
$itinerary = $itinResult->fetch_assoc();
$stmt->close();

// Fetch general package details
$pkgStmt = $conn->prepare("SELECT * FROM packages WHERE package_name = ? LIMIT 1");
$pkgStmt->bind_param("s", $packageName);
$pkgStmt->execute();
$pkgResult = $pkgStmt->get_result();
$package = $pkgResult->fetch_assoc();
$pkgStmt->close();

if (!$itinerary && !$package) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Package Not Found - TripTrove</title>
        <link rel="stylesheet" href="styles.css">
        <style>
            .not-found-wrap { max-width: 600px; margin: 80px auto; background: white; padding: 40px; border-radius: 12px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
            .not-found-wrap h2 { color: #dc3545; }
            .not-found-wrap a { display: inline-block; margin-top: 20px; background: #0056b3; color: white; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; }
        </style>
    </head>
    <body>
        <div class="navbar"><div class="logo">TripTrove</div></div>
        <div class="not-found-wrap">
            <h2>Package Not Found</h2>
            <p>Sorry, we could not find itinerary details for <strong><?php echo htmlspecialchars($packageName); ?></strong>.</p>
            <a href="category.php">View All Packages</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Consolidate data
$title = !empty($package['package_name']) ? $package['package_name'] : $itinerary['package_name'];
$category = !empty($package['category']) ? $package['category'] : 'Featured Tour';
$duration = !empty($itinerary['duration']) ? $itinerary['duration'] : (!empty($package['duration']) ? $package['duration'] : 'Flexible Duration');
$budget = !empty($itinerary['budget']) ? floatval($itinerary['budget']) : (!empty($package['price']) ? floatval($package['price']) : 0);
$departure = !empty($itinerary['departure_location']) ? $itinerary['departure_location'] : 'Selected Cities';
$destination = !empty($itinerary['destination']) ? $itinerary['destination'] : $title;
$transport = !empty($itinerary['transport']) ? $itinerary['transport'] : 'Flights & Ground Transfers';

// Image resolution
$imageName = !empty($itinerary['image']) ? $itinerary['image'] : (!empty($package['image']) ? $package['image'] : 'hero.jpg');
$imagePath = 'uploads/' . $imageName;
if (!file_exists($imagePath)) {
    $imagePath = 'images/' . $imageName;
}
if (!file_exists($imagePath)) {
    $imagePath = 'images/packages-hero.jpg';
}

// Parse Day-by-Day itinerary from description
$rawDescription = !empty($itinerary['description']) ? $itinerary['description'] : (!empty($package['description']) ? $package['description'] : '');

$days = [];
if (!empty($rawDescription)) {
    // Match Day X: ... blocks
    preg_match_all('/(Day\s+\d+:[^\r\n]+)([\s\S]*?)(?=(?:Day\s+\d+:|$))/i', $rawDescription, $matches, PREG_SET_ORDER);
    if (!empty($matches)) {
        foreach ($matches as $match) {
            $dayTitle = trim($match[1]);
            $dayContent = trim($match[2]);
            // Clean separator lines if present
            $dayContent = preg_replace('/_{3,}/', '', $dayContent);
            $days[] = [
                'title' => $dayTitle,
                'content' => $dayContent
            ];
        }
    } else {
        // Fallback if not formatted with "Day X:"
        $paragraphs = preg_split('/\r\n\r\n|\n\n/', $rawDescription);
        foreach ($paragraphs as $idx => $para) {
            if (trim($para)) {
                $days[] = [
                    'title' => 'Itinerary Part ' . ($idx + 1),
                    'content' => trim($para)
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Detailed Itinerary | TripTrove</title>
    <link rel="icon" type="image/png" href="images/logoTT.png">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0056b3;
            --primary-dark: #004085;
            --secondary: #ff6600;
            --secondary-hover: #e65c00;
            --dark: #1e293b;
            --muted: #64748b;
            --bg-light: #f8fafc;
            --card-border: #e2e8f0;
            --success: #10b981;
        }

        body {
            background-color: #f4f7fb;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--dark);
            margin: 0;
            padding: 0;
        }

        /* Hero Banner with Modern Backdrop */
        .itinerary-hero-modern {
            position: relative;
            background: linear-gradient(135deg, rgba(0, 40, 85, 0.88), rgba(0, 86, 179, 0.75)), url('<?php echo htmlspecialchars($imagePath); ?>') center/cover no-repeat;
            color: white;
            padding: 55px 20px 45px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .itinerary-hero-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 15px;
        }
        .breadcrumb a {
            color: #ffd166;
            text-decoration: none;
            transition: color 0.2s;
        }
        .breadcrumb a:hover {
            color: #fff;
            text-decoration: underline;
        }

        .hero-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 20px;
        }

        .hero-title-group h1 {
            font-size: 38px;
            margin: 0 0 10px;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.2;
            color: #ffffff;
        }

        .category-pill {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .hero-meta-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 15px;
        }
        .meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(6px);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .meta-pill i {
            color: #ffcc00;
        }

        /* Main Layout Grid */
        .itinerary-layout-container {
            max-width: 1200px;
            margin: 35px auto 60px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 35px;
            align-items: start;
        }

        /* Left Column Content */
        .itinerary-main-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* Visual Cover Image Card */
        .itinerary-image-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid var(--card-border);
            position: relative;
        }
        .itinerary-image-card img {
            width: 100%;
            height: 380px;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }
        .itinerary-image-card:hover img {
            transform: scale(1.02);
        }
        .image-overlay-badge {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            color: white;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Highlights Summary Grid */
        .highlights-card {
            background: white;
            padding: 26px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid var(--card-border);
        }
        .highlights-card h2 {
            margin: 0 0 20px;
            font-size: 20px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .highlights-card h2 i { color: var(--primary); }

        .highlights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
        }
        .highlight-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: var(--bg-light);
            border-radius: 12px;
            border: 1px solid #edf2f7;
        }
        .highlight-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: rgba(0, 86, 179, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .highlight-text .label {
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            font-weight: 600;
            display: block;
            margin-bottom: 2px;
        }
        .highlight-text .value {
            font-size: 14px;
            font-weight: 700;
            color: var(--dark);
        }

        /* Day-by-Day Timeline */
        .timeline-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid var(--card-border);
        }
        .timeline-card h2 {
            margin: 0 0 25px;
            font-size: 22px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .timeline-card h2 i { color: var(--primary); }

        .itinerary-timeline {
            position: relative;
            padding-left: 35px;
        }
        .itinerary-timeline::before {
            content: '';
            position: absolute;
            left: 14px;
            top: 15px;
            bottom: 20px;
            width: 3px;
            background: linear-gradient(180deg, var(--primary) 0%, #cbd5e1 100%);
            border-radius: 2px;
        }

        .timeline-step {
            position: relative;
            margin-bottom: 28px;
        }
        .timeline-step:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -35px;
            top: 2px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 0 0 4px rgba(0, 86, 179, 0.18);
        }

        .timeline-step-card {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 20px 22px;
            border: 1px solid #edf2f7;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .timeline-step-card:hover {
            transform: translateX(4px);
            box-shadow: 0 6px 16px rgba(0, 86, 179, 0.08);
            border-color: #cbd5e1;
        }

        .timeline-step-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--primary);
            margin: 0 0 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .timeline-step-body {
            font-size: 14px;
            line-height: 1.7;
            color: #334155;
            white-space: pre-line;
        }

        /* Inclusions & Exclusions Card */
        .policy-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid var(--card-border);
        }
        .policy-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        .policy-col h3 {
            font-size: 16px;
            margin: 0 0 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .policy-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .policy-col ul li {
            font-size: 13.5px;
            padding: 7px 0;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #475569;
            line-height: 1.5;
        }
        .policy-col.inclusions h3 { color: #059669; }
        .policy-col.inclusions ul li i { color: #059669; margin-top: 3px; }
        .policy-col.exclusions h3 { color: #dc2626; }
        .policy-col.exclusions ul li i { color: #dc2626; margin-top: 3px; }

        /* Right Column (Sticky Sidebar) */
        .itinerary-sidebar {
            position: sticky;
            top: 25px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .booking-cta-card {
            background: white;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.09);
            border: 1px solid var(--card-border);
        }

        .sidebar-price-header {
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 18px;
            margin-bottom: 20px;
        }
        .price-label {
            font-size: 13px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .price-val {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary);
            margin: 6px 0 2px;
        }
        .price-sub {
            font-size: 12.5px;
            color: #059669;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .sidebar-features {
            list-style: none;
            padding: 0;
            margin: 0 0 22px;
        }
        .sidebar-features li {
            font-size: 13.5px;
            padding: 8px 0;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-features li i {
            color: var(--primary);
            font-size: 14px;
        }

        .btn-book-now {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, var(--secondary) 0%, #ff8c00 100%);
            color: white;
            text-align: center;
            padding: 16px 20px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 6px 18px rgba(255, 102, 0, 0.35);
            transition: all 0.3s ease;
            box-sizing: border-box;
            border: none;
            cursor: pointer;
        }
        .btn-book-now:hover {
            background: linear-gradient(135deg, #e65c00 0%, #ff7700 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 102, 0, 0.45);
            color: white;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 15px;
        }
        .secure-badge i { color: #059669; }

        /* Assistance Widget */
        .support-card {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 14px;
            padding: 20px;
            display: flex;
            gap: 14px;
            align-items: center;
        }
        .support-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .support-text h4 {
            margin: 0 0 3px;
            font-size: 14px;
            color: var(--dark);
        }
        .support-text p {
            margin: 0;
            font-size: 12.5px;
            color: var(--muted);
        }
        .support-text a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        @media (max-width: 992px) {
            .itinerary-layout-container {
                grid-template-columns: 1fr;
            }
            .itinerary-sidebar {
                position: static;
            }
            .policy-grid {
                grid-template-columns: 1fr;
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

<!-- Modern Hero Banner -->
<section class="itinerary-hero-modern">
    <div class="itinerary-hero-container">
        <!-- Breadcrumbs -->
        <div class="breadcrumb">
            <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
            <span>/</span>
            <a href="category.php">Packages</a>
            <span>/</span>
            <span><?php echo htmlspecialchars($title); ?></span>
        </div>

        <div class="hero-title-row">
            <div class="hero-title-group">
                <span class="category-pill"><?php echo htmlspecialchars($category); ?></span>
                <h1><?php echo htmlspecialchars($title); ?> Itinerary</h1>
                <div class="hero-meta-pills">
                    <span class="meta-pill"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($duration); ?></span>
                    <span class="meta-pill"><i class="fa-solid fa-plane-departure"></i> From <?php echo htmlspecialchars($departure); ?></span>
                    <span class="meta-pill"><i class="fa-solid fa-location-dot"></i> To <?php echo htmlspecialchars($destination); ?></span>
                    <span class="meta-pill"><i class="fa-solid fa-van-shuttle"></i> <?php echo htmlspecialchars($transport); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Content Grid -->
<div class="itinerary-layout-container">
    <!-- Left Column -->
    <div class="itinerary-main-content">
        <!-- Cover Photo Card -->
        <div class="itinerary-image-card">
            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($title); ?>">
            <div class="image-overlay-badge">
                <i class="fa-solid fa-camera"></i>
                <span>Destination Highlights: <?php echo htmlspecialchars($destination); ?></span>
            </div>
        </div>

        <!-- Quick Summary Box -->
        <div class="highlights-card">
            <h2><i class="fa-solid fa-bolt"></i> Tour Key Highlights</h2>
            <div class="highlights-grid">
                <div class="highlight-item">
                    <div class="highlight-icon-box"><i class="fa-regular fa-calendar-days"></i></div>
                    <div class="highlight-text">
                        <span class="label">Trip Duration</span>
                        <span class="value"><?php echo htmlspecialchars($duration); ?></span>
                    </div>
                </div>
                <div class="highlight-item">
                    <div class="highlight-icon-box"><i class="fa-solid fa-plane"></i></div>
                    <div class="highlight-text">
                        <span class="label">Starting Point</span>
                        <span class="value"><?php echo htmlspecialchars($departure); ?></span>
                    </div>
                </div>
                <div class="highlight-item">
                    <div class="highlight-icon-box"><i class="fa-solid fa-car"></i></div>
                    <div class="highlight-text">
                        <span class="label">Transport Mode</span>
                        <span class="value"><?php echo htmlspecialchars($transport); ?></span>
                    </div>
                </div>
                <div class="highlight-item">
                    <div class="highlight-icon-box"><i class="fa-solid fa-hotel"></i></div>
                    <div class="highlight-text">
                        <span class="label">Accommodation</span>
                        <span class="value">4/5-Star Premium Stays</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Day-by-Day Detailed Timeline -->
        <div class="timeline-card">
            <h2><i class="fa-solid fa-route"></i> Detailed Day-by-Day Schedule</h2>
            <?php if (!empty($days)): ?>
                <div class="itinerary-timeline">
                    <?php foreach ($days as $index => $day): ?>
                        <div class="timeline-step">
                            <div class="timeline-dot"><?php echo ($index + 1); ?></div>
                            <div class="timeline-step-card">
                                <h3 class="timeline-step-title">
                                    <i class="fa-regular fa-compass"></i> <?php echo htmlspecialchars($day['title']); ?>
                                </h3>
                                <div class="timeline-step-body"><?php echo htmlspecialchars($day['content']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color:var(--muted); line-height:1.6;">Detailed daily schedule will be coordinated by our travel specialist upon booking confirmation.</p>
            <?php endif; ?>
        </div>

        <!-- Inclusions & Exclusions Card -->
        <div class="policy-card">
            <div class="policy-grid">
                <div class="policy-col inclusions">
                    <h3><i class="fa-solid fa-circle-check"></i> What's Included</h3>
                    <ul>
                        <li><i class="fa-solid fa-check"></i> Return flight tickets / primary transit as specified</li>
                        <li><i class="fa-solid fa-check"></i> Dedicated private air-conditioned vehicle for sightseeing</li>
                        <li><i class="fa-solid fa-check"></i> Daily breakfast & curated dinners at handpicked hotels</li>
                        <li><i class="fa-solid fa-check"></i> All airport / rail station transfers</li>
                        <li><i class="fa-solid fa-check"></i> Toll taxes, parking charges, and fuel allowances</li>
                        <li><i class="fa-solid fa-check"></i> 24/7 dedicated local tour coordinator</li>
                    </ul>
                </div>
                <div class="policy-col exclusions">
                    <h3><i class="fa-solid fa-circle-xmark"></i> What's Excluded</h3>
                    <ul>
                        <li><i class="fa-solid fa-xmark"></i> Personal expenses like laundry, minibar, phone calls</li>
                        <li><i class="fa-solid fa-xmark"></i> Optional adventure activities (Rafting, Paragliding, Gondola)</li>
                        <li><i class="fa-solid fa-xmark"></i> Entry monument tickets unless explicitly stated</li>
                        <li><i class="fa-solid fa-xmark"></i> Any medical or personal travel insurance fees</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column (Sticky Booking Sidebar) -->
    <div class="itinerary-sidebar">
        <div class="booking-cta-card">
            <div class="sidebar-price-header">
                <div class="price-label">Starting Price From</div>
                <div class="price-val">₹<?php echo number_format($budget, 2); ?></div>
                <div class="price-sub"><i class="fa-solid fa-shield-check"></i> All Taxes & Transfers Included</div>
            </div>

            <ul class="sidebar-features">
                <li><i class="fa-solid fa-check-circle"></i> Instant Online Seat Confirmation</li>
                <li><i class="fa-solid fa-check-circle"></i> Secure Razorpay UPI / Card Payment</li>
                <li><i class="fa-solid fa-check-circle"></i> Printable PDF Booking Receipt</li>
                <li><i class="fa-solid fa-check-circle"></i> Free Cancellation up to 7 days prior</li>
            </ul>

            <a href="booking.php?package_name=<?php echo urlencode($title); ?>" class="btn-book-now">
                <i class="fa-solid fa-ticket"></i> Book This Tour Now
            </a>

            <div class="secure-badge">
                <i class="fa-solid fa-lock"></i>
                <span>Razorpay Secured 256-bit Encrypted Checkout</span>
            </div>
        </div>

        <!-- Help Widget -->
        <div class="support-card">
            <div class="support-icon"><i class="fa-solid fa-headset"></i></div>
            <div class="support-text">
                <h4>Need Travel Advice?</h4>
                <p>Call our tour advisors anytime:</p>
                <a href="tel:+919876543210">+91 98765 43210</a>
            </div>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2025 TripTrove. All rights reserved.</p>
</footer>

</body>
</html>
