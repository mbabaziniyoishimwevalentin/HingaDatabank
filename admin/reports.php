<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$db = (new Database())->getConnection();

function countTable($db, $table) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM $table");
    $stmt->execute();
    return $stmt->fetchColumn();
}

$farmers = $db->prepare("SELECT COUNT(*) FROM users WHERE role = 'farmer'");
$farmers->execute();
$total_farmers = $farmers->fetchColumn();

$institutions = $db->prepare("SELECT COUNT(*) FROM users WHERE role = 'institution'");
$institutions->execute();
$total_institutions = $institutions->fetchColumn();
?>
<div class="bg-white rounded-xl shadow-lg border border-green-200 p-8 w-full max-w-lg mx-auto">
    <h2 class="text-2xl font-bold text-center text-green-800 mb-6 flex items-center justify-center">
        <i data-feather="bar-chart-2" class="w-6 h-6 mr-2 text-green-500"></i>
        Platform Reports
    </h2>
    <ul class="space-y-4">
        <li class="flex items-center justify-between bg-green-50 rounded-lg px-4 py-3 border border-green-100">
            <span class="flex items-center"><i data-feather="user" class="w-5 h-5 mr-2 text-green-500"></i> <span class="font-semibold">Total Farmers</span></span>
            <span class="text-2xl font-bold text-green-700"><?= $total_farmers ?></span>
        </li>
        <li class="flex items-center justify-between bg-blue-50 rounded-lg px-4 py-3 border border-blue-100">
            <span class="flex items-center"><i data-feather="building" class="w-5 h-5 mr-2 text-blue-500"></i> <span class="font-semibold">Total Institutions</span></span>
            <span class="text-2xl font-bold text-blue-700"><?= $total_institutions ?></span>
        </li>
        <li class="flex items-center justify-between bg-yellow-50 rounded-lg px-4 py-3 border border-yellow-100">
            <span class="flex items-center"><i data-feather="dollar-sign" class="w-5 h-5 mr-2 text-yellow-500"></i> <span class="font-semibold">Total Loans</span></span>
            <span class="text-2xl font-bold text-yellow-700"><?= countTable($db, 'loan_requests') ?></span>
        </li>
        <li class="flex items-center justify-between bg-green-100 rounded-lg px-4 py-3 border border-green-200">
            <span class="flex items-center"><i data-feather="leaf" class="w-5 h-5 mr-2 text-green-600"></i> <span class="font-semibold">Total Crops</span></span>
            <span class="text-2xl font-bold text-green-800"><?= countTable($db, 'crops') ?></span>
        </li>
        <li class="flex items-center justify-between bg-orange-50 rounded-lg px-4 py-3 border border-orange-100">
            <span class="flex items-center"><i data-feather="package" class="w-5 h-5 mr-2 text-orange-500"></i> <span class="font-semibold">Total Harvests</span></span>
            <span class="text-2xl font-bold text-orange-700"><?= countTable($db, 'harvests') ?></span>
        </li>
        <li class="flex items-center justify-between bg-red-50 rounded-lg px-4 py-3 border border-red-100">
            <span class="flex items-center"><i data-feather="heart" class="w-5 h-5 mr-2 text-red-500"></i> <span class="font-semibold">Total Livestock Records</span></span>
            <span class="text-2xl font-bold text-red-700"><?= countTable($db, 'livestock') ?></span>
        </li>
    </ul>
</div>
<script>if(window.feather) feather.replace();</script>
