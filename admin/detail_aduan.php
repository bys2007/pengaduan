<?php require_once('../Connections/koneksi.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF'] . "?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")) {
  $logoutAction .= "&" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) && ($_GET['doLogout'] == "true")) {
  $_SESSION['MM_Username'] = NULL;
  $_SESSION['MM_UserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  unset($_SESSION['MM_Username']);
  unset($_SESSION['MM_UserGroup']);
  unset($_SESSION['PrevUrl']);

  $logoutGoTo = "../index.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "admin";
$MM_donotCheckaccess = "false";

function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup)
{
  $isValid = false;

  if (!empty($UserName)) {
    $arrUsers = explode(",", $strUsers);
    $arrGroups = explode(",", $strGroups);
    if (in_array($UserName, $arrUsers)) {
      $isValid = true;
    }
    if (in_array($UserGroup, $arrGroups)) {
      $isValid = true;
    }
    if (($strUsers == "") && false) {
      $isValid = true;
    }
  }
  return $isValid;
}

$MM_restrictGoTo = "data_pengaduan.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) {
    $MM_qsChar = "&";
  }
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) {
    $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  }
  $MM_restrictGoTo = $MM_restrictGoTo . $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: " . $MM_restrictGoTo);
  exit;
}
?>
<?php
if (!function_exists("GetSQLValueString")) {
  function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
  {
    if (PHP_VERSION < 6) {
      $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
    }

    $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

    switch ($theType) {
      case "text":
        $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
        break;
      case "long":
      case "int":
        $theValue = ($theValue != "") ? intval($theValue) : "NULL";
        break;
      case "double":
        $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
        break;
      case "date":
        $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
        break;
      case "defined":
        $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
        break;
    }
    return $theValue;
  }
}

$statusMessage = "";
$statusType = "info";
$statusClassMap = array(
  "success" => "bg-green-50 border border-green-200 text-green-700",
  "error"   => "bg-red-50 border border-red-200 text-red-700",
  "warning" => "bg-yellow-50 border border-yellow-200 text-yellow-700",
  "info"    => "bg-blue-50 border border-blue-200 text-blue-700"
);
$statusIconMap = array(
  "success" => "fas fa-check-circle",
  "error"   => "fas fa-times-circle",
  "warning" => "fas fa-exclamation-triangle",
  "info"    => "fas fa-info-circle"
);

$validStatusValues = array('0', 'proses', 'selesai');

if (isset($_GET['status'])) {
  switch ($_GET['status']) {
    case 'status_updated':
      $statusMessage = "Status pengaduan berhasil diperbarui.";
      $statusType = "success";
      break;
    case 'status_invalid':
      $statusMessage = "Status pengaduan tidak dikenali.";
      $statusType = "error";
      break;
    case 'tanggapan_added':
      $statusMessage = "Tanggapan berhasil ditambahkan.";
      $statusType = "success";
      break;
    case 'tanggapan_updated':
      $statusMessage = "Tanggapan berhasil diperbarui.";
      $statusType = "success";
      break;
    case 'tanggapan_deleted':
      $statusMessage = "Tanggapan berhasil dihapus.";
      $statusType = "success";
      break;
    case 'error':
      $statusMessage = "Terjadi kesalahan saat memproses permintaan Anda.";
      $statusType = "error";
      break;
  }
}

$colname_Radmin = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_Radmin = $_SESSION['MM_Username'];
}
mysql_select_db($database_koneksi, $koneksi);
$query_Radmin = sprintf("SELECT * FROM petugas WHERE username = %s", GetSQLValueString($colname_Radmin, "text"));
$Radmin = mysql_query($query_Radmin, $koneksi) or die(mysql_error());
$row_Radmin = mysql_fetch_assoc($Radmin);
$totalRows_Radmin = mysql_num_rows($Radmin);

$adminUsername = isset($row_Radmin['username']) ? $row_Radmin['username'] : 'Administrator';
$adminFoto = isset($row_Radmin['foto']) ? $row_Radmin['foto'] : '';
$adminInitial = strtoupper(substr($adminUsername, 0, 1));
if ($adminInitial === '') {
  $adminInitial = 'A';
}
$adminId = isset($row_Radmin['id_petugas']) ? intval($row_Radmin['id_petugas']) : 0;

$idPengaduan = isset($_GET['id_pengaduan']) ? intval($_GET['id_pengaduan']) : 0;
if ($idPengaduan <= 0) {
  header("Location: data_pengaduan.php?status=not_found");
  exit;
}

