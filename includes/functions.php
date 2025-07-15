<?php
function isRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}
?>
