<?php

/**
 * Crafted Studio — Editor Dashboard
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('editor');

$db = getDB();
$flash = getFlash();

// Handle POST: status update + file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $editingId = (int)($_POST['editing_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    $catatan   = trim($_POST['catatan'] ?? '');

    if ($editingId && in_array($newStatus, ['editing', 'selesai'])) {
        // Verify this task belongs to current editor
        $stmt = $db->prepare("SELECT ef.id_editing, ef.id_booking FROM editing_foto ef WHERE ef.id_editing = ? AND ef.id_editor = ?");
        $stmt->execute([$editingId, $_SESSION['user_id']]);
        $editData = $stmt->fetch();

        if ($editData) {
            // Handle file upload when completing
            if ($newStatus === 'selesai' && isset($_FILES['photos']) && $_FILES['photos']['error'][0] !== UPLOAD_ERR_NO_FILE) {
                $uploadDir = __DIR__ . '/../../assets/uploads/gallery/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $bookingId = $editData['id_booking'];
                $files = $_FILES['photos'];
                $uploadCount = 0;

                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                            $newName = 'CS-' . str_pad($bookingId, 4, '0', STR_PAD_LEFT) . '_' . uniqid() . '.' . $ext;
                            if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $newName)) {
                                $db->prepare("INSERT INTO galeri_foto (id_booking, nama_file, url_file, status) VALUES (?, ?, ?, 'published')")
                                    ->execute([$bookingId, $files['name'][$i], '/crafted-studio/assets/uploads/gallery/' . $newName]);
                                $uploadCount++;
                            }
                        }
                    }
                }
                if ($uploadCount === 0) {
                    setFlash('error', 'Gagal upload foto. Pastikan format file valid (JPG, PNG, WebP, GIF).');
                    header('Location: dashboard.php');
                    exit;
                }
            } elseif ($newStatus === 'selesai') {
                // No files uploaded for selesai
                setFlash('error', 'Upload minimal 1 foto hasil editing untuk menyelesaikan tugas.');
                header('Location: dashboard.php');
                exit;
            }

            $db->prepare("UPDATE editing_foto SET status = ?, catatan = ?, tanggal_edit = NOW() WHERE id_editing = ? AND id_editor = ?")
                ->execute([$newStatus, $catatan, $editingId, $_SESSION['user_id']]);

            $msg = $newStatus === 'editing' ? 'Tugas editing dimulai.' : 'Editing selesai & foto berhasil diupload.';
            setFlash('success', $msg);
        }
        header('Location: dashboard.php');
        exit;
    }
}

$editingTasks = getEditingByEditor($_SESSION['user_id']);
$menunggu = array_filter($editingTasks, fn($t) => $t['status'] === 'menunggu');
$editing  = array_filter($editingTasks, fn($t) => $t['status'] === 'editing');
$selesai  = array_filter($editingTasks, fn($t) => $t['status'] === 'selesai');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Editor — Crafted Studio</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <style>
        .upload-zone {
            border: 2px dashed var(--admin-border);
            border-radius: var(--radius-md);
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.02);
        }

        .upload-zone:hover {
            border-color: var(--accent);
            background: rgba(200, 165, 90, 0.05);
        }

        .upload-zone i {
            font-size: 2rem;
            color: var(--text-muted);
            margin-bottom: 8px;
            display: block;
        }

        .upload-zone p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin: 0;
        }

        .upload-zone .file-count {
            color: var(--accent);
            font-weight: 600;
            margin-top: 8px;
            display: none;
        }

        .photo-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.75rem;
            color: var(--success);
        }
    </style>
</head>

<body>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h2>Crafted <span>Studio</span></h2>
                <p>Dashboard Editor</p>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Menu</div>
                    <a href="dashboard.php" class="nav-item active">
                        <span class="icon"><i class="ph ph-squares-four"></i></span>
                        Dashboard
                        <?php if (count($menunggu) > 0): ?>
                            <span class="badge-nav"><?= count($menunggu) ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 2)) ?></div>
                    <div class="user-info">
                        <h4><?= e($_SESSION['nama']) ?></h4>
                        <p>Editor</p>
                    </div>
                </div>
                <a href="../logout.php?role=editor" class="nav-item" style="margin-top:12px;color:var(--danger);">
                    <span class="icon"><i class="ph ph-sign-out"></i></span>
                    Logout
                </a>
            </div>
        </aside>

        <!-- Main -->
        <main class="admin-main">
            <div class="admin-topbar">
                <h1>Halo, <?= e($_SESSION['nama']) ?> 🎨</h1>
                <span style="font-size:0.85rem;color:var(--text-muted);">
                    <i class="ph ph-calendar"></i> <?= date('d M Y') ?>
                </span>
            </div>

            <?php if ($flash): ?>
                <div class="flash flash-<?= $flash['type'] ?>">
                    <i class="ph ph-<?= $flash['type'] === 'success' ? 'check-circle' : 'warning-circle' ?>"></i>
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon red"><i class="ph ph-clock"></i></div>
                    <div class="stat-value"><?= count($menunggu) ?></div>
                    <div class="stat-label">Menunggu</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="ph ph-paint-brush"></i></div>
                    <div class="stat-value"><?= count($editing) ?></div>
                    <div class="stat-label">Sedang Editing</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="ph ph-check-circle"></i></div>
                    <div class="stat-value"><?= count($selesai) ?></div>
                    <div class="stat-label">Selesai</div>
                </div>
            </div>

            <!-- Task List -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3><i class="ph ph-paint-brush" style="color:var(--accent);margin-right:8px;"></i>Tugas Editing</h3>
                </div>
                <div class="admin-card-body" style="padding:0;">
                    <?php if (empty($editingTasks)): ?>
                        <div class="empty-state">
                            <div class="icon"><i class="ph ph-image"></i></div>
                            <h3>Belum ada tugas editing</h3>
                            <p>Fotografer akan mengirimkan tugas editing kepada Anda</p>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Booking</th>
                                    <th>Pelanggan</th>
                                    <th>Paket</th>
                                    <th>Tanggal Sesi</th>
                                    <th>Status</th>
                                    <th>Foto</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($editingTasks as $t): ?>
                                    <tr>
                                        <td><strong style="color:var(--accent);">#CS-<?= str_pad($t['id_booking'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                                        <td><?= e($t['nama_pelanggan']) ?></td>
                                        <td><?= e($t['nama_paket']) ?></td>
                                        <td><?= date('d M Y', strtotime($t['tanggal_booking'])) ?></td>
                                        <td><?= statusBadge($t['status']) ?></td>
                                        <td>
                                            <?php $photoCount = getPhotoCountByBooking($t['id_booking']); ?>
                                            <?php if ($photoCount > 0): ?>
                                                <span class="photo-count-badge"><i class="ph ph-image"></i> <?= $photoCount ?> foto</span>
                                            <?php else: ?>
                                                <span style="color:var(--text-muted);font-size:0.8rem;">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($t['status'] === 'menunggu'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="editing_id" value="<?= $t['id_editing'] ?>">
                                                    <input type="hidden" name="status" value="editing">
                                                    <input type="hidden" name="catatan" value="">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="ph ph-play"></i> Mulai Edit
                                                    </button>
                                                </form>
                                            <?php elseif ($t['status'] === 'editing'): ?>
                                                <button class="btn btn-success btn-sm" onclick="openUploadModal(<?= $t['id_editing'] ?>, '<?= e($t['nama_pelanggan']) ?>', '#CS-<?= str_pad($t['id_booking'], 4, '0', STR_PAD_LEFT) ?>')">
                                                    <i class="ph ph-upload"></i> Upload & Selesai
                                                </button>
                                            <?php else: ?>
                                                <span class="badge badge-success">✓ Selesai</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Upload & Confirm Modal -->
    <div class="modal-overlay" id="uploadModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="ph ph-upload" style="color:var(--success);margin-right:8px;"></i>Upload Foto & Konfirmasi</h3>
                <button class="modal-close" onclick="closeUploadModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="editing_id" id="uploadEditingId">
                    <input type="hidden" name="status" value="selesai">

                    <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:20px;">
                        Upload foto hasil editing untuk booking <strong id="uploadBookingLabel"></strong> — <span id="uploadCustomerLabel"></span>
                    </p>

                    <div class="form-group">
                        <label>Foto Hasil Editing <span style="color:var(--danger);">*</span></label>
                        <label class="upload-zone" for="photoInput" id="uploadZone">
                            <i class="ph ph-cloud-arrow-up"></i>
                            <p>Klik untuk pilih foto atau drag & drop</p>
                            <p style="font-size:0.75rem;margin-top:4px;">JPG, PNG, WebP, GIF — maksimal beberapa file sekaligus</p>
                            <p class="file-count" id="fileCount"></p>
                        </label>
                        <input type="file" name="photos[]" id="photoInput" multiple accept="image/*" required
                            style="position:absolute;opacity:0;pointer-events:none;">
                    </div>

                    <div class="form-group">
                        <label>Catatan (opsional)</label>
                        <textarea name="catatan" class="form-control" rows="3" placeholder="Catatan tentang hasil editing..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeUploadModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-check-circle"></i> Konfirmasi Selesai
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUploadModal(editingId, customer, bookingCode) {
            document.getElementById('uploadEditingId').value = editingId;
            document.getElementById('uploadBookingLabel').textContent = bookingCode;
            document.getElementById('uploadCustomerLabel').textContent = customer;
            document.getElementById('photoInput').value = '';
            document.getElementById('fileCount').style.display = 'none';
            document.getElementById('uploadModal').classList.add('active');
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.remove('active');
        }
        document.getElementById('uploadModal').addEventListener('click', function(e) {
            if (e.target === this) closeUploadModal();
        });
        // Show selected file count
        document.getElementById('photoInput').addEventListener('change', function() {
            var fc = document.getElementById('fileCount');
            if (this.files.length > 0) {
                fc.textContent = this.files.length + ' file dipilih';
                fc.style.display = 'block';
            } else {
                fc.style.display = 'none';
            }
        });
    </script>

</body>

</html>