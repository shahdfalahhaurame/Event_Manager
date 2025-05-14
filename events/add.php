<?php
$page_title = 'Add New Event';
ob_start(); // Start output buffering

require_once '../includes/header.php';

require_once '../includes/db.php';

$error_message = '';
$success_message = '';

// --- Handle Form Submission (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Check if the database connection was successful before proceeding ---
    if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
        error_log("Database connection failed during POST in events/add.php: " . (($conn instanceof mysqli && $conn->connect_error) ? $conn->connect_error : 'Unknown connection error'));
        $error_message = 'A database connection error occurred. Please try again later.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        $state = $_POST['state'];
        $location = trim($_POST['location'] ?? '');
        $need_title = trim($_POST['need_title'] ?? '');
        $need_description = trim($_POST['need_description'] ?? '');
        $priority = $_POST['priority'] ?? '';

        $created_by = $_SESSION['user_id'] ?? 1;
        $now = date('Y-m-d H:i:s');

        // --- Basic Validation ---
        if (empty($title) || empty($description) || empty($date) || empty($time) || empty($state) || empty($location) || empty($need_title) || empty($need_description) || empty($priority)) {
            $error_message = 'Please fill in all required fields.';
        }
        else {
            // --- Database Transaction ---
            $conn->begin_transaction();
            $insert_successful = false;

            try {
                $sql_event = "INSERT INTO events (title, description, date, time, location, state, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_event = $conn->prepare($sql_event);

                if ($stmt_event === false) {
                    throw new mysqli_sql_exception("Prepare failed for event insert: (" . $conn->errno . ") " . $conn->error);
                }

                $bind_result = $stmt_event->bind_param("sssssisss", $title, $description, $date, $time, $location, $state, $created_by, $now, $now);
                if ($bind_result === false) {
                    error_log("Bind failed: " . $stmt_event->error);
                }

                if ($stmt_event->execute()) {
                    $event_id = $conn->insert_id;

                    $sql_need = "INSERT INTO needs (event_id, title, description, priority, is_fulfilled, created_at) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_need = $conn->prepare($sql_need);

                    if ($stmt_need === false) {
                        throw new mysqli_sql_exception("Prepare failed for need insert: (" . $conn->errno . ") " . $conn->error);
                    }

                    $is_fulfilled = 0;
                    $stmt_need->bind_param("isssis", $event_id, $need_title, $need_description, $priority, $is_fulfilled, $now);

                    if ($stmt_need->execute()) {
                        $conn->commit();
                        $insert_successful = true;
                    } else {
                        $conn->rollback();
                        throw new mysqli_sql_exception("Need insert execute failed for event ID " . $event_id . ": (" . $stmt_need->errno . ") " . $stmt_need->error);
                    }
                    $stmt_need->close();

                } else {
                    $conn->rollback();
                    throw new mysqli_sql_exception("Event insert execute failed: (" . $stmt_event->errno . ") " . $stmt_event->error);
                }
                $stmt_event->close();

            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                error_log("MySQLi Exception during event/need insert in events/add.php: " . $exception->getMessage());
                $error_message = 'An unexpected database error occurred during insertion. Please try again.';
            } catch (Exception $e) {
                $conn->rollback(); 
                error_log("General Exception during event/need insert in events/add.php: " . $e->getMessage());
                $error_message = 'An unexpected error occurred. Please try again.';
            }

            if ($conn !== null && $conn instanceof mysqli) {
                $conn->close();
            }

            // --- Redirect based on success or failure ---
            if ($insert_successful) {
                header("Location: index.php?success=event_added");
                ob_end_flush(); // Flush the output buffer

                exit();
            }
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
        <p class="text-[#1A71F6] text-sm font-bold">Add New Event</p>
    </div>
</div>

<?php if ($success_message): ?>
    <div class="bg-green-500 text-white p-3 rounded mb-4 text-center"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="bg-red-600 text-white p-3 rounded mb-4 text-center"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>


<form action="add.php" method="POST" class="bg-[#1A1A1B] p-6 rounded-lg shadow-md space-y-6 border border-[#3D3D3D]">
    <div>
        <label class="flex gap-2 items-center text-gray-300 mb-1" for="title">
            <img src="../assets/icons/GroupIcon/filter-outline.svg" alt="" class="h-5 w-5">
            Event Title:
        </label>
        <input type="text" id="title" name="title" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" placeholder="ex: Graduate of my friends" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
    </div>

    <div>
        <label class="flex gap-2 items-center text-gray-300 mb-1" for="description">
            <img src="../assets/icons/GroupIcon/float-right.svg" alt="" class="h-5 w-5">
            Description:
        </label>
        <textarea id="description" name="description" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" rows="3" placeholder="this event is important ..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="flex gap-2 items-center text-gray-300 mb-1" for="date">
                <img src="../assets/icons/GroupIcon/today-outline.svg" alt="" class="h-5 w-5">
                Date
            </label>
            <input type="date" id="date" name="date" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>" required>
        </div>
        <div>
            <label class="flex gap-2 items-center text-gray-300 mb-1" for="time">
                <img src="../assets/icons/GroupIcon/time-outline.svg" alt="" class="h-5 w-5">
                Time
            </label>
            <input type="time" id="time" name="time" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" value="<?php echo htmlspecialchars($_POST['time'] ?? ''); ?>" required>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="flex gap-2 items-center text-gray-300 mb-1" for="state">
                <img src="../assets/icons/GroupIcon/area-search.svg" alt="" class="h-5 w-5">
                State
            </label>
            <select id="state" name="state" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500">  
                <option value="" <?php echo (($_POST['state'] ?? '') === '') ? 'selected' : ''; ?>>-- Select State --</option>
                <option value="draft" <?php echo (($_POST['state'] ?? '') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                <option value="active" <?php echo (($_POST['state'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
                <option value="full" <?php echo (($_POST['state'] ?? '') === 'full') ? 'selected' : ''; ?>>Full</option>
                <option value="cancelled" <?php echo (($_POST['state'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                <option value="completed" <?php echo (($_POST['state'] ?? '') === 'completed') ? 'selected' : ''; ?>>Completed</option>
            </select>
        </div>
        <div>
            <label class="flex gap-2 items-center text-gray-300 mb-1" for="location">
                <img src="../assets/icons/GroupIcon/golf-outline.svg" alt="" class="h-5 w-5">
                Location (URL)
            </label>
            <input type="text" id="location" name="location" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" placeholder="university of Halabja/https://halabja.h" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" required>
        </div>
    </div>

    <h3 class="text-lg text-white mt-6 border-t border-gray-700 pt-4">Need Information</h3>

    <div>
        <label class="flex gap-2 items-center text-gray-300 mb-1" for="need_title">
            <img src="../assets/icons/GroupIcon/receipt.svg" alt="" class="h-5 w-5">
            Need Title
        </label>
        <input type="text" id="need_title" name="need_title" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" placeholder="the gifts" value="<?php echo htmlspecialchars($_POST['need_title'] ?? ''); ?>" required>
    </div>

    <div>
        <label class="flex gap-2 items-center text-gray-300 mb-1" for="need_description">
            <img src="../assets/icons/GroupIcon/float-right.svg" alt="" class="h-5 w-5">
            Need Description
        </label>
        <textarea id="need_description" name="need_description" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500" rows="2" placeholder="I will buy a book for my friends" required><?php echo htmlspecialchars($_POST['need_description'] ?? ''); ?></textarea>
    </div>

    <div>
        <label class="flex gap-2 items-center text-gray-300 mb-1" for="priority">Priority</label>
        <select id="priority" name="priority" class="w-full bg-[#2B2B2B] text-white border border-[#3D3D3D] rounded px-3 py-2 focus:outline-none focus:border-blue-500">
            <option value="Low" <?php echo (($_POST['priority'] ?? '') === 'Low') ? 'selected' : ''; ?>>Low</option>
            <option value="Medium" <?php echo (($_POST['priority'] ?? '') === 'Medium') ? 'selected' : ''; ?>>Medium</option>
            <option value="High" <?php echo (($_POST['priority'] ?? '') === 'High') ? 'selected' : ''; ?>>High</option>
        </select>
    </div>

    <div class="flex justify-between mt-6">
        <a href="index.php" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-md">
            Discard
        </a>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-md flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add New Event
        </button>
    </div>
</form>

<?php
require_once '../includes/footer.php';
?>