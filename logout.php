<?php
session_start(); // Spustiť reláciu
session_unset(); // Odstrániť všetky premenné relácie
session_destroy(); // Zničiť reláciu
header("Location: index.php"); // Presmerovať na index.php po odhlásení
exit();
?>
