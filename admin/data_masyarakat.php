<?php require_once('../Connections/koneksi.php'); ?>
<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
}

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
  //to fully log out a visitor we need to clear the session varialbles
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

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
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
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
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

if (!function_exists('sanitizeFileName')) {
function sanitizeFileName($filename) {
  $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename);
  return trim($filename, '_');
}
}

if (!function_exists('generateUploadedFileName')) {
function generateUploadedFileName($originalName) {
  $extension = pathinfo($originalName, PATHINFO_EXTENSION);
  $extension = $extension ? '.' . strtolower($extension) : '';
  $basename = pathinfo($originalName, PATHINFO_FILENAME);
  $basename = sanitizeFileName($basename);
  if ($basename === '') {
    $basename = 'foto';
  }
  $timestamp = date('YmdHis');
  $random = mt_rand(1000, 9999);
  $basename = substr($basename, 0, 40);
  return $timestamp . '_' . $random . '_' . $basename . $extension;
}
}

$uploadDir = '../foto_akun/';

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$statusMessage = "";
$statusType = "info";

if (isset($_GET['status'])) {
  switch ($_GET['status']) {
    case "created":
      $statusMessage = "Data masyarakat berhasil ditambahkan.";
      $statusType = "success";
      break;
    case "updated":
      $statusMessage = "Data masyarakat berhasil diperbarui.";
      $statusType = "success";
      break;
    case "deleted":
      $statusMessage = "Data masyarakat berhasil dihapus.";
      $statusType = "success";
      break;
    case "exists":
      $statusMessage = "NIK atau username sudah terdaftar. Silakan gunakan data lain.";
      $statusType = "warning";
      break;
    case "not_found":
      $statusMessage = "Data masyarakat tidak ditemukan.";
      $statusType = "error";
      break;
    case "error":
      $statusMessage = "Terjadi kesalahan. Silakan coba lagi.";
      $statusType = "error";
      break;
  }
}

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : "";

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form_tambah")) {
  $nikBaru = isset($_POST['nik']) ? trim($_POST['nik']) : "";
  $namaBaru = isset($_POST['nama']) ? trim($_POST['nama']) : "";
  $usernameBaru = isset($_POST['username']) ? trim($_POST['username']) : "";
  $passwordBaru = isset($_POST['password']) ? trim($_POST['password']) : "";
  $telpBaru = isset($_POST['telp']) ? trim($_POST['telp']) : "";

  mysql_select_db($database_koneksi, $koneksi);
  $queryCheck = sprintf("SELECT nik FROM masyarakat WHERE nik = %s OR username = %s LIMIT 1",
                        GetSQLValueString($nikBaru, "text"),
                        GetSQLValueString($usernameBaru, "text"));
  $resultCheck = mysql_query($queryCheck, $koneksi) or die(mysql_error());
  if (mysql_num_rows($resultCheck) > 0) {
    mysql_free_result($resultCheck);
    header("Location: data_masyarakat.php?status=exists");
    exit;
  }
  mysql_free_result($resultCheck);

  $fotoFileName = "";
  if (!empty($_FILES['foto']['name']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
    $fotoFileName = generateUploadedFileName($_FILES['foto']['name']);
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fotoFileName)) {
      $fotoFileName = "";
    }
  }

  $insertSQL = sprintf("INSERT INTO masyarakat (nik, nama, username, password, telp, foto) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($nikBaru, "text"),
                       GetSQLValueString($namaBaru, "text"),
                       GetSQLValueString($usernameBaru, "text"),
                       GetSQLValueString($passwordBaru, "text"),
                       GetSQLValueString($telpBaru, "text"),
                       GetSQLValueString($fotoFileName, "text"));

  mysql_select_db($database_koneksi, $koneksi);
  mysql_query($insertSQL, $koneksi) or die(mysql_error());

  header("Location: data_masyarakat.php?status=created");
  exit;
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form_edit")) {
  $nikLama = isset($_POST['nik_lama']) ? trim($_POST['nik_lama']) : "";
  $nikBaru = isset($_POST['nik']) ? trim($_POST['nik']) : "";
  $namaBaru = isset($_POST['nama']) ? trim($_POST['nama']) : "";
  $usernameBaru = isset($_POST['username']) ? trim($_POST['username']) : "";
  $passwordBaru = isset($_POST['password']) ? trim($_POST['password']) : "";
  $telpBaru = isset($_POST['telp']) ? trim($_POST['telp']) : "";
  $fotoLama = isset($_POST['foto_lama']) ? $_POST['foto_lama'] : "";

  mysql_select_db($database_koneksi, $koneksi);
  $queryCheckUpdate = sprintf("SELECT nik FROM masyarakat WHERE (nik = %s OR username = %s) AND nik <> %s LIMIT 1",
                              GetSQLValueString($nikBaru, "text"),
                              GetSQLValueString($usernameBaru, "text"),
                              GetSQLValueString($nikLama, "text"));
  $resultCheckUpdate = mysql_query($queryCheckUpdate, $koneksi) or die(mysql_error());
  if (mysql_num_rows($resultCheckUpdate) > 0) {
    mysql_free_result($resultCheckUpdate);
    header("Location: data_masyarakat.php?status=exists&edit_nik=" . urlencode($nikLama));
    exit;
  }
  mysql_free_result($resultCheckUpdate);

  $fotoFileName = $fotoLama;
  if (!empty($_FILES['foto']['name']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
    $fotoBaru = generateUploadedFileName($_FILES['foto']['name']);
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fotoBaru)) {
      $fotoFileName = $fotoBaru;
      if (!empty($fotoLama) && file_exists($uploadDir . $fotoLama) && $fotoLama !== $fotoFileName) {
        @unlink($uploadDir . $fotoLama);
      }
    }
  }

  $updateSQL = sprintf("UPDATE masyarakat SET nik=%s, nama=%s, username=%s, password=%s, telp=%s, foto=%s WHERE nik=%s",
                       GetSQLValueString($nikBaru, "text"),
                       GetSQLValueString($namaBaru, "text"),
                       GetSQLValueString($usernameBaru, "text"),
                       GetSQLValueString($passwordBaru, "text"),
                       GetSQLValueString($telpBaru, "text"),
                       GetSQLValueString($fotoFileName, "text"),
                       GetSQLValueString($nikLama, "text"));

  mysql_select_db($database_koneksi, $koneksi);
  mysql_query($updateSQL, $koneksi) or die(mysql_error());

  header("Location: data_masyarakat.php?status=updated");
  exit;
}

