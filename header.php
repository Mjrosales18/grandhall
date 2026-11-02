<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Grand M.J Rosales Horizon Function Hall</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="images/logo.png">

    <style>

        /* ==============================
           MAIN HEADER
        ============================== */

        .header-banner {
            background: #1F232B;
            color: #ffffff;
            padding: 0.8rem 2.5rem;
            height: 75px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            box-shadow: 0 4px 12px rgba(14, 15, 18, 0.12);

            position: sticky;
            top: 0;
            z-index: 1000;

            border-bottom: 3px solid #D4AF37;
        }

        /* ==============================
           LOGO AND BRAND
        ============================== */

        .header-brand {
            display: flex;
            align-items: center;
            gap: 18px;
            height: 100%;
            min-width: 0;
        }

        .logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;

            margin-top: 40px;

            text-decoration: none;
            position: relative;
            z-index: 1005;
            flex-shrink: 0;
        }

        .header-logo {
            width: 110px;
            height: 110px;

            object-fit: contain;
            border-radius: 50%;

            background: #0E0F12;
            padding: 2px;

            border: 2px solid #D4AF37;

            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.45);

            transition: transform 0.2s ease;
        }

        .header-logo:hover {
            transform: scale(1.06);
        }

        /* ==============================
           BRAND TITLE
        ============================== */

        .header-title-group {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
        }

        .header-title-group h1 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #ffffff;

            line-height: 1.15;
            letter-spacing: -0.01em;

            margin: 0;
            white-space: nowrap;
        }

        .header-title-group p {
            font-size: 0.95rem;
            font-weight: 500;

            color: #D4AF37;

            margin-top: 3px;
            white-space: nowrap;
        }

        /* ==============================
           RIGHT HEADER
        ============================== */

        .header-right {
            display: flex;
            align-items: center;
            gap: 18px;

            position: relative;
            flex-shrink: 0;
        }

        /* ==============================
           NAVIGATION
        ============================== */

        nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        nav a {
            color: #DDE3EA;
            text-decoration: none;

            font-weight: 600;
            font-size: 0.92rem;

            padding: 8px 16px;
            border-radius: 6px;

            transition: all 0.2s ease;
            white-space: nowrap;
        }

        nav a:hover {
            background: rgba(212, 175, 55, 0.2);
            color: #D4AF37;
        }

        /* ==============================
           MENU BUTTON
        ============================== */

        .nav-toggle {
            display: none;

            background: #2E3542;
            color: #D4AF37;

            border: 1px solid #D4AF37;

            padding: 7px 14px;

            font-size: 0.95rem;
            font-weight: 700;

            border-radius: 6px;
            cursor: pointer;

            transition: all 0.2s ease;
        }

        .nav-toggle:hover {
            background: #D4AF37;
            color: #0E0F12;
        }

        /* ==============================
           PHILIPPINE CLOCK
        ============================== */

        .ph-clock-box {
            background: rgba(221, 227, 234, 0.1);

            border: 1px solid rgba(212, 175, 55, 0.4);
            border-radius: 6px;

            padding: 5px 12px;

            text-align: right;
            line-height: 1.25;

            display: inline-flex;
            flex-direction: column;
            justify-content: center;

            flex-shrink: 0;
        }

        .ph-clock-box .ph-date {
            font-size: 0.8rem;
            font-weight: 600;
            color: #D4AF37;
        }

        .ph-clock-box .ph-time-str {
            font-size: 0.95rem;
            font-weight: 700;

            color: #ffffff;

            font-family: monospace;
            letter-spacing: 0.03em;
        }

        /* ==============================
           TABLET AND HALF-SCREEN
        ============================== */

        @media (max-width: 1100px) {

            .header-banner {
                padding: 0.8rem 1.2rem;
            }

            .header-title-group h1 {
                font-size: 1.25rem;
            }

            .header-title-group p {
                font-size: 0.8rem;
            }

            .nav-toggle {
                display: flex;
                align-items: center;
                gap: 6px;
            }

            nav {
                display: none;

                position: absolute;
                top: 55px;
                right: 0;

                background: #1F232B;

                border: 1px solid #D4AF37;
                border-radius: 8px;

                box-shadow: 0 10px 20px rgba(0,0,0,0.5);

                flex-direction: column;

                padding: 10px;
                width: 190px;

                z-index: 1010;
            }

            nav.show-menu {
                display: flex !important;
            }

            nav a {
                width: 100%;
                padding: 10px 14px;
            }
        }

        /* ==============================
           MOBILE HEADER
        ============================== */

        @media (max-width: 700px) {

            .header-banner {
                height: auto;
                min-height: 0;

                padding: 14px 12px;

                flex-wrap: wrap;
                gap: 12px;
            }

            /* LOGO AND TITLE ROW */

            .header-brand {
                width: 100%;
                min-width: 0;

                gap: 12px;

                align-items: center;
            }

            .logo-wrapper {
                margin-top: 0;
                flex-shrink: 0;
            }

            .header-logo {
                width: 65px;
                height: 65px;
            }

            /* BIGGER BRAND TITLE */

            .header-title-group {
                min-width: 0;
                flex: 1;
            }

            .header-title-group h1 {
                font-size: clamp(20px, 5.5vw, 25px) !important;

                font-weight: 800;
                line-height: 1.2;

                letter-spacing: 0;

                white-space: normal;

                color: #ffffff;

                overflow-wrap: break-word;
            }

            /* BIGGER SUBTITLE */

            .header-title-group p {
                font-size: clamp(9px, 2.8vw, 12px) !important;

                line-height: 1.4;

                white-space: normal;

                margin-top: 5px;

                color: #D4AF37;
            }

            /* MENU AND CLOCK ROW */

            .header-right {
                width: 100%;

                display: flex;

                justify-content: space-between;
                align-items: center;

                gap: 8px;
                flex-wrap: wrap;
            }

            .nav-toggle {
                display: inline-flex;

                align-items: center;
                gap: 6px;

                font-size: 1rem;
                padding: 9px 14px;
            }

            /* MOBILE DROPDOWN */

            nav {
                top: calc(100% + 5px);
                right: 0;

                width: min(220px, calc(100vw - 28px));
            }

            /* MOBILE CLOCK */

            .ph-clock-box {
                padding: 6px 9px;
            }

            .ph-clock-box .ph-date {
                font-size: 11px;
            }

            .ph-clock-box .ph-time-str {
                font-size: 12px;
            }
        }

        /* ==============================
           SMALL MOBILE PHONES
        ============================== */

        @media (max-width: 380px) {

            .header-banner {
                padding: 12px 9px;
            }

            .header-brand {
                gap: 9px;
            }

            .header-logo {
                width: 58px;
                height: 58px;
            }

            .header-title-group h1 {
                font-size: 22px !important;
            }

            .header-title-group p {
                font-size: 9px !important;
            }

            .nav-toggle {
                font-size: 13px;
                padding: 8px 10px;
            }

            .ph-clock-box .ph-date {
                font-size: 10px;
            }

            .ph-clock-box .ph-time-str {
                font-size: 11px;
            }
        }

    </style>

