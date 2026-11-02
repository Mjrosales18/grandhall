
<?php

session_start();

include('db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// ==========================================
// HANDLE APPROVE / REJECT / DELETE
// ==========================================

if (isset($_GET['action']) && isset($_GET['id'])) {

    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == 'approve') {

        $findReservation = $conn->prepare(
            "SELECT * FROM reservations WHERE id = ? LIMIT 1"
        );

        $findReservation->bind_param("i", $id);
        $findReservation->execute();

        $reservationToApprove = $findReservation->get_result()->fetch_assoc();

        $findReservation->close();

        if ($reservationToApprove && $reservationToApprove['status'] === 'Pending') {

            $status = 'Approved';

            $stmt = $conn->prepare(
                "UPDATE reservations SET status = ? WHERE id = ? AND status = 'Pending'"
            );

            $stmt->bind_param("si", $status, $id);
            $stmt->execute();

            $wasApproved = ($stmt->affected_rows === 1);

            $stmt->close();

            if ($wasApproved && !empty($reservationToApprove['email'])) {

                require_once __DIR__ . '/email_functions.php';

                sendGrandHorizonReservationEmail(
                    $reservationToApprove['email'],
                    $reservationToApprove['full_name'],
                    $reservationToApprove,
                    'approved'
                );
            }
        }

    } elseif ($action == 'reject') {

        $status = 'Rejected';

        $stmt = $conn->prepare(
            "UPDATE reservations SET status = ? WHERE id = ?"
        );

        $stmt->bind_param("si", $status, $id);
        $stmt->execute();
        $stmt->close();

    } elseif ($action == 'delete') {

        $stmt = $conn->prepare(
            "DELETE FROM reservations WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_dashboard.php");
    exit();
}

// ==========================================
// SEARCH
// ==========================================

$search_query = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

include('admin_header.php');

?>

<style>

/* ==========================================
   DASHBOARD CARD
========================================== */

.dashboard-card {
    background: #ffffff;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(14, 15, 18, 0.12);
    border-top: 5px solid #D4AF37;
    margin-bottom: 25px;
    min-width: 0;
}

/* ==========================================
   DASHBOARD TITLE AND SEARCH
========================================== */

.dashboard-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 25px;
}

.dashboard-toolbar h2 {
    font-size: 1.45rem;
    font-weight: 800;
    color: #0E0F12;
    margin: 0;
    flex: 1 1 350px;
    min-width: 0;
}

/* ==========================================
   SEARCH BAR
========================================== */

.search-box-wrapper {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    gap: 8px;
    background: #1F232B;
    border: 1px solid #D4AF37;
    border-radius: 6px;
    padding: 5px 7px;
    width: 380px;
    max-width: 100%;
    flex: 0 1 380px;
    min-width: 0;
    box-sizing: border-box;
}

.search-box-wrapper input {
    background: transparent;
    border: none;
    color: #ffffff;
    padding: 10px;
    font-size: 14px;
    outline: none;
    flex: 1 1 auto;
    width: 0;
    min-width: 0;
    box-sizing: border-box;
    white-space: nowrap;
}

.search-box-wrapper input::placeholder {
    color: #94a3b8;
}

.search-btn {
    background: #D4AF37;
    color: #0E0F12;
    border: none;
    padding: 10px 14px;
    min-width: 76px;
    flex: 0 0 auto;
    border-radius: 4px;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    white-space: nowrap;
    word-break: normal;
    overflow-wrap: normal;
    line-height: 1.2;
    transition: background 0.2s ease;
}

.search-btn:hover {
    background: #c59b27;
}

/* ==========================================
   TABLE NAVIGATION
   LEFT BUTTON = FAR LEFT
   RIGHT BUTTON = FAR RIGHT
========================================== */

.table-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    gap: 12px;
    margin-bottom: 12px;
}

.scroll-arrow {
    width: 45px;
    height: 40px;
    flex-shrink: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #1F232B;
    color: #D4AF37;
    border: 1px solid #D4AF37;
    border-radius: 7px;
    font-size: 23px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s ease;
}

.scroll-arrow:hover:not(:disabled) {
    background: #D4AF37;
    color: #0E0F12;
}

.scroll-arrow:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.table-navigation-info {
    flex: 1;
    text-align: center;
    color: #64748b;
    font-size: 13px;
}

/* ==========================================
   VIEW MEDIA BUTTONS
========================================== */

