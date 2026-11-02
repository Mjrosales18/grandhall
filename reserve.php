
<?php
include('db.php');

$selected_date = isset($_GET['date']) ? $_GET['date'] : '';
$date_object = DateTime::createFromFormat('Y-m-d', $selected_date);

if (
    !$date_object ||
    $date_object->format('Y-m-d') !== $selected_date ||
    $selected_date < date('Y-m-d')
) {
    header("Location: calendar.php?unavailable=1");
    exit();
}

$check = $conn->prepare("
    SELECT id FROM reservations
    WHERE event_date = ?
    AND status IN ('Pending','Approved')
    LIMIT 1
");
$check->bind_param("s", $selected_date);
$check->execute();

$has_reservation = $check->get_result()->num_rows > 0;
$check->close();

$blocked = $conn->prepare("
    SELECT id FROM blocked_dates
    WHERE blocked_date = ?
    LIMIT 1
");
$blocked->bind_param("s", $selected_date);
$blocked->execute();

$has_block = $blocked->get_result()->num_rows > 0;
$blocked->close();

if ($has_reservation || $has_block) {
    header("Location: calendar.php?unavailable=1");
    exit();
}

include('header.php');
?>

<style>

/* RESERVATION WRAPPER */

.reservation-wrapper {
    max-width: 800px;
    margin: 2rem auto;
}

.reservation-card {
    padding: 1.8rem;
    border-top: 5px solid #D4AF37;
}

/* FORM GRID */

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    min-width: 0;
}

.form-group label {
    display: block;
    font-weight: 700;
    font-size: .88rem;
    margin-bottom: 5px;
    color: #1F232B;
}

.form-group input,
.form-group select {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 12px;
    border: 1px solid #2E3542;
    border-radius: 6px;
    font-size: .9rem;
    background: #ffffff;
    color: #1F232B;
    outline: none;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #D4AF37;
    box-shadow: 0 0 0 2px rgba(212, 175, 55, .15);
}

/* REQUIRED FIELD ASTERISK */

.required-star {
    color: #dc2626;
    font-weight: 700;
}

/* SECONDARY CONTACT NOTE */

.secondary-contact-note {
    display: block;
    color: #64748b;
    font-size: .8rem;
    line-height: 1.5;
    margin-top: 6px;
}

/* SECTION BOX */

.section-box {
    background: #f8fafc;
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid #DDE3EA;
    margin-bottom: 1rem;
}

/* ADD-ON CHECKBOXES */

.addon-options {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    font-size: .88rem;
}

.addon-options label {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}

.addon-options input {
    accent-color: #D4AF37;
    width: 16px;
    height: 16px;
}

/* PRICE SUMMARY */

.price-summary {
    background: #ffffff;
    border: 2px solid #D4AF37;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    color: #1F232B;
    font-size: .9rem;
}

.price-row:last-child {
    margin-bottom: 0;
}

.price-total {
    font-size: 1.1rem;
    font-weight: 800;
    color: #15803d;
}

.price-note {
    display: block;
    color: #64748b;
    font-size: .78rem;
    line-height: 1.5;
    margin-top: 12px;
}

/* QR CODE */

.qr-container {
    text-align: center;
    display: none;
    background: white;
    padding: 16px;
    border-radius: 10px;
    border: 2px solid #D4AF37;
    margin: 1.2rem 0;
}

/* MOBILE RESPONSIVE */

@media(max-width: 600px) {

    .reservation-wrapper {
        margin: 1rem 10px;
    }

    .reservation-card {
        padding: 1rem;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .price-row {
        font-size: .85rem;
    }

}

</style>


<!-- RESERVATION FORM -->

<div class="reservation-wrapper">

<div class="card reservation-card">

    <!-- HEADER -->

    <div style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        flex-wrap:wrap;
        gap:10px;
        border-bottom:2px solid #DDE3EA;
        padding-bottom:12px;
        margin-bottom:20px;
    ">

        <h2 style="
            margin:0;
            font-size:1.35rem;
            color:#0E0F12;
        ">
            📝 Event Reservation Form
        </h2>

        <span style="
            background:rgba(212,175,55,.15);
            color:#0E0F12;
            padding:6px 12px;
            border-radius:20px;
            font-weight:700;
            font-size:.85rem;
            border:1px solid #D4AF37;
        ">
            📅 Date: <?php echo htmlspecialchars($selected_date); ?>
        </span>

    </div>


    <form
        action="process_reservation.php"
        method="POST"
        enctype="multipart/form-data"
    >

        <input
            type="hidden"
            name="event_date"
            value="<?php echo htmlspecialchars($selected_date); ?>"
        >


        <!-- FULL NAME AND EMAIL -->

        <div class="form-grid">

            <div class="form-group">

                <label>
                    Full Name:
                    <span class="required-star">*</span>
                </label>

                <input
                    type="text"
                    name="full_name"
                    required
                    oninput="this.value = this.value.replace(/\b\w/g, function(char) { return char.toUpperCase(); });"
                >

            </div>


            <div class="form-group">

                <label>
                    Email Address:
                    <span style="color:#64748b;font-weight:500;">
                        (Optional)
                    </span>
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    autocomplete="email"
                >

            </div>

        </div>


        <!-- PHONE, EVENT TYPE, GUESTS, DURATION -->

        <div class="form-grid">

            <div class="form-group">

                <label>
                    Phone Number:
                    <span class="required-star">*</span>
                </label>

                <input
                    type="tel"
                    name="phone"
                    required
                    placeholder="Enter mobile number"
                >

            </div>


            <div class="form-group">

                <label>
                    Event Type:
                    <span class="required-star">*</span>
                </label>

                <select name="event_type" required>

                    <option value="Wedding">Wedding</option>
                    <option value="Birthday">Birthday</option>
                    <option value="Corporate Event">Corporate Event</option>
                    <option value="Anniversary">Anniversary</option>
                    <option value="Other">Other</option>

                </select>

            </div>


            <div class="form-group">

                <label>
                    Guests (Max 400):
                    <span class="required-star">*</span>
                </label>

                <input
                    type="number"
                    name="guest_count"
                    max="400"
                    min="1"
                    required
                    placeholder="e.g. 150"
                >

            </div>


            <div class="form-group">

                <label>
                    Event Duration (Hours):
                    <span class="required-star">*</span>
                </label>

                <input
                    type="number"
                    name="event_hours"
                    min="1"
                    max="24"
                    step="1"
                    required
                    placeholder="e.g. 5"
                >

                <small style="
                    display:block;
                    color:#64748b;
                    font-size:.78rem;
                    margin-top:5px;
                ">
                    Enter the total number of hours for your event.
                </small>

            </div>

        </div>


        <!-- SECONDARY CONTACT PERSON -->

        <div class="form-grid">

            <div class="form-group">

                <label>
                    📞 Secondary Contact Name:
                    <span class="required-star">*</span>
                </label>

                <input
                    type="text"
                    name="secondary_contact_name"
                    placeholder="Enter full name"
                    maxlength="150"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Secondary Contact Number:
                    <span class="required-star">*</span>
                </label>

                <input
                    type="tel"
                    name="secondary_contact_number"
                    placeholder="Enter mobile number"
                    maxlength="30"
                    required
                >

            </div>

        </div>

        <small class="secondary-contact-note">
            Both secondary contact details are required.
            Please obtain this person's permission before sharing
            their contact information.
        </small>


        <!-- ADD-ON SERVICES -->

        <div class="section-box">

            <label style="
                display:block;
                font-weight:700;
                font-size:.88rem;
                margin-bottom:12px;
                color:#1F232B;
            ">
                ✨ Optional Add-on Services:
            </label>

            <div class="addon-options">

                <label>
                    <input
                        type="checkbox"
                        name="addons[]"
                        value="Catering Service (₱8,000)"
                        class="addon-checkbox"
                        data-price="8000"
                    >
                    Catering Service (₱8,000)
                </label>


                <label>
                    <input
                        type="checkbox"
                        name="addons[]"
                        value="Sound and DJ System (₱5,000)"
                        class="addon-checkbox"
                        data-price="5000"
                    >
                    Sound & DJ System (₱5,000)
                </label>


                <label>
                    <input
                        type="checkbox"
                        name="addons[]"
                        value="Floral Decoration (₱4,000)"
                        class="addon-checkbox"
                        data-price="4000"
                    >
                    Floral Decoration (₱4,000)
                </label>

            </div>

        </div>


        <!-- VALID ID -->

        <div class="section-box">

            <label style="
                display:block;
                font-weight:700;
                font-size:.88rem;
                margin-bottom:6px;
                color:#1F232B;
            ">
                🪪 Upload Valid ID (JPG or PNG):
                <span class="required-star">*</span>
            </label>

            <input
                type="file"
                name="id_proof"
                accept="image/png, image/jpeg"
                required
                style="width:100%;"
            >

        </div>


        <!-- PAYMENT DETAILS -->

        <div style="
            border-top:2px dashed #DDE3EA;
            padding-top:1rem;
            margin-top:1rem;
        ">

            <h3 style="
                font-size:1.1rem;
                color:#0E0F12;
                margin-bottom:12px;
            ">
                💳 Payment Details
            </h3>


            <!-- VENUE RATE -->

            <div style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                flex-wrap:wrap;
                gap:8px;
                background:#fff8e6;
                border:1px solid #fde68a;
                padding:12px 14px;
                border-radius:6px;
                margin-bottom:1rem;
            ">

                <span style="font-size:.9rem;color:#1F232B;">
                    Base Venue Rate:
                    <strong>₱15,000.00</strong>
                </span>

                <span style="
                    color:#991b1b;
                    font-weight:700;
                    font-size:.88rem;
                ">
                    Required Downpayment: 50%
                </span>

            </div>


            <!-- AUTOMATIC PRICE SUMMARY -->

            <div class="price-summary">

                <h3 style="
                    margin:0 0 16px;
                    color:#1F232B;
                    font-size:1rem;
                ">
                    🧾 Reservation Price Summary
                </h3>


                <div class="price-row">

                    <span>Base Venue Rate</span>

                    <strong>₱15,000.00</strong>

                </div>


                <div class="price-row">

                    <span>Selected Add-on Services</span>

                    <strong id="addon-total">₱0.00</strong>

                </div>


                <hr style="
                    border:0;
                    border-top:1px dashed #ccc;
                    margin:12px 0;
                ">


                <div class="price-row">

                    <strong>Full Total</strong>

                    <strong
                        id="full-total"
                        class="price-total"
                    >
                        ₱15,000.00
                    </strong>

                </div>


                <div class="price-row">

                    <span>Required Downpayment (50%)</span>

                    <strong id="downpayment-total">
                        ₱7,500.00
                    </strong>

                </div>


                <div class="price-row">

                    <span>Remaining Balance</span>

                    <strong id="remaining-total">
                        ₱7,500.00
                    </strong>

                </div>


                <small class="price-note">
                    Your total automatically changes when you select
                    or remove an optional add-on service.
                </small>

            </div>


            <!-- PAYMENT METHOD -->

            <div class="form-group" style="margin-bottom:1rem;">

                <label>
                    Select Payment Method:
                    <span class="required-star">*</span>
                </label>

                <select
                    id="payment_method"
                    name="payment_method"
                    onchange="showQR()"
                    required
                >

                    <option value="">-- Select Payment --</option>
                    <option value="GCash">GCash</option>
                    <option value="Paymaya">PayMaya</option>
                    <option value="GoTyme">GoTyme Bank</option>

                </select>

            </div>


            <!-- QR CODE -->

            <div id="qr-container" class="qr-container">

                <p style="
                    font-size:.95rem;
                    font-weight:700;
                    color:#0E0F12;
                ">
                    📲 Scan QR Code to Pay Downpayment:
                </p>

                <p style="
                    font-size:.8rem;
                    color:#D4AF37;
                    font-weight:600;
                ">
                    (Click image to view full screen)
                </p>

                <img
                    id="qr-code"
                    src=""
                    alt="Payment QR"
                    onclick="openQrModal()"
                    style="
                        width:240px;
                        height:240px;
                        object-fit:contain;
                        border:2px solid #1F232B;
                        padding:8px;
                        border-radius:8px;
                        background:white;
                        cursor:pointer;
                    "
                >

            </div>


            <!-- PAYMENT RECEIPT -->

            <div class="form-group" style="margin-top:1rem;">

                <label>
                    Upload Payment Receipt Screenshot:
                    <span class="required-star">*</span>
                </label>

                <input
                    type="file"
                    name="payment_receipt"
                    accept="image/*"
                    required
                    style="width:100%;"
                >

            </div>

        </div>


        <!-- SUBMIT BUTTON -->

        <button
            type="submit"
            class="btn btn-success"
            style="
                width:100%;
                padding:12px;
                margin-top:1.5rem;
                font-size:1rem;
                font-weight:700;
            "
        >
            Submit Reservation Request
        </button>

    </form>

</div>
</div>


<!-- QR MODAL -->

<div
    id="qr-modal"
    onclick="closeQrModal()"
    style="
        display:none;
        position:fixed;
        top:0;
        left:0;
        width:100vw;
        height:100vh;
        background:rgba(14,15,18,.88);
        z-index:9999;
        justify-content:center;
        align-items:center;
        cursor:pointer;
    "
>

    <div style="
        position:relative;
        text-align:center;
        max-width:90%;
        max-height:90%;
    ">

        <span style="
            position:absolute;
            top:-35px;
            right:0;
            background:#2E3542;
            color:#D4AF37;
            border:1px solid #D4AF37;
            padding:4px 12px;
            border-radius:6px;
            font-size:.85rem;
            font-weight:700;
        ">
            Close ✕
        </span>

        <img
            id="modal-qr-img"
            src=""
            alt="Full Payment QR"
            style="
                max-width:85vw;
                max-height:80vh;
                object-fit:contain;
                border:3px solid #D4AF37;
                border-radius:12px;
                background:white;
                padding:10px;
            "
        >

    </div>

</div>


<script>

/* ==========================================
   QR CODE PAYMENT METHOD
========================================== */

function showQR() {

    var method = document.getElementById("payment_method").value;

    var qrContainer = document.getElementById("qr-container");

    var qrImg = document.getElementById("qr-code");


    if (method === "GCash") {

        qrImg.src = "images/gcash_qr.jpg";
        qrContainer.style.display = "block";

    } else if (method === "Paymaya") {

        qrImg.src = "images/maya_qr.jpg";
        qrContainer.style.display = "block";

    } else if (method === "GoTyme") {

        qrImg.src = "images/gotyme_qr.jpg";
        qrContainer.style.display = "block";

    } else {

        qrContainer.style.display = "none";
        qrImg.src = "";

    }

}


/* ==========================================
   QR CODE FULLSCREEN MODAL
========================================== */

function openQrModal() {

    var qrSrc = document.getElementById("qr-code").src;

    if (qrSrc) {

        document.getElementById("modal-qr-img").src = qrSrc;

        document.getElementById("qr-modal").style.display = "flex";

    }

}


function closeQrModal() {

    document.getElementById("qr-modal").style.display = "none";

}


/* ==========================================
   AUTOMATIC RESERVATION PRICE CALCULATOR
========================================== */

const BASE_PRICE = 15000;


function formatPeso(amount) {

    return "₱" + amount.toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

}


function calculateReservationTotal() {

    let addonTotal = 0;

    const addonCheckboxes = document.querySelectorAll(
        ".addon-checkbox"
    );


    // Calculate selected add-ons

    addonCheckboxes.forEach(function(checkbox) {

        if (checkbox.checked) {

            addonTotal += Number(checkbox.dataset.price);

        }

    });


    // Calculate reservation amount

    const fullTotal = BASE_PRICE + addonTotal;

    const downpayment = fullTotal * 0.50;

    const remainingBalance = fullTotal - downpayment;


    // Display updated amounts

    document.getElementById("addon-total").textContent =
        formatPeso(addonTotal);

    document.getElementById("full-total").textContent =
        formatPeso(fullTotal);

    document.getElementById("downpayment-total").textContent =
        formatPeso(downpayment);

    document.getElementById("remaining-total").textContent =
        formatPeso(remainingBalance);

}


// Listen for checkbox changes

document.querySelectorAll(".addon-checkbox").forEach(function(checkbox) {

    checkbox.addEventListener("change", calculateReservationTotal);

});


// Calculate initial reservation price

calculateReservationTotal();

</script>


<?php include('footer.php'); ?>
