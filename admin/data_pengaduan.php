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

$MM_restrictGoTo = "../petugas/dashboard.php";
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
$uploadDirPengaduan = '../foto_pengaduan/';

if (isset($_GET['status'])) {
  switch ($_GET['status']) {
    case 'status_updated':
      $statusMessage = "Status pengaduan berhasil diperbarui.";
      $statusType = "success";
      break;
    case 'status_invalid':
      $statusMessage = "Status pengaduan tidak dikenal.";
      $statusType = "error";
      break;
    case 'deleted':
      $statusMessage = "Data pengaduan berhasil dihapus.";
      $statusType = "success";
      break;
    case 'not_found':
      $statusMessage = "Data pengaduan tidak ditemukan.";
      $statusType = "error";
      break;
    case 'error':
      $statusMessage = "Terjadi kesalahan saat memproses permintaan Anda.";
      $statusType = "error";
      break;
  }
}

if (isset($_GET['update_status']) && isset($_GET['id_pengaduan'])) {
  $newStatus = strtolower(trim($_GET['update_status']));
  $idPengaduanUpdate = intval($_GET['id_pengaduan']);

  if (in_array($newStatus, $validStatusValues, true) && $idPengaduanUpdate > 0) {
    mysql_select_db($database_koneksi, $koneksi);
    $updateStatusSQL = sprintf(
      "UPDATE pengaduan SET status=%s WHERE id_pengaduan=%s",
      GetSQLValueString($newStatus, "text"),
      GetSQLValueString($idPengaduanUpdate, "int")
    );
    mysql_query($updateStatusSQL, $koneksi) or die(mysql_error());
    header("Location: data_pengaduan.php?status=status_updated");
    exit;
  }
  header("Location: data_pengaduan.php?status=status_invalid");
  exit;
}

