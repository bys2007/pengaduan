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
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

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
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "../login_masyarakat.php";
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  // Ambil nama file jika ada upload
  $nama_file = '';
  if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $nama_file = basename($_FILES['foto']['name']);
    move_uploaded_file($_FILES['foto']['tmp_name'], '../foto_pengaduan/' . $nama_file);
  }

  $insertSQL = sprintf("INSERT INTO pengaduan (id_pengaduan, tgl_pengaduan, nik, isi_laporan, foto, status) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['id_pengaduan'], "int"),
                       GetSQLValueString($_POST['tgl_pengaduan'], "date"),
                       GetSQLValueString($_POST['nik'], "text"),
                       GetSQLValueString($_POST['isi_laporan'], "text"),
                       GetSQLValueString($nama_file, "text"),
                       GetSQLValueString($_POST['status'], "text"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  $insertGoTo = "pengaduan.php?success=1&nik=" . urlencode($_POST['nik']);
  header(sprintf("Location: %s", $insertGoTo));
}

mysql_select_db($database_koneksi, $koneksi);
$query_Rmasyarakat = "SELECT * FROM masyarakat";
$Rmasyarakat = mysql_query($query_Rmasyarakat, $koneksi) or die(mysql_error());
$row_Rmasyarakat = mysql_fetch_assoc($Rmasyarakat);

$colname_Rpengaduan = "-1";
if (isset($_GET['nik'])) {
  $colname_Rpengaduan = $_GET['nik'];
}
mysql_select_db($database_koneksi, $koneksi);
$query_Rpengaduan = sprintf("SELECT * FROM pengaduan WHERE nik = %s", GetSQLValueString($colname_Rpengaduan, "text"));
$Rpengaduan = mysql_query($query_Rpengaduan, $koneksi) or die(mysql_error());
$row_Rpengaduan = mysql_fetch_assoc($Rpengaduan);
$totalRows_Rpengaduan = mysql_num_rows($Rpengaduan);

$colname_Rmasyarakat = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_Rmasyarakat = $_SESSION['MM_Username'];
}
mysql_select_db($database_koneksi, $koneksi);
$query_Rmasyarakat = sprintf("SELECT * FROM masyarakat WHERE username = %s", GetSQLValueString($colname_Rmasyarakat, "text"));
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
  </style>
 </head>
 <body class="bg-[#f5f7fa] min-h-screen flex">
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
    <a href="pengaduan.php?nik=<?php echo $row_Rmasyarakat['nik']; ?>" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-edit text-base">
     </i>
     <span>
      Menulis Laporan Pengaduan
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
    <span class="font-semibold text-gray-700 text-lg">Selamat Datang, <?php echo $row_Rmasyarakat['username']; ?></span>
 </div>
 <div class="flex items-center space-x-6">
  <button aria-label="Notifications" class="relative text-gray-500 hover:text-gray-700 focus:outline-none">
    <i class="far fa-bell text-xl">
    </i>
    <!-- <span class="absolute -top-1 -right-1 bg-[#ff4d4f] text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
     1
    </span> -->
  </button>
  <button aria-label="User menu" class="relative">
    <img alt="User avatar" class="w-8 h-8 rounded-full clickable-image cursor-pointer" height="32" src="../foto_akun/<?php echo $row_Rmasyarakat['foto']; ?>" width="32"/>
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
     Pengaduan
    </span>
   </div>
    <section class="bg-white rounded-lg shadow p-4 max-w-full overflow-x-auto" style="min-width: 720px">
      <h2 class="font-semibold text-gray-700 text-xl mb-4 text-center">
        Form Pengaduan
      </h2>
      <form method="post" name="form1" action="<?php echo $editFormAction; ?>" enctype="multipart/form-data">
        <div class="mb-4">
          <label for="tgl_pengaduan" class="block text-gray-700 font-semibold mb-2">Tanggal Pengaduan</label>
          <input type="date" name="tgl_pengaduan" id="tgl_pengaduan" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300" required>
        </div>
        <div class="mb-4">
          <label for="nik" class="block text-gray-700 font-semibold mb-2">NIK</label>
          <input type="text" name="nik" value="<?php echo isset($_GET['nik']) ? $_GET['nik'] : ''; ?>" readonly class="w-full px-3 py-2 border rounded bg-gray-100 cursor-not-allowed">
        </div>
        <div class="mb-4">
          <label for="isi_laporan" class="block text-gray-700 font-semibold mb-2">Isi Laporan</label>
          <textarea name="isi_laporan" id="isi_laporan" rows="4" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300" required></textarea>
        </div>
        <div class="mb-4">
          <label for="foto" class="block text-gray-700 font-semibold mb-2">Foto</label>
          <div class="flex items-center space-x-3">
            <label class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded cursor-pointer font-semibold transition flex items-center">
              <i class="fas fa-upload mr-2"></i>
              <span>Pilih Foto</span>
              <input type="file" name="foto" id="foto" accept="image/*" class="hidden" onChange="document.getElementById('file-name').textContent = this.files[0]?.name || '';">
            </label>
            <span id="file-name" class="text-gray-500 text-sm"></span>
          </div>
        </div>
        <div class="flex justify-end">
          <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 font-semibold transition">Kirim Pengaduan</button>
        </div>
        <input type="hidden" name="id_pengaduan" value="">
        <input type="hidden" name="status" value="0">
        <input type="hidden" name="MM_insert" value="form1">
      </form>
    </section>
    <section class="bg-white rounded-lg shadow p-4 max-w-full overflow-x-auto" style="min-width: 720px;">
      <?php if ($totalRows_Rpengaduan == 0) { // Show if recordset empty ?>
        <h2 class="font-semibold text-gray-700 text-lg mb-4"> Maaf Anda belum pernah mengirim pengaduan. Silahkan mengirim pengaduan melalui form di atas. </h2>
        <?php } // Show if recordset empty ?>
      <?php if ($totalRows_Rpengaduan > 0) { // Show if recordset not empty ?>
        <h2 class="font-semibold text-gray-700 text-xl mb-4 text-center">Data Pengaduan Anda</h2>
  <div class="mt-8 overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden shadow-sm">
      <thead class="bg-[#4B88FE]">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">ID Pengaduan</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Tanggal Pengaduan</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">NIK</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Isi Laporan</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Foto</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Status</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Aksi</th>
          </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-100">
        <?php do { ?>
          <tr class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo $row_Rpengaduan['id_pengaduan']; ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo $row_Rpengaduan['tgl_pengaduan']; ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo $row_Rpengaduan['nik']; ?></td>
            <td style="max-width: 50vh;" class="px-6 py-4 text-sm text-gray-700 max-w-xs break-words"><?php echo $row_Rpengaduan['isi_laporan']; ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
              <?php if (!empty($row_Rpengaduan['foto'])): ?>
              <img src="../foto_pengaduan/<?php echo $row_Rpengaduan['foto']; ?>" alt="Foto" class="h-12 w-12 object-cover rounded clickable-image cursor-pointer" />
              <?php else: ?>
              <span class="text-gray-400 italic">-</span>
              <?php endif; ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?php
            $status = strtolower($row_Rpengaduan['status']);
            if ($status == '0') {
              echo '<span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-semibold">Belum Diproses</span>';
            } elseif ($status == 'proses') {
              echo '<span class="bg-yellow-500 text-white px-2 py-1 rounded text-xs font-semibold">Proses</span>';
            } elseif ($status == 'selesai') {
              echo '<span class="bg-green-500 text-white px-2 py-1 rounded text-xs font-semibold">Selesai</span>';
            } else {
              echo '<span class="bg-gray-400 text-white px-2 py-1 rounded text-xs font-semibold">'.htmlspecialchars($row_Rpengaduan['status']).'</span>';
            }
          ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm"><a class="text-blue-500 hover:underline" href="detail_aduan.php?id_pengaduan=<?php echo $row_Rpengaduan['id_pengaduan']; ?>">Detail</a></td>
          </tr>
          <?php } while ($row_Rpengaduan = mysql_fetch_assoc($Rpengaduan)); ?>
      </tbody>
    </table>
  </div>
  <?php } // Show if recordset not empty ?>
    </section>
   <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
<div id="successModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50" style="top: -3rem;">
  <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full text-center">
    <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
    <h2 class="text-lg font-semibold mb-2">Pengaduan Berhasil!</h2>
    <p class="text-gray-600 mb-4">Terima kasih, pengaduan Anda telah dikirim.</p>
    <button onClick="document.getElementById('successModal').style.display='none'"
      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
      Tutup
    </button>
  </div>
</div>
<?php endif; ?>
   <footer class="text-center text-gray-500 text-sm mt-12" style="padding-bottom: 2rem;">
    &copy; 2025 - Pengaduan Masyarakat | SMK Negeri 5 Kendal
   </footer>
  </main>

  <!-- Modal Universal untuk Gambar -->
  <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <img id="modalImage" src="" alt="Gambar Detail" class="max-w-[90vw] max-h-[90vh] object-contain">
    <button id="closeImageModal" class="absolute top-4 right-4 text-white text-3xl font-bold">&times;</button>
  </div>
  
  <script src="../asset/script.js"></script>
 </body>
</html>
<?php
mysql_free_result($Rpengaduan);

mysql_free_result($Rmasyarakat);
?>
