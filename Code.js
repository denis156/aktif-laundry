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
 * Generate thermal receipt HTML - called from frontend
 */
function generateThermalReceiptHTML(data) {
  try {
    Logger.log('generateThermalReceiptHTML: Starting...');
    Logger.log('Transaction data received: ' + JSON.stringify(data));

    var settings = getAllSettings();
    Logger.log('Settings loaded: ' + JSON.stringify(settings));

    var html = buildThermalReceiptHTML(data, settings);
    Logger.log('HTML generated, length: ' + html.length);

    return html;

  } catch (error) {
    Logger.log('Error generateThermalReceiptHTML: ' + error);
    Logger.log('Error stack: ' + error.stack);
    throw new Error('Gagal membuat HTML struk: ' + error.message);
  }
}

/**
 * Build thermal receipt HTML - 58mm thermal printer
 */
function buildThermalReceiptHTML(data, settings) {
  var namaToko = settings.nama_toko || 'AKTIF LAUNDRY';
  var telepon = settings.telepon || '021-12345678';
  var alamat = settings.alamat || '';
  var email = settings.email || '';
  var whatsapp = settings.whatsapp || '';

  // Konfigurasi ukuran kertas thermal dari settings (default 57mm)
  var paperWidth = settings.thermal_paper_width || '57';
  var fontSize = settings.thermal_font_size || '8';
  var lineHeight = settings.thermal_line_height || '1.1';
  var padding = settings.thermal_padding || '1.5';

  // Format tanggal
  var tanggalMasuk = formatDateTimeForReceipt(data.tanggalMasuk);
  var tanggalSelesai = formatDateTimeForReceipt(data.tanggalSelesai);

  // Format currency untuk tampilan
  var hargaPerKg = formatCurrencyForReceipt(data.hargaPerKg || 0);
  var subtotal = formatCurrencyForReceipt(data.subtotal || 0);
  var diskon = formatCurrencyForReceipt(data.diskon || 0);
  var total = formatCurrencyForReceipt(data.total || 0);

  var html = '<!DOCTYPE html>' +
'<html>' +
'<head>' +
'  <base target="_top">' +
'  <meta charset="UTF-8">' +
'  <meta name="viewport" content="width=' + paperWidth + 'mm">' +
'  <style>' +
'    * { margin: 0; padding: 0; box-sizing: border-box; }' +
'    @page {' +
'      size: ' + paperWidth + 'mm auto;' +
'      margin: 0;' +
'    }' +
'    @media print {' +
'      html, body {' +
'        width: ' + paperWidth + 'mm !important;' +
'        margin: 0 !important;' +
'        padding: 0 !important;' +
'      }' +
'      body { page-break-after: avoid; }' +
'    }' +
'    html { width: ' + paperWidth + 'mm; }' +
'    body {' +
'      font-family: "Courier New", monospace;' +
'      font-size: ' + fontSize + 'px;' +
'      line-height: ' + lineHeight + ';' +
'      width: ' + paperWidth + 'mm;' +
'      max-width: ' + paperWidth + 'mm;' +
'      margin: 0;' +
'      padding: ' + padding + 'mm;' +
'      background: white;' +
'      color: black;' +
'    }' +
'    .header { text-align: center; margin-bottom: 3px; border-bottom: 1px dashed #000; padding-bottom: 3px; }' +
'    .store-name { font-size: 11px; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; }' +
'    .store-info { font-size: 7px; line-height: 1.2; }' +
'    .transaction-info { margin: 3px 0; font-size: 8px; }' +
'    .info-row { display: flex; justify-content: space-between; margin-bottom: 1px; }' +
'    .info-label { font-weight: bold; }' +
'    .separator { border-top: 1px dashed #000; margin: 3px 0; }' +
'    .separator-double { border-top: 1px solid #000; margin: 3px 0; }' +
'    .items { margin: 3px 0; }' +
'    .item-row { display: flex; justify-content: space-between; margin-bottom: 1px; font-size: 8px; }' +
'    .item-name { flex: 1; font-weight: bold; }' +
'    .item-detail { font-size: 8px; margin-left: 2px; color: #333; }' +
'    .summary { margin-top: 3px; }' +
'    .summary-row { display: flex; justify-content: space-between; margin-bottom: 1px; font-size: 8px; }' +
'    .summary-row.total { font-size: 9px; font-weight: bold; margin-top: 2px; padding-top: 2px; border-top: 1px solid #000; }' +
'    .footer { text-align: center; margin-top: 3px; padding-top: 3px; border-top: 1px dashed #000; font-size: 7px; }' +
'    .thank-you { font-size: 8px; font-weight: bold; margin-bottom: 2px; }' +
'    .bold { font-weight: bold; }' +
'  </style>' +
'</head>' +
'<body>' +
'  <div class="header">' +
'    <div class="store-name">' + namaToko + '</div>' +
'    <div class="store-info">' +
      (alamat ? alamat + '<br>' : '') +
      (telepon ? 'Telp: ' + telepon + '<br>' : '') +
      (whatsapp ? 'WA: 0' + whatsapp + '<br>' : '') +
      (email ? email : '') +
'    </div>' +
'  </div>' +
'  <div class="transaction-info">' +
'    <div class="info-row"><span class="info-label">No. Transaksi</span><span>' + (data.id || '-') + '</span></div>' +
'    <div class="info-row"><span class="info-label">Tanggal</span><span>' + tanggalMasuk + '</span></div>' +
'  </div>' +
'  <div class="separator"></div>' +
'  <div class="transaction-info">' +
'    <div class="info-row"><span class="info-label">Pelanggan</span></div>' +
'    <div class="info-row"><span>' + (data.namaPelanggan || '-') + '</span></div>' +
      (data.alamatPelanggan ? '<div class="info-row"><span style="font-size: 7px; opacity: 0.8;">' + data.alamatPelanggan + '</span></div>' : '') +
      (data.noHp ? '<div class="info-row"><span>HP: ' + data.noHp + '</span></div>' : '') +
'  </div>' +
'  <div class="separator"></div>' +
'  <div class="items">' +
'    <div class="item-row bold"><span>Layanan</span><span>Harga</span></div>' +
'    <div class="separator"></div>' +
'    <div class="item-row"><span class="item-name">' + (data.namaLayanan || '-') + '</span></div>' +
'    <div class="item-row item-detail"><span>' + (data.berat || 0) + ' Kg x ' + hargaPerKg + '</span><span>' + subtotal + '</span></div>' +
'  </div>' +
'  <div class="separator"></div>' +
'  <div class="summary">' +
'    <div class="summary-row"><span>Subtotal</span><span>' + subtotal + '</span></div>' +
      (parseFloat(data.diskon || 0) > 0 ? '<div class="summary-row"><span>Diskon</span><span>- ' + diskon + '</span></div>' : '') +
'    <div class="summary-row total"><span>TOTAL</span><span>' + total + '</span></div>' +
'    <div class="summary-row"><span>Pembayaran</span><span>' + (data.metodePembayaran || 'Tunai') + '</span></div>' +
'  </div>' +
'  <div class="separator"></div>' +
'  <div class="transaction-info">' +
'    <div class="info-row"><span class="info-label">Estimasi Selesai</span></div>' +
'    <div class="info-row"><span class="bold">' + tanggalSelesai + '</span></div>' +
'    <div class="info-row" style="margin-top: 4px;"><span class="info-label">Status</span><span>' + (data.status || 'Menunggu') + '</span></div>' +
'  </div>' +
  (data.catatan ? '<div class="separator"></div><div class="transaction-info"><div class="info-row"><span class="info-label">Catatan</span></div><div class="info-row"><span>' + data.catatan + '</span></div></div>' : '') +
'  <div class="separator-double"></div>' +
'  <div class="footer">' +
'    <div class="thank-you">TERIMA KASIH</div>' +
'    <div style="margin-top: 2px;">Barang yg sudah dicuci</div>' +
'    <div>tidak dapat dikembalikan</div>' +
'    <div style="margin-top: 2px;">Simpan struk sbg bukti</div>' +
'  </div>' +
'  <div style="height: 3mm;"></div>' +
'</body>' +
'</html>';

  return html;
}

