<?php require_once('Connections/koneksi.php'); ?>
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
    move_uploaded_file($_FILES['foto']['tmp_name'], 'foto_akun/' . $nama_file);
  }
  $insertSQL = sprintf("INSERT INTO masyarakat (nik, nama, username, password, telp, foto) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['nik'], "text"),
                       GetSQLValueString($_POST['nama'], "text"),
                       GetSQLValueString($_POST['username'], "text"),
                       GetSQLValueString($_POST['password'], "text"),
                       GetSQLValueString($_POST['telp'], "text"),
                       GetSQLValueString($nama_file, "text"));

  mysql_select_db($database_koneksi, $koneksi);
  $Result1 = mysql_query($insertSQL, $koneksi) or die(mysql_error());

  $insertGoTo = "login_masyarakat.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}

mysql_select_db($database_koneksi, $koneksi);
$query_Rregistrasi = "SELECT * FROM masyarakat";
$Rregistrasi = mysql_query($query_Rregistrasi, $koneksi) or die(mysql_error());
$row_Rregistrasi = mysql_fetch_assoc($Rregistrasi);
$totalRows_Rregistrasi = mysql_num_rows($Rregistrasi);
?>
<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <link type="image/x-icon" href="asset\logo COLORFUL.png" rel="icon"/>
  <link type="image/x-icon" href="asset\logo COLORFUL.png" rel="shortcut icon"/>
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
    <img alt="GitLab logo icon in orange and red colors" class="w-10 h-10" src="asset\logo COLORFUL.png" style="width:50px; height:50px;" />
    <span class="font-semibold text-xl text-white">
     Pengaduan Masyarakat
    </span>
 </div>
 <nav class="flex flex-col space-y-6 text-white text-sm font-normal">
    <a href="index.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-home text-base"></i>
     <span>
        Home
     </span>
    </a>
    <div class="uppercase text-xs font-semibold text-gray-200 tracking-wider mt-6" style="opacity: 0.8;">
     Akun
    </div>
    <a href="login_masyarakat.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-sign-out-alt text-base"></i>
     <span>
        Login
     </span>
    </a>
    <a href="registrasi.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-user-plus text-base"></i>
     <span>
        Registrasi
     </span>
    </a>
 </nav>
</aside>
  <!-- Main content -->
<main class="flex-1 p-6 md:p-8 space-y-6 ml-64" style="max-width: calc(100vw - 16rem); margin-left: 16rem;">
 <!-- Breadcrumb -->
 <div class="text-gray-600 text-sm">
    <span class="font-semibold text-gray-900">
     Registrasi
    </span>
 </div>
 <section class="bg-white rounded-lg shadow p-4 max-w-full overflow-x-auto" style="min-width: 720px">
    <form enctype="multipart/form-data" method="post" name="form1" action="<?php echo $editFormAction; ?>" class="max-w-lg mx-auto space-y-6" style="margin-top: 2rem;">
        <div>
            <label for="nik" class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
            <input type="text" name="nik" id="nik" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4B88FE]" required>
        </div>
        <div>
            <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
            <input type="text" name="nama" id="nama" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4B88FE]" required>
        </div>
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text" name="username" id="username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4B88FE]" required>
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" id="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4B88FE]" required>
        </div>
        <div>
            <label for="telp" class="block text-sm font-medium text-gray-700 mb-1">No Telepon</label>
            <input type="text" name="telp" id="telp" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#4B88FE]" required>
        </div>
        <div class="mb-4">
          <label for="foto" class="block text-gray-700 font-semibold mb-2">Foto Profil</label>
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
            <input type="hidden" name="MM_insert" value="form1">
            <button type="submit" class="bg-[#4B88FE] text-white px-6 py-2 rounded-lg font-semibold hover:bg-[#376fd6] transition">Submit</button>
        </div>
    </form>
     <p>&nbsp;</p>
 </section>
 <footer class="text-center text-gray-500 text-sm mt-12" style="padding-bottom: 2rem;">
    &copy; 2025 - Pengaduan Masyarakat | SMK Negeri 5 Kendal
 </footer>
</main>
 </body>
</html>
<?php
mysql_free_result($Rregistrasi);
?>
