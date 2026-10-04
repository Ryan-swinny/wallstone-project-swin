<?php
// Disable error reporting
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

// Start the session
session_start();

// Initialize response array
$response = array('status' => 'error', 'message' => 'Unknown error');

// Logout logic
if (isset($_SESSION['customer_id'])) {
    // Clear all session variables
    $_SESSION = array();

    // If it's desired to kill the session, also delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Finally, destroy the session
    session_destroy();

    $response['status'] = 'success';
    $response['message'] = 'User has been successfully logged out';
} else {
    $response['message'] = 'No active user session, but logout process completed';
}

// Clear any previous output
ob_clean();

// Set the correct Content-Type
header('Content-Type: application/json');

// If CORS (Cross-Origin Resource Sharing) is needed
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
// header('Access-Control-Allow-Headers: Content-Type');

// Output JSON encoded response
echo json_encode($response);

// Ensure the script ends here
exit();