if (isset($_GET['ubah_status'])) {
  $newStatus = strtolower(trim($_GET['ubah_status']));
  if (in_array($newStatus, $validStatusValues, true)) {
    mysql_select_db($database_koneksi, $koneksi);
    $updateStatusSQL = sprintf(
      "UPDATE pengaduan SET status=%s WHERE id_pengaduan=%s",
      GetSQLValueString($newStatus, "text"),
      GetSQLValueString($idPengaduan, "int")
    );
    mysql_query($updateStatusSQL, $koneksi) or die(mysql_error());
    header("Location: detail_aduan.php?id_pengaduan=" . urlencode($idPengaduan) . "&status=status_updated");
    exit;
  } else {
    header("Location: detail_aduan.php?id_pengaduan=" . urlencode($idPengaduan) . "&status=status_invalid");
    exit;
  }
}

if (isset($_GET['hapus_tanggapan'])) {
  $idTanggapan = intval($_GET['hapus_tanggapan']);
  mysql_select_db($database_koneksi, $koneksi);
  $deleteTanggapanSQL = sprintf(
    "DELETE FROM tanggapan WHERE id_tanggapan=%s AND id_pengaduan=%s",
    GetSQLValueString($idTanggapan, "int"),
    GetSQLValueString($idPengaduan, "int")
  );
  mysql_query($deleteTanggapanSQL, $koneksi) or die(mysql_error());
  if (mysql_affected_rows($koneksi) > 0) {
    header("Location: detail_aduan.php?id_pengaduan=" . urlencode($idPengaduan) . "&status=tanggapan_deleted");
  } else {
    header("Location: detail_aduan.php?id_pengaduan=" . urlencode($idPengaduan) . "&status=error");
  }
  exit;
}

if (isset($_POST['submit_tanggapan'])) {
  $postedPengaduan = isset($_POST['id_pengaduan']) ? intval($_POST['id_pengaduan']) : 0;
  $postedPetugas = isset($_POST['id_petugas']) ? intval($_POST['id_petugas']) : 0;
  $postedTanggapan = isset($_POST['tanggapan']) ? trim($_POST['tanggapan']) : "";

  if ($postedPengaduan === $idPengaduan && $postedPetugas > 0 && $postedTanggapan !== "") {
    mysql_select_db($database_koneksi, $koneksi);
    $insertSQL = sprintf(
      "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) VALUES (%s, %s, %s, %s)",
      GetSQLValueString($postedPengaduan, "int"),
      GetSQLValueString(date('Y-m-d'), "date"),
      GetSQLValueString($postedTanggapan, "text"),
      GetSQLValueString($postedPetugas, "int")
    );
    mysql_query($insertSQL, $koneksi) or die(mysql_error());

    $updateStatusSQL = sprintf(
      "UPDATE pengaduan SET status=%s WHERE id_pengaduan=%s",
      GetSQLValueString('proses', "text"),
      GetSQLValueString($idPengaduan, "int")
    );
    mysql_query($updateStatusSQL, $koneksi) or die(mysql_error());

    header("Location: detail_aduan.php?id_pengaduan=" . urlencode($idPengaduan) . "&status=tanggapan_added");
    exit;
  }

  header("Location: detail_aduan.php?id_pengaduan=" . urlencode($idPengaduan) . "&status=error");
  exit;
}

if (isset($_POST['update_tanggapan'])) {
  $postedTanggapan = isset($_POST['tanggapan']) ? trim($_POST['tanggapan']) : "";
  $idTanggapan = isset($_POST['id_tanggapan']) ? intval($_POST['id_tanggapan']) : 0;

  if ($idTanggapan > 0 && $postedTanggapan !== "") {
    mysql_select_db($database_koneksi, $koneksi);
    $updateSQL = sprintf(
      "UPDATE tanggapan SET tanggapan=%s, tgl_tanggapan=%s WHERE id_tanggapan=%s AND id_pengaduan=%s",
      GetSQLValueString($postedTanggapan, "text"),
      GetSQLValueString(date('Y-m-d'), "date"),
      GetSQLValueString($idTanggapan, "int"),
      GetSQLValueString($idPengaduan, "int")
    );
    mysql_query($updateSQL, $koneksi) or die(mysql_error());

    header("Location: detail_aduan.php?id_pengaduan=" . urlencode($idPengaduan) . "&status=tanggapan_updated");
    exit;
  }

  header("Location: detail_aduan.php?id_pengaduan=" . urlencode($idPengaduan) . "&status=error");
  exit;
}

