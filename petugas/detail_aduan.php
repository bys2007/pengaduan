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
$MM_authorizedUsers = "petugas";
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

$MM_restrictGoTo = "../login_petugas.php";
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
// PINDAHKAN FUNGSI KE SINI
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

// PROSES UPDATE TANGGAPAN
if (isset($_POST['update_tanggapan'])) {
  $id_tanggapan = GetSQLValueString($_POST['id_tanggapan'], "int");
  $tanggapan_baru = GetSQLValueString($_POST['tanggapan'], "text");
  $tgl_sekarang = GetSQLValueString(date('Y-m-d'), "date");

  $updateSQL = sprintf("UPDATE tanggapan SET tanggapan=%s, tgl_tanggapan=%s WHERE id_tanggapan=%s",
                       $tanggapan_baru,
                       $tgl_sekarang,
                       $id_tanggapan);

  mysql_select_db($database_koneksi, $koneksi);
  mysql_query($updateSQL, $koneksi) or die(mysql_error());

  // Redirect untuk refresh halaman dan melihat perubahan
  header("Location: ".$_SERVER['REQUEST_URI']);
  exit;
}

// PROSES INSERT TANGGAPAN BARU
if (isset($_POST['submit_tanggapan'])) {
  $id_pengaduan = GetSQLValueString($_POST['id_pengaduan'], "int");
  $id_petugas = GetSQLValueString($_POST['id_petugas'], "int");
  $tanggapan = GetSQLValueString($_POST['tanggapan'], "text");
  $tgl_sekarang = GetSQLValueString(date('Y-m-d'), "date");

  // 1. Insert tanggapan
  $insertSQL = sprintf("INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) VALUES (%s, %s, %s, %s)",
                       $id_pengaduan,
                       $tgl_sekarang,
                       $tanggapan,
                       $id_petugas);

  mysql_select_db($database_koneksi, $koneksi);
  mysql_query($insertSQL, $koneksi) or die(mysql_error());

  // 2. Update status pengaduan menjadi 'proses'
  $updateStatusSQL = sprintf("UPDATE pengaduan SET status='proses' WHERE id_pengaduan=%s", $id_pengaduan);
  mysql_query($updateStatusSQL, $koneksi) or die(mysql_error());

  // Redirect untuk refresh halaman
  header("Location: ".$_SERVER['REQUEST_URI']);
  exit;
}

// PROSES UBAH STATUS PENGADUAN
if (isset($_GET['ubah_status']) && isset($_GET['id_pengaduan'])) {
  $new_status = GetSQLValueString($_GET['ubah_status'], "text");
  $id_pengaduan_to_update = GetSQLValueString($_GET['id_pengaduan'], "int");

  $updateStatusSQL = sprintf("UPDATE pengaduan SET status=%s WHERE id_pengaduan=%s",
                             $new_status,
                             $id_pengaduan_to_update);

  mysql_select_db($database_koneksi, $koneksi);
  mysql_query($updateStatusSQL, $koneksi) or die(mysql_error());

  // Redirect kembali ke halaman detail tanpa parameter 'ubah_status' untuk mencegah re-submit
  $redirectURL = "detail_aduan.php?id_pengaduan=" . $_GET['id_pengaduan'];
  header("Location: " . $redirectURL);
  exit;
}
?>
<?php
$colname_Rpetugas = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_Rpetugas = $_SESSION['MM_Username'];
}
mysql_select_db($database_koneksi, $koneksi);
$query_Rpetugas = sprintf("SELECT * FROM petugas WHERE username = %s", GetSQLValueString($colname_Rpetugas, "text"));
$Rpetugas = mysql_query($query_Rpetugas, $koneksi) or die(mysql_error());
$row_Rpetugas = mysql_fetch_assoc($Rpetugas);
$totalRows_Rpetugas = mysql_num_rows($Rpetugas);

$colname_Raduan = "-1";
if (isset($_GET['id_pengaduan'])) {
  $colname_Raduan = $_GET['id_pengaduan'];
}
mysql_select_db($database_koneksi, $koneksi);
$query_Raduan = sprintf("SELECT * FROM pengaduan WHERE id_pengaduan = %s", GetSQLValueString($colname_Raduan, "int"));
$Raduan = mysql_query($query_Raduan, $koneksi) or die(mysql_error());
$row_Raduan = mysql_fetch_assoc($Raduan);
$totalRows_Raduan = mysql_num_rows($Raduan);

