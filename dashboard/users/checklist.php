<?php
 goto L8Uk7; ci_iZ: echo $result["\154\145\x61\166\145\137\142\x61\154\141\x6e\143\145\163"]; goto nT8SH; IBDJG: $id = $user[5]; goto OFgzz; QadsY: if ($_SERVER["\122\x45\x51\125\x45\x53\124\x5f\x4d\105\x54\x48\x4f\104"] === "\120\117\123\x54" && isset($_POST["\x69\164\145\155"])) { $item = $_POST["\x69\164\145\x6d"]; $stmt = $con->prepare("\111\116\x53\x45\122\x54\40\x49\116\124\117\x20\143\x68\x65\x63\x6b\x6c\151\x73\x74\137\x69\164\x65\155\x73\x20\50\x75\x73\x65\x72\137\151\144\x2c\x20\x69\164\x65\x6d\x2c\40\x63\x72\145\141\164\x65\144\x5f\x61\x74\51\x20\x56\x41\114\x55\x45\x53\x20\50\x3f\54\40\77\54\40\116\x4f\x57\x28\51\x29"); $stmt->bind_param("\x69\x73", $user_id, $item); $stmt->execute(); $stmt->close(); } goto HwM8l; WlV_G: ?>
</div></div></div><div class="fade modal"id="editModal"tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="post"><div class="modal-header"><h5 class="modal-title">Edit Task</h5><button class="btn-close"type="button"data-bs-dismiss="modal"></button></div><div class="modal-body"><input name="edit_id"type="hidden"id="edit-id"> <input name="edit_item"class="form-control"required id="edit-item"></div><div class="modal-footer"><button class="btn btn-secondary"type="button"data-bs-dismiss="modal">Cancel</button> <button class="btn btn-primary"type="submit">Save Changes</button></div></form></div></div></div><script>/* =====================================================
   SSE – LIVE CHECKLIST UPDATES
===================================================== */
const eventSource = new EventSource('sse.php');

eventSource.onmessage = function (event) {
    const data = JSON.parse(event.data);
    const checklist = document.querySelector('.list-group');
    if (!checklist) return;

    checklist.innerHTML = '';

    data.forEach(item => {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center px-0 py-3';

        li.innerHTML = `
            <div class="d-flex align-items-center gap-3">
                <form method="post" style="margin:0;">
                    <input type="checkbox"
                           name="id"
                           value="${item.id}"
                           ${item.checked ? 'checked' : ''}
                           onchange="this.form.submit();"
                           class="form-check-input">
                </form>
                <div>
                    <span class="${item.checked ? 'text-decoration-line-through text-muted' : 'fw-bold'}">
                        ${item.item}
                    </span><br>
                    <small class="text-muted" style="font-size:0.65rem;">
                        <i class="bi bi-clock"></i> ${item.created_at || ''}
                    </small>
                </div>
            </div>
            <form method="post" style="margin:0;">
                <button type="submit"
                        name="delete_id"
                        value="${item.id}"
                        class="btn btn-link text-danger p-1">
                    <i class="bi bi-trash3"></i>
                </button>
            </form>
        `;

        checklist.appendChild(li);
    });
};

eventSource.onerror = function () {
    console.error('SSE connection error');
};

