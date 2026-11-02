
<?php
include('db.php');
include('header.php');

// ==========================================
// MONTH AND YEAR
// ==========================================

$month = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('m');
$year  = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');

if ($month > 12) {
    $month = 1;
    $year++;
}

if ($month < 1) {
    $month = 12;
    $year--;
}

// Prevent invalid years
if ($year < 2020 || $year > 2100) {
    $month = (int)date('m');
    $year = (int)date('Y');
}

// ==========================================
// FETCH RESERVATION DATES
// ==========================================

$date_statuses = [];
$blocked_date_reasons = [];

$stmt = $conn->prepare("
    SELECT event_date, status
    FROM reservations
    WHERE status IN ('Approved', 'Pending')
    AND MONTH(event_date) = ?
    AND YEAR(event_date) = ?
");

$stmt->bind_param("ii", $month, $year);
$stmt->execute();

$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $date_statuses[$row['event_date']] = $row['status'];
}

$stmt->close();

// ==========================================
// FETCH BLOCKED DATES
// ==========================================

$blocked_stmt = $conn->prepare("
    SELECT blocked_date, reason
    FROM blocked_dates
    WHERE MONTH(blocked_date) = ?
    AND YEAR(blocked_date) = ?
");

if ($blocked_stmt) {
    $blocked_stmt->bind_param("ii", $month, $year);
    $blocked_stmt->execute();

    $blocked_result = $blocked_stmt->get_result();

    while ($blocked_row = $blocked_result->fetch_assoc()) {
        $blocked_date_reasons[$blocked_row['blocked_date']] =
            $blocked_row['reason'];
    }

    $blocked_stmt->close();
}

// ==========================================
// CALENDAR INFORMATION
// ==========================================

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$firstDay = (int)date(
    'w',
    strtotime(sprintf('%04d-%02d-01', $year, $month))
);

$monthName = date('F', mktime(0, 0, 0, $month, 10));

$today = date('Y-m-d');

// Previous month
$prevMonth = $month - 1;
$prevYear = $year;

if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

// Next month
$nextMonth = $month + 1;
$nextYear = $year;

if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Escape output
function calendarEscape($value) {
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<style>

/* ==========================================
   CALENDAR MAIN CARD
========================================== */

.calendar-card {
    width: 100%;
    max-width: 950px;
    margin: 0 auto;
    padding: 25px;
    box-sizing: border-box;
    overflow: hidden;
}

.calendar-card h2 {
    color: #1F232B;
    margin-bottom: 12px;
    font-size: 1.5rem;
    line-height: 1.3;
}

/* ==========================================
   MONTH NAVIGATION
========================================== */

.calendar-nav {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 12px;

    margin-bottom: 25px;
    background: #1F232B;
    padding: 15px;
    border-radius: 10px;
}

.calendar-nav h3 {
    margin: 0;
    font-size: 1.25rem;
    color: #D4AF37;
    text-align: center;
    white-space: nowrap;
}

.calendar-nav .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    text-decoration: none;
    padding: 12px 15px;
    border-radius: 7px;
    font-size: 14px;
    font-weight: 700;
    white-space: nowrap;
}

.calendar-nav .prev-btn {
    justify-self: start;
}

.calendar-nav .next-btn {
    justify-self: end;
}

/* ==========================================
   CALENDAR GRID
========================================== */

.calendar-grid {
    display: grid;

    /* Always seven equal columns */
    grid-template-columns: repeat(7, minmax(0, 1fr));

    gap: 8px;
    width: 100%;
    box-sizing: border-box;
}

/* ==========================================
   WEEKDAY HEADERS
========================================== */

.calendar-header {
    min-width: 0;
    width: 100%;
    box-sizing: border-box;

    display: flex;
    justify-content: center;
    align-items: center;

    background: #1F232B;
    color: #D4AF37;

    padding: 12px 2px;

    border-radius: 7px;
    border: 1px solid #2E3542;

    font-size: 13px;
    font-weight: 800;
    text-align: center;

    white-space: nowrap;
    overflow: hidden;
}

/* ==========================================
   CALENDAR DAY BASE
========================================== */

.calendar-day {
    min-width: 0;
    width: 100%;
    min-height: 95px;

    box-sizing: border-box;

    padding: 10px 4px;

    border-radius: 10px;

    display: flex;
    flex-direction: column;

    justify-content: space-between;
    align-items: center;

    text-decoration: none;
    text-align: center;

    font-weight: 700;

    cursor: pointer;

    transition: transform 0.2s ease,
                box-shadow 0.2s ease;
}

.calendar-day span {
    display: block;
    font-size: 19px;
    line-height: 1.3;
    white-space: nowrap;
}

.calendar-day small {
    display: block;

    width: 100%;

    font-size: 11px;
    font-weight: 700;

    line-height: 1.3;

    text-align: center;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ==========================================
   AVAILABLE - GREEN
========================================== */

.calendar-day.available {
    background: #dcfce7;
    color: #166534;
    border: 1.5px solid #86efac;
}

.calendar-day.available small {
    color: #15803d;
}

.calendar-day.available:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(22, 101, 52, 0.15);
    background: #bbf7d0;
}

/* ==========================================
   BOOKED - RED
========================================== */

.calendar-day.booked {
    background: #fee2e2;
    color: #991b1b;
    cursor: not-allowed;
    border: 1.5px solid #fca5a5;
}

.calendar-day.booked small {
    color: #dc2626;
}

/* ==========================================
   PENDING - ORANGE
========================================== */

.calendar-day.pending {
    background: #ffedd5;
    color: #c2410c;
    cursor: not-allowed;
    border: 1.5px solid #fdba74;
}

.calendar-day.pending small {
    color: #ea580c;
}

/* ==========================================
   PAST DATE - GREY
========================================== */

.calendar-day.past {
    background: #e2e8f0;
    color: #64748b;
    cursor: not-allowed;
    border: 1.5px solid #cbd5e1;
    opacity: 0.85;
}

.calendar-day.past small {
    color: #64748b;
}

/* ==========================================
   ADMIN BLOCKED DATE
========================================== */

.calendar-day.blocked {
    background: #e5e7eb;
    color: #374151;
    cursor: not-allowed;
    border: 1.5px solid #9ca3af;
}

.calendar-day.blocked small {
    color: #4b5563;
}

/* EMPTY CELLS */

.calendar-day.empty {
    background: transparent;
    border: none;
    cursor: default;
}

/* ==========================================
   LEGEND
========================================== */

.legend {
    display: flex;
    flex-wrap: wrap;
    gap: 14px 20px;

    justify-content: center;

    margin-bottom: 25px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 7px;

    font-size: 13px;
    font-weight: 700;

    color: #1F232B;
    white-space: nowrap;
}

.legend-box {
    width: 18px;
    height: 18px;

    flex-shrink: 0;

    border-radius: 5px;
    display: inline-block;
}

/* ==========================================
   MOBILE RESPONSIVE
========================================== */

@media screen and (max-width: 600px) {

    .calendar-card {
        padding: 14px 10px;
        border-radius: 10px;
    }

    .calendar-card h2 {
        font-size: 21px;
    }

    /* MONTH NAVIGATION */

    .calendar-nav {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        padding: 13px 9px;
    }

    .calendar-nav h3 {
        grid-column: 1 / -1;
        grid-row: 1;
        font-size: 21px;
        padding: 5px;
    }

    .calendar-nav .prev-btn {
        grid-column: 1;
        grid-row: 2;
        justify-self: stretch;
    }

    .calendar-nav .next-btn {
        grid-column: 2;
        grid-row: 2;
        justify-self: stretch;
    }

    .calendar-nav .btn {
        font-size: 12px;
        padding: 11px 5px;
        white-space: normal;
    }

    /* LEGEND */

    .legend {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px 8px;
        margin-bottom: 20px;
        justify-content: stretch;
    }

    .legend-item {
        font-size: 12px;
        gap: 6px;
        white-space: normal;
    }

    .legend-box {
        width: 15px;
        height: 15px;
    }

    /* CALENDAR GRID */

    .calendar-grid {
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 4px;
    }

    /* WEEKDAY HEADER */

    .calendar-header {
        padding: 9px 0;
        font-size: 11px;
        border-radius: 5px;
        letter-spacing: 0;
    }

    /* CALENDAR CELLS */

    .calendar-day {
        min-height: 72px;
        padding: 8px 1px;
        border-radius: 7px;
    }

    .calendar-day span {
        font-size: 16px;
    }

    .calendar-day small {
        font-size: 9px;
        line-height: 1.2;

        white-space: nowrap;
        overflow: hidden;
        text-overflow: clip;
    }

}

/* ==========================================
   EXTRA SMALL PHONES
========================================== */

@media screen and (max-width: 380px) {

    .calendar-card {
        padding: 12px 7px;
    }

    .calendar-grid {
        gap: 3px;
    }

    .calendar-header {
        font-size: 10px;
        padding: 8px 0;
    }

    .calendar-day {
        min-height: 65px;
        padding: 7px 0;
    }

    .calendar-day span {
        font-size: 14px;
    }

    .calendar-day small {
        font-size: 8px;
    }

    .legend-item {
        font-size: 11px;
    }

}

/* ==========================================
   LARGE DESKTOP
========================================== */

@media screen and (min-width: 601px) {

    .calendar-day {
        min-height: 100px;
    }

}

</style>


<!-- ==========================================
     CALENDAR CONTENT
========================================== -->

<div class="card calendar-card">

    <h2>📅 Select Event Date</h2>

    <p style="color:var(--color-text-muted,#64748b);margin-bottom:20px;line-height:1.6;">
        Click on any available green slot to initiate your reservation request.
        Dates marked as blocked are temporarily unavailable.
    </p>

    <?php if (isset($_GET['unavailable'])): ?>

        <p style="
            padding:12px;
            background:#fee2e2;
            color:#991b1b;
            border-radius:7px;
            margin-bottom:15px;
        ">
            That date is no longer available. Please choose another date.
        </p>

    <?php endif; ?>


    <!-- MONTH NAVIGATION -->

    <div class="calendar-nav">

        <a
            href="calendar.php?m=<?php echo $prevMonth; ?>&y=<?php echo $prevYear; ?>"
            class="btn btn-secondary prev-btn"
        >
            ❮ Previous Month
        </a>

        <h3>
            <?php echo calendarEscape("$monthName $year"); ?>
        </h3>

        <a
            href="calendar.php?m=<?php echo $nextMonth; ?>&y=<?php echo $nextYear; ?>"
            class="btn btn-secondary next-btn"
        >
            Next Month ❯
        </a>

    </div>


    <!-- CALENDAR LEGEND -->

    <div class="legend">

        <div class="legend-item">
            <span class="legend-box" style="background:#dcfce7;border:1px solid #86efac;"></span>
            Available
        </div>

        <div class="legend-item">
            <span class="legend-box" style="background:#fee2e2;border:1px solid #fca5a5;"></span>
            Booked
        </div>

        <div class="legend-item">
            <span class="legend-box" style="background:#ffedd5;border:1px solid #fdba74;"></span>
            Pending
        </div>

        <div class="legend-item">
            <span class="legend-box" style="background:#e2e8f0;border:1px solid #cbd5e1;"></span>
            Past Date
        </div>

        <div class="legend-item">
            <span class="legend-box" style="background:#e5e7eb;border:1px solid #9ca3af;"></span>
            Unavailable
        </div>

    </div>


    <!-- CALENDAR GRID -->

    <div class="calendar-grid">

        <!-- WEEKDAYS -->

        <div class="calendar-header">SUN</div>
        <div class="calendar-header">MON</div>
        <div class="calendar-header">TUE</div>
        <div class="calendar-header">WED</div>
        <div class="calendar-header">THU</div>
        <div class="calendar-header">FRI</div>
        <div class="calendar-header">SAT</div>


        <?php

        // Empty cells before the first day
        for ($i = 0; $i < $firstDay; $i++) {
            echo '<div class="calendar-day empty"></div>';
        }


        // Generate calendar dates
        for ($day = 1; $day <= $daysInMonth; $day++) {

            $currentDate = sprintf(
                '%04d-%02d-%02d',
                $year,
                $month,
                $day
            );

            $isPast = ($currentDate < $today);

            $status = $date_statuses[$currentDate] ?? null;

            $isBlocked = array_key_exists(
                $currentDate,
                $blocked_date_reasons
            );

            $blockedReason = $blocked_date_reasons[$currentDate] ?? 'Unavailable';


            // PAST DATE
            if ($isPast) {

                echo "
                    <div class='calendar-day past'>
                        <span>$day</span>
                        <small>Past</small>
                    </div>
                ";

            }

            // APPROVED RESERVATION
            elseif ($status === 'Approved') {

                echo "
                    <div class='calendar-day booked'>
                        <span>$day</span>
                        <small>Booked</small>
                    </div>
                ";

            }

            // PENDING RESERVATION
            elseif ($status === 'Pending') {

                echo "
                    <div class='calendar-day pending'>
                        <span>$day</span>
                        <small>Pending</small>
                    </div>
                ";

            }

            // ADMIN BLOCKED DATE
            elseif ($isBlocked) {

                $reason = calendarEscape($blockedReason);

                echo "
                    <div
                        class='calendar-day blocked'
                        title='$reason'
                    >
                        <span>$day</span>
                        <small>Blocked</small>
                    </div>
                ";

            }

            // AVAILABLE DATE
            else {

                echo "
                    <a
                        href='reserve.php?date=$currentDate'
                        class='calendar-day available'
                    >
                        <span>$day</span>
                        <small>Available</small>
                    </a>
                ";

            }

        }

        ?>

    </div>

</div>

</div>

<?php include('footer.php'); ?>