if (isset($_GET['delete_nik'])) {
  $deleteNik = trim($_GET['delete_nik']);

  mysql_select_db($database_koneksi, $koneksi);
  $queryFoto = sprintf("SELECT foto FROM masyarakat WHERE nik = %s", GetSQLValueString($deleteNik, "text"));
  $resultFoto = mysql_query($queryFoto, $koneksi) or die(mysql_error());
  $rowFoto = mysql_fetch_assoc($resultFoto);

  if ($rowFoto) {
    if (!empty($rowFoto['foto']) && file_exists($uploadDir . $rowFoto['foto'])) {
      @unlink($uploadDir . $rowFoto['foto']);
    }
    $deleteSQL = sprintf("DELETE FROM masyarakat WHERE nik = %s", GetSQLValueString($deleteNik, "text"));
    mysql_query($deleteSQL, $koneksi) or die(mysql_error());
    mysql_free_result($resultFoto);
    header("Location: data_masyarakat.php?status=deleted");
    exit;
  } else {
    if ($resultFoto) {
      mysql_free_result($resultFoto);
    }
    header("Location: data_masyarakat.php?status=not_found");
    exit;
  }
}

$editNik = isset($_GET['edit_nik']) ? trim($_GET['edit_nik']) : "";
$isEditing = false;
$masyarakatToEdit = null;

