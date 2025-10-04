<?php
include 'db.php';
file_put_contents("test_log.txt", "Form reached process_checkin.php\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Basic validation
    if (empty($name) || empty($phone) || empty($reason)) {
        echo "<script>alert('Please fill in all required fields.'); window.history.back();</script>";
        exit;
    }

    // Check if visitor checked in recently (within 5 minutes)
    $checkQuery = $conn->prepare("SELECT * FROM visitors WHERE phone = ? AND checkin_time > (NOW() - INTERVAL 5 MINUTE)");
    $checkQuery->bind_param("s", $phone);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('You have already checked in recently. Please wait 5 minutes before trying again.'); window.location.href='checkin.php';</script>";
        exit;
    }

    // Insert visitor record
    $stmt = $conn->prepare("INSERT INTO visitors (name, phone, purpose) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $phone, $reason);

    if ($stmt->execute()) {
        echo "<script>alert('Check-in successful!'); window.location.href='success.php';</script>";
    } else {
        echo "<script>alert('Database error: " . $conn->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
