<?php
session_start();

// Determine which form to show based on a URL parameter, default to login
$form_to_show = isset($_GET['form']) ? $_GET['form'] : 'login';
// Initialize variables for messages
$error_message = '';
$success_message = '';

// Check for error messages from the URL parameters
if (isset($_GET['error'])) {
    $error_code = $_GET['error'];
    switch ($error_code) {
        case 'emptyfields':
            $error_message = 'Please fill in all required fields.';
            break;
        case 'invalidemail':
            $error_message = 'Please enter a valid email address.';
            break;
        case 'passwordmismatch':
            $error_message = 'Passwords do not match.';
            break;
        case 'dbconnection':
            $error_message = 'A database connection error occurred. Please try again later.';
            break;
        case 'preparefailed':
            $error_message = 'An application error occurred (prepare failed). Please try again later.';
            break;
        case 'preparecheckfailed':
            $error_message = 'An application error occurred (prepare check failed). Please try again later.';
            break;
        case 'prepareinsertfailed':
            $error_message = 'An application error occurred (prepare insert failed). Please try again later.';
            break;
        case 'emailexists':
            $error_message = 'This email address is already registered.';
            break;
        case 'dberror':
            $error_message = 'A database error occurred during registration. Please try again.';
            break;
        case 'incorrectpassword':
            $error_message = 'Incorrect email or password.';
            break;
        case 'usernotfound':
            $error_message = 'Incorrect email or password.';
            break;
        default:
            $error_message = 'An unknown error occurred.';
            break;
    }
}

if (isset($_GET['success'])) {
    $success_code = $_GET['success'];
    switch ($success_code) {
        case 'registered':
            $success_message = 'Registration successful! Please log in.';
            break;
        case 'signedup_placeholder':
            $success_message = 'Sign up successful (placeholder). Please log in.';
            break;
        default:
            $success_message = 'Operation successful.';
            break;
    }
}

$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./assets/icons/logo.svg" type="image/x-icon">
    <title>Event Manager - <?php echo ($form_to_show === 'signup') ? 'Sign Up' : 'Sign In'; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-[#121212] text-white flex justify-center items-center min-h-screen p-5">

    <div class="container flex flex-col md:flex-row w-full max-w-4xl bg-[#1f1f1f] rounded-lg overflow-hidden shadow-lg">

        <div class="form-section flex-1 p-8 md:p-10 flex flex-col justify-center">
            <div class="logo flex items-center text-xl font-bold mb-8 text-white">
                <img class="mr-2 h-8" src="./assets/icons/logo.svg" alt="Event Manager Logo">
                Event Manager
            </div>

            <?php
                if ($success_message) {
                    echo "<div class='bg-green-500 text-white p-3 rounded mb-4 text-center'>" . htmlspecialchars($success_message) . '</div>';
                }
                if ($error_message) {
                    echo "<div class='bg-red-600 text-white p-3 rounded mb-4 text-center'>" . htmlspecialchars($error_message) . '</div>';
                }
            ?>

            <?php if ($form_to_show === 'signup'): ?>
                <h2 class="text-2xl font-semibold mb-2">Sign Up</h2>
                <p class="text-gray-400 mb-6">Welcome to make you Event Management!</p>

                <form action="./auth/process_signup.php" method="POST">
                    <div class="mb-5">
                        <label for="signup_name" class="block text-gray-300 text-sm font-bold mb-2">Name:</label>
                        <input type="text" id="signup_name" name="name" required placeholder="Mohammed Ali" minlength="3"
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-[#252525] border-[#3e3e3e] focus:border-blue-500">
                    </div>
                    <div class="mb-5">
                        <label for="signup_email" class="block text-gray-300 text-sm font-bold mb-2">Email</label>
                        <input type="email" id="signup_email" name="email" required placeholder="yourname@gmail.com"
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-[#252525] border-[#3e3e3e] focus:border-blue-500">
                    </div>
                    <div class="mb-6">
                        <label for="signup_password" class="block text-gray-300 text-sm font-bold mb-2">Password</label>
                        <input type="password" id="signup_password" name="password" required placeholder="********" minlength="8"
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-[#252525] border-[#3e3e3e] focus:border-blue-500">
                    </div>
                    <div class="mb-6">
                        <label for="signup_confirm_password" class="block text-gray-300 text-sm font-bold mb-2">Confirm Password</label>
                        <input type="password" id="signup_confirm_password" name="confirm_password" required placeholder="********" minlength="8"
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-[#252525] border-[#3e3e3e] focus:border-blue-500">
                    </div>

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline w-full transition duration-200 ease-in-out">
                        Sign Up
                    </button>
                </form>

                <p class="text-center text-gray-400 text-sm mt-6">
                    Already have an account? <a href="?form=login" class="text-blue-500 hover:underline">Sign In</a>
                </p>

            <?php else: ?>
                <h2 class="text-2xl font-semibold mb-2">Sign In</h2>
                <p class="text-gray-400 mb-6">Welcome back! Please sign in to continue.</p>

                <form action="./auth/process_login.php" method="POST">
                    <div class="mb-5">
                        <label for="login_email" class="block text-gray-300 text-sm font-bold mb-2">Email</label>
                        <input type="email" id="login_email" name="email" required placeholder="yourname@gmail.com" value="<?php echo htmlspecialchars($email); ?>"
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-[#252525] border-[#3e3e3e] focus:border-blue-500">
                    </div>
                    <div class="mb-6">
                        <label for="login_password" class="block text-gray-300 text-sm font-bold mb-2">Password</label>
                        <input type="password" id="login_password" name="password" required placeholder="**********"
                            class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-[#252525] border-[#3e3e3e] focus:border-blue-500">
                    </div>

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline w-full transition duration-200 ease-in-out">
                        Sign In
                    </button>
                </form>

                <p class="text-center text-gray-400 text-sm mt-6">
                    Do not have an account? <a href="?form=signup" class="text-blue-500 hover:underline">Sign Up</a>
                </p>

            <?php endif; ?>

        </div>

        <div class="promo-section flex-1 bg-blue-600 p-8 md:p-10 md:flex hidden flex-col justify-center items-center text-center relative">
            <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('./path/to/your/pattern.svg');"></div>

            <div class="relative z-10">
                <img src="./assets/dashboard-screenshot.png" alt="Dashboard Screenshot" class="max-w-full h-auto rounded-lg shadow-xl mb-6">
                <h2 class="text-2xl font-semibold mb-3 text-white">Easy-to-Use Dashboard for Managing Your Business.</h2>
                <p class="text-blue-100">Streamline the Your Business Management with Our User-Friendly Dashboard. Securely complete tasks, tracking metrics and make informed decisions effortlessly.</p>
            </div>
        </div>
    </div>
</body>

</html>