mysql_select_db($database_koneksi, $koneksi);
$query_Raduan = sprintf(
  "SELECT p.*, m.nama, m.username AS masyarakat_username, m.telp FROM pengaduan p LEFT JOIN masyarakat m ON p.nik = m.nik WHERE p.id_pengaduan = %s",
  GetSQLValueString($idPengaduan, "int")
);
$Raduan = mysql_query($query_Raduan, $koneksi) or die(mysql_error());
$row_Raduan = mysql_fetch_assoc($Raduan);

if (!$row_Raduan) {
  if (isset($Raduan)) {
    mysql_free_result($Raduan);
  }
  header("Location: data_pengaduan.php?status=not_found");
  exit;
}

$currentStatus = isset($row_Raduan['status']) ? strtolower($row_Raduan['status']) : '';
$statusLabel = ($currentStatus !== '' && $currentStatus !== null) ? $currentStatus : 'Tidak diketahui';
$statusTextMap = array(
  '0' => 'Belum Diproses',
  'proses' => 'Proses',
  'selesai' => 'Selesai'
);
$statusClassMapBadge = array(
  '0' => 'bg-red-100 text-red-700 border border-red-200',
  'proses' => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
  'selesai' => 'bg-green-100 text-green-700 border border-green-200'
);
$statusLabel = isset($statusTextMap[$currentStatus]) ? $statusTextMap[$currentStatus] : ucfirst($currentStatus);
$statusBadgeClass = isset($statusClassMapBadge[$currentStatus]) ? $statusClassMapBadge[$currentStatus] : 'bg-gray-100 text-gray-700 border border-gray-200';

mysql_select_db($database_koneksi, $koneksi);
$query_Tanggapan = sprintf(
  "SELECT t.*, p.nama_petugas FROM tanggapan t LEFT JOIN petugas p ON t.id_petugas = p.id_petugas WHERE t.id_pengaduan = %s ORDER BY t.tgl_tanggapan DESC, t.id_tanggapan DESC",
  GetSQLValueString($idPengaduan, "int")
);
$RtanggapanResource = mysql_query($query_Tanggapan, $koneksi) or die(mysql_error());
$tanggapanList = array();
if ($RtanggapanResource) {
  while ($row = mysql_fetch_assoc($RtanggapanResource)) {
    $tanggapanList[] = $row;
  }
  mysql_free_result($RtanggapanResource);
}
$totalRows_Rtanggapan = count($tanggapanList);
$tanggapanAktif = $totalRows_Rtanggapan > 0 ? $tanggapanList[0] : null;

$pelaporNama = isset($row_Raduan['nama']) ? $row_Raduan['nama'] : '-';
$pelaporUsername = isset($row_Raduan['masyarakat_username']) ? $row_Raduan['masyarakat_username'] : '-';
$pelaporTelp = isset($row_Raduan['telp']) ? $row_Raduan['telp'] : '-';
$tanggalPengaduan = isset($row_Raduan['tgl_pengaduan']) ? $row_Raduan['tgl_pengaduan'] : '';