.btn-view {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    background-color: #f0f6f9;
    color: #253f4b;
    border: 1px solid #779eb2;
    padding: 7px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    margin-bottom: 4px;
    white-space: nowrap;
}

.btn-view:hover {
    background-color: #779eb2;
    color: #ffffff;
    border-color: #253f4b;
}

.media-buttons {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

/* ==========================================
   ACTION BUTTONS
========================================== */

.action-buttons {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
}

.action-buttons .btn {
    min-width: 85px;
    min-height: 36px;
    padding: 6px 12px;
    font-size: 0.82rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    border-radius: 6px;
    box-shadow: none;
}

/* ==========================================
   RESERVATION TABLE
========================================== */

.table-responsive {
    width: 100%;
    overflow-x: auto;
    border-radius: 10px;
    border: 1px solid #DDE3EA;
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
}

#reservations-table {
    width: 100%;
    min-width: 1400px;
    border-collapse: collapse;
    background: #ffffff;
    font-size: 0.9rem;
}

/* TABLE HEADER */

#reservations-table th {
    background: #0E0F12;
    color: #D4AF37;
    padding: 15px 16px;
    text-align: left;
    font-weight: 700;
    font-size: 0.85rem;
    text-transform: uppercase;
    border-bottom: 2px solid #D4AF37;
    white-space: nowrap;
}

/* TABLE CELLS */

#reservations-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #DDE3EA;
    color: #0E0F12;
    vertical-align: middle;
}

/* STATUS */

#reservations-table .badge {
    display: inline-block;
    white-space: nowrap;
}

/* RESERVATION CODE */

#reservations-table .res-code {
    white-space: nowrap !important;
    word-break: normal !important;
    overflow-wrap: normal !important;
    min-width: 145px;
}

#reservations-table .res-code strong {
    white-space: nowrap !important;
    display: inline-block;
    font-size: 0.88rem;
}

/* CUSTOMER NAME */

#reservations-table .res-name {
    min-width: 130px;
}

/* CONTACT COLUMNS */

#reservations-table .contact-info,
#reservations-table .secondary-contact {
    min-width: 170px;
    line-height: 1.7;
}

#reservations-table .secondary-contact strong {
    display: block;
    color: #0E0F12;
}

#reservations-table .secondary-contact small {
    color: #525a66;
}

/* EVENT DURATION */

#reservations-table .event-duration {
    min-width: 130px;
    white-space: nowrap;
}

.duration-value {
    display: inline-block;
    background: #fff8df;
    color: #856404;
    border: 1px solid #e8d28a;
    padding: 6px 10px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 0.85rem;
}

/* ==========================================
   MOBILE RESPONSIVE
========================================== */

@media (max-width: 768px) {

    .dashboard-card {
        padding: 15px;
        margin: 12px 0;
    }

    .dashboard-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }

    .dashboard-toolbar h2 {
        font-size: 1.2rem;
        flex: 1 1 auto;
    }

    .search-box-wrapper {
        width: 100%;
        max-width: 100%;
        flex: 1 1 auto;
    }

    .search-box-wrapper input {
        font-size: 16px;
    }

    #reservations-table {
        min-width: 1400px;
    }

    .action-buttons {
        flex-wrap: wrap;
    }

    .table-navigation-info {
        font-size: 11px;
    }
}

/* SMALL MOBILE */

@media (max-width: 400px) {

    .dashboard-card {
        padding: 10px;
    }

    .dashboard-toolbar h2 {
        font-size: 1.05rem;
    }

    .search-btn {
        min-width: 65px;
        padding: 9px 10px;
    }

    .scroll-arrow {
        width: 40px;
        height: 38px;
    }

}

</style>


<!-- ==========================================
     DASHBOARD
========================================== -->

