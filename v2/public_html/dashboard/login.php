<?php
// Legacy sign-in page. It queried a "users" table with plaintext passwords and is no longer used.
// Authentication is handled by the portal's sign-in at /index.php; send everyone there.
header('Location: /index.php', true, 301);
exit;
