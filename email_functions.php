
<?php
require_once __DIR__ . '/email_config.php';

$phpMailerBase = __DIR__ . '/PHPMailer 7.1.1 source code/PHPMailer-PHPMailer-36d2461/src/';

require_once $phpMailerBase . 'Exception.php';
require_once $phpMailerBase . 'PHPMailer.php';
require_once $phpMailerBase . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/** Escape dynamic values before placing them in HTML email. */
function emailEscape($value): string
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/** Format an amount as Philippine pesos. */
function emailPeso($amount): string
{
    return number_format((float)$amount, 2);
}

/** Build and send the reservation email. */
function sendGrandHorizonReservationEmail(
    string $recipientEmail,
    string $recipientName,
    array $reservation,
    string $emailType
): bool {

    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // Check SMTP configuration
    if (
        SMTP_USERNAME === 'YOUR_GMAIL_ADDRESS@gmail.com' ||
        SMTP_PASSWORD === 'YOUR_16_CHARACTER_APP_PASSWORD'
    ) {
        error_log(
            'Grand Horizon email not sent: configure SMTP credentials in email_config.php.'
        );

        return false;
    }

    // Reservation status
    $isApproved = ($emailType === 'approved');

    $subject = $isApproved
        ? 'Your Reservation Has Been Approved - Grand M.J Rosales Horizon'
        : 'Reservation Received - Pending Approval | Grand M.J Rosales Horizon';

    // Customer information
    $name = emailEscape($recipientName);

    $code = emailEscape(
        $reservation['reservation_code'] ?? ''
    );

    $phone = emailEscape(
        $reservation['phone'] ?? ''
    );

    // SECONDARY CONTACT INFORMATION
    $secondaryContactName = trim(
        (string)($reservation['secondary_contact_name'] ?? '')
    );

    $secondaryContactNumber = trim(
        (string)($reservation['secondary_contact_number'] ?? '')
    );

    $secondaryContactNameDisplay = $secondaryContactName !== ''
        ? emailEscape($secondaryContactName)
        : 'Not provided';

    $secondaryContactNumberDisplay = $secondaryContactNumber !== ''
        ? emailEscape($secondaryContactNumber)
        : 'Not provided';

    // Event date
    $eventDateRaw = (string)($reservation['event_date'] ?? '');

    $eventDate = $eventDateRaw;

    $dateObject = DateTime::createFromFormat(
        'Y-m-d',
        $eventDateRaw
    );

    if (
        $dateObject &&
        $dateObject->format('Y-m-d') === $eventDateRaw
    ) {
        $eventDate = $dateObject->format('F j, Y');
    }

    // Event information
    $eventType = emailEscape(
        $reservation['event_type'] ?? ''
    );

    $guestCount = emailEscape(
        $reservation['guest_count'] ?? ''
    );

    $eventHours = (int)($reservation['event_hours'] ?? 0);

    $eventHoursDisplay = $eventHours > 0
        ? $eventHours . ' Hours'
        : 'Not specified';

    $paymentMethod = emailEscape(
        $reservation['payment_method'] ?? ''
    );

    // Add-ons
    $addons = trim(
        (string)($reservation['add_ons'] ?? '')
    );

    $addonsDisplay = $addons !== ''
        ? emailEscape($addons)
        : 'None';

    /*
    ==========================================
    RESERVATION PRICE SUMMARY
    ==========================================

    These prices must match the prices used
    in process_reservation.php.
    */

    $baseHallPrice = 15000;

    $cateringPrice = 8000;
    $soundDjPrice = 5000;
    $floralPrice = 4000;

    // Detect selected add-ons
    $hasCatering = stripos($addons, 'catering') !== false;

    $hasSoundDj = (
        stripos($addons, 'sound') !== false ||
        stripos($addons, 'dj') !== false
    );

    $hasFloral = stripos($addons, 'floral') !== false;

    // Build price breakdown
    $priceItems = [];

    $priceItems[] = [
        'name' => 'Function Hall Rental',
        'amount' => $baseHallPrice
    ];

    if ($hasCatering) {
        $priceItems[] = [
            'name' => 'Catering Service',
            'amount' => $cateringPrice
        ];
    }

    if ($hasSoundDj) {
        $priceItems[] = [
            'name' => 'Sound System / DJ',
            'amount' => $soundDjPrice
        ];
    }

    if ($hasFloral) {
        $priceItems[] = [
            'name' => 'Floral Decoration',
            'amount' => $floralPrice
        ];
    }

    // Official amounts from the database
    $totalAmount = (float)($reservation['total_amount'] ?? 0);

    $downpaymentAmount = (float)(
        $reservation['downpayment_amount'] ?? 0
    );

    $remainingBalance = max(
        0,
        $totalAmount - $downpaymentAmount
    );

    // Calculate displayed itemized subtotal
    $itemizedSubtotal = 0;

    foreach ($priceItems as $item) {
        $itemizedSubtotal += $item['amount'];
    }

    /*
    If the stored total differs from the itemized
    prices, display the difference as an adjustment.

    The database total remains the official amount.
    */
    $priceDifference = $totalAmount - $itemizedSubtotal;

    if (abs($priceDifference) > 0.01) {
        $priceItems[] = [
            'name' => 'Other Price Adjustment',
            'amount' => $priceDifference
        ];
    }

    $total = emailPeso($totalAmount);

    $downpayment = emailPeso($downpaymentAmount);

    $balance = emailPeso($remainingBalance);

    /*
    ==========================================
    EMAIL PRICE BREAKDOWN HTML
    ==========================================
    */

    $priceBreakdownHtml = '';

    foreach ($priceItems as $item) {

        $itemName = emailEscape($item['name']);

        $itemAmount = emailPeso($item['amount']);

        $priceBreakdownHtml .= '
            <tr>
                <td style="
                    padding:9px 7px;
                    color:#64748b;
                    border-bottom:1px solid #f1f5f9;
                ">
                    ' . $itemName . '
                </td>

                <td align="right" style="
                    padding:9px 7px;
                    font-weight:bold;
                    color:#1F232B;
                    border-bottom:1px solid #f1f5f9;
                    white-space:nowrap;
                ">
                    &#8369;' . $itemAmount . '
                </td>
            </tr>
        ';
    }

    // Status design
    $statusColor = $isApproved
        ? '#15803d'
        : '#b7791f';

    $statusText = $isApproved
        ? 'APPROVED'
        : 'PENDING';

    $headline = $isApproved
        ? 'Your reservation is approved!'
        : 'Thank you for choosing us!';

    $intro = $isApproved
        ? 'Good news! Our team has approved your function hall reservation. Your event date is now confirmed in our reservation system.'
        : 'We have successfully received your reservation request. Our team will review your submitted details and payment proof. Your reservation is currently pending approval.';

    $closing = $isApproved
        ? 'We look forward to welcoming you and helping make your event memorable.'
        : 'Please keep your reservation code for future reference. We will notify you by email once the administrator makes a decision.';

    // Create mailer
    $mail = new PHPMailer(true);

    try {

        /* =====================================
           SMTP CONFIGURATION
        ===================================== */

        $mail->isSMTP();

        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom(
            SMTP_FROM_EMAIL,
            SMTP_FROM_NAME
        );

        $mail->addAddress(
            $recipientEmail,
            $recipientName
        );

        $mail->isHTML(true);
        $mail->Subject = $subject;

        /* =====================================
           EMBED LOGO
        ===================================== */

        $logoPath = __DIR__ . '/images/logo.png';

        $logoHtml = '';

        if (is_file($logoPath)) {

            $mail->addEmbeddedImage(
                $logoPath,
                'grand_horizon_logo',
                'logo.png'
            );

            $logoHtml = '
                <img
                    src="cid:grand_horizon_logo"
                    width="88"
                    height="88"
                    alt="Grand M.J Rosales Horizon"
                    style="
                        display:block;
                        width:88px;
                        height:88px;
                        object-fit:contain;
                        border:2px solid #D4AF37;
                        border-radius:50%;
                        padding:3px;
                        background:#0E0F12;
                        margin:0 auto 12px;
                    "
                >
            ';
        }

        /* =====================================
           EMAIL HTML TEMPLATE
        ===================================== */

        $mail->Body = '

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">

            <meta
                name="viewport"
                content="width=device-width, initial-scale=1.0"
            >
        </head>

        <body style="
            margin:0;
            padding:0;
            background:#f1f4f8;
            font-family:Arial,Helvetica,sans-serif;
            color:#1F232B;
        ">

          <table
            role="presentation"
            width="100%"
            cellspacing="0"
            cellpadding="0"
            style="background:#f1f4f8;padding:28px 12px;"
          >

            <tr>
              <td align="center">

                <table
                    role="presentation"
                    width="600"
                    cellspacing="0"
                    cellpadding="0"
                    style="
                        width:100%;
                        max-width:600px;
                        background:#ffffff;
                        border-radius:14px;
                        overflow:hidden;
                        box-shadow:0 5px 22px rgba(0,0,0,.08);
                    "
                >

                    <!-- HEADER -->

                    <tr>
                        <td
                            align="center"
                            style="
                                background:#1F232B;
                                padding:26px 22px;
                                border-bottom:4px solid #D4AF37;
                            "
                        >

                            ' . $logoHtml . '

                            <div style="
                                font-family:Georgia,serif;
                                font-size:25px;
                                font-weight:bold;
                                color:#ffffff;
                                line-height:1.2;
                            ">
                                Grand M.J Rosales Horizon
                            </div>

                            <div style="
                                margin-top:7px;
                                font-size:11px;
                                letter-spacing:2px;
                                color:#D4AF37;
                                font-weight:bold;
                            ">
                                FUNCTION HALL RESERVATION &amp; VENUE
                            </div>

                        </td>
                    </tr>

                    <!-- EMAIL CONTENT -->

                    <tr>
                        <td style="padding:30px 28px 12px;">

                            <div style="
                                font-size:22px;
                                font-weight:bold;
                                color:#1F232B;
                                margin-bottom:12px;
                            ">
                                ' . $headline . '
                            </div>

                            <p style="
                                font-size:14px;
                                line-height:1.75;
                                color:#4b5563;
                                margin:0 0 14px;
                            ">
                                Dear ' . $name . ',
                            </p>

                            <p style="
                                font-size:14px;
                                line-height:1.75;
                                color:#4b5563;
                                margin:0 0 22px;
                            ">
                                ' . $intro . '
                            </p>

                            <!-- RESERVATION SUMMARY -->

                            <div style="
                                border:1px solid #e5e7eb;
                                border-radius:10px;
                                overflow:hidden;
                                margin-bottom:22px;
                            ">

                                <div style="
                                    background:#f8fafc;
                                    padding:14px 16px;
                                    border-bottom:1px solid #e5e7eb;
                                    font-weight:bold;
                                    color:#1F232B;
                                ">
                                    RESERVATION SUMMARY
                                </div>

                                <div style="padding:16px;">

                                    <div style="
                                        font-size:12px;
                                        color:#64748b;
                                        margin-bottom:5px;
                                    ">
                                        RESERVATION CODE
                                    </div>

                                    <div style="
                                        font-size:21px;
                                        font-weight:bold;
                                        letter-spacing:1px;
                                        color:#1F232B;
                                        margin-bottom:16px;
                                    ">
                                        ' . $code . '
                                    </div>

                                    <table
                                        role="presentation"
                                        width="100%"
                                        cellspacing="0"
                                        cellpadding="7"
                                        style="
                                            font-size:13px;
                                            border-collapse:collapse;
                                        "
                                    >

                                        <tr>
                                            <td style="
                                                color:#64748b;
                                                width:42%;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                Customer Name
                                            </td>

                                            <td style="
                                                font-weight:bold;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                ' . $name . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="
                                                color:#64748b;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                Primary Phone Number
                                            </td>

                                            <td style="
                                                font-weight:bold;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                ' . $phone . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="
                                                color:#64748b;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                Secondary Contact Person
                                            </td>

                                            <td style="
                                                font-weight:bold;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                ' . $secondaryContactNameDisplay . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="
                                                color:#64748b;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                Secondary Contact Number
                                            </td>

                                            <td style="
                                                font-weight:bold;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                ' . $secondaryContactNumberDisplay . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="
                                                color:#64748b;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                Event
                                            </td>

                                            <td style="
                                                font-weight:bold;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                ' . $eventType . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="
                                                color:#64748b;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                Event Date
                                            </td>

                                            <td style="
                                                font-weight:bold;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                ' . emailEscape($eventDate) . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="
                                                color:#64748b;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                Event Duration
                                            </td>

                                            <td style="
                                                font-weight:bold;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                ' . emailEscape($eventHoursDisplay) . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="
                                                color:#64748b;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                Number of Guests
                                            </td>

                                            <td style="
                                                font-weight:bold;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                ' . $guestCount . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="
                                                color:#64748b;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                Add-on Services
                                            </td>

                                            <td style="
                                                font-weight:bold;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                ' . $addonsDisplay . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="
                                                color:#64748b;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                Payment Method
                                            </td>

                                            <td style="
                                                font-weight:bold;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                ' . $paymentMethod . '
                                            </td>
                                        </tr>

                                    </table>

                                </div>

                            </div>

                            <!-- RESERVATION PRICE SUMMARY -->

                            <div style="
                                border:1px solid #e5e7eb;
                                border-radius:10px;
                                overflow:hidden;
                                margin-bottom:22px;
                            ">

                                <div style="
                                    background:#1F232B;
                                    color:#D4AF37;
                                    padding:14px 16px;
                                    font-weight:bold;
                                    letter-spacing:.5px;
                                ">
                                    RESERVATION PRICE SUMMARY
                                </div>

                                <div style="padding:16px;">

                                    <table
                                        role="presentation"
                                        width="100%"
                                        cellspacing="0"
                                        cellpadding="0"
                                        style="
                                            font-size:13px;
                                            border-collapse:collapse;
                                        "
                                    >

                                        ' . $priceBreakdownHtml . '

                                        <tr>
                                            <td style="
                                                padding:13px 7px;
                                                font-weight:bold;
                                                color:#1F232B;
                                                border-top:2px solid #D4AF37;
                                            ">
                                                TOTAL RESERVATION PRICE
                                            </td>

                                            <td align="right" style="
                                                padding:13px 7px;
                                                font-size:16px;
                                                font-weight:bold;
                                                color:#1F232B;
                                                border-top:2px solid #D4AF37;
                                                white-space:nowrap;
                                            ">
                                                &#8369;' . $total . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="
                                                padding:9px 7px;
                                                color:#64748b;
                                                border-bottom:1px solid #f1f5f9;
                                            ">
                                                Required Downpayment (50%)
                                            </td>

                                            <td align="right" style="
                                                padding:9px 7px;
                                                font-weight:bold;
                                                color:#15803d;
                                                border-bottom:1px solid #f1f5f9;
                                                white-space:nowrap;
                                            ">
                                                &#8369;' . $downpayment . '
                                            </td>
                                        </tr>

                                        <tr>
                                            <td style="
                                                padding:12px 7px;
                                                font-weight:bold;
                                                color:#1F232B;
                                            ">
                                                REMAINING BALANCE
                                            </td>

                                            <td align="right" style="
                                                padding:12px 7px;
                                                font-size:16px;
                                                font-weight:bold;
                                                color:#b7791f;
                                                white-space:nowrap;
                                            ">
                                                &#8369;' . $balance . '
                                            </td>
                                        </tr>

                                    </table>

                                    <p style="
                                        font-size:11px;
                                        line-height:1.6;
                                        color:#64748b;
                                        margin:12px 0 0;
                                    ">
                                        The amounts shown above are based on your saved reservation record.
                                    </p>

                                </div>

                            </div>

                            <!-- RESERVATION STATUS -->

                            <div style="
                                background:#fffaf0;
                                border:1px solid #f1d58a;
                                border-radius:9px;
                                padding:15px 17px;
                                margin-bottom:22px;
                            ">

                                <div style="
                                    font-size:11px;
                                    color:#64748b;
                                    font-weight:bold;
                                    letter-spacing:1px;
                                    margin-bottom:7px;
                                ">
                                    RESERVATION STATUS
                                </div>

                                <span style="
                                    display:inline-block;
                                    background:' . $statusColor . ';
                                    color:#ffffff;
                                    padding:7px 15px;
                                    border-radius:20px;
                                    font-size:12px;
                                    font-weight:bold;
                                    letter-spacing:1px;
                                ">
                                    ' . $statusText . '
                                </span>

                                <div style="
                                    font-size:12px;
                                    color:#64748b;
                                    margin-top:9px;
                                ">
                                    ' . (
                                        $isApproved
                                        ? 'Your reservation has been approved by the administrator.'
                                        : 'Your request is not yet confirmed. Please wait for the administrator’s approval.'
                                    ) . '
                                </div>

                            </div>

                            <!-- CLOSING -->

                            <p style="
                                font-size:14px;
                                line-height:1.75;
                                color:#4b5563;
                                margin:0 0 22px;
                            ">
                                ' . $closing . '
                            </p>

                            <p style="
                                font-size:14px;
                                line-height:1.7;
                                color:#4b5563;
                                margin:0 0 8px;
                            ">
                                Thank you,
                            </p>

                            <p style="
                                font-size:14px;
                                font-weight:bold;
                                color:#1F232B;
                                margin:0 0 24px;
                            ">
                                Grand M.J Rosales Horizon Team
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->

                    <tr>
                        <td
                            align="center"
                            style="
                                background:#1F232B;
                                padding:18px 20px;
                                border-top:3px solid #D4AF37;
                            "
                        >

                            <div style="
                                font-size:11px;
                                color:#D4AF37;
                                font-weight:bold;
                                letter-spacing:1px;
                            ">
                                GRAND M.J ROSALES HORIZON
                            </div>

                            <div style="
                                font-size:11px;
                                color:#cbd5e1;
                                margin-top:6px;
                                line-height:1.6;
                            ">
                                This is an automated reservation notification.
                                Please keep this email for your records.
                            </div>

                        </td>
                    </tr>

                </table>

              </td>
            </tr>

          </table>

        </body>
        </html>
        ';

        /* =====================================
           PLAIN TEXT EMAIL
        ===================================== */

        $plainPriceBreakdown = '';

        foreach ($priceItems as $item) {

            $plainPriceBreakdown .=
                $item['name'] . ': PHP ' .
                emailPeso($item['amount']) . "\n";
        }

        $mail->AltBody =
            "Dear {$recipientName},\n\n" .

            "{$intro}\n\n" .

            "RESERVATION SUMMARY\n" .
            "Reservation Code: " . ($reservation['reservation_code'] ?? '') . "\n" .
            "Customer Name: {$recipientName}\n" .
            "Primary Phone Number: " . ($reservation['phone'] ?? '') . "\n" .

            "Secondary Contact Person: " .
            ($reservation['secondary_contact_name'] ?? 'Not provided') . "\n" .

            "Secondary Contact Number: " .
            ($reservation['secondary_contact_number'] ?? 'Not provided') . "\n" .

            "Event: " . ($reservation['event_type'] ?? '') . "\n" .
            "Event Date: {$eventDate}\n" .
            "Event Duration: {$eventHoursDisplay}\n" .
            "Number of Guests: " . ($reservation['guest_count'] ?? '') . "\n" .
            "Add-on Services: " . ($reservation['add_ons'] ?? 'None') . "\n" .
            "Payment Method: " . ($reservation['payment_method'] ?? '') . "\n\n" .

            "RESERVATION PRICE SUMMARY\n" .
            $plainPriceBreakdown . "\n" .
            "Total Reservation Price: PHP {$total}\n" .
            "Required Downpayment (50%): PHP {$downpayment}\n" .
            "Remaining Balance: PHP {$balance}\n\n" .

            "Reservation Status: {$statusText}\n\n" .

            "{$closing}\n\n" .

            "Thank you,\n" .
            "Grand M.J Rosales Horizon Team";

        /* =====================================
           SEND EMAIL
        ===================================== */

        $mail->send();

        return true;

    } catch (Exception $e) {

        error_log(
            'Grand Horizon email error: ' . $mail->ErrorInfo
        );

        return false;
    }
}
