<?php
include '../../Databased/db_connect.php';

// — Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $del = $conn->prepare("DELETE FROM attendance WHERE id = ?");
    $del->bind_param("i", $id);
    $del->execute();
    header("Location: manageatd.php");
    exit;
}

// — Handle AJAX Save (bulk update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updates'])) {
    $updates = json_decode($_POST['updates'], true);
    $upd = $conn->prepare(
        "UPDATE attendance
         SET attendance_name = ?,
             event_name = ?,
             status = ?,
             start_time = ?,
             end_time = ?,
             attendance_date=?,
             location_name = ?
         WHERE id = ?"
    );
    foreach ($updates as $r) {
        $upd->bind_param(
            "ssssssi",
            $r['attendance_name'],
            $r['event_name'],
            $r['status'],
            $r['start_time'],
            $r['end_time'],
            $r['attendance_date'],
            $r['location_name'],
            $r['id']
        );
        $upd->execute();
    }
    echo json_encode(['success' => true]);
    exit;
}

// — Fetch all records with proper joins
$res = $conn->query("SELECT a.*, e.title as event_name, u.username as attendance_name, e.location
                     FROM attendance a 
                     JOIN events e ON a.event_id = e.event_id 
                     JOIN users u ON a.user_id = u.user_id 
                     ORDER BY a.attendance_id DESC");

include '../HADER_SIDER_FOOTER/HST.PHP';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Attendance | MyPetakom</title>
    <link rel="stylesheet" href="manageatd.css">
</head>
<body>
    <section class="content">
        <h2>Manage Attendance</h2>
        <div class="table-wrap">
            <table id="attd-table">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Attendance Name</th>
                    <th>Event Name</th>
                    <th>Status</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Attendance QR</th>
                    <th>Delete</th>
                </tr>
                </thead>
                <tbody>                <?php $i = 1; while ($row = $res->fetch_assoc()):
                    $status = $row['status_attd'] ?? '';
                    $fillUrl = "fillatd.php?id=" . $row['attendance_id'];
                    $imgUrl = "qr.php?data=" . urlencode($fillUrl);
                    $dlUrl = $imgUrl . "&download=1";
                ?>
                <tr data-id="<?= $row['attendance_id'] ?>">
                    <td><?= $i ?></td>
                    <td contenteditable="true" data-field="attendance_name"><?= htmlspecialchars($row['attendance_name']) ?></td>
                    <td contenteditable="true" data-field="event_name"><?= htmlspecialchars($row['event_name']) ?></td>
                    <td contenteditable="true" data-field="status"><?= htmlspecialchars($status) ?></td>
                    <td contenteditable="true" data-field="start_time"><?= date('M d, Y', strtotime($row['timestamp'])) ?></td>
                    <td contenteditable="true" data-field="end_time"><?= date('h:i A', strtotime($row['timestamp'])) ?></td>
                    <td contenteditable="true" data-field="end_time"><?= date('M d, Y', strtotime($row['timestamp'])) ?></td>
                    <td contenteditable="true" data-field="location_name"><?= htmlspecialchars($row['location'] ?? $row['location_verified']) ?></td>
                    <td>
                        <a href="<?= $dlUrl ?>" download="qr_<?= $row['attendance_id'] ?>.png">
                            <img src="<?= $imgUrl ?>" alt="QR">
                        </a>
                    </td>                    <td>
                        <button class="delete-btn" data-id="<?= $row['attendance_id'] ?>">Delete</button>
                    </td>
                </tr>
                <?php $i++; endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="actions">
            <button id="save-btn">Save</button>
        </div>
    </section>
</div>

<script>
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (confirm('Delete this record?')) {
                window.location = '?delete_id=' + btn.dataset.id;
            }
        });
    });

    document.getElementById('save-btn').addEventListener('click', () => {
        if (!confirm('Save all changes?')) return;

        const rows = [];
        document.querySelectorAll('#attd-table tbody tr').forEach(tr => {
            const obj = { id: tr.dataset.id };
            tr.querySelectorAll('td[contenteditable]').forEach(td => {
                obj[td.dataset.field] = td.innerText.trim();
            });
            rows.push(obj);
        });

        fetch('manageatd.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'updates=' + encodeURIComponent(JSON.stringify(rows))
        })
            .then(res => res.json())
            .then(resp => {
                if (!resp.success) {
                    alert('Error saving.');
                    return;
                }

                document.querySelectorAll('#attd-table tbody tr').forEach(tr => {
                    const id = tr.dataset.id;
                    const fillUrl = `fillatd.php?id=${id}`;
                    const imgUrl = `qr.php?data=${encodeURIComponent(fillUrl)}`;
                    const dlUrl = `${imgUrl}&download=1`;

                    const link = tr.querySelector('td:nth-child(8) a');
                    const img = link.querySelector('img');
                    link.href = dlUrl;
                    link.download = `qr_${id}.png`;
                    img.src = imgUrl;
                });

                alert('All changes saved and QR codes updated.');
            });
    });
</script>
</body>
</html>