$colname_Rtanggapan = "-1";
if (isset($_GET['id_pengaduan'])) {
  $colname_Rtanggapan = $_GET['id_pengaduan'];
}
mysql_select_db($database_koneksi, $koneksi);
$query_Rtanggapan = sprintf("SELECT * FROM tanggapan WHERE id_pengaduan = %s", GetSQLValueString($colname_Rtanggapan, "int"));
$Rtanggapan = mysql_query($query_Rtanggapan, $koneksi) or die(mysql_error());
$row_Rtanggapan = mysql_fetch_assoc($Rtanggapan);
$totalRows_Rtanggapan = mysql_num_rows($Rtanggapan);
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
      Menu
    </div>
    <a href="data_masyarakat.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
      <i class="fas fa-users text-base"></i>
      <span>Data Masyarakat</span>
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
    <span class="font-semibold text-gray-700 text-lg">Selamat Datang, <?php echo $row_Rpetugas['username']; ?></span>
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
    <img alt="User Avatar" class="w-8 h-8 rounded-full clickable-image cursor-pointer" height="32" src="../foto_akun/<?php echo $row_Rpetugas['foto']; ?>" width="32"/>
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
    <span class="text-gray-400">
     Data Pengaduan
    </span>
    <span>
     /
    </span>
    <span class="font-semibold text-gray-900">
     Detail Pengaduan
    </span>
   </div>
   <section class="bg-white rounded-lg shadow p-6 max-w-4xl mx-auto">
      <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Detail Pengaduan</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8">
        <!-- Kolom Kiri (Detail) -->
        <div class="md:col-span-2 space-y-5">
          <div>
            <label class="font-semibold text-gray-500 text-sm">ID Pengaduan</label>
            <p class="text-gray-800 text-lg"><?php echo $row_Raduan['id_pengaduan']; ?></p>
          </div>
          <div>
            <label class="font-semibold text-gray-500 text-sm">Tanggal Pengaduan</label>
            <p class="text-gray-800"><?php echo date('d F Y', strtotime($row_Raduan['tgl_pengaduan'])); ?></p>
          </div>
          <div>
            <label class="font-semibold text-gray-500 text-sm">NIK Pelapor</label>
            <p class="text-gray-800"><?php echo $row_Raduan['nik']; ?></p>
          </div>
          <div>
            <label class="font-semibold text-gray-500 text-sm">Isi Laporan</label>
            <p class="text-gray-800 bg-gray-50 p-3 rounded-md border break-words"><?php echo $row_Raduan['isi_laporan']; ?></p>
          </div>
           <div>
            <label class="font-semibold text-gray-500 text-sm">Status</label>
            <div class="mt-1">
             <?php
              $status = strtolower($row_Raduan['status']);
              if ($status == '0') {
                echo '<span class="bg-gray-400 text-white px-3 py-1 rounded-full text-xs font-semibold">Belum Diproses</span>';
              } elseif ($status == 'proses') {
                echo '<span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold">Proses</span>';
              } elseif ($status == 'selesai') {
                echo '<span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">Selesai</span>';
              } else {
                echo '<span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">'.htmlspecialchars($row_Raduan['status']).'</span>';
              }
            ?>
            </div>
          </div>

          <div class="border-t pt-4 mt-4">
            <label class="font-semibold text-gray-500 text-sm">Ubah Status</label>
            <div class="flex flex-wrap gap-2 mt-2">
              
              <!-- Tombol Belum Diproses -->
              <a href="?id_pengaduan=<?php echo $row_Raduan['id_pengaduan']; ?>&ubah_status=0" 
                 class="px-3 py-1.5 rounded-md text-sm font-semibold transition
                        <?php echo ($row_Raduan['status'] == '0') 
                               ? 'bg-gray-600 text-white pointer-events-none' 
                               : 'bg-gray-400 text-white hover:bg-gray-500'; ?>">
                <i class="fas fa-inbox mr-1"></i> Belum Diproses
              </a>

              <!-- Tombol Proses -->
              <a href="?id_pengaduan=<?php echo $row_Raduan['id_pengaduan']; ?>&ubah_status=proses" 
                 class="px-3 py-1.5 rounded-md text-sm font-semibold transition
                        <?php echo ($row_Raduan['status'] == 'proses') 
                               ? 'bg-yellow-600 text-white pointer-events-none' 
                               : 'bg-yellow-500 text-white hover:bg-yellow-600'; ?>">
                <i class="fas fa-spinner mr-1"></i> Proses
              </a>

              <!-- Tombol Selesai -->
              <a href="?id_pengaduan=<?php echo $row_Raduan['id_pengaduan']; ?>&ubah_status=selesai" 
                 class="px-3 py-1.5 rounded-md text-sm font-semibold transition
                        <?php echo ($row_Raduan['status'] == 'selesai') 
                               ? 'bg-green-600 text-white pointer-events-none' 
                               : 'bg-green-500 text-white hover:bg-green-600'; ?>">
                <i class="fas fa-check-circle mr-1"></i> Selesai
              </a>

            </div>
          </div>
        </div>

        <!-- Kolom Kanan (Foto & Tanggapan) -->
        <div class="space-y-6 mt-6 md:mt-0">
          <div>
            <label class="font-semibold text-gray-500 text-sm">Foto Bukti</label>
            <?php if (!empty($row_Raduan['foto'])): ?>
              <img src="../foto_pengaduan/<?php echo $row_Raduan['foto']; ?>" alt="Foto Bukti" class="mt-2 w-full rounded-lg border clickable-image cursor-pointer" />
            <?php else: ?>
              <p class="text-gray-400 italic mt-2">- Tidak ada foto -</p>
            <?php endif; ?>
          </div>
          
          <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
            <label class="font-semibold text-gray-700 text-sm">Tanggapan Petugas</label>
            <?php if ($totalRows_Rtanggapan > 0): ?>
              <p class="text-gray-800 mt-2 text-sm">"<?php echo $row_Rtanggapan['tanggapan']; ?>"</p>
              <p class="text-gray-500 text-xs mt-2">Ditanggapi pada: <?php echo date('d F Y', strtotime($row_Rtanggapan['tgl_tanggapan'])); ?></p>
              <button id="editTanggapanBtn" class="inline-block mt-3 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 font-semibold text-sm transition">
                <i class="fas fa-edit mr-2"></i>Edit Tanggapan
              </button>
            <?php else: ?>
              <p class="text-gray-500 italic mt-2 text-sm">Belum ada tanggapan.</p>
              <button id="beriTanggapanBtn" class="inline-block mt-3 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 font-semibold text-sm transition">
                <i class="fas fa-pen mr-2"></i>Beri Tanggapan
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <div class="mt-8 border-t pt-4 text-right">
        <a href="data_pengaduan.php" class="text-gray-600 hover:text-gray-800 font-semibold">
          &larr; Kembali ke Data Pengaduan
        </a>
      </div>
   </section>
   <footer class="text-center text-gray-500 text-sm mt-12" style="padding-bottom: 2rem;">
    &copy; 2025 - Pengaduan Masyarakat | SMK Negeri 5 Kendal
   </footer>
  </main>

  <!-- Modal untuk Beri Tanggapan -->
  <div id="beriTanggapanModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
      <h3 class="text-lg font-bold mb-4">Beri Tanggapan</h3>
      <form method="POST" action="">
        <!-- Input tersembunyi untuk data otomatis -->
        <input type="hidden" name="id_pengaduan" value="<?php echo $row_Raduan['id_pengaduan']; ?>">
        <input type="hidden" name="id_petugas" value="<?php echo $row_Rpetugas['id_petugas']; ?>">
        
        <label for="tanggapan_text" class="block text-sm font-medium text-gray-700 mb-2">Tulis tanggapan Anda:</label>
        <textarea id="tanggapan_text" name="tanggapan" rows="5" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
        
        <div class="mt-4 text-right space-x-2">
          <button type="button" id="cancelBeriTanggapanBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">Batal</button>
          <button type="submit" name="submit_tanggapan" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Kirim Tanggapan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal untuk Edit Tanggapan -->
  <div id="editTanggapanModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
      <h3 class="text-lg font-bold mb-4">Edit Tanggapan</h3>
      <form method="POST" action="">
        <input type="hidden" name="id_tanggapan" value="<?php echo $row_Rtanggapan['id_tanggapan']; ?>">
        <textarea name="tanggapan" rows="5" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($row_Rtanggapan['tanggapan']); ?></textarea>
        <div class="mt-4 text-right space-x-2">
          <button type="button" id="cancelEditBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400">Batal</button>
          <button type="submit" name="update_tanggapan" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Update</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Universal untuk Gambar -->
  <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <img id="modalImage" src="" alt="Gambar Detail" class="max-w-[90vw] max-h-[90vh] object-contain">
    <button id="closeImageModal" class="absolute top-4 right-4 text-white text-3xl font-bold">&times;</button>
  </div>

  <script src="../asset/script.js"></script>
 </body>
</html>
<?php
mysql_free_result($Rpetugas);

mysql_free_result($Raduan);

mysql_free_result($Rtanggapan);
?>
