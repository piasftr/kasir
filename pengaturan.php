<?php
// ==== Bagian PROSES: Jangan ada output HTML di sini ====
session_start();
require 'config.php'; // Koneksi DB

// Cek login dulu
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role'] ?? 'admin';
$owner_nama_toko = $_SESSION['nama_toko'] ?? '';

// ==== Proses Update User/Admin ====
if (isset($_POST['get'])) {
    $id = $_POST['id_login'];
    $user = $_POST['user'];
    $toko = $_POST['nama_toko'] ?? '';
    $alamat = $_POST['alamat'];
    $telp = $_POST['telp'];
    $pass = $_POST['pass'];

    if ($user_role === 'owner') {
        $stmt = mysqli_prepare($conn, "UPDATE login SET user=?, pass=?, nama_toko=?, alamat=?, telp=? WHERE id_login = ?");
        mysqli_stmt_bind_param($stmt, "sssssi", $user, $pass, $toko, $alamat, $telp, $id);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE login SET user=?, pass=?, alamat=?, telp=? WHERE id_login = ?");
        mysqli_stmt_bind_param($stmt, "ssssi", $user, $pass, $alamat, $telp, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => "✅ Data berhasil diupdate."];
        if ($id == $_SESSION['id_login']) {
            $_SESSION['user'] = $user;
            $_SESSION['nama_toko'] = $toko;
        }
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => "❌ Data gagal diupdate: " . mysqli_error($conn)];
    }

    mysqli_stmt_close($stmt);
    header("Location: pengaturan.php");
    exit();
}

// ==== Proses Tambah Admin Baru ====
if (isset($_POST['buat_admin'])) {
    $new_user = $_POST['new_admin_user'];
    $new_pass = $_POST['new_admin_pass'];
    $new_alamat = $_POST['new_admin_alamat'];
    $new_telp = $_POST['new_admin_telp'];
    $new_toko = $_POST['new_admin_toko'];
    $role = 'admin';

    $stmt = mysqli_prepare($conn, "INSERT INTO login (user, pass, nama_toko, alamat, telp, role) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssss", $new_user, $new_pass, $new_toko, $new_alamat, $new_telp, $role);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => "✅ Admin baru berhasil dibuat."];
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => "❌ Gagal membuat admin: " . mysqli_error($conn)];
    }

    mysqli_stmt_close($stmt);
    header("Location: pengaturan.php");
    exit();
}

// ==== Promote Admin ke Owner ====
if (isset($_GET['action']) && $_GET['action'] === 'make_owner' && isset($_GET['id'])) {
    if ($user_role === 'owner') {
        $target_id = $_GET['id'];
        $cek = mysqli_query($conn, "SELECT role FROM login WHERE id_login = '$target_id'");
        $cek_role = mysqli_fetch_assoc($cek)['role'];

        if ($cek_role === 'admin') {
            $stmt = mysqli_prepare($conn, "UPDATE login SET role = 'owner' WHERE id_login = ?");
            mysqli_stmt_bind_param($stmt, "i", $target_id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = ['type' => 'success', 'text' => "✅ Admin berhasil dijadikan Owner."];
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => "❌ Gagal ubah role: " . mysqli_error($conn)];
            }

            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['message'] = ['type' => 'warning', 'text' => "⚠️ Pengguna ini bukan admin atau sudah owner."];
        }
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => "🚫 Akses ditolak!"];
    }
    header("Location: pengaturan.php");
    exit();
}

// ==== Hapus Admin ====
if (isset($_GET['action']) && $_GET['action'] === 'delete_admin' && isset($_GET['id'])) {
    if ($user_role === 'owner') {
        $target_id = $_GET['id'];
        
        // Pastikan tidak menghapus diri sendiri (owner yang sedang login)
        if ($target_id == $_SESSION['id_login']) {
            $_SESSION['message'] = ['type' => 'danger', 'text' => "❌ Anda tidak bisa menghapus akun Anda sendiri."];
            header("Location: pengaturan.php");
            exit();
        }

        // Cek apakah yang dihapus adalah admin
        $cek = mysqli_query($conn, "SELECT role FROM login WHERE id_login = '$target_id'");
        $cek_role = mysqli_fetch_assoc($cek)['role'];

        if ($cek_role === 'admin') {
            $stmt = mysqli_prepare($conn, "DELETE FROM login WHERE id_login = ? AND role = 'admin'");
            mysqli_stmt_bind_param($stmt, "i", $target_id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = ['type' => 'success', 'text' => "✅ Admin berhasil dihapus."];
            } else {
                $_SESSION['message'] = ['type' => 'danger', 'text' => "❌ Gagal menghapus admin: " . mysqli_error($conn)];
            }

            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['message'] = ['type' => 'warning', 'text' => "⚠️ Pengguna ini bukan admin, tidak dapat dihapus melalui fitur ini."];
        }
    } else {
        $_SESSION['message'] = ['type' => 'danger', 'text' => "🚫 Akses ditolak!"];
    }
    header("Location: pengaturan.php");
    exit();
}


// ==== Ambil data user untuk ditampilkan ====
$result1 = mysqli_query($conn, "SELECT * FROM login WHERE user = '" . $_SESSION['user'] . "'");
$current_user_data = mysqli_fetch_array($result1);

if (!$current_user_data) {
    header("Location: login.php");
    exit();
}

$list_admins = [];
if ($user_role === 'owner') {
    $admin_query = mysqli_query($conn, "SELECT * FROM login WHERE role = 'admin' AND nama_toko = '" . mysqli_real_escape_string($conn, $owner_nama_toko) . "'");
    while ($admin_data = mysqli_fetch_array($admin_query)) {
        $list_admins[] = $admin_data;
    }
}
?>