<div class="dashboard-card">

    <div class="dashboard-toolbar">

        <h2>
            📋 Reservation Management Dashboard
        </h2>

        <!-- SEARCH FORM -->

        <form
            method="GET"
            action="admin_dashboard.php"
            class="search-box-wrapper"
        >

            <input
                type="text"
                id="live-search"
                name="search"
                placeholder="🔍 Search Code, Customer or Contact..."
                value="<?php echo htmlspecialchars($search_query); ?>"
                onkeyup="filterTable()"
            >

            <button
                type="submit"
                class="search-btn"
            >
                Search
            </button>

        </form>

    </div>


    <!-- ==========================================
         TABLE NAVIGATION BUTTONS
    =========================================== -->

    <div class="table-navigation">

        <!-- LEFT BUTTON -->

        <button
            type="button"
            id="scrollLeft"
            class="scroll-arrow"
            title="Scroll table left"
            aria-label="Scroll table left"
        >
            &#8592;
        </button>


        <!-- CENTER TEXT -->

        <span class="table-navigation-info">
            Scroll to view all reservation columns
        </span>


        <!-- RIGHT BUTTON -->

        <button
            type="button"
            id="scrollRight"
            class="scroll-arrow"
            title="Scroll table right"
            aria-label="Scroll table right"
        >
            &#8594;
        </button>

    </div>


    <!-- ==========================================
         RESERVATION TABLE
    =========================================== -->

    <div class="table-responsive" id="tableScroll">

        <table id="reservations-table">

            <thead>

                <tr>

                    <th>Status</th>

                    <th>Code</th>

                    <th>Customer Name</th>

                    <th>Contact Info</th>

                    <th>Secondary Contact</th>

                    <th>Event Date</th>

                    <th>Event Duration (Hours)</th>

                    <th>Event Details</th>

                    <th>Verification Media</th>

                    <th>Actions</th>

                </tr>

            </thead>

            <tbody>

                <?php

                // ==========================================
                // SEARCH DATABASE
                // ==========================================

                if ($search_query !== '') {

                    $stmt = $conn->prepare(
                        "SELECT * FROM reservations
                         WHERE reservation_code LIKE ?
                         OR full_name LIKE ?
                         OR secondary_contact_name LIKE ?
                         OR secondary_contact_number LIKE ?
                         OR phone LIKE ?
                         ORDER BY id DESC"
                    );

                    $param = "%" . $search_query . "%";

                    $stmt->bind_param(
                        "sssss",
                        $param,
                        $param,
                        $param,
                        $param,
                        $param
                    );

                    $stmt->execute();

                    $res = $stmt->get_result();

                } else {

                    $res = $conn->query(
                        "SELECT * FROM reservations ORDER BY id DESC"
                    );

                }


                if ($res && $res->num_rows > 0):

                    while ($row = $res->fetch_assoc()):

                ?>

                <tr class="reservation-row">

                    <!-- STATUS -->

                    <td>

                        <span class="badge badge-<?php
                            echo strtolower(htmlspecialchars($row['status']));
                        ?>">

                            <?php echo htmlspecialchars($row['status']); ?>

                        </span>

                    </td>


                    <!-- RESERVATION CODE -->

                    <td class="res-code">

                        <strong>
                            <?php echo htmlspecialchars($row['reservation_code']); ?>
                        </strong>

                    </td>


                    <!-- CUSTOMER NAME -->

                    <td class="res-name">

                        <?php echo htmlspecialchars($row['full_name']); ?>

                    </td>


                    <!-- PRIMARY CONTACT -->

                    <td class="contact-info">

                        <?php

                        echo !empty($row['email'])
                            ? htmlspecialchars($row['email'])
                            : '<span style="color:#94a3b8;">No Email</span>';

                        ?>

                        <br>

                        <small style="color: #525a66;">

                            📞 <?php echo htmlspecialchars($row['phone']); ?>

                        </small>

                    </td>


                    <!-- SECONDARY CONTACT -->

                    <td class="secondary-contact">

                        <?php if (
                            !empty($row['secondary_contact_name']) ||
                            !empty($row['secondary_contact_number'])
                        ): ?>

                            <strong>
                                <?php
                                echo !empty($row['secondary_contact_name'])
                                    ? htmlspecialchars($row['secondary_contact_name'])
                                    : 'Name not provided';
                                ?>
                            </strong>

                            <small>
                                📱
                                <?php
                                echo !empty($row['secondary_contact_number'])
                                    ? htmlspecialchars($row['secondary_contact_number'])
                                    : 'Number not provided';
                                ?>
                            </small>

                        <?php else: ?>

                            <span style="color:#94a3b8; font-style:italic;">
                                No Secondary Contact
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- EVENT DATE -->

                    <td>

                        <strong>
                            <?php echo htmlspecialchars($row['event_date']); ?>
                        </strong>

                    </td>


                    <!-- EVENT DURATION -->

                    <td class="event-duration">

                        <?php

                        $eventHours = isset($row['event_hours'])
                            ? (int)$row['event_hours']
                            : 0;

                        ?>

                        <?php if ($eventHours > 0): ?>

                            <span class="duration-value">
                                ⏱️ <?php echo $eventHours; ?>
                                <?php echo $eventHours === 1 ? 'Hour' : 'Hours'; ?>
                            </span>

                        <?php else: ?>

                            <span style="color:#94a3b8; font-style:italic;">
                                Not specified
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- EVENT DETAILS -->

                    <td>

                        <?php echo htmlspecialchars($row['event_type']); ?>

                        <br>

                        <small style="color: #525a66;">

                            Guests:
                            <?php echo htmlspecialchars($row['guest_count']); ?>

                        </small>

                    </td>


                    <!-- VERIFICATION MEDIA -->

                    <td>

                        <div class="media-buttons">

                            <a
                                href="<?php echo htmlspecialchars($row['id_proof_path']); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn-view"
                            >
                                🪪 View ID
                            </a>

                            <a
                                href="<?php echo htmlspecialchars($row['payment_receipt_path']); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn-view"
                            >
                                🧾 View Receipt
                            </a>

                        </div>

                    </td>


                    <!-- ACTIONS -->

                    <td>

                        <div class="action-buttons">

                            <?php if ($row['status'] == 'Pending'): ?>

                                <a
                                    href="admin_dashboard.php?action=approve&id=<?php echo (int)$row['id']; ?>"
                                    class="btn btn-success"
                                    title="Approve Reservation"
                                >
                                    Approve
                                </a>

                                <a
                                    href="admin_dashboard.php?action=reject&id=<?php echo (int)$row['id']; ?>"
                                    class="btn btn-secondary"
                                    title="Reject Reservation"
                                >
                                    Reject
                                </a>

                            <?php endif; ?>


                            <!-- PRINT -->

                            <a
                                href="print_invoice.php?code=<?php echo urlencode($row['reservation_code']); ?>"
                                target="_blank"
                                class="btn"
                                title="Print Invoice"
                            >
                                🖨️ Print
                            </a>


                            <!-- DELETE -->

                            <a
                                href="admin_dashboard.php?action=delete&id=<?php echo (int)$row['id']; ?>"
                                class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to permanently delete this reservation?');"
                                title="Delete Reservation"
                            >
                                🗑️ Delete
                            </a>

                        </div>

                    </td>

                </tr>


                <?php

                    endwhile;

                else:

                ?>

                <tr>

                    <td colspan="10" style="text-align: center; padding: 30px;">

                        No reservations found.

                    </td>

                </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