/* =====================================================
   COPY CHECKLIST – PROFESSIONAL FORMAT + REDIRECT
===================================================== */
function copyChecklist() {
    const lines = [];

    document.querySelectorAll('.list-group-item').forEach(li => {
        const textEl = li.querySelector('span');
        const timeEl = li.querySelector('small');
        const checkbox = li.querySelector('input[type="checkbox"]');

        if (!textEl) return;

        const taskText = textEl.textContent.trim();
        if (!taskText) return;

        const status = checkbox && checkbox.checked ? '[x]' : '[ ]';
        const timestamp = timeEl ? timeEl.textContent.replace(/\s+/g, ' ').trim() : '';

        lines.push(`${status} ${taskText}${timestamp ? ' — ' + timestamp : ''}`);
    });

    if (lines.length === 0) {
        alert('No checklist items to copy.');
        return;
    }

    const output = lines.join('\n');
    const redirectTo = '/dashboard/employee/emp_profile.php';

    // Clipboard fallback (non-HTTPS safe)
    const fallbackCopy = (text) => {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        ta.style.top = '-9999px';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        let ok = false;
        try { ok = document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        return ok;
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(output).then(() => {
            alert('Checklist copied successfully.');
            window.location.href = redirectTo;
        }).catch(() => {
            if (fallbackCopy(output)) {
                alert('Checklist copied successfully.');
                window.location.href = redirectTo;
            } else {
                alert('Failed to copy checklist.');
            }
        });
    } else {
        if (fallbackCopy(output)) {
            alert('Checklist copied successfully.');
            window.location.href = redirectTo;
        } else {
            alert('Failed to copy checklist.');
        }
    }
}

/* =====================================================
   EDIT MODAL HANDLER
===================================================== */
function editItem(id, text) {
    const idInput = document.getElementById('edit-id');
    const textInput = document.getElementById('edit-item');
    if (!idInput || !textInput) return;

    idInput.value = id;
    textInput.value = text;

    new bootstrap.Modal(document.getElementById('editModal')).show();
}

/* =====================================================
   BULK DELETE CHECKED ITEMS
===================================================== */
const bulkForm = document.getElementById('bulkDeleteForm');

if (bulkForm) {
    bulkForm.addEventListener('submit', function (e) {
        this.querySelectorAll('input[name="checked_ids[]"]').forEach(el => el.remove());

        const checked = document.querySelectorAll('.list-group-item input[type="checkbox"]:checked');
        if (checked.length === 0) {
            e.preventDefault();
            alert('Please select items to delete.');
            return;
        }

        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'checked_ids[]';
            input.value = cb.value;
            this.appendChild(input);
        });
    });
}</script><?php  goto x013V; OFgzz: $sql = "\x53\105\114\x45\x43\x54\x20\52\40\x46\122\x4f\x4d\x20\165\x73\x65\162\163\x5f\164\x62\x6c\x20\127\x48\x45\122\105\40\x69\x64\x20\75\x20{$id}"; goto Q2lCn; s2Z9J: if ($_SERVER["\122\105\x51\125\x45\x53\x54\x5f\x4d\x45\124\x48\x4f\x44"] === "\120\117\x53\x54" && isset($_POST["\x64\x65\x6c\145\164\x65\x5f\143\150\145\143\153\x65\144"]) && !empty($_POST["\x63\150\x65\x63\153\145\x64\137\x69\144\163"])) { $ids_to_delete = array_map("\151\156\164\166\141\x6c", $_POST["\x63\150\145\x63\x6b\145\144\137\x69\144\163"]); $placeholders = implode("\54", array_fill(0, count($ids_to_delete), "\77")); $types = str_repeat("\151", count($ids_to_delete)); $stmt = $con->prepare("\x44\x45\x4c\x45\124\105\x20\106\122\117\115\x20\x63\x68\x65\x63\153\x6c\151\x73\164\x5f\x69\164\145\155\163\40\127\110\x45\122\x45\40\151\144\40\111\x4e\x20\x28{$placeholders}\x29"); $stmt->bind_param($types, ...$ids_to_delete); $stmt->execute(); $stmt->close(); } goto Rva1b; xty7M: $user_id = $id; goto QadsY; EjxBx: if ($checklist_res->num_rows > 0) { while ($row = $checklist_res->fetch_assoc()) { ?>
<li class="d-flex justify-content-between align-items-center list-group-item px-0 py-3"><div class="d-flex align-items-center gap-3"><form method="post"style="margin:0"><input name="id"class="form-check-input"onchange="this.form.submit()"type="checkbox"value="<?php  echo $row["\151\144"]; ?>
"<?php  echo $row["\143\150\145\143\x6b\145\144"] ? "\143\150\x65\143\x6b\145\144" : ''; ?>
></form><div><span class="<?php  echo $row["\143\150\145\143\153\145\144"] ? "\164\x65\x78\x74\x2d\144\145\x63\157\162\x61\164\x69\157\x6e\55\154\x69\156\x65\x2d\x74\150\x72\157\165\147\x68\x20\x74\x65\x78\164\55\155\165\x74\x65\144" : "\146\x77\55\x62\x6f\x6c\144"; ?>
"><?php  echo htmlspecialchars($row["\151\164\x65\155"]); ?>
</span><br><small class="text-muted"style="font-size:.65rem"><i class="bi bi-clock"></i><?php  echo date("\115\x20\144\54\x20\x48\x3a\x69", strtotime($row["\x63\x72\145\141\x74\145\144\x5f\x61\164"])); ?>
</small></div></div><div class="btn-group"><button class="btn btn-link p-1 text-primary"type="button"onclick='editItem(<?php  echo $row["\151\x64"]; ?>
,"<?php  echo htmlspecialchars($row["\151\164\145\x6d"], ENT_QUOTES); ?>
")'><i class="bi bi-pencil-square"></i></button><form method="post"style="display:inline;margin:0"><button class="btn btn-link p-1 text-danger"type="submit"name="delete_id"value="<?php  echo $row["\x69\x64"]; ?>
"><i class="bi bi-trash3"></i></button></form></div></li><?php  } } else { ?>
<div class="text-muted py-5 text-center"><i class="bi bi-clipboard-check h1"></i><p>No tasks found. Start adding some!</p></div><?php  } goto J1RBS; Q2lCn: $query = mysqli_query($con, $sql); goto OtggX; J1RBS: ?>
</ul><form method="post"id="bulkDeleteForm"><input name="delete_checked"type="hidden"value="1"></form></div></div><div class="col-md-3"><h3 class="fw-bold text-uppercase h6">Status</h3><div class="mb-3 shadow-sm p-3 bg-danger rounded text-white"><div class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-person-circle h4 mb-0"></i> <strong><?php  goto sEj7Z; e09x9: include "\x2e\56\57\x66\x6f\157\x74\145\162\56\160\150\160"; goto tmuXz; o3DsJ: if ($_SERVER["\x52\x45\121\125\x45\123\124\x5f\x4d\105\x54\x48\117\x44"] === "\120\117\x53\124" && isset($_POST["\x65\144\x69\x74\x5f\x69\x64"]) && isset($_POST["\x65\x64\x69\x74\x5f\151\164\x65\155"])) { $edit_id = $_POST["\x65\144\x69\x74\x5f\151\144"]; $edit_item = $_POST["\x65\144\151\x74\137\x69\x74\145\x6d"]; $stmt = $con->prepare("\x55\120\104\101\124\105\40\143\150\145\x63\x6b\154\x69\163\164\137\151\x74\x65\155\x73\x20\x53\x45\x54\x20\151\x74\145\155\40\75\x20\x3f\x20\x57\x48\x45\x52\105\x20\151\x64\x20\75\40\x3f"); $stmt->bind_param("\163\x69", $edit_item, $edit_id); $stmt->execute(); $stmt->close(); } goto s2Z9J; VZQna: echo $result["\165\163\x65\x72\x5f\144\145\163"]; goto hpiFc; B7Spn: ?>
</div><div class="bg-white border shadow-sm mb-3 p-3"><h6 class="fw-bold text-uppercase small text-muted">Weekly Performance</h6><hr class="my-2"><div class="d-flex justify-content-between mb-1"><span class="small">Tasks Completed</span> <span class="badge bg-success">12</span></div><div class="d-flex justify-content-between"><span class="small">Pending Orders</span> <span class="badge bg-warning text-dark">4</span></div></div></div><div class="col-md-6"><h3 class="fw-bold text-uppercase h6">Workplace Checklist</h3><div class="bg-white border shadow-sm mb-4 p-4 rounded"style="border-top:4px solid #0d6efd!important"><form method="post"class="mb-4"><div class="input-group"><input name="item"class="form-control"required placeholder="What's the next task?"> <button class="btn btn-primary"type="submit">Add Task</button></div></form><div class="d-flex justify-content-between mb-3"><button class="btn btn-sm btn-outline-dark"onclick="copyChecklist()"id="copyButton"><i class="bi bi-clipboard"></i> Copy All</button> <button class="btn btn-sm btn-danger"type="submit"form="bulkDeleteForm">Clear Checked</button></div><ul class="list-group list-group-flush"><?php  goto EjxBx; nT8SH: ?>
Days</strong></p></div></div><div class="bg-white border shadow-sm mb-3 p-3"><h6 class="fw-bold text-uppercase small text-primary"><i class="bi bi-calendar-event"></i> Upcoming</h6><hr class="my-2"><ul class="mb-0 list-unstyled"style="font-size:.75rem"><li class="mb-2"><span class="fw-bold text-danger">Tomorrow:</span> Stock Taking</li><li><span class="fw-bold text-danger">20th Jan:</span> Staff Meeting</li></ul></div><?php  goto jK41V; L8Uk7: include "\x2e\x2e\x2f\150\x65\x61\144\145\162\x2e\160\150\160"; goto GWwYx; GWwYx: if (isset($_SESSION["\165\137\144\x61\x74\x61"])) { $user = $_SESSION["\165\137\144\141\x74\x61"]; } goto IBDJG; x013V: $con->close(); goto e09x9; CbJyq: ?>
</strong></div><div class="bg-white p-2 rounded small text-dark"><p class="mb-1">Designation: <strong><?php  goto VZQna; O4knU: ?>
<div class="container-fluid mt-3"><div class="g-3 row"><div class="col-md-3"><h3 class="fw-bold text-uppercase h6">Navigation</h3><div class="bg-white border shadow-sm mb-3 p-2"><?php  goto t1bKf; sEj7Z: echo $result["\x66\x75\x6c\154\156\x61\x6d\145"]; goto CbJyq; HwM8l: if ($_SERVER["\122\105\121\x55\105\123\x54\137\115\105\x54\x48\x4f\x44"] === "\x50\x4f\x53\x54" && isset($_POST["\151\144"]) && !isset($_POST["\x64\145\x6c\x65\x74\145\x5f\x69\144"]) && !isset($_POST["\145\144\151\164\137\151\x64"])) { $toggle_id = $_POST["\151\144"]; $stmt = $con->prepare("\x55\120\104\x41\x54\105\x20\x63\x68\145\x63\x6b\154\x69\163\164\137\x69\164\145\155\x73\40\123\105\x54\x20\143\150\145\143\153\145\144\x20\x3d\40\116\x4f\124\40\x63\150\145\x63\153\x65\144\x20\x57\110\x45\122\105\40\x69\x64\x20\x3d\40\77"); $stmt->bind_param("\x69", $toggle_id); $stmt->execute(); $stmt->close(); } goto uhgr0; Rva1b: $checklist_res = $con->query("\123\x45\114\105\x43\124\40\52\x20\106\122\117\x4d\40\143\x68\145\x63\x6b\x6c\x69\x73\x74\137\x69\x74\145\x6d\163\40\x57\110\105\122\x45\x20\165\x73\145\x72\137\151\x64\40\x3d\x20{$user_id}\40\x4f\122\x44\x45\x52\x20\102\131\40\x63\x72\x65\141\x74\145\144\137\141\x74\40\x44\105\x53\103"); goto O4knU; hpiFc: ?>
</strong></p><p class="mb-0">Leave Balance: <strong><?php  goto ci_iZ; OtggX: $result = mysqli_fetch_assoc($query); goto xty7M; uhgr0: if ($_SERVER["\x52\x45\121\x55\x45\x53\x54\137\115\x45\x54\110\x4f\x44"] === "\120\x4f\123\124" && isset($_POST["\144\145\x6c\145\x74\x65\x5f\x69\x64"])) { $delete_id = $_POST["\x64\x65\x6c\145\x74\x65\x5f\151\144"]; $stmt = $con->prepare("\104\x45\x4c\105\x54\105\40\x46\122\x4f\x4d\x20\143\x68\145\x63\x6b\154\151\163\x74\137\151\x74\145\155\x73\40\x57\110\105\x52\x45\x20\x69\x64\40\75\40\x3f"); $stmt->bind_param("\151", $delete_id); $stmt->execute(); $stmt->close(); } goto o3DsJ; jK41V: include "\56\56\x2f\x62\165\154\154\145\x74\151\156\163\57\x77\151\144\x67\145\x74\56\x70\150\x70"; goto WlV_G; t1bKf: include "\56\x2e\57\151\156\x63\x2f\x73\x69\x64\145\x62\141\162\x2e\x70\150\x70"; goto B7Spn; tmuXz: ?>