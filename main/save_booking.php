<?php
// save_booking.php - Process and save booking with Razorpay payment details
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Support both JSON payload and standard POST
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!$data) {
    $data = $_POST;
}

$firstName = isset($data['firstName']) ? trim($data['firstName']) : '';
$lastName = isset($data['lastName']) ? trim($data['lastName']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$destination = isset($data['destination']) ? trim($data['destination']) : '';
$duration = isset($data['duration']) ? trim($data['duration']) : '';
$price = isset($data['price']) ? floatval($data['price']) : 0.00;
$departure = isset($data['departuredate']) ? trim($data['departuredate']) : '';
$arrival = isset($data['arrivaldate']) ? trim($data['arrivaldate']) : '';
$travelers = isset($data['travelers']) ? intval($data['travelers']) : 1;
if ($travelers < 1) $travelers = 1;

$paymentId = isset($data['payment_id']) && !empty($data['payment_id']) 
    ? trim($data['payment_id']) 
    : 'PAY_' . strtoupper(bin2hex(random_bytes(6)));

$paymentStatus = isset($data['payment_status']) && !empty($data['payment_status']) 
    ? trim($data['payment_status']) 
    : 'Paid';

if (empty($firstName) || empty($email) || empty($destination) || empty($departure)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required booking fields.']);
    exit();
}

// Generate unique Booking Reference
$bookingRef = 'TT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

$stmt = $conn->prepare("INSERT INTO booking1 (firstName, lastName, email, phone, destination, duration, price, departure_date, arrival_date, travelers, payment_id, payment_status, booking_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if ($stmt) {
    $stmt->bind_param("ssssssdssisss", 
        $firstName, 
        $lastName, 
        $email, 
        $phone, 
        $destination, 
        $duration, 
        $price, 
        $departure, 
        $arrival, 
        $travelers, 
        $paymentId, 
        $paymentStatus, 
        $bookingRef
    );

    if ($stmt->execute()) {
        $bookingId = $stmt->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Booking confirmed successfully!',
            'booking_id' => $bookingId,
            'booking_reference' => $bookingRef,
            'payment_id' => $paymentId,
            'redirect_url' => 'receipt.php?booking_id=' . $bookingId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
}

$conn->close();
?>
