<?php if (isset($_GET['deleted'])): ?>

    <div class="success-message">
        ✓ Employee deleted successfully.
    </div>

<?php endif; ?>

<?php
require_once "admin_auth.php";
require_once "../db/connect.php";

$admin_username = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Admin';

/*
=========================================================
SEARCH
=========================================================
*/

$search = trim($_GET['search'] ?? '');

/*
=========================================================
EMPLOYEE QUERY
=========================================================
*/

$sql = "
    SELECT
        u.id,
        u.name,
        u.email,
        u.symbol_prefix,
        u.symbol_no,
        u.photo,
        u.is_verified,
        u.is_active,

        CASE
            WHEN fd.user_id IS NOT NULL THEN 1
            ELSE 0
        END AS face_registered

    FROM users u

    LEFT JOIN (
        SELECT DISTINCT user_id
        FROM face_data
    ) fd ON fd.user_id = u.id
";

/*
=========================================================
SEARCH CONDITION
=========================================================
*/

$params = [];
$types = "";

if ($search !== '') {

    $sql .= "
        WHERE
            u.name LIKE ?
            OR u.email LIKE ?
            OR u.symbol_no LIKE ?
            OR CONCAT(u.symbol_prefix, u.symbol_no) LIKE ?
    ";

    $keyword = "%" . $search . "%";

    $params = [
        $keyword,
        $keyword,
        $keyword,
        $keyword
    ];

    $types = "ssss";
}

$sql .= " ORDER BY u.id DESC";

/*
=========================================================
PREPARED STATEMENT
=========================================================
*/

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database query failed: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Employees | YSE Attendance Admin</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {

    font-family: Arial, Helvetica, sans-serif;

    background:
        radial-gradient(circle at top left, rgba(0,255,255,0.08), transparent 35%),
        radial-gradient(circle at bottom right, rgba(255,0,255,0.08), transparent 35%),
        #050505;

    color: #ffffff;

    min-height: 100vh;
}


/* =====================================================
   SIDEBAR
===================================================== */

.sidebar {

    position: fixed;

    left: 0;
    top: 0;

    width: 240px;

    height: 100vh;

    background: #090909;

    border-right: 1px solid rgba(0,255,255,0.25);

    padding: 25px 15px;

    box-shadow:
        0 0 20px rgba(0,255,255,0.08);

}

.logo {

    text-align: center;

    font-size: 24px;

    font-weight: bold;

    letter-spacing: 3px;

    color: #00ffff;

    text-shadow:
        0 0 5px #00ffff,
        0 0 15px #00ffff;

    margin-bottom: 35px;

}

.admin-name {

    text-align: center;

    color: #aaa;

    font-size: 13px;

    margin-bottom: 25px;

}

.nav {

    display: flex;

    flex-direction: column;

    gap: 8px;

}

.nav a {

    text-decoration: none;

    color: #bbb;

    padding: 13px 15px;

    border-radius: 8px;

    transition: 0.25s;

    border: 1px solid transparent;

}

.nav a:hover,
.nav a.active {

    color: #00ffff;

    border-color: rgba(0,255,255,0.35);

    background: rgba(0,255,255,0.06);

    box-shadow:
        0 0 12px rgba(0,255,255,0.12);

}

.nav a.logout {

    color: #ff4dff;

}

.nav a.logout:hover {

    border-color: rgba(255,0,255,0.4);

    background: rgba(255,0,255,0.07);

}


/* =====================================================
   MAIN
===================================================== */

.main {

    margin-left: 240px;

    padding: 35px;

}


/* =====================================================
   HEADER
===================================================== */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 30px;

}

.page-header h1 {

    font-size: 30px;

    color: #ffffff;

    text-shadow:
        0 0 10px rgba(255,255,255,0.15);

}

.page-header p {

    color: #888;

    margin-top: 6px;

}


/* =====================================================
   ADD BUTTON
===================================================== */

.add-btn {

    text-decoration: none;

    padding: 12px 20px;

    border-radius: 8px;

    color: #000;

    font-weight: bold;

    background: #00ffff;

    box-shadow:
        0 0 10px rgba(0,255,255,0.5);

    transition: 0.25s;

}

.add-btn:hover {

    box-shadow:
        0 0 20px rgba(0,255,255,0.8);

    transform: translateY(-1px);

}


/* =====================================================
   SEARCH
===================================================== */

.search-box {

    background: rgba(10,10,10,0.9);

    border: 1px solid rgba(0,255,255,0.2);

    border-radius: 12px;

    padding: 18px;

    margin-bottom: 25px;

}

.search-form {

    display: flex;

    gap: 10px;

}

.search-input {

    flex: 1;

    padding: 12px 15px;

    background: #111;

    border: 1px solid #333;

    border-radius: 7px;

    color: white;

    outline: none;

}

.search-input:focus {

    border-color: #00ffff;

    box-shadow:
        0 0 10px rgba(0,255,255,0.15);

}

