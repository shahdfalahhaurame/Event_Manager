<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$page_title = $page_title ?? 'Event Manager';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/icons/logo.svg" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Include jQuery and DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>

</head>

<body class=" text-white" style="background-color: #101011;">
    <div class="flex flex-1 md:flex-row flex-col">

        <?php
            require_once 'sidebar.php';
        ?>

        <main class="flex-1 md:p-8 p-4">
            <header class="bg-[#1f1f1f] shadow-lg rounded-lg p-4 mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($page_title); ?></h1>
            </header>

