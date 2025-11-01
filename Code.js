/**
 * Fungsi utama untuk membuka aplikasi web
 */
function doGet(e) {
  return HtmlService.createTemplateFromFile('Index')
    .evaluate()
    .setTitle('Aktif Laundry - Sistem Kasir')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
}

/**
 * Fungsi untuk include file HTML/CSS/JS
 * Digunakan dengan <?!= include('filename') ?> di HTML
 */
function include(filename) {
  return HtmlService.createTemplateFromFile(filename).evaluate().getContent();
}

/**
 * Fungsi untuk include Layanan Scripts
 * Khusus untuk load JavaScript functions dari Layanan/LayananScripts.html
 */
function includeLayananScripts() {
  return HtmlService.createHtmlOutputFromFile('Layanan/LayananScripts').getContent();
}

/**
 * Fungsi untuk include Pelanggan Scripts
 * Khusus untuk load JavaScript functions dari Pelanggan/PelangganScripts.html
 */
function includePelangganScripts() {
  return HtmlService.createHtmlOutputFromFile('Pelanggan/PelangganScripts').getContent();
}


/**
 * Fungsi untuk include Kasir Modals
 * Khusus untuk load modal components dari Kasir/KasirModals.html
 */
function includeKasirModals() {
  return HtmlService.createHtmlOutputFromFile('Kasir/KasirModals').getContent();
}

/**
 * Fungsi untuk include Kasir Scripts
 * Khusus untuk load JavaScript functions dari Kasir/KasirScripts.html
 */
function includeKasirScripts() {
  return HtmlService.createHtmlOutputFromFile('Kasir/KasirScripts').getContent();
}

/**
 * Fungsi untuk load halaman dinamis via navigasi
 */
function loadPage(page) {
  try {
    // Jika halaman Layanan, pass data langsung
    if (page === 'Layanan') {
      var template = HtmlService.createTemplateFromFile(page);
      var result = getAllLayanan();

      if (result.success) {
        template.layananData = result.data;
      } else {
        template.layananData = [];
      }

      return template.evaluate().getContent();
    }

    // Jika halaman Pelanggan, pass data langsung
    if (page === 'Pelanggan') {
      var template = HtmlService.createTemplateFromFile(page);
      var result = getAllPelanggan();

      if (result.success) {
        template.pelangganData = result.data;
      } else {
        template.pelangganData = [];
      }

      return template.evaluate().getContent();
    }

    // Jika halaman Transaksi, pass data langsung
    if (page === 'Transaksi') {
      var template = HtmlService.createTemplateFromFile(page);
      var result = getAllTransaksi();

      if (result.success) {
        template.transaksiData = result.data;
      } else {
        template.transaksiData = [];
      }

      return template.evaluate().getContent();
    }

    // Jika halaman Kasir, gunakan template untuk proses include
    if (page === 'Kasir') {
      var template = HtmlService.createTemplateFromFile(page);
      return template.evaluate().getContent();
    }

    // Untuk halaman lain, load biasa
    return HtmlService.createHtmlOutputFromFile(page).getContent();

  } catch (error) {
    Logger.log('Error loadPage: ' + error);
    return '<div class="alert alert-error"><span>Error: ' + error.message + '</span></div>';
  }
}

/**
 * HELPER FUNCTIONS - Dibutuhkan oleh frontend untuk dropdown di modal transaksi
 * Fungsi-fungsi ini dipanggil via google.script.run dari TransaksiScripts.html
 */

/**
 * Fungsi untuk mengambil pelanggan aktif saja (untuk dropdown)
 * Dipanggil dari: Transaksi/TransaksiScripts.html
 * RENAMED untuk menghindari konflik dengan file lain
 */
function getActivePelanggan() {
  return fetchActivePelangganList();
}

/**
 * IMPLEMENTASI SEBENARNYA - Self-contained
 */
function fetchActivePelangganList() {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Pelanggan');

    if (!sheet) {
      return { success: false, message: 'Sheet Pelanggan tidak ditemukan' };
    }

    var lastRow = sheet.getLastRow();

    // Jika tidak ada data
    if (lastRow <= 1) {
      return { success: true, data: [] };
    }

    // Ambil semua data (skip header)
    var range = sheet.getRange(2, 1, lastRow - 1, 8);
    var values = range.getValues();

    // Filter hanya pelanggan aktif dan convert ke array of objects
    var activePelanggan = [];
    for (var i = 0; i < values.length; i++) {
      if (values[i][7] === 'Aktif') {  // Kolom 8 (index 7) adalah Status
        activePelanggan.push({
          id: values[i][0] ? String(values[i][0]) : '',
          nama: values[i][1] ? String(values[i][1]) : '',
          noHp: values[i][2] ? String(values[i][2]) : '',  // Always convert to String
          alamat: values[i][3] ? String(values[i][3]) : '',
          email: values[i][4] ? String(values[i][4]) : '',
          tanggalDaftar: values[i][5] ? values[i][5].toString() : '',  // Convert Date to String
          totalTransaksi: values[i][6] ? Number(values[i][6]) : 0,
          status: values[i][7] ? String(values[i][7]) : ''
        });
      }
    }

    return { success: true, data: activePelanggan };

  } catch (error) {
    Logger.log('ERROR in fetchActivePelangganList: ' + error);
    return { success: false, message: error.toString() };
  }
}

/**
 * Fungsi untuk mengambil layanan aktif saja (untuk dropdown)
 * Dipanggil dari: Transaksi/TransaksiScripts.html
 */
function getActiveLayanan() {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Layanan');

    if (!sheet) {
      return { success: false, message: 'Sheet Layanan tidak ditemukan' };
    }

    var lastRow = sheet.getLastRow();

    // Jika tidak ada data
    if (lastRow <= 1) {
      return { success: true, data: [] };
    }

    // Ambil semua data (skip header)
    var range = sheet.getRange(2, 1, lastRow - 1, 6);
    var values = range.getValues();

    // Filter hanya layanan aktif dan convert ke array of objects
    var activeLayanan = [];
    for (var i = 0; i < values.length; i++) {
      if (values[i][5] === 'Aktif') {  // Kolom 6 (index 5) adalah Status
        activeLayanan.push({
          id: values[i][0],
          nama: values[i][1],
          harga: values[i][2],
          durasi: values[i][3],
          deskripsi: values[i][4],
          status: values[i][5]
        });
      }
    }

    return { success: true, data: activeLayanan };

  } catch (error) {
    Logger.log('Error getActiveLayanan: ' + error);
    return { success: false, message: error.toString() };
  }
}