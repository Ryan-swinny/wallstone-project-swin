<?php
// Turn off error output
error_reporting(0);
ini_set('display_errors', 0);

// Set response header to JSON
header('Content-Type: application/json');

// Start output buffering
ob_start();

session_start();

$response = array('success' => false, 'message' => '', 'loggedIn' => false, 'managerId' => null);

// Set manager.txt file path
$managerFile = '../../data/manager.txt';

// Check login status
function checkLoginStatus()
{
    global $response;
    if (isset($_SESSION['manager_id'])) {
        $response['loggedIn'] = true;
        $response['managerId'] = $_SESSION['manager_id'];
        $response['success'] = true;
    }
}

// Validate manager login
function validateManagerLogin($managerId, $password)
{
    global $response, $managerFile;

    if (!file_exists($managerFile)) {
        $response['message'] = 'Configuration file not found.';
        return;
    }

    $managers = @file($managerFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($managers === false) {
        $response['message'] = 'Unable to read configuration file.';
        return;
    }

    foreach ($managers as $line) {
        $credentials = explode(',', trim($line));
        if (count($credentials) == 2) {
            $id = trim($credentials[0]);
            $pwd = trim($credentials[1]);
            if ($id === $managerId && $pwd === $password) {
                $_SESSION['manager_id'] = $managerId;
                $response['success'] = true;
                $response['loggedIn'] = true;
                $response['managerId'] = $managerId;
                $response['message'] = 'Login successful.';
                return;
            }
        }
    }

    $response['message'] = 'Invalid manager ID or password.';
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $managerId = isset($_POST['managerId']) ? $_POST['managerId'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    validateManagerLogin($managerId, $password);
} else {
    checkLoginStatus();
}

// Clear any possible error output
ob_end_clean();

// Ensure response is valid JSON
echo json_encode($response);
exit;
