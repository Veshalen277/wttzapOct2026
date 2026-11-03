<?php
// /dashboard/post_react.php
require_once __DIR__ . '/header.php'; // must set $con and session

if (!isset($_SESSION['u_data'])) {
  header("Location: /dashboard/login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /dashboard/");
  exit();
}

if (!$con) {
  die("DB connection error");
}

$user_id = (int)($_SESSION['u_data'][5] ?? 0);
if ($user_id <= 0) {
  die("Invalid user");
}

$item_type = $_POST['item_type'] ?? 'post';          // 'post' | 'work'
$item_id   = (int)($_POST['item_id'] ?? 0);          // id of messages.id or work_tbl.id
$reaction  = $_POST['reaction'] ?? '';               // like | dislike | heart
$return    = $_POST['return'] ?? ($_SERVER['HTTP_REFERER'] ?? '/dashboard/');

$allowedTypes = ['post','work'];
$allowedReactions = ['like','dislike','heart'];

if (!in_array($item_type, $allowedTypes, true)) die("Bad item_type");
if ($item_id <= 0) die("Bad item_id");
if (!in_array($reaction, $allowedReactions, true)) die("Bad reaction");

// If user clicks same reaction again -> remove (toggle off)
$stmt = mysqli_prepare($con, "
  SELECT reaction_type
  FROM item_reactions
  WHERE item_type = ? AND item_id = ? AND user_id = ?
  LIMIT 1
");
mysqli_stmt_bind_param($stmt, "sii", $item_type, $item_id, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$existing = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);

if ($existing && $existing['reaction_type'] === $reaction) {
  $del = mysqli_prepare($con, "
    DELETE FROM item_reactions
    WHERE item_type = ? AND item_id = ? AND user_id = ?
  ");
  mysqli_stmt_bind_param($del, "sii", $item_type, $item_id, $user_id);
  mysqli_stmt_execute($del);
  mysqli_stmt_close($del);
} else {
  // Upsert reaction
  $ins = mysqli_prepare($con, "
    INSERT INTO item_reactions (item_type, item_id, user_id, reaction_type, created_at, updated_at)
    VALUES (?, ?, ?, ?, NOW(), NOW())
    ON DUPLICATE KEY UPDATE reaction_type = VALUES(reaction_type), updated_at = NOW()
  ");
  mysqli_stmt_bind_param($ins, "siis", $item_type, $item_id, $user_id, $reaction);
  mysqli_stmt_execute($ins);
  mysqli_stmt_close($ins);
}

header("Location: " . $return);
exit();
