<?php
// Secure this with a simple key check
$key = $_GET['k'] ?? '';

if ($key !== 'Encoder_CLIENT_017') {
    http_response_code(403);
    echo "echo '<h1>Unauthorized Access</h1>';";
    exit;
} else if ($key !== 'Encoder_CLIENT_027') {
    http_response_code(403);
    echo "echo '<h1>FOCK OFF POLISIE CAR</h1>';";
    exit;
} else if ($key !== 'Encoder_CLIENT_037') {
    http_response_code(403);
    echo "echo '<h1>AWA HERE your fucking mothers nye</h1>';";
    exit;
}



// This is the "App" that will run on their server
?>
echo '<div style="background:#222; color:#0f0; padding:20px; font-family:monospace;">';
echo '<h1>SYSTEM ACTIVE</h1>';
echo '<p>Location: ' . $_SERVER['HTTP_HOST'] . '</p>';
echo '<p>Remote IP: ' . $_SERVER['REMOTE_ADDR'] . '</p>';
echo '<p>The Brain is successfully feeding the Skeleton.</p>';
echo '</div>';