</head>

<body>

    <!-- ==============================
         WEBSITE HEADER
    ============================== -->

    <header class="header-banner">

        <div class="header-brand">

            <!-- CLICKABLE LOGO -->

            <a href="index.php" class="logo-wrapper" title="Go to Home Page">

                <img
                    src="images/logo.png"
                    alt="Grand M.J Rosales Logo"
                    class="header-logo"
                >

            </a>

            <!-- WEBSITE BRANDING -->

            <div class="header-title-group">

                <h1>
                    𝓖𝓻𝓪𝓷𝓭 𝓜.𝓙 𝓡𝓸𝓼𝓪𝓵𝓮𝓼 𝓗𝓸𝓻𝓲𝔃𝓸𝓷
                </h1>

                <p>
                    ꜰᴜɴᴄᴛɪᴏɴ ʜᴀʟʟ ʀᴇꜱᴇʀᴠᴀᴛɪᴏɴ & ᴠᴇɴᴜᴇ ꜱʏꜱᴛᴇᴍ
                </p>

            </div>

        </div>

        <!-- ==============================
             HEADER RIGHT SECTION
        ============================== -->

        <div class="header-right">

            <!-- MENU BUTTON -->

            <button
                class="nav-toggle"
                id="nav-toggle-btn"
                aria-label="Toggle Navigation"
                aria-expanded="false"
            >
                ☰ Menu
            </button>

            <!-- NAVIGATION LINKS -->

            <nav id="main-nav">

                <a href="index.php">Home</a>

                <a href="about.php">About Us</a>

                <a href="calendar.php">Book Now</a>

                <a href="reviews.php">Reviews</a>

                <a href="status.php">Check Status</a>

            </nav>

            <!-- PHILIPPINE TIME -->

            <div class="ph-clock-box">

                <span class="ph-date" id="ph-date-display">
                    PH: Loading...
                </span>

                <span class="ph-time-str" id="ph-time-display">
                    --:--:-- --
                </span>

            </div>

        </div>

    </header>

    <!-- ==============================
         JAVASCRIPT
    ============================== -->

    <script>

        // MENU TOGGLE

        const menuButton = document.getElementById('nav-toggle-btn');
        const mainNav = document.getElementById('main-nav');

        menuButton.addEventListener('click', function(e) {

            e.stopPropagation();

            const isOpen = mainNav.classList.toggle('show-menu');

            menuButton.setAttribute('aria-expanded', isOpen);

        });


        // CLOSE MENU WHEN CLICKING OUTSIDE

        document.addEventListener('click', function(event) {

            if (
                !mainNav.contains(event.target) &&
                !menuButton.contains(event.target)
            ) {

                mainNav.classList.remove('show-menu');

                menuButton.setAttribute('aria-expanded', 'false');

            }

        });


        // REAL-TIME PHILIPPINE CLOCK

        function updatePHTime() {

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

            document.getElementById('ph-date-display').innerText =
                'PH: ' + new Intl.DateTimeFormat('en-US', dateOptions).format(now);

            document.getElementById('ph-time-display').innerText =
                new Intl.DateTimeFormat('en-US', timeOptions).format(now);

        }

        // UPDATE CLOCK EVERY SECOND

        setInterval(updatePHTime, 1000);

        updatePHTime();

    </script>

    <div class="container">