<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db.php';

// --- Check if the form was submitted using the POST method ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Check if the database connection was successful before proceeding with DB operations ---
    if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
        error_log("Database connection failed in auth/process_signup.php: " . (($conn instanceof mysqli && $conn->connect_error) ? $conn->connect_error : 'Unknown connection error'));
        header("Location: ../index.php?form=signup&error=dbconnection");
        exit();
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Basic Validation ---
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        header("Location: ../index.php?form=signup&error=emptyfields");
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         header("Location: ../index.php?form=signup&error=invalidemail");
         exit();
    } elseif ($password !== $confirm_password) {
         header("Location: ../index.php?form=signup&error=passwordmismatch");
         exit();
    } else {
        // --- Database Interaction ---
        $check_email_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_email_sql);

        // --- Check if the check statement preparation was successful ---
        if ($check_stmt === false) {
             error_log("Prepare failed for email check in auth/process_signup.php: (" . $conn->errno . ") " . $conn->error);
             header("Location: ../index.php?form=signup&error=preparecheckfailed");
             exit();
        } else {
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $check_stmt->close();
                header("Location: ../index.php?form=signup&error=emailexists");
                exit();
            } else {
                $check_stmt->close();

                $insert_user_sql = "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_user_sql);

                // --- Check if the insert statement preparation was successful ---
                if ($insert_stmt === false) {
                    error_log("Prepare failed for user insert in auth/process_signup.php: (" . $conn->errno . ") " . $conn->error);
                    header("Location: ../index.php?form=signup&error=prepareinsertfailed");
                    exit();
                } else {
                    // --- Security: Hash the password ---
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $insert_stmt->bind_param("sss", $name, $email, $hashed_password); // "sss" indicates three string parameters

                    if ($insert_stmt->execute()) {
                        // --- Get the ID of the newly inserted user ---
                        $new_user_id = $conn->insert_id;

                        // --- Fetch the newly created user's data (specifically name) ---
                        $fetch_user_sql = "SELECT id, name FROM users WHERE id = ?";
                        $fetch_stmt = $conn->prepare($fetch_user_sql);

                        if ($fetch_stmt === false) {
                            error_log("Prepare failed for fetching new user data: (" . $conn->errno . ") " . $conn->error);
                            $_SESSION['user_id'] = $new_user_id;
                            $_SESSION['user_name'] = 'User';
                        } else {
                            $fetch_stmt->bind_param("i", $new_user_id);
                            $fetch_stmt->execute();
                            $fetch_result = $fetch_stmt->get_result();

                            if ($fetch_result->num_rows === 1) {
                                $new_user_data = $fetch_result->fetch_assoc();
                                // --- Set session variables for the logged-in user ---
                                $_SESSION['user_id'] = $new_user_data['id'];
                                $_SESSION['user_name'] = $new_user_data['name'];
                            } else {
                                error_log("Newly inserted user with ID " . $new_user_id . " not found immediately after insert.");
                                $_SESSION['user_id'] = $new_user_id;
                                $_SESSION['user_name'] = 'User';
                            }
                            $fetch_stmt->close();
                        }

                        $insert_stmt->close();
                        if ($conn !== null && $conn instanceof mysqli) {
                            $conn->close();
                        }

                        // --- Redirect to the dashboard ---
                        header("Location: ../dashboard/index.php");
                        exit();

                    } else {
                        error_log("Execute failed for user insert in auth/process_signup.php: (" . $conn->errno . ") " . $conn->error);
                        header("Location: ../index.php?form=signup&error=dberror");
                        exit();
                    }
                }
            }
        }
    }
    if ($conn !== null && $conn instanceof mysqli) {
        $conn->close();
    }

} else {
    header("Location: ../index.php?form=signup");
    exit();
}
?>
