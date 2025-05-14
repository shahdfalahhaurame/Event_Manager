<?php
// event-management/events/edit.php
// This file displays the form for editing an existing event and processes the form submission.

// Set the page title before including the header
$page_title = 'Edit Event';
ob_start(); // Start output buffering

// Include the header file (handles session check and includes sidebar)
// The header file also opens the <body> and the main content flex container, and the <main> tag.
require_once '../includes/header.php';

// Include your database connection file
require_once '../includes/db.php';

// Initialize variables for form input and messages
$error_message = '';
$success_message = '';
$event_data = null; // Variable to hold existing event data
$need_data = null; // Variable to hold existing need data
$event_id = null; // Variable to hold the event ID being edited

// --- Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the event ID from the hidden input field
    $event_id = filter_var($_POST['event_id'] ?? '', FILTER_SANITIZE_NUMBER_INT);

    // Validate the event ID
    if ($event_id === false || $event_id <= 0) {
        $error_message = 'Invalid event ID provided for update.';
    } else {
        // --- Check if the database connection was successful before proceeding ---
        if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
            error_log("Database connection failed during POST in events/edit.php: " . (($conn instanceof mysqli && $conn->connect_error) ? $conn->connect_error : 'Unknown connection error'));
            $error_message = 'A database connection error occurred. Please try again later.';
        } else {
            // Get form data (using null coalescing operator ?? '' for safety and trim for whitespace)
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $date = $_POST['date'] ?? ''; // Date input format should match YYYY-MM-DD
            $time = $_POST['time'] ?? ''; // Time input format should match HH:MM
            $state = $_POST['state'] ?? '';
            $location = trim($_POST['location'] ?? '');
            $need_title = trim($_POST['need_title'] ?? '');
            $need_description = trim($_POST['need_description'] ?? '');
            $priority = $_POST['priority'] ?? '';

            $updated_by = $_SESSION['user_id'] ?? 1;
            $now = date('Y-m-d H:i:s');

            // --- Basic Validation ---
            if (empty($title) || empty($description) || empty($date) || empty($time) || empty($state) || empty($location) || empty($need_title) || empty($need_description) || empty($priority)) {
                $error_message = 'Please fill in all required fields.';
            }

            else {
                // --- Database Transaction for Update ---
                $conn->begin_transaction();
                $update_successful = false;

                try {
                    $sql_event_update = "UPDATE events SET title = ?, description = ?, date = ?, time = ?, location = ?, state = ?, updated_at = ? WHERE id = ?";
                    $stmt_event_update = $conn->prepare($sql_event_update);

                    if ($stmt_event_update === false) {
                         throw new mysqli_sql_exception("Prepare failed for event update: (" . $conn->errno . ") " . $conn->error);
                    }

                    $stmt_event_update->bind_param("sssssssi", $title, $description, $date, $time, $location, $state, $now, $event_id);

                    if ($stmt_event_update->execute()) {

                        $sql_need_update = "UPDATE needs SET title = ?, description = ?, priority = ?, created_at = ? WHERE event_id = ?"; // Note: Updating created_at is unusual for an update, consider updated_at if you have it in needs table
                        $stmt_need_update = $conn->prepare($sql_need_update);

                        if ($stmt_need_update === false) {
                             throw new mysqli_sql_exception("Prepare failed for need update: (" . $conn->errno . ") " . $conn->error);
                        }

                        $stmt_need_update->bind_param("ssssi", $need_title, $need_description, $priority, $now, $event_id); // Using $now for created_at update as in your original snippet - consider changing if you have an updated_at for needs

                        if ($stmt_need_update->execute()) {
                            // Need updated successfully
                            $conn->commit();
                            $update_successful = true;
                        } else {
                            $conn->rollback();
                            throw new mysqli_sql_exception("Need update execute failed for event ID " . $event_id . ": (" . $stmt_need_update->errno . ") " . $stmt_need_update->error);
                        }
                        $stmt_need_update->close();

                    } else {
                        // Event update failed
                        $conn->rollback();
                         throw new mysqli_sql_exception("Event update execute failed for ID " . $event_id . ": (" . $stmt_event_update->errno . ") " . $stmt_event_update->error);
                    }
                    $stmt_event_update->close(); // Close event statement

                } catch (mysqli_sql_exception $exception) {
                    $conn->rollback();
                    error_log("MySQLi Exception during event/need update in events/edit.php: " . $exception->getMessage());
                    $error_message = 'An unexpected database error occurred during update. Please try again.';
                } catch (Exception $e) {
                     $conn->rollback(); // Ensure rollback
                     error_log("General Exception during event/need update in events/edit.php: " . $e->getMessage());
                     $error_message = 'An unexpected error occurred. Please try again.';
                }

                if ($conn !== null && $conn instanceof mysqli) {
                    $conn->close();
                }

                // --- Redirect based on success or failure ---
                if ($update_successful) {
                    header("Location: ./index.php?success=event_updated");
                    ob_end_flush(); // Flush the output buffer
                    exit();
                }
            }
        }
    }

}

