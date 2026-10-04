<?php
// Turn off error output
error_reporting(0);
ini_set('display_errors', 0);

// Set response header to JSON
header('Content-Type: application/json');

// Start output buffering
ob_start();

session_start();

$response = array('success' => false, 'message' => '', 'loggedOutId' => null);

// Check if there's an active session
if (isset($_SESSION['manager_id'])) {
    $response['loggedOutId'] = $_SESSION['manager_id'];

    // Clear all session variables
    $_SESSION = array();

    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }

    // Destroy session
    session_destroy();

    $response['success'] = true;
    $response['message'] = 'Successfully logged out';
} else {
    $response['message'] = 'No active session to logout';
}

// Clear any possible error output
ob_end_clean();

// Ensure response is valid JSON
echo json_encode($response);
