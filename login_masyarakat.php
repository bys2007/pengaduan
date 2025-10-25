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
?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['user'])) {
  $loginUsername=$_POST['user'];
  $password=$_POST['pass'];
  $MM_fldUserAuthorization = "";
  $MM_redirectLoginSuccess = "masyarakat/dashboard.php";
  $MM_redirectLoginFailed = "login_fail.php";
  $MM_redirecttoReferrer = false;
  mysql_select_db($database_koneksi, $koneksi);
  
  $LoginRS__query=sprintf("SELECT username, password FROM masyarakat WHERE username=%s AND password=%s",
    GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text")); 
   
  $LoginRS = mysql_query($LoginRS__query, $koneksi) or die(mysql_error());
  $loginFoundUser = mysql_num_rows($LoginRS);
  if ($loginFoundUser) {
     $loginStrGroup = "";
    
	if (PHP_VERSION >= 5.1) {session_regenerate_id(true);} else {session_regenerate_id();}
    //declare two session variables and assign them
    $_SESSION['MM_Username'] = $loginUsername;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;	      

    if (isset($_SESSION['PrevUrl']) && false) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
    header("Location: " . $MM_redirectLoginSuccess );
    exit;
  } else {
    $loginError = true; // Tambahkan variabel error
  }
}
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
    <a href="index.php" class="flex items-center space-x-2 text-white hover:text-gray-200" href="#">
     <i class="fas fa-home text-base">
     </i>
     <span>
        Home
     </span>
    </a>
    <div class="uppercase text-xs font-semibold text-gray-200 tracking-wider mt-6" style="opacity: 0.8;">
     Akun
    </div>
    <a href="login_masyarakat.php" class="flex items-center space-x-2 text-white hover:text-gray-200" href="#">
     <i class="fas fa-sign-out-alt text-base">
     </i>
     <span>
        Login
     </span>
    </a>
    <a href="registrasi.php" class="flex items-center space-x-2 text-white hover:text-gray-200" href="#">
     <i class="fas fa-user-plus text-base">
     </i>
     <span>
        Registrasi
     </span>
    </a>
 </nav>
</aside>
  <!-- Main content -->
  <main class="flex-1 p-6 md:p-8 space-y-6" style="max-width: calc(100vw - 16rem); margin-left: 16rem;">
   <!-- Breadcrumb -->
   <div class="text-gray-600 text-sm">
    <span class="text-gray-400">
     Login
    </span>
    <span>
     /
    </span>
    <span class="font-semibold text-gray-900">
     Masyarakat
    </span>
   </div>
   <section class="bg-white rounded-lg shadow p-4 max-w-full overflow-x-auto" style="min-width: 720px">
   <form id="form1" name="form1" method="POST" action="<?php echo $loginFormAction; ?>" class="max-w-md mx-auto space-y-6">
      <div>
         <label for="user" class="block text-gray-700 font-semibold mb-2">Username</label>
         <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus-within:border-[#4B88FE]">
            <i class="fas fa-user text-gray-400 mr-2"></i>
            <input type="text" name="user" id="user" placeholder="Username" required
               class="bg-transparent outline-none flex-1 text-gray-700 placeholder-gray-400" />
         </div>
      </div>
      <div>
         <label for="pass" class="block text-gray-700 font-semibold mb-2">Password</label>
         <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus-within:border-[#4B88FE]">
            <i class="fas fa-lock text-gray-400 mr-2"></i>
            <input type="password" name="pass" id="pass" placeholder="Password" required
               class="bg-transparent outline-none flex-1 text-gray-700 placeholder-gray-400" />
         </div>
      </div>
      <div>
         <input type="submit" name="Blogin" id="Blogin" value="Login"
        class="w-full bg-[#4B88FE] hover:bg-[#376fd6] text-white font-semibold py-2 rounded-lg shadow transition duration-150 cursor-pointer" />
      </div>
   </form> <br/>
   Belum punya akun? <a href="registrasi.php" class="text-blue-500 hover:underline">Registrasi</a> <br/>
   Login sebagai petugas? <a href="login_petugas.php" class="text-blue-500 hover:underline">Login Petugas</a>
   </section>
   <!-- Modal Login Gagal -->
<?php if (isset($loginError) && $loginError): ?>
<div id="loginModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50" style="top: -3rem;">
  <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full text-center">
    <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-2"></i>
    <h2 class="text-lg font-semibold mb-2">Login Gagal</h2>
    <p class="text-gray-600 mb-4">Username atau password salah.</p>
    <button onclick="document.getElementById('loginModal').style.display='none'"
      class="bg-[#4B88FE] hover:bg-[#376fd6] text-white px-4 py-2 rounded font-semibold">
      Tutup
    </button>
  </div>
</div>
<?php endif; ?>
   <footer class="text-center text-gray-500 text-sm mt-12" style="padding-bottom: 2rem;">
    &copy; 2025 - Pengaduan Masyarakat | SMK Negeri 5 Kendal
   </footer>
  </main>
 </body>
</html>