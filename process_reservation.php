
<?php
include('db.php');

/* =========================================
   RESERVATION ERROR MESSAGE
========================================= */

function reservationError($message) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reservation Notice</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <main class="container">
            <div class="card">
                <h2>Reservation Not Submitted</h2>
                <p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>
                <a class="btn btn-success" href="javascript:history.back()">Go Back</a>
                <a class="btn btn-secondary" href="calendar.php">Choose Another Date</a>
            </div>
        </main>
    </body>
    </html>';
    exit();
}


/* =========================================
   CHECK REQUEST METHOD
========================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: calendar.php');
    exit();
}


/* =========================================
   GET CUSTOMER INFORMATION
========================================= */

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

$secondary_contact_name = trim($_POST['secondary_contact_name'] ?? '');
$secondary_contact_number = trim($_POST['secondary_contact_number'] ?? '');

$event_type = trim($_POST['event_type'] ?? '');

$guest_count = filter_var(
    $_POST['guest_count'] ?? null,
    FILTER_VALIDATE_INT
);

$event_hours = filter_var(
    $_POST['event_hours'] ?? null,
    FILTER_VALIDATE_INT
);

$event_date = trim($_POST['event_date'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');


/* =========================================
   VALIDATE EVENT DATE
========================================= */

$date_object = DateTime::createFromFormat('Y-m-d', $event_date);

if (
    !$date_object ||
    $date_object->format('Y-m-d') !== $event_date ||
    $event_date < date('Y-m-d')
) {
    reservationError('Please choose a valid future event date.');
}


/* =========================================
   VALIDATE CUSTOMER INFORMATION
========================================= */

if (
    $full_name === '' ||
    ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) ||
    $phone === '' ||
    $event_type === '' ||
    !$guest_count ||
    $guest_count < 1 ||
    $guest_count > 400
) {
    reservationError(
        'Please complete the form with valid contact details and a guest count from 1 to 400. If you provide an email address, make sure it is valid.'
    );
}


/* =========================================
   VALIDATE EVENT DURATION
========================================= */

if (
    $event_hours === false ||
    $event_hours < 1 ||
    $event_hours > 24
) {
    reservationError(
        'Please enter a valid event duration between 1 and 24 hours.'
    );
}


/* =========================================
   VALIDATE REQUIRED SECONDARY CONTACT
========================================= */

if (
    $secondary_contact_name === '' ||
    $secondary_contact_number === ''
) {
    reservationError(
        'Secondary contact person name and number are required. Please complete both fields.'
    );
}

if (
    strlen($secondary_contact_name) > 150 ||
    strlen($secondary_contact_number) > 30
) {
    reservationError(
        'The secondary contact information is too long.'
    );
}


/* =========================================
   VALIDATE EVENT TYPE
========================================= */

$allowed_event_types = [
    'Wedding',
    'Birthday',
    'Corporate Event',
    'Anniversary',
    'Other'
];

if (!in_array($event_type, $allowed_event_types, true)) {
    reservationError('Please select a valid event type.');
}


/* =========================================
   VALIDATE PAYMENT METHOD
========================================= */

if (!in_array($payment_method, ['GCash', 'Paymaya', 'GoTyme'], true)) {
    reservationError('Please select a valid payment method.');
}


/* =========================================
   CHECK BLOCKED DATE
========================================= */

$blocked = $conn->prepare("
    SELECT id
    FROM blocked_dates
    WHERE blocked_date = ?
    LIMIT 1
");

if (!$blocked) {
    error_log('Blocked date query error: ' . $conn->error);
    reservationError('Unable to check date availability.');
}

$blocked->bind_param('s', $event_date);
$blocked->execute();

if ($blocked->get_result()->num_rows > 0) {
    $blocked->close();

    reservationError(
        'The administrator has marked this date as unavailable. Please choose another date.'
    );
}

$blocked->close();


/* =========================================
   CHECK EXISTING RESERVATION
========================================= */

$existing = $conn->prepare("
    SELECT id
    FROM reservations
    WHERE event_date = ?
    AND status IN ('Pending','Approved')
    LIMIT 1
");

if (!$existing) {
    error_log('Existing reservation query error: ' . $conn->error);
    reservationError('Unable to check existing reservations.');
}

$existing->bind_param('s', $event_date);
$existing->execute();

if ($existing->get_result()->num_rows > 0) {
    $existing->close();

    reservationError(
        'This date already has a pending or approved reservation. Please choose another date.'
    );
}

$existing->close();


/* =========================================
   CALCULATE ADD-ON PRICES
========================================= */

// Server-controlled add-on prices.
// Do not trust prices submitted by the browser.

$allowed_addons = [
    'Catering Service (₱8,000)' => 8000,
    'Sound and DJ System (₱5,000)' => 5000,
    'Floral Decoration (₱4,000)' => 4000
];

$base_price = 15000;
$total = $base_price;
$addons_list = [];

$submitted_addons = $_POST['addons'] ?? [];

if (!is_array($submitted_addons)) {
    $submitted_addons = [];
}

foreach ($submitted_addons as $addon) {

    if (
        is_string($addon) &&
        isset($allowed_addons[$addon]) &&
        !in_array($addon, $addons_list, true)
    ) {
        $addons_list[] = $addon;

        $total += $allowed_addons[$addon];
    }
}

// Save selected add-ons as a comma-separated string.

$addons_str = implode(', ', $addons_list);

// Calculate 50% downpayment.

$downpayment = $total * 0.50;


/* =========================================
   HANDLE IMAGE UPLOADS
========================================= */

$upload_dir = __DIR__ . '/uploads/';

if (
    !is_dir($upload_dir) &&
    !mkdir($upload_dir, 0755, true)
) {
    reservationError(
        'The upload folder could not be created. Please contact the administrator.'
    );
}

$finfo = new finfo(FILEINFO_MIME_TYPE);

$allowed_mimes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp'
];

$uploaded_paths = [];

foreach (['id_proof', 'payment_receipt'] as $field) {

    if (
        !isset($_FILES[$field]) ||
        $_FILES[$field]['error'] !== UPLOAD_ERR_OK ||
        !is_uploaded_file($_FILES[$field]['tmp_name']) ||
        $_FILES[$field]['size'] > 5 * 1024 * 1024
    ) {

        foreach ($uploaded_paths as $path) {
            if (is_file(__DIR__ . '/' . $path)) {
                unlink(__DIR__ . '/' . $path);
            }
        }

        reservationError(
            'Please upload both images. Each file must be no larger than 5 MB.'
        );
    }

    $mime = $finfo->file($_FILES[$field]['tmp_name']);

    if (!isset($allowed_mimes[$mime])) {

        foreach ($uploaded_paths as $path) {
            if (is_file(__DIR__ . '/' . $path)) {
                unlink(__DIR__ . '/' . $path);
            }
        }

        reservationError(
            'Only JPG, PNG, or WEBP image files are accepted for ID and payment receipt uploads.'
        );
    }

    $filename = $field . '_' .
        bin2hex(random_bytes(12)) . '.' .
        $allowed_mimes[$mime];

    $relative_path = 'uploads/' . $filename;

    if (!move_uploaded_file(
        $_FILES[$field]['tmp_name'],
        $upload_dir . $filename
    )) {

        foreach ($uploaded_paths as $path) {
            if (is_file(__DIR__ . '/' . $path)) {
                unlink(__DIR__ . '/' . $path);
            }
        }

        reservationError(
            'An image could not be uploaded. Please try again.'
        );
    }

    $uploaded_paths[] = $relative_path;
}

$id_file = $uploaded_paths[0];
$receipt_file = $uploaded_paths[1];


/* =========================================
   GENERATE RESERVATION CODE
========================================= */

$res_code = 'RES-' . strtoupper(bin2hex(random_bytes(4)));


/* =========================================
   SAVE RESERVATION TO DATABASE
========================================= */

$stmt = $conn->prepare("
    INSERT INTO reservations (
        reservation_code,
        full_name,
        email,
        phone,
        secondary_contact_name,
        secondary_contact_number,
        event_type,
        guest_count,
        event_hours,
        event_date,
        add_ons,
        id_proof_path,
        payment_method,
        payment_receipt_path,
        total_amount,
        downpayment_amount
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {

    error_log('Reservation prepare error: ' . $conn->error);

    foreach ($uploaded_paths as $path) {
        if (is_file(__DIR__ . '/' . $path)) {
            unlink(__DIR__ . '/' . $path);
        }
    }

    reservationError(
        'The reservation could not be prepared. Please contact the administrator.'
    );
}


/* =========================================
   BIND DATABASE PARAMETERS
========================================= */

// 7 strings, 2 integers, 5 strings, 2 decimals.

$stmt->bind_param(
    'sssssssiisssssdd',
    $res_code,
    $full_name,
    $email,
    $phone,
    $secondary_contact_name,
    $secondary_contact_number,
    $event_type,
    $guest_count,
    $event_hours,
    $event_date,
    $addons_str,
    $id_file,
    $payment_method,
    $receipt_file,
    $total,
    $downpayment
);


/* =========================================
   EXECUTE RESERVATION
========================================= */

if ($stmt->execute()) {

    $stmt->close();

    /* =====================================
       SEND PENDING RESERVATION EMAIL
    ===================================== */

    if ($email !== '') {

        try {

            require_once __DIR__ . '/email_functions.php';

            $emailReservation = [
                'reservation_code' => $res_code,
                'event_date' => $event_date,
                'event_type' => $event_type,
                'guest_count' => $guest_count,
                'event_hours' => $event_hours,
                'phone' => $phone,

                'secondary_contact_name' => $secondary_contact_name,
                'secondary_contact_number' => $secondary_contact_number,

                'add_ons' => $addons_str,
                'payment_method' => $payment_method,
                'total_amount' => $total,
                'downpayment_amount' => $downpayment
            ];

            sendGrandHorizonReservationEmail(
                $email,
                $full_name,
                $emailReservation,
                'pending'
            );

        } catch (Throwable $e) {

            error_log(
                'Reservation email error: ' . $e->getMessage()
            );

        }
    }

    /* =====================================
       REDIRECT TO RESERVATION STATUS
    ===================================== */

    header(
        'Location: status.php?code=' . urlencode($res_code)
    );

    exit();

} else {

    $database_error = $stmt->error;
    $database_errno = $stmt->errno;

    $stmt->close();

    foreach ($uploaded_paths as $path) {
        if (is_file(__DIR__ . '/' . $path)) {
            unlink(__DIR__ . '/' . $path);
        }
    }

    if ($database_errno == 1062) {
        reservationError(
            'Sorry, another customer reserved this date moments ago. Please choose a different date.'
        );
    }

    error_log('Reservation database error: ' . $database_error);

    reservationError(
        'Error saving reservation. Please try again or contact the administrator.'
    );
}
?>