if ($editNik !== "") {
  mysql_select_db($database_koneksi, $koneksi);
  $queryEdit = sprintf("SELECT * FROM masyarakat WHERE nik = %s", GetSQLValueString($editNik, "text"));
  $resultEdit = mysql_query($queryEdit, $koneksi) or die(mysql_error());
  $masyarakatToEdit = mysql_fetch_assoc($resultEdit);
  mysql_free_result($resultEdit);
  if ($masyarakatToEdit) {
    $isEditing = true;
  } else {
    header("Location: data_masyarakat.php?status=not_found");
    exit;
  }
}

$bodyClass = ($isEditing && $masyarakatToEdit) ? 'modal-open' : '';

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
$whereClause = "";
if ($searchTerm !== "") {
  $searchLike = "%" . $searchTerm . "%";
  $whereClause = sprintf(" WHERE nik LIKE %s OR nama LIKE %s OR username LIKE %s OR telp LIKE %s",
                         GetSQLValueString($searchLike, "text"),
                         GetSQLValueString($searchLike, "text"),
                         GetSQLValueString($searchLike, "text"),
                         GetSQLValueString($searchLike, "text"));
}
$query_Rmasyarakat = "SELECT * FROM masyarakat" . $whereClause . " ORDER BY nama ASC";
$Rmasyarakat = mysql_query($query_Rmasyarakat, $koneksi) or die(mysql_error());
$row_Rmasyarakat = mysql_fetch_assoc($Rmasyarakat);
$totalRows_Rmasyarakat = mysql_num_rows($Rmasyarakat);
?>
<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <link type="image/x-icon" href="../asset/logo COLORFUL.png" rel="icon"/>
  <link type="image/x-icon" href="../asset/logo COLORFUL.png" rel="shortcut icon"/>
  <meta content="Layanan Pengaduan Masyarakat Online SMK Negeri 5 Kendal untuk memudahkan pelaporan dan penanganan aduan masyarakat secara efisien." name="description"/>
  <title>
    Pengaduan Masyarakat
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;display=swap" rel="stylesheet"/>
  <style>
   body {
      font-family: 'Inter', sans-serif;
    }
   .modal-open {
      overflow: hidden;
    }
  </style>
 </head>
 <body class="bg-[#f5f7fa] min-h-screen flex <?php echo $bodyClass; ?>">
  <!-- Sidebar -->
  <aside class="w-64 bg-white border-r border-gray-200 flex flex-col px-6 py-8 select-none fixed inset-y-0 left-0 z-30" style="background-color: #4B88FE; box-shadow: 0px 0 5px rgba(0, 0, 0, 0.5);">
   <div class="flex items-center space-x-3 mb-10">
    <img alt="GitLab logo icon in orange and red colors" class="w-10 h-10" src="../asset/logo COLORFUL.png" style="width:50px; height:50px;" />
    <span class="font-semibold text-xl text-white">
     Pengaduan Masyarakat
    </span>
   </div>
   <nav class="flex flex-col space-y-6 text-white text-sm font-normal">
    <a href="dashboard.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-home text-base">
     </i>
     <span>
      Dashboard
     </span>
    </a>
    <div class="uppercase text-xs font-semibold text-gray-200 tracking-wider mt-6" style="opacity: 0.8;">
     Menu
    </div>
    <a href="data_petugas.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-user-tie text-base">
     </i>
     <span>
      Data Petugas
     </span>
    </a>
    <a href="data_masyarakat.php" class="flex items-center space-x-2 bg-white text-[#4B88FE] hover:bg-white hover:text-[#4B88FE] rounded-lg px-3 py-2">
     <i class="fas fa-users text-base">
     </i>
     <span>
      Data Masyarakat
     </span>
    </a>
    <a href="data_pengaduan.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-file-alt text-base">
     </i>
     <span>
      Data Pengaduan
     </span>
    </a>
    <div class="uppercase text-xs font-semibold text-gray-200 tracking-wider mt-6" style="opacity: 0.8;">
     Logout
    </div>
    <a href="<?php echo $logoutAction ?>" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-sign-out-alt text-base">
     </i>
     <span>
      Logout
     </span>
    </a>
   </nav>
  </aside>
  <!-- Main content -->
  <main class="flex-1 p-6 md:p-8 space-y-6" style="max-width: calc(100vw - 16rem); margin-left: 16rem;">
