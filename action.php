<?php

session_start();

include "db/connect.php";


/* =========================================
   GET VERIFIED USERS
========================================= */

$users = $conn->query("
    SELECT
        symbol_no,
        name,
        photo
    FROM users
    WHERE is_verified = 1
    AND is_active = 1
    ORDER BY symbol_no ASC
");


if (!$users) {
    die("Failed to load employees: " . $conn->error);
}

?>
<!DOCTYPE html>

<html lang="ja">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Attendance System</title>


    <style>

        :root {

            --neon-cyan: #00f3ff;

            --neon-pink: #ff0055;

            --neon-green: #39ff14;

            --bg-color: #0d0e15;

            --panel-bg: #161925;

        }


        * {

            box-sizing: border-box;

        }


        body {

            background-color: var(--bg-color);

            color: #ffffff;

            font-family:
                'Segoe UI',
                Roboto,
                Helvetica,
                Arial,
                sans-serif;

            margin: 0;

            padding: 20px;

            display: flex;

            justify-content: center;

            align-items: center;

            min-height: 100vh;

        }


        /* =========================================
           MAIN CONTAINER
        ========================================= */

        .system-container {

            background: var(--panel-bg);

            border:
                2px solid
                var(--neon-cyan);

            border-radius: 16px;

            width: 100%;

            max-width: 800px;

            padding: 30px;

            box-shadow:
                0 0 15px
                rgba(0, 243, 255, 0.2),

                inset 0 0 15px
                rgba(0, 243, 255, 0.1);

            position: relative;

        }


        /* =========================================
           HEADER
        ========================================= */

        .header-section {

            text-align: center;

            margin-bottom: 40px;

            position: relative;

        }


        .system-title {

            font-size: 28px;

            letter-spacing: 4px;

            margin: 0;

            text-transform: uppercase;

            color: #fff;

            text-shadow:
                0 0 10px var(--neon-cyan),
                0 0 20px var(--neon-cyan);

        }


        .new-member-btn {

            position: absolute;

            top: 0;

            right: 0;

            display: flex;

            align-items: center;

            gap: 8px;

            text-decoration: none;

            color: #fff;

            font-size: 13px;

            transition: all 0.3s ease;

        }


        .new-member-btn:hover {

            color: var(--neon-pink);

            text-shadow:
                0 0 8px
                var(--neon-pink);

        }


        .new-member-btn svg {

            width: 24px;

            height: 24px;

            fill: none;

            stroke: currentColor;

            stroke-width: 2;

        }


        /* =========================================
           MAIN LAYOUT
        ========================================= */

        .main-layout {

            display: grid;

            grid-template-columns:
                1fr 240px;

            gap: 30px;

            align-items: start;

        }


        @media (max-width: 650px) {

            .main-layout {

                grid-template-columns: 1fr;

            }


            .new-member-btn {

                position: static;

                justify-content: center;

                margin-top: 15px;

            }

        }


        /* =========================================
           TABLE
        ========================================= */

        .attendance-table {

            width: 100%;

            border-collapse: collapse;

            background:
                rgba(255, 255, 255, 0.02);

            border-radius: 8px;

            overflow: hidden;

            border:
                1px solid
                rgba(0, 243, 255, 0.2);

        }


        .attendance-table th,
        .attendance-table td {

            padding: 12px 15px;

            text-align: left;

            border-bottom:
                1px solid
                rgba(0, 243, 255, 0.1);

        }


        .attendance-table th {

            background:
                rgba(0, 243, 255, 0.05);

            color: var(--neon-cyan);

            font-size: 14px;

            text-transform: uppercase;

            letter-spacing: 1px;

        }


        .attendance-table td {

            color: #e0e0e3;

            font-size: 15px;

        }


        /* =========================================
           SELECTABLE ROW
        ========================================= */

        .selectable-row {

            cursor: pointer;

            transition:
                all 0.2s ease;

        }


        .selectable-row:hover {

            background:
                rgba(0, 243, 255, 0.08);

        }


        .selectable-row.selected {

            background:
                rgba(0, 243, 255, 0.2)
                !important;

            box-shadow:
                inset 0 0 12px
                rgba(0, 243, 255, 0.5);

        }


        .selectable-row.selected td {

            color: #ffffff;

            font-weight: bold;

            text-shadow:
                0 0 5px
                var(--neon-cyan);

            border-bottom:
                1px solid
                var(--neon-cyan);

        }


        /* =========================================
           EMPLOYEE IMAGE
        ========================================= */

        .user-avatar {

            width: 45px;

            height: 45px;

            border-radius: 50%;

            object-fit: cover;

            border:
                2px solid
                var(--neon-cyan);

            box-shadow:
                0 0 8px
                rgba(0, 243, 255, 0.5);

            display: block;

            transition:
                transform 0.2s ease;

        }


        .selectable-row.selected
        .user-avatar {

            transform:
                scale(1.1);

            box-shadow:
                0 0 15px
                var(--neon-cyan);

        }


        .no-photo {

            width: 45px;

            height: 45px;

            border-radius: 50%;

            border:
                2px solid
                #555;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #111827;

            color: #68768b;

            font-size: 10px;

            text-align: center;

        }


        /* =========================================
           ACTION PANEL
        ========================================= */

        .action-panel {

            display: flex;

            flex-direction: column;

            gap: 15px;

        }


        .neon-btn {

            display: block;

            width: 100%;

            box-sizing: border-box;

            padding: 14px 20px;

            text-align: center;

            text-decoration: none;

            font-size: 18px;

            font-weight: bold;

            letter-spacing: 2px;

            border-radius: 50px;

            transition:
                all 0.3s ease;

            background: transparent;

            pointer-events: none;

            opacity: 0.4;

            filter: grayscale(80%);

        }


        .action-panel.user-chosen
        .neon-btn {

            pointer-events: auto;

            opacity: 1;

            filter: none;

            cursor: pointer;

        }


        /* =========================================
           CLOCK IN
        ========================================= */

        .btn-clock-in {

            color:
                var(--neon-green);

            border:
                2px solid
                var(--neon-green);

            box-shadow:
                inset 0 0 8px
                rgba(57, 255, 20, 0.1),

                0 0 8px
                rgba(57, 255, 20, 0.1);

        }


        .action-panel.user-chosen
        .btn-clock-in:hover {

            background:
                var(--neon-green);

            color: #000;

            box-shadow:
                0 0 20px
                var(--neon-green);

        }


        /* =========================================
           BREAK
        ========================================= */

        .btn-break-start,
        .btn-break-end {

            color:
                var(--neon-cyan);

            border:
                2px solid
                var(--neon-cyan);

            box-shadow:
                inset 0 0 8px
                rgba(0, 243, 255, 0.1),

                0 0 8px
                rgba(0, 243, 255, 0.1);

        }


        .action-panel.user-chosen
        .btn-break-start:hover,

        .action-panel.user-chosen
        .btn-break-end:hover {

            background:
                var(--neon-cyan);

            color: #000;

            box-shadow:
                0 0 20px
                var(--neon-cyan);

        }


        /* =========================================
           CLOCK OUT
        ========================================= */

        .btn-clock-out {

            color:
                var(--neon-pink);

            border:
                2px solid
                var(--neon-pink);

            box-shadow:
                inset 0 0 8px
                rgba(255, 0, 85, 0.1),

                0 0 8px
                rgba(255, 0, 85, 0.1);

        }


        .action-panel.user-chosen
        .btn-clock-out:hover {

            background:
                var(--neon-pink);

            color: #000;

            box-shadow:
                0 0 20px
                var(--neon-pink);

        }


        /* =========================================
           CLOCK
        ========================================= */

        .timestamp {

            text-align: center;

            margin-top: 25px;

            font-family: monospace;

            color:
                rgba(255, 255, 255, 0.4);

            font-size: 13px;

        }

    </style>

</head>


<body>


<div class="system-container">


    <!-- =========================================
         HEADER
    ========================================= -->

    <div class="header-section">

        <h1 class="system-title">
            Attendance System
        </h1>


        <a
            href="register.php"
            class="new-member-btn"
        >

            <svg viewBox="0 0 24 24">

                <path
                    d="
                    M20 21v-2a4 4 0 0 0-4-4H8
                    a4 4 0 0 0-4 4v2
                    "
                ></path>

                <circle
                    cx="12"
                    cy="7"
                    r="4"
                ></circle>

            </svg>

            <span>
                Be a new member
            </span>

        </a>

    </div>


    <!-- =========================================
         MAIN
    ========================================= -->

    <div class="main-layout">


        <!-- =====================================
             EMPLOYEE TABLE
        ====================================== -->

        <div class="table-responsive">

            <table class="attendance-table">

                <thead>

                    <tr>

                        <th>
                            Symbol no.
                        </th>

                        <th>
                            Name
                        </th>

                        <th>
                            Image
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php while (
                    $user =
                    $users->fetch_assoc()
                ): ?>


                    <?php

                    /*
                     * PHOTO PATH
                     *
                     * Database already contains:
                     *
                     * uploads/employee_xxx.jpg
                     *
                     * So DO NOT add uploads/
                     * again.
                     */

                    $photo =
                        trim(
                            $user['photo'] ?? ''
                        );

                    ?>


                    <tr
                        class="selectable-row"

                        data-symbol="<?=
                            htmlspecialchars(
                                $user['symbol_no'],
                                ENT_QUOTES,
                                'UTF-8'
                            )
                        ?>"
                    >


                        <!-- SYMBOL -->

                        <td>

                            <?= htmlspecialchars(
                                $user['symbol_no']
                            ) ?>

                        </td>


                        <!-- NAME -->

                        <td>

                            <?= htmlspecialchars(
                                $user['name']
                            ) ?>

                        </td>


                        <!-- IMAGE -->

                        <td>


                            <?php if (
                                $photo !== ''
                                &&
                                $photo !== 'default.png'
                            ): ?>


                                <img

                                    src="<?=
                                        htmlspecialchars(
                                            $photo,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )
                                    ?>"

                                    class="user-avatar"

                                    alt="Employee photo"

                                    onerror="
                                        this.style.display='none';
                                        this.nextElementSibling
                                        .style.display='flex';
                                    "
                                >


                                <div
                                    class="no-photo"
                                    style="display:none;"
                                >
                                    No Image
                                </div>


                            <?php else: ?>


                                <div class="no-photo">

                                    No Image

                                </div>


                            <?php endif; ?>


                        </td>


                    </tr>


                <?php endwhile; ?>


                </tbody>

            </table>

        </div>


        <!-- =====================================
             ACTION BUTTONS
        ====================================== -->

        <div
            class="action-panel"
            id="actionPanel"
        >

            <a
                class="
                    neon-btn
                    btn-clock-in
                "

                id="btn-in"

                href="scan.php?code=&type=出勤"
            >
                出勤
            </a>


            <a
                class="
                    neon-btn
                    btn-break-start
                "

                id="btn-bstart"

                href="scan.php?code=&type=休憩入り"
            >
                休憩入り
            </a>


            <a
                class="
                    neon-btn
                    btn-break-end
                "

                id="btn-bend"

                href="scan.php?code=&type=休憩戻り"
            >
                休憩戻り
            </a>


            <a
                class="
                    neon-btn
                    btn-clock-out
                "

                id="btn-out"

                href="scan.php?code=&type=退勤"
            >
                退勤
            </a>

        </div>


    </div>


    <!-- =========================================
         LIVE CLOCK
    ========================================= -->

    <div
        class="timestamp"
        id="live-clock"
    >

        <?= date("Y-m-d H:i:s") ?>

    </div>


</div>


<script>


/* =========================================
   SELECTION
========================================= */

const rows =
    document.querySelectorAll(
        '.selectable-row'
    );


const panel =
    document.getElementById(
        'actionPanel'
    );


const btnIn =
    document.getElementById(
        'btn-in'
    );


const btnBStart =
    document.getElementById(
        'btn-bstart'
    );


const btnBEnd =
    document.getElementById(
        'btn-bend'
    );


const btnOut =
    document.getElementById(
        'btn-out'
    );


rows.forEach(row => {


    row.addEventListener(
        'click',
        () => {


            /* Remove old selection */

            rows.forEach(r => {

                r.classList.remove(
                    'selected'
                );

            });


            /* Select this employee */

            row.classList.add(
                'selected'
            );


            const symbolNumber =
                row.getAttribute(
                    'data-symbol'
                );


            /*
             * Encode symbol number so
             * special characters cannot
             * break the URL.
             */

            const encodedSymbol =
                encodeURIComponent(
                    symbolNumber
                );


            /* Update action links */

            btnIn.href =
                `scan.php?code=${encodedSymbol}&type=${encodeURIComponent('出勤')}`;


            btnBStart.href =
                `scan.php?code=${encodedSymbol}&type=${encodeURIComponent('休憩入り')}`;


            btnBEnd.href =
                `scan.php?code=${encodedSymbol}&type=${encodeURIComponent('休憩戻り')}`;


            btnOut.href =
                `scan.php?code=${encodedSymbol}&type=${encodeURIComponent('退勤')}`;


            /* Enable buttons */

            panel.classList.add(
                'user-chosen'
            );

        }
    );

});


/* =========================================
   LIVE CLOCK
========================================= */

function updateClock() {


    const now =
        new Date();


    const year =
        now.getFullYear();


    const month =
        String(
            now.getMonth() + 1
        ).padStart(2, '0');


    const day =
        String(
            now.getDate()
        ).padStart(2, '0');


    const hours =
        String(
            now.getHours()
        ).padStart(2, '0');


    const minutes =
        String(
            now.getMinutes()
        ).padStart(2, '0');


    const seconds =
        String(
            now.getSeconds()
        ).padStart(2, '0');


    document.getElementById(
        'live-clock'
    ).textContent =

        `${year}-${month}-${day} ` +
        `${hours}:${minutes}:${seconds}`;

}


setInterval(
    updateClock,
    1000
);


</script>


</body>

</html>
