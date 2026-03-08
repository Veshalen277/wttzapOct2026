<?php
 goto DpshW; DoZ62: if ($stmt = $con->prepare($sql)) { $stmt->bind_param("\151", $_SESSION["\x75\x5f\144\141\164\141"][5]); $stmt->execute(); $result = $stmt->get_result(); while ($row = $result->fetch_assoc()) { $file_path = "\x75\160\154\x6f\x61\144\x73\x2f\160\157\x6c\x69\x63\x69\145\x73\57" . $row["\x66\151\154\x65\x5f\156\141\x6d\145"]; echo "\74\x64\x69\x76\40\x63\x6c\x61\x73\163\x3d\x27\x70\144\146\x2d\151\x74\145\x6d\47\x3e"; echo "\74\x68\x33\76" . htmlspecialchars($row["\146\151\x6c\x65\x5f\156\x61\x6d\145"]) . "\74\57\150\63\76"; echo "\74\x69\146\162\141\x6d\145\x20\163\162\143\75\47{$file_path}\47\x20\x77\151\x64\164\x68\x3d\x27\61\x30\60\45\x27\x20\x68\145\151\147\x68\x74\x3d\47\66\60\x30\x70\170\x27\x3e\x3c\57\x69\x66\162\x61\x6d\145\76"; echo "\x3c\154\x61\142\x65\x6c\76\x3c\x69\156\160\165\164\40\x74\x79\160\x65\x3d\x27\x63\x68\x65\143\153\142\x6f\x78\47\x20\156\141\155\x65\x3d\47\141\143\153\156\x6f\167\x6c\145\x64\x67\145\133\x5d\47\40\166\141\x6c\x75\145\75\x27" . $row["\x69\144"] . "\x27\x3e\x20\x41\x63\153\x6e\157\167\x6c\145\x64\147\x65\x20\x74\150\151\163\x20\x70\157\154\151\x63\171\x3c\57\154\141\x62\145\x6c\x3e"; echo "\x3c\57\x64\151\166\76\74\142\162\76"; } $stmt->close(); } goto LrX2a; LrX2a: ?>
<button class="btn btn-primary"name="acknowledge_btn"type="submit">Submit Acknowledgments</button></form></div></main><?php  goto Zjvru; DpshW: include "\56\x2e\x2f\x68\145\141\144\x65\162\x2e\160\150\x70"; goto KtxtK; Zjvru: include "\56\56\x2f\x66\x6f\x6f\164\145\162\x2e\160\x68\x70"; goto JmzKj; KtxtK: if (isset($_POST["\x61\x63\x6b\156\157\x77\x6c\x65\144\147\145\x5f\142\164\x6e"])) { if (isset($_POST["\141\143\153\156\157\167\x6c\x65\144\147\145"])) { $acknowledge_ids = $_POST["\x61\143\153\156\157\167\154\145\x64\147\145"]; $user_id = $_SESSION["\165\137\x64\141\x74\141"][5]; foreach ($acknowledge_ids as $policy_id) { $sql = "\111\116\123\105\122\x54\x20\x49\x4e\x54\117\x20\141\x63\153\x6e\x6f\167\x6c\x65\x64\147\155\x65\x6e\164\x73\x20\x28\165\163\145\162\137\x69\144\x2c\x20\160\x6f\154\151\x63\171\x5f\151\x64\x2c\40\141\143\x6b\137\x64\x61\x74\x65\x29\x20\x56\x41\114\x55\x45\123\40\x28\77\x2c\40\x3f\x2c\40\116\x4f\x57\50\x29\x29"; if ($stmt = $con->prepare($sql)) { $stmt->bind_param("\151\151", $user_id, $policy_id); $stmt->execute(); $stmt->close(); } else { echo "\x45\x72\162\x6f\x72\40\151\156\x73\x65\162\164\x69\x6e\147\x20\x61\x63\153\x6e\157\x77\x6c\145\x64\147\x6d\x65\x6e\164\x3a\40" . $con->error; } } echo "\101\x63\x6b\x6e\x6f\167\x6c\x65\144\x67\155\145\156\x74\163\x20\162\145\x63\x6f\162\144\145\x64\40\x73\165\x63\x63\145\163\163\146\x75\154\x6c\x79\x21"; } else { echo "\x4e\x6f\x20\160\157\x6c\x69\x63\x69\x65\163\x20\x73\x65\154\x65\143\164\x65\x64\x20\146\x6f\162\x20\x61\143\x6b\x6e\x6f\167\x6c\145\144\147\155\x65\x6e\x74\x2e"; } } goto MJftv; ix3lu: $sql = "\x53\105\114\x45\x43\x54\x20\52\40\106\x52\117\115\x20\x70\157\154\x69\x63\151\x65\x73\40\x57\110\105\x52\105\40\165\163\x65\162\137\x69\144\x20\x3d\x20\x3f"; goto DoZ62; MJftv: ?>
<main><div class="pdf-container"><h2>Policies</h2><form method="POST"><?php  goto ix3lu; JmzKj: ?>


