/**
 * Format date time untuk receipt
 */
function formatDateTimeForReceipt(dateValue) {
  if (!dateValue) return '-';

  try {
    var date;

    if (typeof dateValue === 'string') {
      // Handle format: dd/MM/yyyy HH:mm
      if (dateValue.indexOf('/') > -1 && dateValue.indexOf(':') > -1) {
        var parts = dateValue.split(' ');
        var dateParts = parts[0].split('/');
        var timeParts = parts[1].split(':');
        date = new Date(dateParts[2], dateParts[1] - 1, dateParts[0], timeParts[0], timeParts[1]);
      }
      // Handle format: yyyy-MM-dd
      else if (dateValue.indexOf('-') > -1 && dateValue.indexOf(':') === -1) {
        var parts = dateValue.split('-');
        date = new Date(parts[0], parts[1] - 1, parts[2], 0, 0);
      }
      else {
        date = new Date(dateValue);
      }
    } else if (dateValue instanceof Date) {
      date = dateValue;
    } else {
      return '-';
    }

    var day = Utilities.formatString('%02d', date.getDate());
    var month = Utilities.formatString('%02d', date.getMonth() + 1);
    var year = date.getFullYear();
    var hours = Utilities.formatString('%02d', date.getHours());
    var minutes = Utilities.formatString('%02d', date.getMinutes());

    return day + '/' + month + '/' + year + ' ' + hours + ':' + minutes;
  } catch (error) {
    return '-';
  }
}