// --- Handle Initial Page Load (GET Request) or POST Failure ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' || ($error_message && $_SERVER['REQUEST_METHOD'] === 'POST')) {

    $event_id = filter_var($_GET['id'] ?? $_POST['event_id'] ?? '', FILTER_SANITIZE_NUMBER_INT);

    // Validate the event ID
    if ($event_id === false || $event_id <= 0) {
        header("Location: index.php?error=invalid_id");
        exit();
    }

    // --- Check if the database connection was successful before proceeding ---
    if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
        error_log("Database connection failed during GET in events/edit.php: " . (($conn instanceof mysqli && $conn->connect_error) ? $conn->connect_error : 'Unknown connection error'));
        $error_message = 'A database connection error occurred. Unable to load event data.';
        $conn = null;
    } else {
        $sql_fetch_event = "SELECT e.id, e.title, e.description, e.date, e.time, e.location, e.state,
                            n.id AS need_id, n.title AS need_title, n.description AS need_description, n.priority
                            FROM events e
                            LEFT JOIN needs n ON e.id = n.event_id
                            WHERE e.id = ?";

        $stmt_fetch = $conn->prepare($sql_fetch_event);

        if ($stmt_fetch === false) {
            error_log("Prepare failed for fetching event data in events/edit.php: (" . $conn->errno . ") " . $conn->error);
            $error_message = 'An application error occurred while fetching event data.';
        } else {
            $stmt_fetch->bind_param("i", $event_id);
            $stmt_fetch->execute();
            $result_fetch = $stmt_fetch->get_result();

            if ($result_fetch->num_rows === 1) {
                $event_data = $result_fetch->fetch_assoc();
                $need_data = [
                    'need_id' => $event_data['need_id'],
                    'need_title' => $event_data['need_title'],
                    'need_description' => $event_data['need_description'],
                    'priority' => $event_data['priority']
                ];
                 unset($event_data['need_id'], $event_data['need_title'], $event_data['need_description'], $event_data['priority']);

            } else {
                $stmt_fetch->close();
                if ($conn !== null && $conn instanceof mysqli) $conn->close();
                header("Location: index.php?error=event_not_found");
                exit();
            }
            $stmt_fetch->close();
        }

        if ($conn !== null && $conn instanceof mysqli) {
             $conn->close();
        }
    }
}
?>

<div class="flex flex-col items-start mb-6">
    <h1 class="text-2xl font-size font-semibold">Events</h1>
    <div class="flex flex-row items-center space-x-2 mt-1">
        <p class="text-gray-400 text-sm">Dashboard</p>
        <img class="w-4" src="../assets/icons/arrow-solid-Right.svg" alt="" srcset="">
        <p class="text-gray-400 text-sm">Events</p>
        <img class="w-4" src="../assets/icons/arrow-solid-Right.svg" alt="" srcset="">
        <p class="text-[#1A71F6] text-sm font-bold">Edit </p>
    </div>
</div>

<?php if ($success_message): ?>
    <div class="bg-green-500 text-white p-3 rounded mb-4 text-center"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="bg-red-600 text-white p-3 rounded mb-4 text-center"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>


