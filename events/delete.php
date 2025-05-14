<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {

    $event_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    if ($event_id === false || $event_id <= 0) {
        header("Location: index.php?error=invalid_id");
        exit();
    }

    // --- Check if the database connection was successful before proceeding ---
    if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
        error_log("Database connection failed during deletion in events/delete.php: " . (($conn instanceof mysqli && $conn->connect_error) ? $conn->connect_error : 'Unknown connection error'));
        header("Location: index.php?error=dbconnection");
        exit();
    }

    // --- Database Transaction for Deletion ---
    $conn->begin_transaction();
    $deletion_successful = false;

    try {
        $sql_delete_needs = "DELETE FROM needs WHERE event_id = ?";
        $stmt_needs = $conn->prepare($sql_delete_needs);

        if ($stmt_needs === false) {
            throw new mysqli_sql_exception("Prepare failed for needs deletion: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt_needs->bind_param("i", $event_id);

        if ($stmt_needs->execute()) {
            $sql_delete_event = "DELETE FROM events WHERE id = ?";
            $stmt_event = $conn->prepare($sql_delete_event);

            if ($stmt_event === false) {
                 throw new mysqli_sql_exception("Prepare failed for event deletion: (" . $conn->errno . ") " . $conn->error);
            }

            $stmt_event->bind_param("i", $event_id);

            if ($stmt_event->execute()) {
                if ($stmt_event->affected_rows > 0) {
                     $conn->commit();
                     $deletion_successful = true;
                } else {
                     $conn->rollback();
                     header("Location: index.php?error=event_not_found");
                     exit();
                }

            } else {
                $conn->rollback();
                throw new mysqli_sql_exception("Event deletion execute failed for ID " . $event_id . ": (" . $stmt_event->errno . ") " . $stmt_event->error);
            }
            $stmt_event->close();
        } else {
            $conn->rollback();
            throw new mysqli_sql_exception("Needs deletion execute failed for event ID " . $event_id . ": (" . $stmt_needs->errno . ") " . $stmt_needs->error);
        }
        $stmt_needs->close();

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        error_log("MySQLi Exception during event/needs deletion in events/delete.php: " . $exception->getMessage());
        header("Location: index.php?error=deletion_failed");
        exit();
    } catch (Exception $e) {
         $conn->rollback();
         error_log("General Exception during event/needs deletion in events/delete.php: " . $e->getMessage());
         header("Location: index.php?error=deletion_failed");
         exit();
    }

    if ($conn !== null && $conn instanceof mysqli) {
        $conn->close();
    }

    // --- Redirect after successful deletion ---
    if ($deletion_successful) {
        header("Location: index.php?success=event_deleted"); // Redirect with a success message
        exit();
    }

} else {
    header("Location: index.php?error=no_id_provided");
    exit();
}
header("Location: index.php?error=unknown_error");
exit();
?>