.search-btn {

    padding: 12px 22px;

    border: none;

    border-radius: 7px;

    background: #00ffff;

    color: #000;

    font-weight: bold;

    cursor: pointer;

}

.reset-btn {

    display: flex;

    align-items: center;

    padding: 0 18px;

    text-decoration: none;

    border-radius: 7px;

    border: 1px solid #444;

    color: #aaa;

}

.reset-btn:hover {

    color: white;

    border-color: #777;

}


/* =====================================================
   TABLE
===================================================== */

.table-container {

    overflow-x: auto;

    background: rgba(10,10,10,0.92);

    border: 1px solid rgba(0,255,255,0.18);

    border-radius: 12px;

}

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1100px;

}

thead {

    background: rgba(0,255,255,0.05);

}

th {

    padding: 15px;

    text-align: left;

    color: #00ffff;

    font-size: 13px;

    border-bottom: 1px solid rgba(0,255,255,0.2);

    white-space: nowrap;

}

td {

    padding: 14px 15px;

    border-bottom: 1px solid #1d1d1d;

    color: #ddd;

    font-size: 14px;

}

tbody tr:hover {

    background: rgba(255,255,255,0.025);

}


/* =====================================================
   PHOTO
===================================================== */

.employee-photo {

    width: 45px;

    height: 45px;

    border-radius: 50%;

    object-fit: cover;

    border: 2px solid #00ffff;

    box-shadow:
        0 0 8px rgba(0,255,255,0.35);

}

.no-photo {

    width: 45px;

    height: 45px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #181818;

    border: 1px solid #444;

    color: #666;

}


/* =====================================================
   BADGES
===================================================== */

.badge {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

}

.badge-green {

    color: #00ff99;

    background: rgba(0,255,153,0.08);

    border: 1px solid rgba(0,255,153,0.3);

}

.badge-red {

    color: #ff5577;

    background: rgba(255,50,80,0.08);

    border: 1px solid rgba(255,50,80,0.3);

}

.badge-cyan {

    color: #00ffff;

    background: rgba(0,255,255,0.08);

    border: 1px solid rgba(0,255,255,0.3);

}


/* =====================================================
   ACTIVE / INACTIVE STATUS
===================================================== */

.status {

    display: inline-block;

    padding: 5px 10px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

    letter-spacing: 1px;

}

.status-active {

    color: #00ffff;

    background: rgba(0,255,255,0.08);

    border: 1px solid rgba(0,255,255,0.35);

}

.status-inactive {

    color: #ff5577;

    background: rgba(255,50,80,0.08);

    border: 1px solid rgba(255,50,80,0.35);

}


/* =====================================================
   ACTIONS
===================================================== */

.actions {

    display: flex;

    gap: 7px;

    flex-wrap: wrap;

}

.action-btn {

    padding: 7px 11px;

    border-radius: 6px;

    text-decoration: none;

    font-size: 12px;

    font-weight: bold;

    transition: 0.2s;

    white-space: nowrap;

}

.view-btn {

    color: #00ffff;

    border: 1px solid rgba(0,255,255,0.3);

}

.edit-btn {

    color: #ffff00;

    border: 1px solid rgba(255,255,0,0.3);

}

.status-btn {

    color: #ff5577;

    border: 1px solid rgba(255,80,100,0.35);

}

.status-btn.activate {

    color: #00ff99;

    border-color: rgba(0,255,153,0.35);

}

.action-btn:hover {

    background: rgba(255,255,255,0.06);

    transform: translateY(-1px);

}


/* =====================================================
   EMPTY
===================================================== */

.empty {

    text-align: center;

    padding: 50px;

    color: #777;

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 800px) {

    .sidebar {

        width: 190px;

    }

    .main {

        margin-left: 190px;

        padding: 20px;

    }

    .page-header {

        align-items: flex-start;

        flex-direction: column;

    }

}

.success-message {
    margin-bottom: 20px;
    padding: 14px 18px;
    border-radius: 8px;

    color: #00ff99;
    background: rgba(0,255,153,0.08);
    border: 1px solid rgba(0,255,153,0.3);

    font-weight: bold;
}

</style>

</head>

<body>

<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar">

```
<div class="logo">
    YSE ADMIN
</div>

<div class="admin-name">
    👤 <?= htmlspecialchars($admin_username) ?>
</div>

<div class="nav">

    <a href="dashboard.php">
        📊 Dashboard
    </a>

    <a href="employees.php" class="active">
        👥 Employees
    </a>

    <a href="attendance.php">
        📋 Attendance
    </a>

    <a href="monthly_report.php">
        📅 Monthly Report
    </a>

    <a href="correction_history.php">
        📝 Correction History
    </a>

    <a href="../index.php">
        🌐 Main Website
    </a>

    <a href="logout.php" class="logout">
        🚪 Logout
    </a>

</div>
```

</div>

<!-- =====================================================
     MAIN
===================================================== -->

<div class="main">

