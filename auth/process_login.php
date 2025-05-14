<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';

// --- Check if the form was submitted using the POST method ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Check if the database connection was successful before proceeding with DB operations ---
    if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
        error_log("Database connection failed in auth/process_login.php: " . (($conn instanceof mysqli && $conn->connect_error) ? $conn->connect_error : 'Unknown connection error'));
        header("Location: ../index.php?form=login&error=dbconnection");
        exit();
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- Basic Validation ---
    if (empty($email) || empty($password)) {
        header("Location: ../index.php?form=login&error=emptyfields");
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         header("Location: ../index.php?form=login&error=invalidemail");
         exit();
    } else {
        // --- Database Interaction: Authenticate the user ---
        $sql = "SELECT id, name, password_hash FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        // --- Check if the statement preparation was successful ---
        if ($stmt === false) {
            error_log("Prepare failed in auth/process_login.php: (" . $conn->errno . ") " . $conn->error);
            header("Location: ../index.php?form=login&error=preparefailed");
            exit();
        } else {
            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $hashed_password_from_db = $user['password_hash'];

                // --- Verify the password ---
                if (password_verify($password, $hashed_password_from_db)) {

                    // --- Start a user session ---
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_name'] = $user['name']; 

                    $stmt->close();
                    $result->close();
                    if ($conn !== null && $conn instanceof mysqli) {
                        $conn->close();
                    }

                    header("Location: ../index.php?form=login&success=registered");
                    sleep(2);
                    header("Location: ../dashboard/index.php");
                    exit();

                } else {
                    header("Location: ../index.php?form=login&error=incorrectpassword");
                    exit();
                }
            } else {
                 header("Location: ../index.php?form=login&error=usernotfound");
                 exit();
            }

            if ($stmt) $stmt->close();
            if ($result) $result->close();
        }
    }

    if ($conn !== null && $conn instanceof mysqli) {
        $conn->close();
    }

} else {
    header("Location: ../index.php?form=login");
    exit();
}
?>
