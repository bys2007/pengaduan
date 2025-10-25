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
     Dashboard
    </span>
   </div>
   <section class="bg-white rounded-lg shadow p-6 max-w-3xl mx-auto">
      <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-4">Profil</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8">
        <!-- Kolom Kiri (Detail) -->
        <div class="md:col-span-2 space-y-5">
          <div>
            <label class="font-semibold text-gray-500 text-sm">NIK</label>
            <p class="text-gray-800 text-lg"><?php echo $row_Rmasyarakat['nik']; ?></p>
          </div>
          <div>
            <label class="font-semibold text-gray-500 text-sm">Nama</label>
            <p class="text-gray-800"><?php echo $row_Rmasyarakat['nama']; ?></p>
          </div>
          <div>
            <label class="font-semibold text-gray-500 text-sm">Username</label>
            <p class="text-gray-800"><?php echo $row_Rmasyarakat['username']; ?></p>
          </div>
          <div>
            <label class="font-semibold text-gray-500 text-sm">Telepon</label>
            <p class="text-gray-800"><?php echo $row_Rmasyarakat['telp']; ?></p>
          </div>
        </div>

        <!-- Kolom Kanan (Foto Profil) -->
        <div class="space-y-6 mt-6 md:mt-0">
          <div>
            <label class="font-semibold text-gray-500 text-sm">Foto Profil</label>
            <?php if (!empty($row_Rmasyarakat['foto'])): ?>
              <img src="../foto_akun/<?php echo $row_Rmasyarakat['foto']; ?>" alt="Foto Profil" class="mt-2 w-full rounded-lg border clickable-image cursor-pointer" />
            <?php else: ?>
              <p class="text-gray-400 italic mt-2">- Tidak ada foto -</p>
            <?php endif; ?>
          </div>
          <div class="mt-8 border-t pt-4 text-right">
            <button id="showPasswordBtn" 
                    data-password="<?php echo $row_Rmasyarakat['password']; ?>" 
                    class="text-blue-600 hover:text-blue-800 font-semibold hover:underline focus:outline-none">
              Lihat Password
            </button>
            <div id="passwordContainer" class="hidden mt-2 bg-gray-100 p-3 rounded-md border text-center">
              <p id="passwordText" class="font-mono text-gray-800 text-lg tracking-wider"></p>
            </div>
          </div>
        </div>
      </div>
   </section>
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
mysql_free_result($Rmasyarakat);
?>
