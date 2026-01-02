<?php
// Start session
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Start new session for flash message
session_start();
$_SESSION['flash_type'] = 'success';
$_SESSION['flash_message'] = 'You have been successfully logged out.';

// Redirect to home page
header('Location: index.php');
exit();
?>
