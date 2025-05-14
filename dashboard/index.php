<?php
$page_title = 'Dashboard';
require_once '../includes/header.php';

require_once '../includes/db.php';

// --- Fetch Dashboard Data for the Logged-in User ---
$user_created_events_count = 0;
$user_upcoming_events_count = 0;
$user_needs_count = 0;

$logged_in_user_id = $_SESSION['user_id'] ?? null;

if ($logged_in_user_id !== null && isset($conn) && $conn !== null) {

    // Query 1: Count total events created by the logged-in user
    $sql_total_events = "SELECT COUNT(*) AS total FROM events WHERE created_by = ?";
    $stmt_total = $conn->prepare($sql_total_events);
    if ($stmt_total) {
        $stmt_total->bind_param("i", $logged_in_user_id);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        if ($result_total && $row = $result_total->fetch_assoc()) {
            $user_created_events_count = $row['total'];
        }
        $stmt_total->close();
    } else {
         error_log("Dashboard SQL Prepare failed (total events): (" . $conn->errno . ") " . $conn->error);
    }


    // Query 2: Count upcoming events created by the logged-in user
    $sql_upcoming_events = "SELECT COUNT(*) AS total FROM events WHERE created_by = ? AND date > CURDATE()";
    $stmt_upcoming = $conn->prepare($sql_upcoming_events);
     if ($stmt_upcoming) {
        $stmt_upcoming->bind_param("i", $logged_in_user_id);
        $stmt_upcoming->execute();
        $result_upcoming = $stmt_upcoming->get_result();
        if ($result_upcoming && $row = $result_upcoming->fetch_assoc()) {
            $user_upcoming_events_count = $row['total'];
        }
        $stmt_upcoming->close();
    } else {
         error_log("Dashboard SQL Prepare failed (upcoming events): (" . $conn->errno . ") " . $conn->error);
    }


    // Query 3: Count needs associated with events created by the logged-in user
    $sql_user_needs = "SELECT COUNT(n.id) AS total
                       FROM needs n
                       JOIN events e ON n.event_id = e.id
                       WHERE e.created_by = ?";
    $stmt_needs = $conn->prepare($sql_user_needs);
     if ($stmt_needs) {
        $stmt_needs->bind_param("i", $logged_in_user_id);
        $stmt_needs->execute();
        $result_needs = $stmt_needs->get_result();
        if ($result_needs && $row = $result_needs->fetch_assoc()) {
            $user_needs_count = $row['total'];
        }
        $stmt_needs->close();
    } else {
         error_log("Dashboard SQL Prepare failed (user needs): (" . $conn->errno . ") " . $conn->error);
    }


    // Close the database connection if opened here
    if ($conn !== null && $conn instanceof mysqli) {
        $conn->close();
    }

} else {
    // Handle the case where the user is not logged in or the connection is not valid
    echo "<p style='color: red;'>User not logged in or database connection failed.</p>";
    exit;
}

?>

<div class="flex flex-col items-start mb-6">
    <h1 class="text-2xl font-size font-semibold">Dashboard</h1>
    <div class="flex flex-row items-center space-x-2 mt-1">
        <p class="text-gray-400 text-sm">Home</p>
        <img class="w-4" src="../assets/icons/arrow-solid-Right.svg" alt="" srcset="">
        <p class="text-[#1A71F6] text-sm font-bold">Dashboard </p>
    </div>
</div>


<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

    <div class="bg-[#1f1f1f] rounded-lg p-6 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-300">My Created Events</h3>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        </div>
        <p class="text-4xl font-bold text-white"><?php echo htmlspecialchars($user_created_events_count); ?></p>
        <p class="text-sm text-gray-400 mt-2">Total events you have created.</p>
    </div>

    <div class="bg-[#1f1f1f] rounded-lg p-6 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-300">My Upcoming Events</h3>
             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M3 10h18"></path><path d="M8 14h.01"></path><path d="M16 14h.01"></path><path d="M12 18h.01"></path><path d="M12 14h.01"></path><path d="M8 18h.01"></path><path d="M16 18h.01"></path></svg>
        </div>
        <p class="text-4xl font-bold text-white"><?php echo htmlspecialchars($user_upcoming_events_count); ?></p>
        <p class="text-sm text-gray-400 mt-2">Upcoming events scheduled after today.</p>
    </div>

    <div class="bg-[#1f1f1f] rounded-lg p-6 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-300">Needs in My Events</h3>
             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-purple-500"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        </div>
        <p class="text-4xl font-bold text-white"><?php echo htmlspecialchars($user_needs_count); ?></p>
        <p class="text-sm text-gray-400 mt-2">Total needs listed in your events.</p>
    </div>

    </div>

<div class="bg-[#1f1f1f] rounded-lg p-6 shadow-lg mb-8">
    <h3 class="text-xl font-semibold mb-4">Recent Activity (User Specific)</h3>
    <p class="text-gray-400">Display a summary of recent actions or important notifications related to your events here.</p>
    </div>

<div class="text-center">
     <a href="../events/index.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md text-lg font-semibold transition duration-200 ease-in-out">
        View My Events
    </a>
</div>

<?php
    include_once '../includes/footer.php';
?>
