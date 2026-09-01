<?php

session_start();

include "db/connect.php";


$users = $conn->query(
"SELECT symbol_no,name,photo 
 FROM users
 WHERE is_verified=1"
);


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System</title>
    <style>
        :root {
            --neon-cyan: #00f3ff;
            --neon-pink: #ff0055;
            --neon-green: #39ff14;
            --bg-color: #0d0e15;
            --panel-bg: #161925;
        }

        body {
            background-color: var(--bg-color);
            color: #ffffff;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Main Container matching the wireframe layout */
        .system-container {
            background: var(--panel-bg);
            border: 2px solid var(--neon-cyan);
            border-radius: 16px;
            width: 100%;
            max-width: 800px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 243, 255, 0.2),
                        inset 0 0 15px rgba(0, 243, 255, 0.1);
            position: relative;
        }

        /* Top Bar & Header */
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
            text-shadow: 0 0 10px var(--neon-cyan),
                         0 0 20px var(--neon-cyan);
        }

        /* "Be a new member" Link at top right */
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
            text-shadow: 0 0 8px var(--neon-pink);
        }

        .new-member-btn svg {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
        }

        /* Main Layout Grid split into Member List and Actions */
        .main-layout {
            display: grid;
            grid-template-columns: 1fr 240px;
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

        /* Neon Table Styling */
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(0, 243, 255, 0.2);
        }

        .attendance-table th, 
        .attendance-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 243, 255, 0.1);
        }

        .attendance-table th {
            background: rgba(0, 243, 255, 0.05);
            color: var(--neon-cyan);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Interactive data rows */
        .selectable-row {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .selectable-row:hover {
            background: rgba(0, 243, 255, 0.08);
        }

        /* High-intensity Neon Selection Effect */
        .selectable-row.selected {
            background: rgba(0, 243, 255, 0.2) !important;
            box-shadow: inset 0 0 12px rgba(0, 243, 255, 0.5);
        }
        
        .selectable-row.selected td {
            color: #ffffff;
            font-weight: bold;
            text-shadow: 0 0 5px var(--neon-cyan);
            border-bottom: 1px solid var(--neon-cyan);
        }

        .attendance-table td {
            color: #e0e0e3;
            font-size: 15px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid var(--neon-cyan);
            box-shadow: 0 0 5px rgba(0, 243, 255, 0.5);
            display: block;
            transition: transform 0.2s ease;
        }
        
        .selectable-row.selected .user-avatar {
            transform: scale(1.1);
            box-shadow: 0 0 10px var(--neon-cyan);
        }

        /* Neon Action Buttons Panel */
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
    transition: all 0.3s ease;
    background: transparent;

    /* disabled */
    pointer-events: none;
    opacity: 0.4;
    filter: grayscale(80%);
}

.action-panel.user-chosen .neon-btn {
    pointer-events: auto;
    opacity: 1;
    filter: none;
    cursor: pointer;
}

        /* Enabled State via JavaScript */
        .action-panel.user-chosen .neon-btn {
            pointer-events: auto;
            opacity: 1;
            filter: none;
        }

        /* Individual Neon Button Color States */
        .btn-clock-in {
            color: var(--neon-green);
            border: 2px solid var(--neon-green);
            box-shadow: inset 0 0 8px rgba(57, 255, 20, 0.1), 0 0 8px rgba(57, 255, 20, 0.1);
        }
        .action-panel.user-chosen .btn-clock-in:hover {
            background: var(--neon-green);
            color: #000;
            box-shadow: 0 0 20px var(--neon-green);
        }

        .btn-break-start, .btn-break-end {
            color: var(--neon-cyan);
            border: 2px solid var(--neon-cyan);
            box-shadow: inset 0 0 8px rgba(0, 243, 255, 0.1), 0 0 8px rgba(0, 243, 255, 0.1);
        }
        .action-panel.user-chosen .btn-break-start:hover, 
        .action-panel.user-chosen .btn-break-end:hover {
            background: var(--neon-cyan);
            color: #000;
            box-shadow: 0 0 20px var(--neon-cyan);
        }

        .btn-clock-out {
            color: var(--neon-pink);
            border: 2px solid var(--neon-pink);
            box-shadow: inset 0 0 8px rgba(255, 0, 85, 0.1), 0 0 8px rgba(255, 0, 85, 0.1);
        }
        .action-panel.user-chosen .btn-clock-out:hover {
            background: var(--neon-pink);
            color: #000;
            box-shadow: 0 0 20px var(--neon-pink);
        }

        /* Live Timestamp display */
        .timestamp {
            text-align: center;
            margin-top: 25px;
            font-family: monospace;
            color: rgba(255, 255, 255, 0.4);
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="system-container">
    
    <div class="header-section">
        <h1 class="system-title">Attendance System</h1>
        
        <a href="register.php" class="new-member-btn">
            <svg viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Be a new member</span>
        </a>
    </div>

    <div class="main-layout">
        
        <div class="table-responsive">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Symbol no.</th>
                        <th>Name</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody>

<?php

while($user = $users->fetch_assoc()){

?>


<tr class="selectable-row"
data-symbol="<?php echo $user['symbol_no']; ?>">


<td>
<?php echo $user['symbol_no']; ?>
</td>


<td>
<?php echo $user['name']; ?>
</td>


<td>

<?php

if(!empty($user['photo'])){

?>

<img 
src="uploads/<?php echo $user['photo']; ?>"
class="user-avatar">

<?php

}
else{

?>

<img 
src="https://picsum.photos/50"
class="user-avatar">

<?php

}

?>

</td>


</tr>


<?php

}

?>

</tbody>
            </table>
        </div>

        <div class="action-panel" id="actionPanel">
            <a class="neon-btn btn-clock-in" id="btn-in" href="scan.php?code=&type=出勤">出勤</a>
            <a class="neon-btn btn-break-start" id="btn-bstart" href="scan.php?code=&type=休憩入り">休憩入り</a>
            <a class="neon-btn btn-break-end" id="btn-bend" href="scan.php?code=&type=休憩戻り">休憩戻り</a>
            <a class="neon-btn btn-clock-out" id="btn-out" href="scan.php?code=&type=退勤">退勤</a>

        </div>
        
    </div>

    <div class="timestamp" id="live-clock">
        <?php echo date("Y-m-d H:i:s"); ?>
    </div>

</div>

<script>
    // Selection Management Script
    const rows = document.querySelectorAll('.selectable-row');
    const panel = document.getElementById('actionPanel');
    
    const btnIn = document.getElementById('btn-in');
    const btnBStart = document.getElementById('btn-bstart');
    const btnBEnd = document.getElementById('btn-bend');
    const btnOut = document.getElementById('btn-out');

    rows.forEach(row => {
        row.addEventListener('click', () => {
            // Remove previous selections
            rows.forEach(r => r.classList.remove('selected'));
            
            // Set selection target
            row.classList.add('selected');
            const symbolNumber = row.getAttribute('data-symbol');

            // Map and update the href dynamic URLs with code query values
            btnIn.href = `scan.php?code=${symbolNumber}&type=出勤`;
            btnBStart.href = `scan.php?code=${symbolNumber}&type=休憩入り`;
            btnBEnd.href = `scan.php?code=${symbolNumber}&type=休憩戻り`;
            btnOut.href = `scan.php?code=${symbolNumber}&type=退勤`;

            // Make panel components interactive
            panel.classList.add('user-chosen');
        });
    });

    // Live update for the clock
    function updateClock() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        document.getElementById('live-clock').textContent = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }
    setInterval(updateClock, 1000);
</script>

</body>
</html>