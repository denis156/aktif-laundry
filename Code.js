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