<?php if ($event_data): ?>
    <form action="edit.php" method="POST" class="bg-[#1A1A1B] p-6 rounded-lg shadow-md space-y-6 border border-[#3D3D3D]">
        <div class="flex flex-col gap-[8px] mb-[16px] relative">
            <h2 class="text-[22px]">Event Information</h2>
            <p class="text-[#B0B0B0]">Information about this specific Event.</p>
            <a href="../events/index.php" class="text-[#B0B0B0] hover:text-[#00aeef] flex items-center gap-2">
                <img class="absolute right-0 top-0 w-8" src="../assets/icons/arrow-back-outline.svg" alt="Back Icon">
            </a>
        </div>

        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_data['id']); ?>">

        <div>
            <label class="flex gap-2 items-center text-gray-300 mb-1" for="title">
                 <img src="../assets/icons/GroupIcon/filter-outline.svg" alt="" class="h-5 w-5">
                Event Title:
            </label>
            <input type="text" id="title" name="title" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" placeholder="ex: Graduate of my friends" value="<?php echo htmlspecialchars($_POST['title'] ?? $event_data['title'] ?? ''); ?>" required>
        </div>

        <div>
            <label class="flex gap-2 items-center text-gray-300 mb-1" for="description">
                 <img src="../assets/icons/GroupIcon/float-right.svg" alt="" class="h-5 w-5">
                Description:
            </label>
            <textarea id="description" name="description" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" rows="3" placeholder="this event is important ..." required><?php echo htmlspecialchars($_POST['description'] ?? $event_data['description'] ?? ''); ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="flex gap-2 items-center text-gray-300 mb-1" for="date">
                     <img src="../assets/icons/GroupIcon/today-outline.svg" alt="" class="h-5 w-5">
                    Date
                </label>
                <input type="date" id="date" name="date" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" value="<?php echo htmlspecialchars($_POST['date'] ?? $event_data['date'] ?? ''); ?>" required>
            </div>
            <div>
                <label class="flex gap-2 items-center text-gray-300 mb-1" for="time">
                    <img src="../assets/icons/GroupIcon/time-outline.svg" alt="" class="h-5 w-5">
                    Time
                </label>
                <input type="time" id="time" name="time" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" value="<?php echo htmlspecialchars($_POST['time'] ?? $event_data['time'] ?? ''); ?>" required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="flex gap-2 items-center text-gray-300 mb-1" for="state">
                     <img src="../assets/icons/GroupIcon/area-search.svg" alt="" class="h-5 w-5">
                    State
                </label>
                <select id="state" name="state" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" required>
                    <option value="draft" <?php echo (($_POST['state'] ?? $event_data['state'] ?? '') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="active" <?php echo (($_POST['state'] ?? $event_data['state'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="full" <?php echo (($_POST['state'] ?? $event_data['state'] ?? '') === 'full') ? 'selected' : ''; ?>>Full</option>
                    <option value="cancelled" <?php echo (($_POST['state'] ?? $event_data['state'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="completed" <?php echo (($_POST['state'] ?? $event_data['state'] ?? '') === 'completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <div>
                <label class="flex gap-2 items-center text-gray-300 mb-1" for="location">
                     <img src="../assets/icons/GroupIcon/golf-outline.svg" alt="" class="h-5 w-5">
                    Location (URL)
                </label>
                <input type="text" id="location" name="location" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" placeholder="university of Halabja/https://halabja.h" value="<?php echo htmlspecialchars($_POST['location'] ?? $event_data['location'] ?? ''); ?>" required>
            </div>
        </div>

        <h3 class="text-lg text-white mt-6 border-t border-gray-700 pt-4">Need Information</h3>

        <div>
            <label class="flex gap-2 items-center text-gray-300 mb-1" for="need_title">
                 <img src="../assets/icons/GroupIcon/receipt.svg" alt="" class="h-5 w-5">
                Need Title
            </label>
            <input type="text" id="need_title" name="need_title" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" placeholder="the gifts" value="<?php echo htmlspecialchars($_POST['need_title'] ?? $need_data['need_title'] ?? ''); ?>" required>
        </div>

        <div>
            <label class="flex gap-2 items-center text-gray-300 mb-1" for="need_description">
                 <img src="../assets/icons/GroupIcon/float-right.svg" alt="" class="h-5 w-5">
                Need Description
            </label>
            <textarea id="need_description" name="need_description" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" rows="2" placeholder="I will buy a book for my friends" required><?php echo htmlspecialchars($_POST['need_description'] ?? $need_data['need_description'] ?? ''); ?></textarea>
        </div>

        <div>
            <label class="flex gap-2 items-center text-gray-300 mb-1" for="priority">Priority</label>
            <select id="priority" name="priority" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                <option value="Low" <?php echo (($_POST['priority'] ?? $need_data['priority'] ?? '') === 'Low') ? 'selected' : ''; ?>>Low</option>
                <option value="Medium" <?php echo (($_POST['priority'] ?? $need_data['priority'] ?? '') === 'Medium') ? 'selected' : ''; ?>>Medium</option>
                <option value="High" <?php echo (($_POST['priority'] ?? $need_data['priority'] ?? '') === 'High') ? 'selected' : ''; ?>>High</option>
            </select>
        </div>

        <div class="flex justify-between mt-6">
            <a href="index.php" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-md">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-md flex items-center">
                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Save Changes
            </button>
        </div>
    </form>
<?php endif; ?>

<?php
require_once '../includes/footer.php';
?>