if (isset($_GET['delete_id'])) {
  $deleteId = intval($_GET['delete_id']);
  mysql_select_db($database_koneksi, $koneksi);
  $queryFoto = sprintf("SELECT foto FROM pengaduan WHERE id_pengaduan = %s", GetSQLValueString($deleteId, "int"));
  $resultFoto = mysql_query($queryFoto, $koneksi) or die(mysql_error());
  $rowFoto = mysql_fetch_assoc($resultFoto);

  if ($rowFoto) {
    $deleteTanggapanSQL = sprintf("DELETE FROM tanggapan WHERE id_pengaduan = %s", GetSQLValueString($deleteId, "int"));
    mysql_query($deleteTanggapanSQL, $koneksi) or die(mysql_error());

    $deleteSQL = sprintf("DELETE FROM pengaduan WHERE id_pengaduan = %s", GetSQLValueString($deleteId, "int"));
    mysql_query($deleteSQL, $koneksi) or die(mysql_error());

    if (!empty($rowFoto['foto']) && file_exists($uploadDirPengaduan . $rowFoto['foto'])) {
      @unlink($uploadDirPengaduan . $rowFoto['foto']);
    }

    mysql_free_result($resultFoto);
    header("Location: data_pengaduan.php?status=deleted");
    exit;
  }

  if ($resultFoto) {
    mysql_free_result($resultFoto);
  }
  header("Location: data_pengaduan.php?status=not_found");
  exit;
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

mysql_select_db($database_koneksi, $koneksi);
$queryCounts = "SELECT
  COUNT(*) AS total,
  SUM(CASE WHEN status='0' THEN 1 ELSE 0 END) AS pending,
  SUM(CASE WHEN status='proses' THEN 1 ELSE 0 END) AS proses,
  SUM(CASE WHEN status='selesai' THEN 1 ELSE 0 END) AS selesai
FROM pengaduan";
$resultCounts = mysql_query($queryCounts, $koneksi) or die(mysql_error());
$countsData = mysql_fetch_assoc($resultCounts);
$totalPengaduan = isset($countsData['total']) ? intval($countsData['total']) : 0;
$totalPending = isset($countsData['pending']) ? intval($countsData['pending']) : 0;
$totalProses = isset($countsData['proses']) ? intval($countsData['proses']) : 0;
$totalSelesai = isset($countsData['selesai']) ? intval($countsData['selesai']) : 0;
mysql_free_result($resultCounts);

$statusFilter = isset($_GET['status_filter']) ? strtolower(trim($_GET['status_filter'])) : 'all';
$allowedStatusFilters = array('all', '0', 'proses', 'selesai');
if (!in_array($statusFilter, $allowedStatusFilters, true)) {
  $statusFilter = 'all';
}

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

$conditions = array();
if ($statusFilter !== 'all') {
  $conditions[] = sprintf("p.status = %s", GetSQLValueString($statusFilter, "text"));
}
if ($searchTerm !== '') {
  $conditions[] = sprintf(
    "(p.nik LIKE %s OR m.nama LIKE %s OR p.isi_laporan LIKE %s)",
    GetSQLValueString('%' . $searchTerm . '%', "text"),
    GetSQLValueString('%' . $searchTerm . '%', "text"),
    GetSQLValueString('%' . $searchTerm . '%', "text")
  );
}
if ($dateFrom !== '' && $dateTo !== '') {
  $conditions[] = sprintf("p.tgl_pengaduan BETWEEN %s AND %s", GetSQLValueString($dateFrom, "date"), GetSQLValueString($dateTo, "date"));
} elseif ($dateFrom !== '') {
  $conditions[] = sprintf("p.tgl_pengaduan >= %s", GetSQLValueString($dateFrom, "date"));
} elseif ($dateTo !== '') {
  $conditions[] = sprintf("p.tgl_pengaduan <= %s", GetSQLValueString($dateTo, "date"));
}

$whereClause = '';
if (!empty($conditions)) {
  $whereClause = ' WHERE ' . implode(' AND ', $conditions);
}

mysql_select_db($database_koneksi, $koneksi);
$query_Rpengaduan = "SELECT p.*, m.nama, m.username AS masyarakat_username, m.telp,
  (SELECT COUNT(*) FROM tanggapan t WHERE t.id_pengaduan = p.id_pengaduan) AS total_tanggapan
FROM pengaduan p
LEFT JOIN masyarakat m ON p.nik = m.nik" . $whereClause . "
ORDER BY p.tgl_pengaduan DESC, p.id_pengaduan DESC";
$Rpengaduan = mysql_query($query_Rpengaduan, $koneksi) or die(mysql_error());
$row_Rpengaduan = mysql_fetch_assoc($Rpengaduan);
$totalRows_Rpengaduan = mysql_num_rows($Rpengaduan);

$statusLabels = array(
  '0' => 'Belum Diproses',
  'proses' => 'Proses',
  'selesai' => 'Selesai'
);
$statusBadgeClasses = array(
  '0' => 'bg-red-100 text-red-700 border border-red-200',
  'proses' => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
  'selesai' => 'bg-green-100 text-green-700 border border-green-200'
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
  <title>Pengaduan Masyarakat</title>
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
    <span class="text-gray-400">Home</span>
    <span>/</span>
    <span class="font-semibold text-gray-900">Data Pengaduan</span>
   </div>
   <header class="bg-white rounded-lg shadow px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
     <h1 class="text-2xl font-bold text-gray-800">Kelola Data Pengaduan</h1>
     <p class="text-gray-500 text-sm mt-1">Pantau dan tindak lanjuti seluruh pengaduan masyarakat secara terpusat.</p>
    </div>
    <div class="flex items-center gap-3">
     <div class="px-3 py-2 rounded-lg bg-blue-100 text-blue-600 text-sm font-semibold">
      Total: <?php echo intval($totalPengaduan); ?> pengaduan
     </div>
     <button onClick="window.open('../petugas/cetak_laporan.php', '_blank');" class="inline-flex items-center gap-2 rounded-lg bg-[#4B88FE] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
      <i class="fas fa-file-pdf text-xs"></i>
      Cetak Laporan
     </button>
    </div>
   </header>
   <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white rounded-lg shadow px-5 py-4">
     <div class="text-sm text-gray-500">Belum Diproses</div>
     <div class="mt-2 text-2xl font-semibold text-red-600"><?php echo intval($totalPending); ?></div>
    </div>
    <div class="bg-white rounded-lg shadow px-5 py-4">
     <div class="text-sm text-gray-500">Dalam Proses</div>
     <div class="mt-2 text-2xl font-semibold text-yellow-500"><?php echo intval($totalProses); ?></div>
    </div>
    <div class="bg-white rounded-lg shadow px-5 py-4">
     <div class="text-sm text-gray-500">Selesai</div>
     <div class="mt-2 text-2xl font-semibold text-green-600"><?php echo intval($totalSelesai); ?></div>
    </div>
   </div>
   <?php if (!empty($statusMessage)) {
     $statusClass = isset($statusClassMap[$statusType]) ? $statusClassMap[$statusType] : $statusClassMap['info'];
     $statusIcon = isset($statusIconMap[$statusType]) ? $statusIconMap[$statusType] : $statusIconMap['info'];
   ?>
   <div class="<?php echo $statusClass; ?> px-4 py-3 rounded-lg flex items-start gap-3">
    <i class="<?php echo $statusIcon; ?> mt-1"></i>
    <div>
     <p class="font-semibold"><?php echo htmlentities($statusMessage, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
   </div>
   <?php } ?>
   <section class="bg-white rounded-lg shadow p-6 space-y-6">
    <form method="get" action="data_pengaduan.php" class="grid grid-cols-1	md:grid-cols-2 xl:grid-cols-4 gap-4 items-end">
     <div>
      <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
      <input id="search" name="search" type="text" value="<?php echo htmlentities($searchTerm, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cari NIK, nama, atau isi laporan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"/>
     </div>
     <div>
      <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
      <select id="status_filter" name="status_filter" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
       <option value="all" <?php if ($statusFilter === 'all') { echo "selected"; } ?>>Semua Status</option>
       <option value="0" <?php if ($statusFilter === '0') { echo "selected"; } ?>>Belum Diproses</option>
       <option value="proses" <?php if ($statusFilter === 'proses') { echo "selected"; } ?>>Proses</option>
       <option value="selesai" <?php if ($statusFilter === 'selesai') { echo "selected"; } ?>>Selesai</option>
      </select>
     </div>
     <div>
      <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
      <input id="date_from" name="date_from" type="date" value="<?php echo htmlentities($dateFrom, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"/>
     </div>
     <div>
      <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
      <input id="date_to" name="date_to" type="date" value="<?php echo htmlentities($dateTo, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"/>
     </div>
     <div class="md:col-span-2 xl:col-span-4 flex flex-wrap gap-2 justify-end">
      <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
       <i class="fas fa-search text-xs"></i>
       Terapkan
      </button>
      <a href="data_pengaduan.php" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
       <i class="fas fa-undo text-xs"></i>
       Reset
      </a>
     </div>
    </form>
    <div class="overflow-x-auto">
     <table class="w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-gray-50">
       <tr>
        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">ID</th>
        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Tanggal</th>
        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Pelapor</th>
        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Isi Laporan</th>
        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Status</th>
        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Tanggapan</th>
        <th class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Aksi</th>
       </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
       <?php if ($totalRows_Rpengaduan > 0) { ?>
       <?php do { ?>
       <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-3 font-medium text-gray-700">#<?php echo htmlentities($row_Rpengaduan['id_pengaduan'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="px-4 py-3 text-gray-700">
         <?php
           $tglPengaduan = isset($row_Rpengaduan['tgl_pengaduan']) ? $row_Rpengaduan['tgl_pengaduan'] : '';
           echo $tglPengaduan ? htmlentities(date('d M Y', strtotime($tglPengaduan)), ENT_QUOTES, 'UTF-8') : '-';
         ?>
        </td>
        <td class="px-4 py-3 text-gray-700">
         <div class="flex flex-col">
          <span class="font-semibold"><?php echo htmlentities(isset($row_Rpengaduan['nama']) ? $row_Rpengaduan['nama'] : '-', ENT_QUOTES, 'UTF-8'); ?></span>
          <span class="text-xs text-gray-500">NIK: <?php echo htmlentities($row_Rpengaduan['nik'], ENT_QUOTES, 'UTF-8'); ?></span>
         </div>
        </td>
        <td class="px-4 py-3 text-gray-700">
         <?php
           $laporan = isset($row_Rpengaduan['isi_laporan']) ? $row_Rpengaduan['isi_laporan'] : '';
           $laporanPreview = strlen($laporan) > 90 ? substr($laporan, 0, 90) . '…' : $laporan;
           echo htmlentities($laporanPreview, ENT_QUOTES, 'UTF-8');
         ?>
        </td>
        <td class="px-4 py-3 text-gray-700">
         <?php
           $statusKey = isset($row_Rpengaduan['status']) ? strtolower($row_Rpengaduan['status']) : '';
           $statusLabel = isset($statusLabels[$statusKey]) ? $statusLabels[$statusKey] : ucfirst($statusKey);
           $badgeClass = isset($statusBadgeClasses[$statusKey]) ? $statusBadgeClasses[$statusKey] : 'bg-gray-100 text-gray-700 border border-gray-200';
         ?>
         <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $badgeClass; ?>">
          <?php echo htmlentities($statusLabel, ENT_QUOTES, 'UTF-8'); ?>
         </span>
        </td>
        <td class="px-4 py-3 text-gray-700">
         <?php
           $totalTanggapan = isset($row_Rpengaduan['total_tanggapan']) ? intval($row_Rpengaduan['total_tanggapan']) : 0;
           echo $totalTanggapan . ' tanggapan';
         ?>
        </td>
        <td class="px-4 py-3 text-gray-700">
         <div class="flex flex-wrap items-center gap-2">
          <a href="detail_aduan.php?id_pengaduan=<?php echo urlencode($row_Rpengaduan['id_pengaduan']); ?>" class="inline-flex items-center gap-1 rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1">
           <i class="fas fa-eye text-xs"></i>
           Detail
          </a>
          <form method="get" action="data_pengaduan.php" class="inline-flex">
           <input type="hidden" name="id_pengaduan" value="<?php echo htmlentities($row_Rpengaduan['id_pengaduan'], ENT_QUOTES, 'UTF-8'); ?>"/>
           <select name="update_status" class="rounded-md border border-gray-300 text-xs px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-400" onchange="this.form.submit()">
            <option value="">Ubah Status</option>
            <option value="0">Belum Diproses</option>
            <option value="proses">Proses</option>
            <option value="selesai">Selesai</option>
           </select>
          </form>
          <a href="data_pengaduan.php?delete_id=<?php echo urlencode($row_Rpengaduan['id_pengaduan']); ?>" class="inline-flex items-center gap-1 rounded-md bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1" onclick="return confirm('Yakin ingin menghapus pengaduan ini? Tindakan ini tidak dapat dibatalkan.');">
           <i class="fas fa-trash-alt text-xs"></i>
           Hapus
          </a>
         </div>
        </td>
       </tr>
       <?php } while ($row_Rpengaduan = mysql_fetch_assoc($Rpengaduan)); ?>
       <?php } else { ?>
       <tr>
        <td colspan="7" class="px-4 py-6 text-center text-gray-500 text-sm">
         <?php if ($searchTerm !== '' || $statusFilter !== 'all' || $dateFrom !== '' || $dateTo !== '') { ?>
         Tidak ada pengaduan yang cocok dengan filter Anda.
         <?php } else { ?>
         Belum ada data pengaduan yang tersimpan.
         <?php } ?>
        </td>
       </tr>
       <?php } ?>
      </tbody>
     </table>
    </div>
   </section>
   <footer class="text-center text-gray-500 text-sm mt-12" style="padding-bottom: 2rem;">
    &copy; 2025 - Pengaduan Masyarakat | SMK Negeri 5 Kendal
   </footer>
  </main>
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
if (isset($Rpengaduan)) {
  mysql_free_result($Rpengaduan);
}
?>
