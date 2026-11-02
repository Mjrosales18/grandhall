
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Portal - Grand M.J Rosales Horizon</title>

    <!-- GOOGLE FONTS -->

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- MAIN CSS -->

    <link rel="stylesheet" href="style.css">

    <!-- WEBSITE LOGO -->

    <link rel="icon" type="image/png" href="images/logo.png">


    <style>


/* ==========================================
   ADMIN HEADER - MATCH MAIN WEBSITE HEADER
========================================== */

.header-banner {
    height: 78px !important;
    min-height: 78px !important;

    padding: 0 40px !important;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 20px;

    box-sizing: border-box;
}

/* LOGO SIZE */

.header-brand {
    display: flex;
    align-items: center;
    gap: 18px;
    height: 100%;
    min-width: 0;
}

.logo-wrapper {
    margin-top: 36px !important;
    flex-shrink: 0;
}

.header-logo {
    width: 110px !important;
    height: 110px !important;
    object-fit: contain;
}

/* WEBSITE TITLE */

.header-title-group h1 {
    font-size: 1.55rem !important;
    font-weight: 800;
    white-space: nowrap;
    line-height: 1.15;
}

.header-title-group p {
    font-size: 1rem !important;
    margin-top: 5px;
    white-space: nowrap;
}

/* NAVIGATION */

.admin-header-right {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 25px;
    flex-shrink: 0;
}

.admin-nav {
    display: flex;
    align-items: center;
    gap: 32px;
    flex-wrap: nowrap;
}

.admin-nav .btn {
    font-size: 0.95rem !important;
    padding: 8px 0 !important;
    white-space: nowrap !important;
}

/* CLOCK */

.ph-clock-box {
    padding: 6px 12px;
    flex-shrink: 0;
}

/* TABLET */

@media (max-width: 950px) {

    .header-banner {
        height: auto !important;
        min-height: 78px !important;
        padding: 12px 20px !important;
        flex-wrap: wrap;
    }

    .header-brand {
        flex: 1 1 100%;
    }

    .logo-wrapper {
        margin-top: 0 !important;
    }

    .header-logo {
        width: 65px !important;
        height: 65px !important;
    }

    .admin-header-right {
        width: 100%;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .header-title-group h1 {
        font-size: 1.2rem !important;
        white-space: normal;
    }

}

/* MOBILE */

@media (max-width: 600px) {

    .header-banner {
        padding: 10px 14px !important;
    }

    .header-logo {
        width: 55px !important;
        height: 55px !important;
    }

    .header-title-group h1 {
        font-size: 0.95rem !important;
    }

    .header-title-group p {
        font-size: 0.62rem !important;
    }

    .admin-header-right {
        flex-direction: column;
        align-items: stretch;
    }

    .admin-nav {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        width: 100%;
        gap: 10px;
    }

    .ph-clock-box {
        width: 100%;
        box-sizing: border-box;
    }

}


    </style>

</head>


<body>


<!-- ==========================================
     ADMIN NAVIGATION HEADER
========================================== -->

<header class="header-banner">


    <!-- BRAND AND LOGO -->

    <div class="header-brand">

        <a href="admin_dashboard.php"
           class="logo-wrapper"
           title="Go to Admin Dashboard">

            <img
                src="images/logo.png"
                alt="Grand M.J Rosales Logo"
                class="header-logo"
            >

        </a>


        <div class="header-title-group">

            <h1>
                𝓖𝓻𝓪𝓷𝓭 𝓜.𝓙 𝓡𝓸𝓼𝓪𝓵𝓮𝓼 𝓗𝓸𝓻𝓲𝔃𝓸𝓷
            </h1>

            <p>
                ᴀᴅᴍɪɴɪꜱᴛʀᴀᴛɪᴠᴇ ᴄᴏɴᴛʀᴏʟ ᴘᴀɴᴇʟ
            </p>

        </div>

    </div>


    <!-- NAVIGATION AND CLOCK -->

    <div class="admin-header-right">


        <nav class="admin-nav">

            <a
                href="admin_dashboard.php"
                class="btn"
            >
                Reservations
            </a>


            <a
                href="admin_blocked_dates.php"
                class="btn"
            >
                Block Dates
            </a>


            <a
                href="admin_reviews.php"
                class="btn"
            >
                Manage Feedback
            </a>


            <a
                href="logout.php"
                class="btn"
            >
                Logout
            </a>

        </nav>


        <!-- PHILIPPINE STANDARD TIME -->

        <div class="ph-clock-box">

            <span
                class="ph-date"
                id="ph-admin-date-display"
            >
                PH: Loading...
            </span>


            <span
                class="ph-time-str"
                id="ph-admin-time-display"
            >
                --:--:-- --
            </span>

        </div>


    </div>

</header>


<!-- ==========================================
     PHILIPPINE REAL-TIME CLOCK
========================================== -->

<script>

function updateAdminPHTime() {

    const now = new Date();


    const dateOptions = {

        timeZone: 'Asia/Manila',

        month: 'short',

        day: 'numeric',

        year: 'numeric'

    };


    const timeOptions = {

        timeZone: 'Asia/Manila',

        hour: '2-digit',

        minute: '2-digit',

        second: '2-digit',

        hour12: true

    };


    const dateDisplay = document.getElementById(
        'ph-admin-date-display'
    );

    const timeDisplay = document.getElementById(
        'ph-admin-time-display'
    );


    if (dateDisplay && timeDisplay) {

        dateDisplay.innerText =
            'PH: ' +
            new Intl.DateTimeFormat(
                'en-US',
                dateOptions
            ).format(now);


        timeDisplay.innerText =
            new Intl.DateTimeFormat(
                'en-US',
                timeOptions
            ).format(now);

    }

}


// INITIAL CLOCK UPDATE

updateAdminPHTime();


// UPDATE EVERY SECOND

setInterval(updateAdminPHTime, 1000);

</script>


<!-- MAIN PAGE CONTAINER -->

<div class="container">