</div>


<!-- ==========================================
     REAL-TIME SEARCH FILTER
========================================== -->

<script>

function filterTable() {

    let input = document
        .getElementById("live-search")
        .value
        .toLowerCase()
        .trim();

    let rows = document.querySelectorAll(".reservation-row");

    rows.forEach(row => {

        let rowText = row.textContent.toLowerCase();

        if (rowText.includes(input)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }

    });

}


/* ==========================================
   LEFT AND RIGHT TABLE SCROLL
========================================== */

document.addEventListener("DOMContentLoaded", function () {

    const tableScroll = document.getElementById("tableScroll");

    const scrollLeft = document.getElementById("scrollLeft");

    const scrollRight = document.getElementById("scrollRight");

    if (!tableScroll || !scrollLeft || !scrollRight) {
        return;
    }

    const scrollAmount = 500;

    // LEFT BUTTON
    scrollLeft.addEventListener("click", function () {

        tableScroll.scrollBy({
            left: -scrollAmount,
            behavior: "smooth"
        });

    });

    // RIGHT BUTTON
    scrollRight.addEventListener("click", function () {

        tableScroll.scrollBy({
            left: scrollAmount,
            behavior: "smooth"
        });

    });

    // UPDATE BUTTON STATES
    function updateScrollButtons() {

        const maxScroll =
            tableScroll.scrollWidth - tableScroll.clientWidth;

        scrollLeft.disabled =
            tableScroll.scrollLeft <= 1;

        scrollRight.disabled =
            tableScroll.scrollLeft >= maxScroll - 2;

    }

    // WHEN TABLE IS SCROLLED
    tableScroll.addEventListener(
        "scroll",
        updateScrollButtons
    );

    // WHEN WINDOW IS RESIZED
    window.addEventListener(
        "resize",
        updateScrollButtons
    );

    // INITIAL BUTTON STATE
    updateScrollButtons();

});

</script>


<?php include('footer.php'); ?>
