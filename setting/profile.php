<?php

$page_title = 'Profile';

require_once '../includes/header.php';

require_once '../includes/db.php';

$user_data = null;
$error_message = '';

// Get the logged-in user's ID from the session
$logged_in_user_id = $_SESSION['user_id'] ?? null;

// Check if the user is logged in and the database connection is valid
if ($logged_in_user_id !== null && isset($conn) && $conn !== null) {

    $sql = "SELECT id, name, email FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Profile SQL Prepare failed: (" . $conn->errno . ") " . $conn->error);
        $error_message = 'An application error occurred while fetching user data.';
    } else {
        $stmt->bind_param("i", $logged_in_user_id);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
        } else {
            $error_message = 'User data not found.';
             header("Location: ../auth/logout.php");
             exit();
        }

        $stmt->close();
        if ($result) $result->close();

    }

    if ($conn !== null && $conn instanceof mysqli) {
        $conn->close();
    }

} else {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        $error_message = 'Database connection failed. Unable to load profile.';
    }
}

?>

<div class="flex flex-col items-start mb-6">
    <h1 class="text-2xl font-size font-semibold">Account & Settings</h1>
    <div class="flex flex-row items-center space-x-2 mt-1">
        <p class="text-gray-400 text-sm">Home</p>
        <img class="w-4" src="../assets/icons/arrow-solid-Right.svg" alt="" srcset="">
        <p class="text-gray-400 text-sm">Account & Settings</p>
        <img class="w-4" src="../assets/icons/arrow-solid-Right.svg" alt="" srcset="">
        <p class="text-[#1A71F6] text-sm font-bold">Profile </p>
    </div>
</div>

<?php if ($error_message): ?>
    <div class="bg-red-600 text-white p-3 rounded mb-4 text-center"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<?php if ($user_data): ?>
    <div class="bg-[#1f1f1f] rounded-lg p-6 shadow-lg">
        <h2 class="text-2xl font-semibold mb-4">Your Profile Information</h2>

        <div class="space-y-4">
            <div>
                <label class="block text-gray-400 text-sm font-semibold mb-1">Name:</label>
                <p class="text-white text-lg"><?php echo htmlspecialchars($user_data['name'] ?? 'N/A'); ?></p>
            </div>
            <div>
                <label class="block text-gray-400 text-sm font-semibold mb-1">Email:</label>
                <p class="text-white text-lg"><?php echo htmlspecialchars($user_data['email'] ?? 'N/A'); ?></p>
            </div>
            </div>

        <div class="mt-6 flex space-x-4">
             <a href="#" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Edit Profile</a>
             <a href="#" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-md">Change Password</a>
        </div>

    </div>
<?php endif; ?>


<?php
    require_once '../includes/footer.php';
?>
