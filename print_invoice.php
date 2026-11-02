
<?php
include('db.php');

// ==========================================
// CHECK RESERVATION CODE
// ==========================================

if (!isset($_GET['code']) || empty($_GET['code'])) {
    die("Invalid reservation code.");
}

$code = trim($_GET['code']);

// ==========================================
// GET APPROVED RESERVATION
// ==========================================

$stmt = $conn->prepare("
    SELECT *
    FROM reservations
    WHERE reservation_code = ?
    AND status = 'Approved'
");

if (!$stmt) {
    die("Database query error: " . $conn->error);
}

$stmt->bind_param("s", $code);
$stmt->execute();

$result = $stmt->get_result();
$res = $result->fetch_assoc();

$stmt->close();

if (!$res) {
    die("Approved reservation not found.");
}

// ==========================================
// ESCAPE OUTPUT
// ==========================================

function clean($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// ==========================================
// PRICE CALCULATION
// ==========================================

$basePrice = 15000;

$cateringPrice = 8000;
$soundPrice = 5000;
$floralPrice = 4000;

$addonsText = $res['add_ons'] ?? '';

$hasCatering = stripos($addonsText, 'catering') !== false;
$hasSound = (
    stripos($addonsText, 'sound') !== false ||
    stripos($addonsText, 'dj') !== false
);
$hasFloral = (
    stripos($addonsText, 'floral') !== false
);

// Use the saved database total as the official total.
$total = (float)($res['total_amount'] ?? 0);

$downpayment = (float)($res['downpayment_amount'] ?? 0);

$balance = $total - $downpayment;

// Calculate displayed item subtotal.
$calculatedSubtotal = $basePrice;

if ($hasCatering) {
    $calculatedSubtotal += $cateringPrice;
}

if ($hasSound) {
    $calculatedSubtotal += $soundPrice;
}

if ($hasFloral) {
    $calculatedSubtotal += $floralPrice;
}

// ==========================================
// FORMAT EVENT INFORMATION
// ==========================================

$eventDate = !empty($res['event_date'])
    ? date('F d, Y', strtotime($res['event_date']))
    : 'Not specified';

$eventHours = !empty($res['event_hours'])
    ? (int)$res['event_hours']
    : 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Reservation Receipt - <?php echo clean($res['reservation_code']); ?>
    </title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f1f1;
            color: #333;
            padding: 40px 15px;
        }

        .receipt {
            max-width: 850px;
            margin: auto;
            background: #ffffff;
            border: 1px solid #e5e5e5;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }

        /* HEADER */

        .header {
            background: #1F232B;
            color: #ffffff;
            text-align: center;
            padding: 35px 20px;
            border-bottom: 5px solid #D4AF37;
        }

        .logo-img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-bottom: 12px;
        }

        .header h1 {
            font-size: 25px;
            letter-spacing: 1px;
            color: #D4AF37;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 13px;
            color: #d1d5db;
            letter-spacing: 1px;
        }

        /* RECEIPT CONTENT */

        .content {
            padding: 35px;
        }

        .receipt-title {
            text-align: center;
            margin-bottom: 25px;
        }

        .receipt-title h2 {
            font-size: 23px;
            color: #1F232B;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .receipt-title p {
            color: #777;
            font-size: 13px;
        }

        /* APPROVED STAMP */

        .status-container {
            text-align: center;
            margin: 25px 0;
        }

        .status {
            display: inline-block;
            background: #f8f4e5;
            border: 2px solid #D4AF37;
            color: #9b7613;
            padding: 10px 25px;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        /* RESERVATION CODE */

        .reservation-code {
            background: #1F232B;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
            border-left: 5px solid #D4AF37;
        }

        .reservation-code p {
            color: #cccccc;
            font-size: 12px;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .reservation-code h2 {
            color: #D4AF37;
            font-size: 23px;
            letter-spacing: 2px;
        }

        /* SECTION */

        .section-title {
            font-size: 15px;
            color: #1F232B;
            font-weight: bold;
            border-bottom: 2px solid #D4AF37;
            padding-bottom: 10px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* DETAILS TABLE */

        .details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .details td {
            padding: 13px 12px;
            border-bottom: 1px solid #eeeeee;
            font-size: 14px;
        }

        .details td:first-child {
            width: 40%;
            font-weight: bold;
            color: #555;
        }

        .details td:last-child {
            color: #222;
            overflow-wrap: anywhere;
        }

        .details tr:nth-child(even) {
            background: #fafafa;
        }

        /* PRICE SUMMARY */

        .price-box {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            margin-bottom: 30px;
            overflow: hidden;
        }

        .price-header {
            background: #1F232B;
            color: #D4AF37;
            padding: 15px 18px;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 13px 18px;
            border-bottom: 1px solid #eeeeee;
            font-size: 14px;
        }

        .price-row span:last-child {
            font-weight: 600;
            white-space: nowrap;
        }

        .price-row.subtotal {
            background: #fafafa;
            font-weight: bold;
        }

        .price-row.total {
            background: #f8f4e5;
            color: #9b7613;
            font-size: 17px;
            font-weight: bold;
            border-top: 2px solid #D4AF37;
        }

        .price-row.downpayment {
            color: #267344;
            font-weight: bold;
        }

        .price-row.balance {
            background: #1F232B;
            color: #D4AF37;
            font-size: 16px;
            font-weight: bold;
        }

        .price-note {
            padding: 12px 18px;
            color: #777;
            font-size: 12px;
            line-height: 1.6;
            background: #fafafa;
        }

        /* FOOTER */

        .footer {
            background: #1F232B;
            color: #cccccc;
            text-align: center;
            padding: 25px 15px;
            border-top: 4px solid #D4AF37;
        }

        .footer h3 {
            color: #D4AF37;
            font-size: 16px;
            margin-bottom: 8px;
        }

        .footer p {
            font-size: 12px;
            line-height: 1.7;
        }

        .footer .gold-line {
            width: 60px;
            height: 2px;
            background: #D4AF37;
            margin: 15px auto;
        }

        /* BUTTONS */

        .actions {
            max-width: 850px;
            margin: 20px auto;
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            text-decoration: none;
        }

        .print-btn {
            background: #D4AF37;
            color: #1F232B;
        }

        .back-btn {
            background: #1F232B;
            color: #ffffff;
        }

        .btn:hover {
            opacity: 0.85;
        }

        /* MOBILE */

        @media screen and (max-width: 600px) {

            body {
                padding: 10px;
            }

            .header {
                padding: 25px 12px;
            }

            .header h1 {
                font-size: 19px;
            }

            .content {
                padding: 20px 15px;
            }

            .details td {
                padding: 11px 7px;
                font-size: 12px;
            }

            .details td:first-child {
                width: 42%;
            }

            .reservation-code h2 {
                font-size: 19px;
            }

            .price-row {
                font-size: 12px;
                padding: 12px;
            }

            .price-row.total,
            .price-row.balance {
                font-size: 14px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                text-align: center;
            }
        }

        /* PRINT SETTINGS */

        @media print {

            @page {
                size: A4;
                margin: 12mm;
            }

            body {
                background: #ffffff;
                padding: 0;
                margin: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .receipt {
                max-width: 100%;
                border: 1px solid #ddd;
                box-shadow: none;
            }

            .actions {
                display: none !important;
            }

            .header,
            .footer,
            .reservation-code,
            .status,
            .price-header,
            .price-row.total,
            .price-row.balance {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

        }

    </style>

</head>

<body>

    <div class="receipt">

        <!-- HEADER -->

        <div class="header">

            <img
                src="images/logo.png"
                alt="Grand M.J Rosales Logo"
                class="logo-img"
            >

            <h1>GRAND M.J ROSALES HORIZON</h1>

            <p>FUNCTION HALL RESERVATION SYSTEM</p>

        </div>


        <div class="content">

            <!-- RECEIPT TITLE -->

            <div class="receipt-title">

                <h2>RESERVATION CONFIRMATION</h2>

                <p>Official Reservation Confirmation Receipt</p>

            </div>


            <!-- APPROVED STATUS -->

            <div class="status-container">

                <span class="status">
                    &#10003; OFFICIALLY APPROVED
                </span>

            </div>


            <!-- RESERVATION CODE -->

            <div class="reservation-code">

                <p>YOUR RESERVATION CODE</p>

                <h2>
                    <?php echo clean($res['reservation_code']); ?>
                </h2>

            </div>


            <!-- RESERVATION INFORMATION -->

            <h3 class="section-title">
                Reservation Information
            </h3>

            <table class="details">

                <tr>
                    <td>Customer Name</td>
                    <td><?php echo clean($res['full_name']); ?></td>
                </tr>

                <tr>
                    <td>Primary Contact Number</td>
                    <td>
                        <?php
                        echo !empty($res['phone'])
                            ? clean($res['phone'])
                            : 'Not provided';
                        ?>
                    </td>
                </tr>

                <tr>
                    <td>Contact Email</td>
                    <td>
                        <?php
                        echo !empty($res['email'])
                            ? clean($res['email'])
                            : 'Not provided';
                        ?>
                    </td>
                </tr>

                <tr>
                    <td>Secondary Contact Person</td>
                    <td>
                        <?php
                        echo !empty($res['secondary_contact_name'])
                            ? clean($res['secondary_contact_name'])
                            : 'Not provided';
                        ?>
                    </td>
                </tr>

                <tr>
                    <td>Secondary Contact Number</td>
                    <td>
                        <?php
                        echo !empty($res['secondary_contact_number'])
                            ? clean($res['secondary_contact_number'])
                            : 'Not provided';
                        ?>
                    </td>
                </tr>

                <tr>
                    <td>Event Date</td>
                    <td><?php echo clean($eventDate); ?></td>
                </tr>

                <tr>
                    <td>Event Duration</td>
                    <td>
                        <?php
                        echo $eventHours > 0
                            ? $eventHours . ($eventHours === 1 ? ' Hour' : ' Hours')
                            : 'Not specified';
                        ?>
                    </td>
                </tr>

                <tr>
                    <td>Event Type</td>
                    <td><?php echo clean($res['event_type']); ?></td>
                </tr>

                <tr>
                    <td>Guest Count</td>
                    <td><?php echo clean($res['guest_count']); ?> Guests</td>
                </tr>

                <tr>
                    <td>Add-ons</td>
                    <td>
                        <?php
                        echo !empty($res['add_ons'])
                            ? clean($res['add_ons'])
                            : 'None';
                        ?>
                    </td>
                </tr>

            </table>


            <!-- ======================================
                 RESERVATION PRICE SUMMARY
            ======================================= -->

            <h3 class="section-title">
                Reservation Price Summary
            </h3>

            <div class="price-box">

                <div class="price-header">
                    RESERVATION COST BREAKDOWN
                </div>


                <!-- BASE VENUE PRICE -->

                <div class="price-row">

                    <span>Function Hall Reservation</span>

                    <span>
                        &#8369;<?php echo number_format($basePrice, 2); ?>
                    </span>

                </div>


                <!-- CATERING -->

                <?php if ($hasCatering): ?>

                    <div class="price-row">

                        <span>Catering</span>

                        <span>
                            &#8369;<?php echo number_format($cateringPrice, 2); ?>
                        </span>

                    </div>

                <?php endif; ?>


                <!-- SOUND SYSTEM / DJ -->

                <?php if ($hasSound): ?>

                    <div class="price-row">

                        <span>Sound System / DJ</span>

                        <span>
                            &#8369;<?php echo number_format($soundPrice, 2); ?>
                        </span>

                    </div>

                <?php endif; ?>


                <!-- FLORAL DECORATION -->

                <?php if ($hasFloral): ?>

                    <div class="price-row">

                        <span>Floral Decoration</span>

                        <span>
                            &#8369;<?php echo number_format($floralPrice, 2); ?>
                        </span>

                    </div>

                <?php endif; ?>


                <!-- SUBTOTAL -->

                <div class="price-row subtotal">

                    <span>Itemized Price Subtotal</span>

                    <span>
                        &#8369;<?php echo number_format($calculatedSubtotal, 2); ?>
                    </span>

                </div>


                <!-- TOTAL -->

                <div class="price-row total">

                    <span>TOTAL RESERVATION PRICE</span>

                    <span>
                        &#8369;<?php echo number_format($total, 2); ?>
                    </span>

                </div>


                <!-- DOWNPAYMENT -->

                <div class="price-row downpayment">

                    <span>50% Downpayment</span>

                    <span>
                        &#8369;<?php echo number_format($downpayment, 2); ?>
                    </span>

                </div>


                <!-- BALANCE -->

                <div class="price-row balance">

                    <span>REMAINING BALANCE</span>

                    <span>
                        &#8369;<?php echo number_format($balance, 2); ?>
                    </span>

                </div>


                <div class="price-note">

                    The remaining balance must be settled according to
                    the reservation payment agreement.

                </div>

            </div>

        </div>


        <!-- FOOTER -->

        <div class="footer">

            <h3>GRAND M.J ROSALES HORIZON</h3>

            <div class="gold-line"></div>

            <p>
                Thank you for choosing our Function Hall.
                <br>
                We look forward to being part of your special occasion.
            </p>

            <p style="margin-top: 12px; color: #D4AF37;">
                OFFICIAL RESERVATION CONFIRMATION
            </p>

        </div>

    </div>


    <!-- ACTION BUTTONS -->

    <div class="actions">

        <button
            class="btn print-btn"
            onclick="window.print()"
        >
            PRINT RECEIPT
        </button>

        <button
            class="btn back-btn"
            onclick="window.history.back()"
        >
            BACK
        </button>

    </div>

</body>

</html>