<?php include 'template/header.php'; ?>
<div class="col-md-9 mb-2">
    <div class="row">
        <?php
        // Tampilkan pesan dari sesi jika ada, di bagian paling atas area konten
        if (isset($_SESSION['message'])) {
            $message_type = $_SESSION['message']['type'];
            $message_text = $_SESSION['message']['text'];
            echo "
            <div class='col-12'>
                <div class='alert alert-$message_type alert-dismissible fade show' role='alert'>
                    $message_text
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>
            </div>
            ";
            unset($_SESSION['message']); // Hapus pesan setelah ditampilkan agar tidak muncul lagi
        }
        ?>
        <div class="col-md-7 mb-2">
            <div class="card">
                <div class="card-header bg-danger">
                    <div class="card-tittle text-white"><i class="fa fa-cog"></i> <b>Pengaturan Akun</b></div>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <fieldset>
                            <div class="form-group row">
                                <input type="hidden" name="id_login" value="<?php echo $current_user_data['id_login'];?>">
                                <?php if ($user_role === 'owner'): ?>
                                    <label class="col-sm-4 col-form-label"><b>Nama Toko :</b></label>
                                    <div class="col-sm-8 mb-2">
                                        <input type="text" name="nama_toko" class="form-control" value="<?php echo $current_user_data['nama_toko'];?>" required>
                                    </div>
                                <?php else: // Role admin tidak perlu melihat dan mengedit nama toko ?>
                                    <input type="hidden" name="nama_toko" value="<?php echo $current_user_data['nama_toko'];?>">
                                <?php endif; ?>

                                <label class="col-sm-4 col-form-label"><b>Telepon :</b></label>
                                <div class="col-sm-8 mb-2">
                                    <input type="number" name="telp" class="form-control" value="<?php echo $current_user_data['telp'];?>" required>
                                </div>
                                <label class="col-sm-4 col-form-label"><b>Alamat :</b></label>
                                <div class="col-sm-8 mb-2">
                                    <input type="text" name="alamat" class="form-control" value="<?php echo $current_user_data['alamat'];?>" required>
                                </div>
                                <label class="col-sm-4 col-form-label"><b>Username :</b></label>
                                <div class="col-sm-8 mb-2">
                                    <input type="text" name="user" class="form-control" value="<?php echo $current_user_data['user'];?>" required>
                                </div>
                                <label class="col-sm-4 col-form-label"><b>New Password:</b></label>
                                <div class="col-sm-8 mb-2">
                                    <input type="password" name="pass" class="form-control" placeholder="****" required>
                                </div>
                            </div>
                            <div class="text-right">
                                <button class="btn btn-success" name="get" type="submit">Update</button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($user_role === 'owner'): ?>
        <div class="col-md-5 mb-2">
            <div class="card">
                <div class="card-header bg-danger">
                    <div class="card-tittle text-white"><i class="fa fa-users"></i> <b>Manajemen Admin</b></div>
                </div>
                <div class="card-body">
                    <h5>Daftar Admin (<a href="#" data-toggle="modal" data-target="#addAdminModal">Buat Admin Baru</a>)</h5>
                    <?php if (empty($list_admins)): ?>
                        <p>Belum ada akun admin.</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($list_admins as $admin): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($admin['user']); ?>
                                    <span>
                                        <a href="pengaturan.php?action=make_owner&id=<?php echo $admin['id_login']; ?>" 
                                           onclick="return confirm('Anda yakin ingin mengubah <?php echo htmlspecialchars($admin['user']); ?> menjadi Owner? Ini akan membatasi jumlah owner di toko Anda.');"
                                           class="btn btn-sm btn-info me-1">Jadikan Owner</a>
                                        <a href="pengaturan.php?action=delete_admin&id=<?php echo $admin['id_login']; ?>" 
                                           onclick="return confirm('Anda yakin ingin menghapus admin <?php echo htmlspecialchars($admin['user']); ?>?');"
                                           class="btn btn-sm btn-danger">Hapus</a>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php if ($user_role === 'owner'): ?>
<div class="modal fade" id="addAdminModal" tabindex="-1" role="dialog" aria-labelledby="addAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-purple text-white">
        <h5 class="modal-title" id="addAdminModalLabel">Buat Admin Baru</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="form-group">
            <label for="new_admin_user">Username:</label>
            <input type="text" class="form-control" id="new_admin_user" name="new_admin_user" required>
          </div>
          <div class="form-group">
            <label for="new_admin_pass">Password:</label>
            <input type="password" class="form-control" id="new_admin_pass" name="new_admin_pass" required>
          </div>
          <div class="form-group">
            <label for="new_admin_alamat">Alamat:</label>
            <input type="text" class="form-control" id="new_admin_alamat" name="new_admin_alamat" required>
          </div>
          <div class="form-group">
            <label for="new_admin_telp">Telepon:</label>
            <input type="number" class="form-control" id="new_admin_telp" name="new_admin_telp" required>
          </div>
          <div class="form-group">
            <label for="new_admin_toko">Nama Toko:</label>
            <input type="text" class="form-control" id="new_admin_toko" name="new_admin_toko" value="<?php echo htmlspecialchars($owner_nama_toko); ?>" readonly>
          </div>
          <input type="hidden" name="role" value="admin">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
          <button type="submit" name="buat_admin" class="btn btn-primary">Buat Admin</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include 'template/footer.php';?>