<?php require_once('../Connections/koneksi.php'); ?>
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

mysql_select_db($database_koneksi, $koneksi);
$query_Rcetak = "SELECT p.*, t.*, pt.nama_petugas FROM pengaduan AS p LEFT JOIN tanggapan AS t ON p.id_pengaduan = t.id_pengaduan LEFT JOIN petugas AS pt ON t.id_petugas = pt.id_petugas ORDER BY p.tgl_pengaduan ASC";
$Rcetak = mysql_query($query_Rcetak, $koneksi) or die(mysql_error());
$row_Rcetak = mysql_fetch_assoc($Rcetak);
$totalRows_Rcetak = mysql_num_rows($Rcetak);

// Mengambil data petugas yang sedang login dari session
$colname_Ruser = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_Ruser = $_SESSION['MM_Username'];
}
mysql_select_db($database_koneksi, $koneksi);
$query_Ruser = sprintf("SELECT * FROM petugas WHERE username = %s", GetSQLValueString($colname_Ruser, "text"));
$Ruser = mysql_query($query_Ruser, $koneksi) or die(mysql_error());
$row_Ruser = mysql_fetch_assoc($Ruser);
$totalRows_Ruser = mysql_num_rows($Ruser);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengaduan Masyarakat</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 190mm; /* A4 width minus margins */
            margin: 0 auto;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px solid #000;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo {
            width: 90px;
            height: auto;
        }
        .school-info {
            text-align: center;
        }
        .school-info h1 {
            font-size: 16pt;
            margin: 0;
        }
        .school-info p {
            font-size: 10pt;
            margin: 2px 0;
        }
        .report-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 25px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            table-layout: fixed; /* Key for controlling width */
        }
        .report-table th, .report-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word; /* Key for wrapping text */
        }
        .report-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .signature-section {
            width: 100%;
            margin-top: 50px;
        }
        .signature-box {
            width: 300px;
            float: right;
            text-align: center;
        }
        .signature-box .date-line {
            margin-bottom: 70px;
        }
        .signature-box .name-line {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="width: 100px;">
                    <img src="../asset/logo COLORFUL.png" alt="Logo" class="logo" />
                </td>
                <td class="school-info">
                    <h1>PEMERINTAH KABUPATEN KENDAL</h1>
                    <h1>SMK NEGERI 5 KENDAL</h1>
                    <p>Jalan Raya Bogosari, Kecamatan Pageruyung, Kabupaten Kendal 51361</p>
                    <p>Telepon: (0294) 451581 | Email: smkn5kendal@yahoo.co.id | Website: smknlimakendal.sch.id</p>
                </td>
            </tr>
        </table>

        <!-- Judul Laporan -->
        <h2 class="report-title">LAPORAN PENGADUAN MASYARAKAT</h2>

        <!-- Tabel Laporan -->
        <?php if ($totalRows_Rcetak > 0): ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Tgl Pengaduan</th>
                    <th style="width: 13%;">NIK</th>
                    <th style="width: 25%;">Isi Laporan</th>
                    <th style="width: 25%;">Tanggapan</th>
                    <th style="width: 15%;">Petugas</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php do { ?>
                <tr>
                    <td><?php echo date('d-m-Y', strtotime($row_Rcetak['tgl_pengaduan'])); ?></td>
                    <td><?php echo $row_Rcetak['nik']; ?></td>
                    <td><?php echo $row_Rcetak['isi_laporan']; ?></td>
                    <td>
                        <?php if($row_Rcetak['tanggapan']): ?>
                            "<?php echo $row_Rcetak['tanggapan']; ?>"
                            <br><small>(<?php echo date('d-m-Y', strtotime($row_Rcetak['tgl_tanggapan'])); ?>)</small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $row_Rcetak['nama_petugas']; ?></td>
                    <?php $status = strtolower($row_Rcetak['status']);
                    if ($status == '0') {
                        echo "<td style='background-color: gray;'>Belum Diproses</td>";
                    } elseif ($status == 'proses') {
                        echo "<td style='background-color: yellow;'>Sedang Diproses</td>";
                    } elseif ($status == 'selesai') {
                        echo "<td style='background-color: green;'>Selesai</td>";
                    }
                    ?>
                </tr>
                <?php } while ($row_Rcetak = mysql_fetch_assoc($Rcetak)); ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="text-align: center; font-style: italic;">Tidak ada data laporan untuk dicetak.</p>
        <?php endif; ?>

        <!-- Tanda Tangan -->
        <div class="signature-section">
            <div class="signature-box">
                <p class="date-line">Kendal, <?php echo date('d F Y'); ?></p>
                <p class="name-line"><?php echo $row_Ruser['nama_petugas']; ?></p>
                <p>Petugas</p>
            </div>
        </div>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
<?php
mysql_free_result($Rcetak);
mysql_free_result($Ruser);
?>