$statusButtons = array(
  array('value' => '0', 'label' => 'Belum Diproses', 'class' => 'bg-red-50 text-red-600 hover:bg-red-100', 'icon' => 'fas fa-clock'),
  array('value' => 'proses', 'label' => 'Proses', 'class' => 'bg-yellow-50 text-yellow-600 hover:bg-yellow-100', 'icon' => 'fas fa-spinner'),
  array('value' => 'selesai', 'label' => 'Selesai', 'class' => 'bg-green-50 text-green-600 hover:bg-green-100', 'icon' => 'fas fa-check-circle')
);
?>
<!DOCTYPE html>
<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <link type="image/x-icon" href="../asset/logo COLORFUL.png" rel="icon"/>
  <link type="image/x-icon" href="../asset/logo COLORFUL.png" rel="shortcut icon"/>
  <meta content="Layanan Pengaduan Masyarakat Online SMK Negeri 5 Kendal untuk memudahkan pelaporan dan penanganan aduan masyarakat secara efisien." name="description"/>
  <title>Detail Pengaduan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"/>
  <style>
   body { font-family: 'Inter', sans-serif; }
   .clickable-image { cursor: pointer; }
  </style>
 </head>
 <body class="bg-[#f5f7fa] min-h-screen flex">
  <aside class="w-64 bg-white border-r border-gray-200 flex flex-col px-6 py-8 select-none fixed inset-y-0 left-0 z-30" style="background-color: #4B88FE; box-shadow: 0px 0 5px rgba(0, 0, 0, 0.5);">
   <div class="flex items-center space-x-3 mb-10">
    <img alt="Logo" class="w-10 h-10" src="../asset/logo COLORFUL.png" style="width:50px; height:50px;"/>
    <span class="font-semibold text-xl text-white">Pengaduan Masyarakat</span>
   </div>
   <nav class="flex flex-col space-y-6 text-white text-sm font-normal">
    <a href="dashboard.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-home text-base"></i>
     <span>Dashboard</span>
    </a>
    <div class="uppercase text-xs font-semibold text-gray-200 tracking-wider mt-6" style="opacity: 0.8;">Menu</div>
    <a href="data_petugas.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-user-shield text-base"></i>
     <span>Data Petugas</span>
    </a>
    <a href="data_masyarakat.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-users text-base"></i>
     <span>Data Masyarakat</span>
    </a>
    <a href="data_pengaduan.php" class="flex items-center space-x-2 bg-white text-[#4B88FE] hover:bg-white hover:text-[#4B88FE] rounded-lg px-3 py-2">
     <i class="fas fa-file-alt text-base"></i>
     <span>Data Pengaduan</span>
    </a>
    <div class="uppercase text-xs font-semibold text-gray-200 tracking-wider mt-6" style="opacity: 0.8;">Logout</div>
    <a href="<?php echo $logoutAction; ?>" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-sign-out-alt text-base"></i>
     <span>Logout</span>
    </a>
   </nav>
  </aside>
  <main class="flex-1 p-6 md:p-8 space-y-6" style="max-width: calc(100vw - 16rem); margin-left: 16rem;">
   <div class="bg-white rounded-lg shadow px-4 py-3 flex items-center justify-between max-w-full" style="min-height:48px">
    <div class="flex-1 flex items-center">
     <span class="font-semibold text-gray-700 text-lg">Selamat Datang, <?php echo htmlentities($adminUsername, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
    <div class="flex items-center space-x-6">
     <button aria-label="Notifications" class="relative text-gray-500 hover:text-gray-700 focus:outline-none">
      <i class="far fa-bell text-xl"></i>
     </button>
     <div aria-label="User menu" class="relative">
      <?php if (!empty($adminFoto)) { ?>
      <img alt="Foto Admin" class="w-8 h-8 rounded-full clickable-image object-cover" height="32" src="../foto_akun/<?php echo htmlentities($adminFoto, ENT_QUOTES, 'UTF-8'); ?>" width="32"/>
      <?php } else { ?>
      <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-semibold"><?php echo htmlentities($adminInitial, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php } ?>
      <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
     </div>
    </div>
   </div>
   <div class="text-gray-600 text-sm">
    <span class="text-gray-400">Data Pengaduan</span>
    <span>/</span>
    <span class="font-semibold text-gray-900">Detail Aduan</span>
   </div>
   <header class="bg-white rounded-lg shadow px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
     <p class="text-sm text-gray-500">Pengaduan</p>
     <h1 class="text-2xl font-bold text-gray-800">Detail Pengaduan #<?php echo htmlentities($row_Raduan['id_pengaduan'], ENT_QUOTES, 'UTF-8'); ?></h1>
     <p class="text-xs text-gray-500 mt-1">Diajukan pada: <?php echo $tanggalPengaduan ? htmlentities(date('d F Y', strtotime($tanggalPengaduan)), ENT_QUOTES, 'UTF-8') : '-'; ?></p>
    </div>
    <div class="flex items-center gap-3">
     <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold <?php echo $statusBadgeClass; ?>">
      <?php echo htmlentities($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
     </span>
     <a href="data_pengaduan.php" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
      <i class="fas fa-arrow-left text-xs"></i>
      Kembali
     </a>
    </div>
   </header>
   <?php if (!empty($statusMessage)) {
     $noticeClass = isset($statusClassMap[$statusType]) ? $statusClassMap[$statusType] : $statusClassMap['info'];
     $noticeIcon = isset($statusIconMap[$statusType]) ? $statusIconMap[$statusType] : $statusIconMap['info'];
   ?>
   <div class="<?php echo $noticeClass; ?> px-4 py-3 rounded-lg flex items-start gap-3">
    <i class="<?php echo $noticeIcon; ?> mt-1"></i>
    <div>
     <p class="font-semibold"><?php echo htmlentities($statusMessage, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
   </div>
   <?php } ?>
   <section class="bg-white rounded-lg shadow p-6 space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
     <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pengaduan</h2>
      <dl class="space-y-3 text-sm text-gray-700">
       <div class="flex justify-between">
        <dt class="font-medium text-gray-500">ID Pengaduan</dt>
        <dd>#<?php echo htmlentities($row_Raduan['id_pengaduan'], ENT_QUOTES, 'UTF-8'); ?></dd>
       </div>
       <div class="flex justify-between">
        <dt class="font-medium text-gray-500">Tanggal</dt>
        <dd><?php echo $tanggalPengaduan ? htmlentities(date('d F Y', strtotime($tanggalPengaduan)), ENT_QUOTES, 'UTF-8') : '-'; ?></dd>
       </div>
       <div class="flex justify-between">
        <dt class="font-medium text-gray-500">NIK Pelapor</dt>
        <dd><?php echo htmlentities($row_Raduan['nik'], ENT_QUOTES, 'UTF-8'); ?></dd>
       </div>
       <div class="flex justify-between">
        <dt class="font-medium text-gray-500">Total Tanggapan</dt>
        <dd><?php echo intval($totalRows_Rtanggapan); ?></dd>
       </div>
      </dl>
     </div>
     <div>
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Tindakan Cepat</h2>
      <div class="flex flex-wrap gap-2">
       <?php foreach ($statusButtons as $button) {
         $isActive = ($currentStatus === $button['value']);
         $buttonClasses = $isActive ? 'bg-blue-600 text-white pointer-events-none' : $button['class'];
       ?>
       <a href="detail_aduan.php?id_pengaduan=<?php echo urlencode($idPengaduan); ?>&ubah_status=<?php echo urlencode($button['value']); ?>" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition <?php echo $buttonClasses; ?>">
        <i class="<?php echo $button['icon']; ?> text-xs"></i>
        <?php echo htmlentities($button['label'], ENT_QUOTES, 'UTF-8'); ?>
       </a>
       <?php } ?>
      </div>
     </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
     <div class="bg-gray-50 rounded-lg p-5 border border-gray-100">
      <h3 class="text-md font-semibold text-gray-800 mb-3">Data Pelapor</h3>
      <dl class="space-y-2 text-sm text-gray-700">
       <div>
        <dt class="font-medium text-gray-500">Nama</dt>
        <dd><?php echo htmlentities($pelaporNama, ENT_QUOTES, 'UTF-8'); ?></dd>
       </div>
       <div>
        <dt class="font-medium text-gray-500">Username</dt>
        <dd><?php echo htmlentities($pelaporUsername, ENT_QUOTES, 'UTF-8'); ?></dd>
       </div>
       <div>
        <dt class="font-medium text-gray-500">Telepon</dt>
        <dd><?php echo htmlentities($pelaporTelp, ENT_QUOTES, 'UTF-8'); ?></dd>
       </div>
      </dl>
     </div>
     <div class="bg-gray-50 rounded-lg p-5 border border-gray-100">
      <h3 class="text-md font-semibold text-gray-800 mb-3">Foto Bukti</h3>
      <?php if (!empty($row_Raduan['foto'])) { ?>
      <img src="../foto_pengaduan/<?php echo htmlentities($row_Raduan['foto'], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto Bukti" class="rounded-lg border clickable-image object-cover max-h-72 w-full"/>
      <?php } else { ?>
      <p class="text-gray-500 text-sm italic">Tidak ada foto yang dilampirkan.</p>
      <?php } ?>
     </div>
    </div>
    <div class="bg-white border border-gray-100 rounded-lg p-5">
     <h3 class="text-md font-semibold text-gray-800 mb-3">Isi Laporan</h3>
     <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
      <?php echo nl2br(htmlentities($row_Raduan['isi_laporan'], ENT_QUOTES, 'UTF-8')); ?>
     </div>
    </div>
   </section>
   <section class="bg-white rounded-lg shadow p-6 space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
     <div>
      <h2 class="text-lg font-semibold text-gray-800">Tanggapan Petugas</h2>
      <p class="text-sm text-gray-500">Kelola tanggapan untuk pengaduan ini.</p>
     </div>
     <?php if ($adminId > 0) { ?>
     <button id="beriTanggapanBtn" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
      <i class="fas fa-pen text-xs"></i>
      Beri Tanggapan
     </button>
     <?php } ?>
    </div>
    <?php if ($tanggapanAktif) { ?>
    <div class="bg-blue-50 border border-blue-100 rounded-lg p-5">
     <div class="flex items-start justify-between">
      <div>
       <h3 class="text-sm font-semibold text-blue-700">Tanggapan Terbaru</h3>
       <p class="text-xs text-blue-600 mt-1">
        <?php echo htmlentities(isset($tanggapanAktif['nama_petugas']) ? $tanggapanAktif['nama_petugas'] : 'Petugas', ENT_QUOTES, 'UTF-8'); ?>
        &middot;
        <?php echo isset($tanggapanAktif['tgl_tanggapan']) ? htmlentities(date('d F Y', strtotime($tanggapanAktif['tgl_tanggapan'])), ENT_QUOTES, 'UTF-8') : '-'; ?>
       </p>
      </div>
      <div class="flex gap-2">
       <button id="editTanggapanBtn" class="inline-flex items-center gap-1 rounded-md bg-green-600 px-3 py-1 text-xs font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-1">
        <i class="fas fa-edit text-xs"></i>
        Edit
       </button>
       <a href="detail_aduan.php?id_pengaduan=<?php echo urlencode($idPengaduan); ?>&hapus_tanggapan=<?php echo urlencode($tanggapanAktif['id_tanggapan']); ?>" class="inline-flex items-center gap-1 rounded-md bg-red-50 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1" onclick="return confirm('Hapus tanggapan ini? Tindakan tidak dapat dibatalkan.');">
        <i class="fas fa-trash-alt text-xs"></i>
        Hapus
       </a>
      </div>
     </div>
     <p class="text-sm text-blue-900 mt-3 leading-relaxed">
      <?php echo nl2br(htmlentities($tanggapanAktif['tanggapan'], ENT_QUOTES, 'UTF-8')); ?>
     </p>
    </div>
    <?php } else { ?>
    <div class="bg-gray-50 border border-dashed border-gray-200 rounded-lg p-6 text-center text-sm text-gray-500">
     Belum ada tanggapan untuk pengaduan ini.
    </div>
    <?php } ?>
   </section>
   <footer class="text-center text-gray-500 text-sm mt-12" style="padding-bottom: 2rem;">
    &copy; 2025 - Pengaduan Masyarakat | SMK Negeri 5 Kendal
   </footer>
  </main>

  <?php if ($adminId > 0) { ?>
  <div id="beriTanggapanModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
      <h3 class="text-lg font-bold mb-4">Beri Tanggapan</h3>
      <form method="POST" action="">
        <input type="hidden" name="id_pengaduan" value="<?php echo htmlentities($idPengaduan, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="id_petugas" value="<?php echo intval($adminId); ?>">
        <label for="tanggapan_text" class="block text-sm font-medium text-gray-700 mb-2">Tulis tanggapan Anda:</label>
        <textarea id="tanggapan_text" name="tanggapan" rows="5" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
        <div class="mt-4 text-right space-x-2">
          <button type="button" id="cancelBeriTanggapanBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">Batal</button>
          <button type="submit" name="submit_tanggapan" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Kirim Tanggapan</button>
        </div>
      </form>
    </div>
  </div>
  <?php } ?>

  <?php if ($tanggapanAktif) { ?>
  <div id="editTanggapanModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
      <h3 class="text-lg font-bold mb-4">Edit Tanggapan</h3>
      <form method="POST" action="">
        <input type="hidden" name="id_tanggapan" value="<?php echo htmlentities($tanggapanAktif['id_tanggapan'], ENT_QUOTES, 'UTF-8'); ?>">
        <textarea name="tanggapan" rows="5" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required><?php echo htmlentities($tanggapanAktif['tanggapan'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        <div class="mt-4 text-right space-x-2">
          <button type="button" id="cancelEditBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">Batal</button>
          <button type="submit" name="update_tanggapan" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Update</button>
        </div>
      </form>
    </div>
  </div>
  <?php } ?>

  <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <img id="modalImage" src="" alt="Gambar Detail" class="max-w-[90vw] max-h-[90vh] object-contain">
    <button id="closeImageModal" class="absolute top-4 right-4 text-white text-3xl font-bold">&times;</button>
  </div>

  <script src="../asset/script.js"></script>
 </body>
</html>
<?php
if (isset($Radmin)) {
  mysql_free_result($Radmin);
}
if (isset($Raduan)) {
  mysql_free_result($Raduan);
}
?>