/**
 * Format currency untuk receipt
 */
function formatCurrencyForReceipt(amount) {
  try {
    return 'Rp ' + Math.round(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  } catch (error) {
    return 'Rp 0';
  }
}

/**
 * Fungsi untuk mendapatkan semua settings dari sheet Setting
 */
function getAllSettings() {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Setting');

    if (!sheet) {
      Logger.log('Sheet Setting tidak ditemukan, menggunakan default settings');
      // Return default settings jika sheet tidak ada
      return {
        nama_toko: 'Aktif Laundry',
        alamat: 'Jl. Merdeka No. 123, Jakarta',
        telepon: '021-12345678',
        email: 'info@aktiflaundry.com',
        whatsapp: '81234567890',
        thermal_paper_width: '57',
        thermal_font_size: '8',
        thermal_line_height: '1.1',
        thermal_padding: '1.5'
      };
    }

    var data = sheet.getDataRange().getValues();
    var settings = {};

    // Start from row 1 (skip header at row 0)
    for (var i = 1; i < data.length; i++) {
      if (data[i][0]) {
        settings[data[i][0]] = data[i][1];
      }
    }

    Logger.log('Settings loaded: ' + JSON.stringify(settings));

    // Pastikan settings tidak kosong, jika kosong return default
    if (Object.keys(settings).length === 0) {
      Logger.log('Settings kosong, menggunakan default');
      return {
        nama_toko: 'Aktif Laundry',
        alamat: 'Jl. Merdeka No. 123, Jakarta',
        telepon: '021-12345678',
        email: 'info@aktiflaundry.com',
        whatsapp: '81234567890',
        thermal_paper_width: '57',
        thermal_font_size: '8',
        thermal_line_height: '1.1',
        thermal_padding: '1.5'
      };
    }

    return settings;

  } catch (error) {
    Logger.log('Error getAllSettings: ' + error);
    // Return default settings on error
    return {
      nama_toko: 'Aktif Laundry',
      alamat: 'Jl. Merdeka No. 123, Jakarta',
      telepon: '021-12345678',
      email: 'info@aktiflaundry.com',
      whatsapp: '81234567890',
      thermal_paper_width: '57',
      thermal_font_size: '8',
      thermal_line_height: '1.1',
      thermal_padding: '1.5'
    };
  }
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

