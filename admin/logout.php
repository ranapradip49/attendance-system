<?php

session_start();


/* =====================================
   REMOVE ADMIN SESSION
===================================== */

unset($_SESSION['admin_id']);

unset($_SESSION['admin_username']);


/* =====================================
   DESTROY SESSION
===================================== */

session_destroy();


/* =====================================
   RETURN TO LOGIN
===================================== */

header(
    "Location: login.php"
);

exit();

?>