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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if (!isset($statusMessage)) {
  $statusMessage = "";
  $statusType = "info";
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form_tambah")) {
  $fotoFileName = "";
  if (!empty($_FILES['foto']['name']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
    $fotoFileName = basename($_FILES['foto']['name']);
    move_uploaded_file($_FILES['foto']['tmp_name'], '../foto_akun/' . $fotoFileName);
  }

  $insertSQL = sprintf("INSERT INTO petugas (id_petugas, nama_petugas, username, password, telp, `level`, foto) VALUES (%s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['id_petugas'], "int"),
                       GetSQLValueString($_POST['nama_petugas'], "text"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['telp'], "text"),
                       GetSQLValueString($_POST['level'], "text"),
                       GetSQLValueString($fotoFileName, "text"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  header("Location: data_petugas.php?status=created");
  exit;
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form_edit")) {
  $fotoFileName = $_POST['foto_lama'];
  if (!empty($_FILES['foto']['name']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
    $fotoFileName = basename($_FILES['foto']['name']);
    move_uploaded_file($_FILES['foto']['tmp_name'], '../foto_akun/' . $fotoFileName);
    if (!empty($_POST['foto_lama']) && file_exists('../foto_akun/' . $_POST['foto_lama']) && $_POST['foto_lama'] !== $fotoFileName) {
      @unlink('../foto_akun/' . $_POST['foto_lama']);
    }
  }

  $updateSQL = sprintf("UPDATE petugas SET nama_petugas=%s, username=%s, password=%s, telp=%s, `level`=%s, foto=%s WHERE id_petugas=%s",
                       GetSQLValueString($_POST['nama_petugas'], "text"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['telp'], "text"),
                       GetSQLValueString($_POST['level'], "text"),
                       GetSQLValueString($fotoFileName, "text"),
                       GetSQLValueString($_POST['id_petugas'], "int"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($updateSQL, $koneksi) or die(mysql_error());

  header("Location: data_petugas.php?status=updated");
  exit;
}

if (isset($_GET['delete_id'])) {
  $deleteId = intval($_GET['delete_id']);

  mysql_select_db($database_koneksi, $koneksi);
  $queryFoto = sprintf("SELECT foto FROM petugas WHERE id_petugas = %s", GetSQLValueString($deleteId, "int"));
  $resultFoto = mysql_query($queryFoto, $koneksi) or die(mysql_error());
  $rowFoto = mysql_fetch_assoc($resultFoto);
  mysql_free_result($resultFoto);

  if ($rowFoto) {
    if (!empty($rowFoto['foto']) && file_exists('../foto_akun/' . $rowFoto['foto'])) {
      @unlink('../foto_akun/' . $rowFoto['foto']);
    }
    $deleteSQL = sprintf("DELETE FROM petugas WHERE id_petugas = %s", GetSQLValueString($deleteId, "int"));
    mysql_query($deleteSQL, $koneksi) or die(mysql_error());
  }

  header("Location: data_petugas.php?status=deleted");
  exit;
}

$editId = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$isEditing = false;
$petugasToEdit = null;

if ($editId > 0) {
  mysql_select_db($database_koneksi, $koneksi);
  $queryEdit = sprintf("SELECT * FROM petugas WHERE id_petugas = %s", GetSQLValueString($editId, "int"));
  $resultEdit = mysql_query($queryEdit, $koneksi) or die(mysql_error());
  $petugasToEdit = mysql_fetch_assoc($resultEdit);
  mysql_free_result($resultEdit);
  if ($petugasToEdit) {
    $isEditing = true;
  } else {
    header("Location: data_petugas.php?status=not_found");
    exit;
  }
}

$status = isset($_GET['status']) ? $_GET['status'] : "";
if ($status === "created") {
  $statusMessage = "Petugas baru berhasil ditambahkan.";
  $statusType = "success";
} elseif ($status === "updated") {
  $statusMessage = "Data petugas berhasil diperbarui.";
  $statusType = "success";
} elseif ($status === "deleted") {
  $statusMessage = "Data petugas berhasil dihapus.";
  $statusType = "success";
} elseif ($status === "not_found") {
  $statusMessage = "Data petugas tidak ditemukan.";
  $statusType = "error";
}

$bodyClass = ($isEditing && $petugasToEdit) ? 'modal-open' : '';

mysql_select_db($database_koneksi, $koneksi);
$query_Radmin = "SELECT * FROM petugas";
$Radmin = mysql_query($query_Radmin, $koneksi) or die(mysql_error());
$row_Radmin = mysql_fetch_assoc($Radmin);
$totalRows_Radmin = mysql_num_rows($Radmin);

mysql_select_db($database_koneksi, $koneksi);
$query_Rpetugas = "SELECT * FROM petugas";
$Rpetugas = mysql_query($query_Rpetugas, $koneksi) or die(mysql_error());
$row_Rpetugas = mysql_fetch_assoc($Rpetugas);
$totalRows_Rpetugas = mysql_num_rows($Rpetugas);
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
     Aksi
    </div>
    <a href="data_petugas.php" class="flex items-center space-x-2 bg-white text-[#4B88FE] hover:bg-white hover:text-[#4B88FE] rounded-lg px-3 py-2">
     <i class="fas fa-user-tie text-base">
     </i>
     <span>
      Data Petugas
     </span>
    </a>
    <a href="data_masyarakat.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
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
    <span class="font-semibold text-gray-700 text-lg">Selamat Datang, <?php echo $row_Radmin['username']; ?></span>
 </div>
 <div class="flex items-center space-x-6">
  <button aria-label="Notifications" class="relative text-gray-500 hover:text-gray-700 focus:outline-none">
    <i class="far fa-bell text-xl">
    </i>
  </button>
  <button aria-label="User menu" class="relative">
    <img alt="User avatar with orange and red fox logo" class="w-8 h-8 rounded-full clickable-image cursor-pointer" height="32" src="../foto_akun/<?php echo $row_Radmin['foto']; ?>" width="32"/>
    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full">
    </span>
  </button>
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
     Data Petugas
    </span>
   </div>
   <?php if (!empty($statusMessage)) { ?>
   <div class="rounded-lg border px-4 py-3 flex items-center justify-between <?php echo ($statusType === 'success') ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'; ?>">
    <span class="text-sm font-medium">
     <?php echo htmlentities($statusMessage, ENT_QUOTES, 'UTF-8'); ?>
    </span>
    <a href="data_petugas.php" class="text-xs font-semibold underline hover:no-underline">
     Tutup
    </a>
   </div>
   <?php } ?>
   <div class="space-y-6">
    <section class="bg-white rounded-lg shadow p-6">
     <div class="mb-6">
      <h2 class="text-lg font-semibold text-gray-800">
       Tambah Petugas
      </h2>
      <p class="text-sm text-gray-500">
       Lengkapi formulir berikut untuk menambahkan petugas baru.
      </p>
     </div>
     <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" class="space-y-4">
      <div>
       <label for="nama_petugas" class="block text-sm font-medium text-gray-700 mb-1">
        Nama Petugas
       </label>
       <input id="nama_petugas" name="nama_petugas" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      </div>
      <div>
       <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
        Username
       </label>
       <input id="username" name="username" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      </div>
      <div>
       <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
        Password
       </label>
       <input id="password" name="password" type="password" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      </div>
      <div>
       <label for="telp" class="block text-sm font-medium text-gray-700 mb-1">
        Nomor Telepon
       </label>
       <input id="telp" name="telp" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
      </div>
      <div>
       <label for="level" class="block text-sm font-medium text-gray-700 mb-1">
        Level
       </label>
       <select id="level" name="level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
        <option value="admin">
         Admin
        </option>
        <option value="petugas" selected>
         Petugas
        </option>
       </select>
      </div>
      <div>
       <label for="foto" class="block text-sm font-medium text-gray-700 mb-1">
        Foto Profil
       </label>
       <input id="foto" name="foto" type="file" accept="image/*" class="text-sm text-gray-600">
       <p class="mt-1 text-xs text-gray-500">
        Format yang didukung: JPG, PNG. Maksimal 2 MB.
       </p>
      </div>
      <div class="pt-2">
       <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
        <i class="fas fa-user-plus text-xs"></i>
        Simpan Data
       </button>
      </div>
      <input type="hidden" name="id_petugas" value="">
      <input type="hidden" name="MM_insert" value="form_tambah">
     </form>
    </section>
    <section class="bg-white rounded-lg shadow p-6 overflow-x-auto">
     <div class="flex items-center justify-between mb-4">
      <div>
       <h2 class="text-lg font-semibold text-gray-800">
        Daftar Petugas
       </h2>
       <p class="text-sm text-gray-500">
        Kelola akun petugas yang sudah terdaftar.
       </p>
      </div>
     </div>
     <table class="min-w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-gray-50">
       <tr>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">
         ID
        </th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">
         Nama
        </th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">
         Username
        </th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">
         Password
        </th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">
         Telepon
        </th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">
         Level
        </th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">
         Foto
        </th>
        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600 uppercase tracking-wide text-xs">
         Aksi
        </th>
       </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
       <?php if ($totalRows_Rpetugas > 0) { ?>
       <?php do { ?>
       <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-4 py-3 font-medium text-gray-700">
         <?php echo $row_Rpetugas['id_petugas']; ?>
        </td>
        <td class="px-4 py-3 text-gray-700">
         <?php echo htmlentities($row_Rpetugas['nama_petugas'], ENT_QUOTES, 'UTF-8'); ?>
        </td>
        <td class="px-4 py-3 text-gray-700">
         <?php echo htmlentities($row_Rpetugas['username'], ENT_QUOTES, 'UTF-8'); ?>
        </td>
        <td class="px-4 py-3 text-gray-700">
         <?php echo htmlentities($row_Rpetugas['password'], ENT_QUOTES, 'UTF-8'); ?>
        </td>
        <td class="px-4 py-3 text-gray-700">
         <?php echo htmlentities($row_Rpetugas['telp'], ENT_QUOTES, 'UTF-8'); ?>
        </td>
        <td class="px-4 py-3">
         <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-600">
          <?php echo htmlentities(ucfirst($row_Rpetugas['level']), ENT_QUOTES, 'UTF-8'); ?>
         </span>
        </td>
        <td class="px-4 py-3">
         <?php if (!empty($row_Rpetugas['foto'])) { ?>
         <img src="../foto_akun/<?php echo $row_Rpetugas['foto']; ?>" alt="Foto Petugas" class="h-12 w-12 rounded-lg object-cover border border-gray-200 clickable-image cursor-pointer">
         <?php } else { ?>
         <span class="text-xs text-gray-400">
          Tidak ada foto
         </span>
         <?php } ?>
        </td>
        <td class="px-4 py-3">
         <div class="flex items-center gap-2">
          <a href="data_petugas.php?edit_id=<?php echo intval($row_Rpetugas['id_petugas']); ?>" class="inline-flex items-center gap-1 rounded-md border border-blue-200 px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50">
           <i class="fas fa-edit text-xs"></i>
           Edit
          </a>
          <a href="data_petugas.php?delete_id=<?php echo intval($row_Rpetugas['id_petugas']); ?>" class="inline-flex items-center gap-1 rounded-md border border-red-200 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-50" onclick="return confirm('Yakin ingin menghapus data petugas ini?');">
           <i class="fas fa-trash text-xs"></i>
           Hapus
          </a>
         </div>
        </td>
       </tr>
       <?php } while ($row_Rpetugas = mysql_fetch_assoc($Rpetugas)); ?>
       <?php } else { ?>
       <tr>
        <td colspan="8" class="px-4 py-6 text-center text-gray-500">
         Belum ada data petugas.
        </td>
       </tr>
       <?php } ?>
      </tbody>
     </table>
    </section>
   </div>
    <footer class="text-center text-gray-500 text-sm mt-12" style="padding-bottom: 2rem;">
    &copy; 2025 - Pengaduan Masyarakat | SMK Negeri 5 Kendal
   </footer>
  </main>

<?php if ($isEditing && $petugasToEdit) { ?>
  <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-8 sm:py-14">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative w-full max-w-2xl max-h-[90vh] overflow-hidden rounded-2xl bg-white shadow-2xl flex flex-col">
      <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-800">Edit Data Petugas</h3>
          <p class="text-sm text-gray-500">Perbarui informasi petugas dengan data terbaru.</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-600">
            ID <?php echo intval($petugasToEdit['id_petugas']); ?>
          </span>
          <a href="data_petugas.php" class="text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Tutup modal">
            <i class="fas fa-times text-lg"></i>
          </a>
        </div>
      </div>
      <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?edit_id=<?php echo intval($petugasToEdit['id_petugas']); ?>" enctype="multipart/form-data" class="flex flex-1 flex-col gap-4 px-6 py-5 overflow-y-auto">
        <div class="space-y-4">
          <div>
            <label for="nama_petugas_edit" class="block text-sm font-medium text-gray-700 mb-1">Nama Petugas</label>
            <input id="nama_petugas_edit" name="nama_petugas" type="text" value="<?php echo htmlentities($petugasToEdit['nama_petugas'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
          </div>
          <div>
            <label for="username_edit" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input id="username_edit" name="username" type="text" value="<?php echo htmlentities($petugasToEdit['username'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="password_edit" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
              <input id="password_edit" name="password" type="password" value="<?php echo htmlentities($petugasToEdit['password'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div>
              <label for="telp_edit" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
              <input id="telp_edit" name="telp" type="text" value="<?php echo htmlentities($petugasToEdit['telp'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>
          </div>
          <div>
            <label for="level_edit" class="block text-sm font-medium text-gray-700 mb-1">Level</label>
            <select id="level_edit" name="level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" required>
              <option value="admin" <?php if ($petugasToEdit['level'] === "admin") { echo "selected"; } ?>>Admin</option>
              <option value="petugas" <?php if ($petugasToEdit['level'] === "petugas") { echo "selected"; } ?>>Petugas</option>
            </select>
          </div>
          <div>
            <label for="foto_edit" class="block text-sm font-medium text-gray-700 mb-1">Foto Profil</label>
            <div class="flex items-center gap-3">
              <input id="foto_edit" name="foto" type="file" accept="image/*" class="text-sm text-gray-600">
              <?php if (!empty($petugasToEdit['foto'])) { ?>
              <img src="../foto_akun/<?php echo $petugasToEdit['foto']; ?>" alt="Foto petugas saat ini" class="h-16 w-16 rounded-lg object-cover border border-gray-200 clickable-image cursor-pointer">
              <?php } ?>
            </div>
            <p class="mt-1 text-xs text-gray-500">Kosongkan bila tidak ingin mengganti foto.</p>
          </div>
        </div>
        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-3 pt-4 border-t border-gray-200">
          <a href="data_petugas.php" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Batal</a>
          <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
            <i class="fas fa-save text-xs"></i>
            Simpan Perubahan
          </button>
        </div>
        <input type="hidden" name="MM_update" value="form_edit">
        <input type="hidden" name="id_petugas" value="<?php echo intval($petugasToEdit['id_petugas']); ?>">
        <input type="hidden" name="foto_lama" value="<?php echo htmlentities($petugasToEdit['foto'], ENT_QUOTES, 'UTF-8'); ?>">
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

mysql_free_result($Rpetugas);
?>
