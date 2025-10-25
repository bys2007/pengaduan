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
     <i class="fas fa-home text-base">
     </i>
     <span>
        Home
     </span>
    </a>
    <div class="uppercase text-xs font-semibold text-gray-200 tracking-wider mt-6" style="opacity: 0.8;">
     Akun
    </div>
    <a href="login_masyarakat.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
     <i class="fas fa-sign-out-alt text-base">
     </i>
     <span>
        Login
     </span>
    </a>
    <a href="registrasi.php" class="flex items-center space-x-2 text-white hover:text-gray-200">
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
    <span class="font-semibold text-gray-900">
     Home
    </span>
   </div>
   <section class="bg-white rounded-lg shadow p-4 max-w-full overflow-x-auto" style="min-width: 720px">
    <h2 class="font-semibold text-gray-700 text-lg mb-4">
     Selamat Datang di Layanan Laporan Pengaduan Online SMK Negeri 5 Kendal
    </h2>
    <p class="text-gray-600">
    Langkah-langkah pengaduan secara online:
    <ol class="list-decimal ml-6 mt-2 text-gray-700">
        <li>Melakukan pendaftaran/regitrasi untuk mendapatkan akun. Siapkan data pribadi yang dibutuhkan.</li>
        <li>Melakukan login akun akun pada halaman login.</li>
        <li>Masukkan username dan password sesuai akun yang telah didaftarkan.</li>
        <li>Klik kirim aduan untuk memproses pengaduan.</li>
        <li>Dimohon menggunakan kata-kata yang sopan dan santun serta tidak menyinggung atau memancing kontroversi.</li>
    </ol>
    </p> <br/>
    <p class="text-gray-600">Silahkan melakukan <a href="login_masyarakat.php" class="text-blue-500 hover:underline">Login</a> untuk melakukan pengaduan masyarakat secara online.</p>
    <p class="text-gray-600">Atau silahkan melakukan <a href="registrasi.php" class="text-blue-500 hover:underline">Regitrasi</a> terlebih dahulu jika belum mempunyai akun.</p>
    </p>
   </section>
   <footer class="text-center text-gray-500 text-sm mt-12" style="padding-bottom: 2rem;">
    &copy; 2025 - Pengaduan Masyarakat | SMK Negeri 5 Kendal
   </footer>
  </main>
 </body>
</html>