<!-- Top bar -->
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
    <img alt="Foto Admin" class="w-8 h-8 rounded-full clickable-image cursor-pointer object-cover" height="32" src="../foto_akun/<?php echo htmlentities($adminFoto, ENT_QUOTES, 'UTF-8'); ?>" width="32"/>
    <?php } else { ?>
    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-semibold">
      <?php echo htmlentities($adminInitial, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php } ?>
    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
  </div>
 </div>
</div>
   <!-- Breadcrumb -->
   <div class="text-gray-600 text-sm">
    <span class="text-gray-400">
     Home
    </span>
    <span>
     /
    </span>
    <span class="font-semibold text-gray-900">
     Data Masyarakat
    </span>
   </div>
   <header class="bg-white rounded-lg shadow px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
     <h1 class="text-2xl font-bold text-gray-800">Kelola Data Masyarakat</h1>
     <p class="text-gray-500 text-sm mt-1">Tambah, ubah, dan awasi akun masyarakat secara terpusat.</p>
    </div>
    <div class="flex items-center gap-3">
     <div class="px-3 py-2 rounded-lg bg-blue-100 text-blue-600 text-sm font-semibold">
      Total: <?php echo intval($totalRows_Rmasyarakat); ?> akun
     </div>
    </div>
   </header>
   <?php if (!empty($statusMessage)) { 
     $statusClass = isset($statusClassMap[$statusType]) ? $statusClassMap[$statusType] : $statusClassMap['info'];
     $statusIcon = isset($statusIconMap[$statusType]) ? $statusIconMap[$statusType] : $statusIconMap['info'];
   ?>
   <div class="<?php echo $statusClass; ?> px-4 py-3 rounded-lg flex items-start gap-3">
    <i class="<?php echo $statusIcon; ?> mt-1"></i>
    <div>
     <p class="font-semibold"><?php echo htmlentities($statusMessage, ENT_QUOTES, 'UTF-8'); ?></p>
     <?php if ($statusType === 'warning' || $statusType === 'error') { ?>
     <p class="text-xs text-gray-600 mt-1">Periksa kembali data yang Anda masukkan sebelum melanjutkan.</p>
     <?php } ?>
    </div>
   </div>
   <?php } ?>
   <section class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
     <div>
      <h2 class="text-lg font-semibold text-gray-800">Tambah Akun Masyarakat</h2>
      <p class="text-sm text-gray-500">Isi formulir berikut untuk menambahkan akun masyarakat baru.</p>
     </div>
     <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" class="space-y-4">
      <div>
       <label for="nik" class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
       <input id="nik" name="nik" type="text" maxlength="20" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      </div>
      <div>
       <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
       <input id="nama" name="nama" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      </div>
      <div>
       <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
       <input id="username" name="username" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      </div>
      <div>
       <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
       <input id="password" name="password" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      </div>
      <div>
       <label for="telp" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
       <input id="telp" name="telp" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      </div>
      <div>
       <label for="foto" class="block text-sm font-medium text-gray-700 mb-1">Foto Profil</label>
       <input id="foto" name="foto" type="file" accept="image/*" class="text-sm text-gray-600">
       <p class="mt-1 text-xs text-gray-500">Format yang didukung: JPG, PNG. Maksimal 2 MB.</p>
      </div>
      <div class="pt-2">
       <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
        <i class="fas fa-user-plus text-xs"></i>
        Simpan Data
       </button>
      </div>
      <input type="hidden" name="MM_insert" value="form_tambah">
     </form>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
     <div class="overflow-x-auto">
     <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
      <div>
       <h2 class="text-lg font-semibold text-gray-800">Daftar Akun Masyarakat</h2>
       <?php if ($searchTerm !== "") { ?>
       <p class="text-sm text-gray-500">Hasil pencarian untuk: <span class="font-medium text-gray-700">"<?php echo htmlentities($searchTerm, ENT_QUOTES, 'UTF-8'); ?>"</span></p>
       <?php } else { ?>
       <p class="text-sm text-gray-500">Menampilkan seluruh akun masyarakat yang terdaftar.</p>
       <?php } ?>
      </div>
      <form method="get" action="data_masyarakat.php" class="w-full sm:w-auto">
       <label for="search" class="sr-only">Cari data masyarakat</label>
       <div class="relative">
        <input id="search" name="search" type="text" value="<?php echo htmlentities($searchTerm, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cari NIK, nama, username, atau telepon" class="w-full sm:w-72 rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"/>
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-search text-sm"></i></span>
       </div>
      </form>
     </div>
     <table class="w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-gray-50">
       <tr>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">NIK</th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Nama</th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Username</th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Password</th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Telepon</th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Foto</th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">Aksi</th>
       </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
       <?php if ($totalRows_Rmasyarakat > 0) { ?>
       <?php do { ?>
       <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-3 font-medium text-gray-700"><?php echo htmlentities($row_Rmasyarakat['nik'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="px-4 py-3 text-gray-700"><?php echo htmlentities($row_Rmasyarakat['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="px-4 py-3 text-gray-700"><?php echo htmlentities($row_Rmasyarakat['username'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="px-4 py-3 text-gray-700"><?php echo htmlentities($row_Rmasyarakat['password'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="px-4 py-3 text-gray-700"><?php echo htmlentities($row_Rmasyarakat['telp'], ENT_QUOTES, 'UTF-8'); ?></td>
        <td class="px-4 py-3 text-gray-700">
         <?php if (!empty($row_Rmasyarakat['foto'])) { ?>
         <img src="../foto_akun/<?php echo htmlentities($row_Rmasyarakat['foto'], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto Profil" class="w-10 h-10 rounded-full object-cover border clickable-image cursor-pointer"/>
         <?php } else { ?>
         <span class="text-xs text-gray-400 italic">Belum ada</span>
         <?php } ?>
        </td>
        <td class="px-4 py-3">
         <div class="flex items-center gap-2">
          <a href="data_masyarakat.php?edit_nik=<?php echo urlencode($row_Rmasyarakat['nik']); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 text-xs font-semibold">
           <i class="fas fa-edit text-xs"></i>
           Edit
          </a>
          <a href="data_masyarakat.php?delete_nik=<?php echo urlencode($row_Rmasyarakat['nik']); ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-red-50 text-red-600 hover:bg-red-100 text-xs font-semibold" onclick="return confirm('Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.');">
           <i class="fas fa-trash-alt text-xs"></i>
           Hapus
          </a>
         </div>
        </td>
       </tr>
       <?php } while ($row_Rmasyarakat = mysql_fetch_assoc($Rmasyarakat)); ?>
       <?php } else { ?>
       <tr>
        <td colspan="7" class="px-4 py-6 text-center text-gray-500 text-sm">
         <?php if ($searchTerm !== "") { ?>
         Tidak ditemukan data yang cocok dengan pencarian Anda.
         <?php } else { ?>
         Belum ada data masyarakat yang terdaftar.
         <?php } ?>
        </td>
       </tr>
       <?php } ?>
      </tbody>
     </table>
     </div>
    </div>
   </section>
    <footer class="text-center text-gray-500 text-sm mt-12" style="padding-bottom: 2rem;">
    &copy; 2025 - Pengaduan Masyarakat | SMK Negeri 5 Kendal
   </footer>
  </main>

<?php if ($isEditing && $masyarakatToEdit) { ?>
  <div class="fixed inset-0 bg-black bg-opacity-60 z-40 flex items-center justify-center px-4">
   <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col">
    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
     <div>
      <h3 class="text-lg font-semibold text-gray-800">Edit Data Masyarakat</h3>
      <p class="text-sm text-gray-500">Perbarui informasi akun masyarakat dengan data terbaru.</p>
     </div>
     <div class="flex items-center gap-2">
      <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-600">
       NIK <?php echo htmlentities($masyarakatToEdit['nik'], ENT_QUOTES, 'UTF-8'); ?>
      </span>
      <a href="data_masyarakat.php" class="text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Tutup modal">
       <i class="fas fa-times text-lg"></i>
      </a>
     </div>
    </div>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?edit_nik=<?php echo urlencode($masyarakatToEdit['nik']); ?>" enctype="multipart/form-data" class="flex flex-1 flex-col gap-4 px-6 py-5 overflow-y-auto">
     <div class="space-y-4">
      <div>
       <label for="nik_edit" class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
       <input id="nik_edit" name="nik" type="text" value="<?php echo htmlentities($masyarakatToEdit['nik'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      </div>
      <div>
       <label for="nama_edit" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
       <input id="nama_edit" name="nama" type="text" value="<?php echo htmlentities($masyarakatToEdit['nama'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      </div>
      <div>
       <label for="username_edit" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
       <input id="username_edit" name="username" type="text" value="<?php echo htmlentities($masyarakatToEdit['username'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
       <div>
        <label for="password_edit" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input id="password_edit" name="password" type="text" value="<?php echo htmlentities($masyarakatToEdit['password'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
       </div>
       <div>
        <label for="telp_edit" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
        <input id="telp_edit" name="telp" type="text" value="<?php echo htmlentities($masyarakatToEdit['telp'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
       </div>
      </div>
      <div>
       <label for="foto_edit" class="block text-sm font-medium text-gray-700 mb-1">Foto Profil</label>
       <div class="flex items-center gap-3">
        <input id="foto_edit" name="foto" type="file" accept="image/*" class="text-sm text-gray-600">
        <?php if (!empty($masyarakatToEdit['foto'])) { ?>
        <img src="../foto_akun/<?php echo htmlentities($masyarakatToEdit['foto'], ENT_QUOTES, 'UTF-8'); ?>" alt="Foto saat ini" class="h-16 w-16 rounded-lg object-cover border border-gray-200 clickable-image">
        <?php } ?>
       </div>
       <p class="mt-1 text-xs text-gray-500">Kosongkan bila tidak ingin mengganti foto.</p>
      </div>
     </div>
     <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-3 pt-4 border-t border-gray-200">
      <a href="data_masyarakat.php" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Batal</a>
      <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
       <i class="fas fa-save text-xs"></i>
       Simpan Perubahan
      </button>
     </div>
     <input type="hidden" name="MM_update" value="form_edit">
     <input type="hidden" name="nik_lama" value="<?php echo htmlentities($masyarakatToEdit['nik'], ENT_QUOTES, 'UTF-8'); ?>">
     <input type="hidden" name="foto_lama" value="<?php echo htmlentities($masyarakatToEdit['foto'], ENT_QUOTES, 'UTF-8'); ?>">
    </form>
   </div>
  </div>
<?php } ?>

  <!-- Modal Universal untuk Gambar -->
  <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <img id="modalImage" src="" alt="Gambar Detail" class="max-w-[90vw] max-h-[90vh] object-contain">
    <button id="closeImageModal" class="absolute top-4 right-4 text-white text-3xl font-bold">&times;</button>
  </div>

  <script src="../asset/script.js"></script>
 </body>
</html>
<?php
mysql_free_result($Radmin);

mysql_free_result($Rmasyarakat);
?>