```
<!-- PAGE HEADER -->

<div class="page-header">

    <div>

        <h1>Employee Management</h1>

        <p>
            Manage employees, account status and facial-recognition registration.
        </p>

    </div>

    <a href="employee_add.php" class="add-btn">
        ＋ Add Employee
    </a>

</div>


<!-- =================================================
     SEARCH
================================================== -->

<div class="search-box">

    <form method="GET" class="search-form">

        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search name, email or symbol number..."
            value="<?= htmlspecialchars($search) ?>"
        >

        <button type="submit" class="search-btn">
            SEARCH
        </button>

        <?php if ($search !== ''): ?>

            <a href="employees.php" class="reset-btn">
                RESET
            </a>

        <?php endif; ?>

    </form>

</div>


<!-- =================================================
     EMPLOYEE TABLE
================================================== -->

<div class="table-container">

    <table>

        <thead>

            <tr>

                <th>Photo</th>

                <th>Employee</th>

                <th>Symbol</th>

                <th>Email</th>

                <th>Account</th>

                <th>Face</th>

                <th>Status</th>

                <th>Actions</th>

            </tr>

        </thead>


        <tbody>

        <?php if ($result->num_rows > 0): ?>

            <?php while ($employee = $result->fetch_assoc()): ?>

                <tr>


                    <!-- PHOTO -->

                    <td>

                        <?php if (!empty($employee['photo'])): ?>

                            <img
                                src="../<?= htmlspecialchars($employee['photo']) ?>"
                                class="employee-photo"
                                alt="Employee photo"
                            >

                        <?php else: ?>

                            <div class="no-photo">
                                👤
                            </div>

                        <?php endif; ?>

                    </td>


                    <!-- NAME -->

                    <td>

                        <strong>
                            <?= htmlspecialchars($employee['name']) ?>
                        </strong>

                    </td>


                    <!-- SYMBOL -->

                    <td>

                        <span class="badge badge-cyan">

                            <?= htmlspecialchars(
                                ($employee['symbol_prefix'] ?? '') .
                                ($employee['symbol_no'] ?? '')
                            ) ?>

                        </span>

                    </td>


                    <!-- EMAIL -->

                    <td>

                        <?= htmlspecialchars($employee['email']) ?>

                    </td>


                    <!-- ACCOUNT VERIFICATION -->

                    <td>

                        <?php if ((int)$employee['is_verified'] === 1): ?>

                            <span class="badge badge-green">
                                ✓ Verified
                            </span>

                        <?php else: ?>

                            <span class="badge badge-red">
                                ✕ Unverified
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- FACE REGISTRATION -->

                    <td>

                        <?php if ((int)$employee['face_registered'] === 1): ?>

                            <span class="badge badge-green">
                                ✓ Registered
                            </span>

                        <?php else: ?>

                            <span class="badge badge-red">
                                ✕ Not Registered
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- ACTIVE STATUS -->

                    <td>

                        <?php if ((int)$employee['is_active'] === 1): ?>

                            <span class="status status-active">
                                ACTIVE
                            </span>

                        <?php else: ?>

                            <span class="status status-inactive">
                                INACTIVE
                            </span>

                        <?php endif; ?>

                    </td>


                    <!-- ACTIONS -->

                    <td>

                      <div class="actions">

    <a
        href="employee_view.php?id=<?= (int)$employee['id'] ?>"
        class="action-btn view-btn"
    >
        VIEW
    </a>

    <a
        href="employee_edit.php?id=<?= (int)$employee['id'] ?>"
        class="action-btn edit-btn"
    >
        EDIT
    </a>

    <a
        href="employee_delete.php?id=<?= (int)$employee['id'] ?>"
        class="action-btn delete-btn"
        onclick="return confirm('⚠️ Are you sure you want to permanently delete <?= htmlspecialchars($employee['name'], ENT_QUOTES) ?>?\\n\\nThis will remove the employee, face data, attendance history and photo from the database/system.');"
    >
        DELETE
    </a>

</div>


                            <!-- ACTIVATE / DEACTIVATE -->

                            <?php if ((int)$employee['is_active'] === 1): ?>

                                <a
                                    href="employee_toggle_status.php?id=<?= (int)$employee['id'] ?>"
                                    class="action-btn status-btn"
                                    onclick="return confirm('Are you sure you want to deactivate <?= htmlspecialchars($employee['name'], ENT_QUOTES) ?>?');"
                                >
                                    DEACTIVATE
                                </a>

                            <?php else: ?>

                                <a
                                    href="employee_toggle_status.php?id=<?= (int)$employee['id'] ?>"
                                    class="action-btn status-btn activate"
                                    onclick="return confirm('Are you sure you want to activate <?= htmlspecialchars($employee['name'], ENT_QUOTES) ?>?');"
                                >
                                    ACTIVATE
                                </a>

                            <?php endif; ?>


                        </div>

                    </td>


                </tr>

            <?php endwhile; ?>


        <?php else: ?>

            <tr>

                <td colspan="8" class="empty">

                    No employees found.

                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

</div>

</body>

</html>

<?php

$stmt->close();

?>
