<?php
session_start(); // Spustiť reláciu
session_unset(); // Odstrániť všetky premenné relácie
session_destroy(); // Zničiť reláciu
header("Location: admin_panel.php"); // Presmerovať na index.php po odhlásení
exit();
?>
