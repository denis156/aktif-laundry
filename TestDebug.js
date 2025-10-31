/**
 * Fungsi untuk test debugging
 * Run fungsi ini dari Apps Script Editor
 */
function testGetAllPelanggan() {
  var result = getAllPelanggan();
  Logger.log('getAllPelanggan result:');
  Logger.log(JSON.stringify(result, null, 2));
  return result;
}

function testGetActivePelanggan() {
  Logger.log('=== TESTING getActivePelanggan ===');

  // Cek apakah fungsi ada
  Logger.log('Function exists: ' + (typeof getActivePelanggan !== 'undefined'));

  var result = getActivePelanggan();
  Logger.log('getActivePelanggan result:');
  Logger.log(JSON.stringify(result, null, 2));

  if (!result) {
    Logger.log('WARNING: Result is null or undefined!');
  }

  return result;
}

function testGetActiveLayanan() {
  var result = getActiveLayanan();
  Logger.log('getActiveLayanan result:');
  Logger.log(JSON.stringify(result, null, 2));
  return result;
}

function testCheckSheets() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheets = ss.getSheets();

  Logger.log('=== Daftar Sheet ===');
  sheets.forEach(function(sheet) {
    Logger.log('Sheet: ' + sheet.getName() + ' - Rows: ' + sheet.getLastRow());
  });
}

/**
 * Fungsi test untuk dipanggil dari web app
 * Ini untuk memastikan fungsi bisa dipanggil via google.script.run
 */
function testFromWebApp() {
  Logger.log('=== TEST FROM WEB APP ===');

  var pelangganResult = getActivePelanggan();
  var layananResult = getActiveLayanan();

  return {
    pelangganTest: pelangganResult,
    layananTest: layananResult,
    timestamp: new Date().toString()
  };
}

/**
 * Fungsi test untuk debug createTransaksi
 * Jalankan fungsi ini dari Apps Script editor untuk melihat logs
 */
function testCreateTransaksi() {
  // Simulasi data dari form Kasir
  var testData = {
    idPelanggan: 'PLG001',  // Ganti dengan ID pelanggan yang ada di sheet kamu
    idLayanan: 'LYN001',     // Ganti dengan ID layanan yang ada di sheet kamu
    tanggalMasuk: new Date(),
    berat: 5,
    diskon: 0,
    metodePembayaran: 'Tunai',
    catatan: 'Test dari debug',
    status: 'Menunggu'
  };

  Logger.log('===== TEST CREATE TRANSAKSI =====');
  Logger.log('Test data: ' + JSON.stringify(testData));

  var result = createTransaksi(testData);

  Logger.log('===== RESULT =====');
  Logger.log('Result: ' + JSON.stringify(result));
  Logger.log('Result type: ' + typeof result);
  Logger.log('Result is null: ' + (result === null));
  Logger.log('Result is undefined: ' + (result === undefined));

  if (result && result.success) {
    Logger.log('✓ TEST SUCCESS');
  } else {
    Logger.log('✗ TEST FAILED');
  }

  return result;
}

/**
 * Fungsi untuk melihat semua ID pelanggan yang ada
 */
function debugListAllPelangganIDs() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName('Pelanggan');

  if (!sheet) {
    Logger.log('Sheet Pelanggan tidak ditemukan');
    return;
  }

  var lastRow = sheet.getLastRow();
  Logger.log('Total rows: ' + lastRow);

  if (lastRow <= 1) {
    Logger.log('Tidak ada data pelanggan');
    return;
  }

  var range = sheet.getRange(2, 1, lastRow - 1, 2); // Ambil kolom ID dan Nama
  var values = range.getValues();

  Logger.log('===== DAFTAR PELANGGAN =====');
  for (var i = 0; i < values.length; i++) {
    Logger.log('Row ' + (i+2) + ': ID="' + values[i][0] + '" | Nama="' + values[i][1] + '"');
  }
}

/**
 * Fungsi untuk melihat semua ID layanan yang ada
 */
function debugListAllLayananIDs() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName('Layanan');

  if (!sheet) {
    Logger.log('Sheet Layanan tidak ditemukan');
    return;
  }

  var lastRow = sheet.getLastRow();
  Logger.log('Total rows: ' + lastRow);

  if (lastRow <= 1) {
    Logger.log('Tidak ada data layanan');
    return;
  }

  var range = sheet.getRange(2, 1, lastRow - 1, 2); // Ambil kolom ID dan Nama
  var values = range.getValues();

  Logger.log('===== DAFTAR LAYANAN =====');
  for (var i = 0; i < values.length; i++) {
    Logger.log('Row ' + (i+2) + ': ID="' + values[i][0] + '" | Nama="' + values[i][1] + '"');
  }
}
