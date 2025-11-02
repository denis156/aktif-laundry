/**
 * Fungsi untuk mengisi semua sheets dengan data dummy
 * Run fungsi ini setelah setupSheets() untuk testing
 */
function insertDummyData() {
  try {
    insertDummyPelanggan();
    insertDummyLayanan();
    insertDummyTransaksi();
    insertDummySetting();
    
    SpreadsheetApp.getUi().alert('Berhasil!', 'Data dummy berhasil ditambahkan.', SpreadsheetApp.getUi().ButtonSet.OK);
    Logger.log('Insert dummy data selesai');
    
  } catch (error) {
    Logger.log('Error insert dummy data: ' + error);
    SpreadsheetApp.getUi().alert('Error', 'Terjadi kesalahan: ' + error, SpreadsheetApp.getUi().ButtonSet.OK);
  }
}

/**
 * Insert dummy data Pelanggan
 */
function insertDummyPelanggan() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName('Pelanggan');
  
  if (!sheet) {
    throw new Error('Sheet Pelanggan tidak ditemukan');
  }
  
  var data = [
    ['PLG001', 'Budi Santoso', '81234567890', 'Jl. Merdeka No. 10, Jakarta', 'budi@email.com', new Date('2025-01-15'), 5, 'Aktif'],
    ['PLG002', 'Siti Nurhaliza', '81234567891', 'Jl. Sudirman No. 20, Jakarta', 'siti@email.com', new Date('2025-02-10'), 3, 'Aktif'],
    ['PLG003', 'Andi Wijaya', '81234567892', 'Jl. Thamrin No. 15, Jakarta', 'andi@email.com', new Date('2025-03-05'), 8, 'Aktif'],
    ['PLG004', 'Dewi Lestari', '81234567893', 'Jl. Gatot Subroto No. 25, Jakarta', 'dewi@email.com', new Date('2025-04-12'), 2, 'Aktif'],
    ['PLG005', 'Rudi Hermawan', '81234567894', 'Jl. Kuningan No. 30, Jakarta', 'rudi@email.com', new Date('2025-05-20'), 1, 'Tidak Aktif'],
    ['PLG006', 'Maya Sari', '81234567895', 'Jl. Casablanca No. 40, Jakarta', 'maya@email.com', new Date('2025-06-18'), 6, 'Aktif'],
    ['PLG007', 'Hendra Gunawan', '81234567896', 'Jl. Menteng No. 12, Jakarta', 'hendra@email.com', new Date('2025-07-25'), 4, 'Aktif'],
    ['PLG008', 'Rina Kartika', '81234567897', 'Jl. Senopati No. 8, Jakarta', 'rina@email.com', new Date('2025-08-14'), 7, 'Aktif'],
    ['PLG009', 'Agus Salim', '81234567898', 'Jl. Kebayoran No. 18, Jakarta', 'agus@email.com', new Date('2025-09-10'), 3, 'Aktif'],
    ['PLG010', 'Linda Permata', '81234567899', 'Jl. Blok M No. 22, Jakarta', 'linda@email.com', new Date('2025-10-05'), 5, 'Aktif']
  ];
  
  sheet.getRange(2, 1, data.length, data[0].length).setValues(data);
  Logger.log('Dummy data Pelanggan inserted');
}

/**
 * Insert dummy data Layanan
 */
function insertDummyLayanan() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName('Layanan');
  
  if (!sheet) {
    throw new Error('Sheet Layanan tidak ditemukan');
  }
  
  var data = [
    ['LYN001', 'Cuci Kering', 5000, 24, 'Cuci dan kering saja', 'Aktif'],
    ['LYN002', 'Cuci Setrika', 7000, 48, 'Cuci, kering, dan setrika rapi', 'Aktif'],
    ['LYN003', 'Setrika Saja', 4000, 12, 'Setrika saja tanpa cuci', 'Aktif'],
    ['LYN004', 'Express 6 Jam', 12000, 6, 'Layanan kilat 6 jam jadi', 'Aktif'],
    ['LYN005', 'Express 12 Jam', 10000, 12, 'Layanan kilat 12 jam jadi', 'Aktif'],
    ['LYN006', 'Cuci Karpet', 15000, 72, 'Cuci karpet dan permadani', 'Aktif'],
    ['LYN007', 'Cuci Sepatu', 25000, 48, 'Cuci sepatu sneakers', 'Aktif'],
    ['LYN008', 'Cuci Boneka', 20000, 24, 'Cuci boneka dan mainan', 'Tidak Aktif']
  ];
  
  sheet.getRange(2, 1, data.length, data[0].length).setValues(data);
  Logger.log('Dummy data Layanan inserted');
}

/**
 * Insert dummy data Transaksi
 */
function insertDummyTransaksi() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName('Transaksi');
  
  if (!sheet) {
    throw new Error('Sheet Transaksi tidak ditemukan');
  }
  
  var data = [
    ['TRX001', new Date('2025-10-20'), 'PLG001', 'Budi Santoso', 'LYN001', 'Cuci Kering', 5, 5000, 25000, 0, 25000, 'Tunai', new Date('2025-10-21'), 'Selesai', 'Pesanan regular'],
    ['TRX002', new Date('2025-10-21'), 'PLG002', 'Siti Nurhaliza', 'LYN002', 'Cuci Setrika', 3, 7000, 21000, 2000, 19000, 'Transfer', new Date('2025-10-23'), 'Selesai', 'Member discount 10%'],
    ['TRX003', new Date('2025-10-22'), 'PLG003', 'Andi Wijaya', 'LYN004', 'Express 6 Jam', 4, 12000, 48000, 0, 48000, 'QRIS', new Date('2025-10-22'), 'Diambil', 'Express service'],
    ['TRX004', new Date('2025-10-23'), 'PLG004', 'Dewi Lestari', 'LYN001', 'Cuci Kering', 7, 5000, 35000, 0, 35000, 'Tunai', new Date('2025-10-24'), 'Selesai', ''],
    ['TRX005', new Date('2025-10-24'), 'PLG006', 'Maya Sari', 'LYN003', 'Setrika Saja', 2, 4000, 8000, 0, 8000, 'Debit', new Date('2025-10-24'), 'Diambil', 'Setrika rapi'],
    ['TRX006', new Date('2025-10-25'), 'PLG007', 'Hendra Gunawan', 'LYN002', 'Cuci Setrika', 6, 7000, 42000, 4000, 38000, 'Transfer', new Date('2025-10-27'), 'Proses', 'Member discount'],
    ['TRX007', new Date('2025-10-26'), 'PLG008', 'Rina Kartika', 'LYN005', 'Express 12 Jam', 3, 10000, 30000, 0, 30000, 'QRIS', new Date('2025-10-27'), 'Proses', 'Butuh cepat'],
    ['TRX008', new Date('2025-10-27'), 'PLG009', 'Agus Salim', 'LYN001', 'Cuci Kering', 4, 5000, 20000, 0, 20000, 'Tunai', new Date('2025-10-28'), 'Menunggu', ''],
    ['TRX009', new Date('2025-10-28'), 'PLG010', 'Linda Permata', 'LYN006', 'Cuci Karpet', 8, 15000, 120000, 10000, 110000, 'Transfer', new Date('2025-10-31'), 'Proses', 'Karpet besar 2x3m'],
    ['TRX010', new Date('2025-10-29'), 'PLG001', 'Budi Santoso', 'LYN002', 'Cuci Setrika', 5, 7000, 35000, 0, 35000, 'Tunai', new Date('2025-10-31'), 'Menunggu', 'Pesanan kedua']
  ];
  
  sheet.getRange(2, 1, data.length, data[0].length).setValues(data);
  Logger.log('Dummy data Transaksi inserted');
}

/**
 * Insert dummy data Setting
 */
function insertDummySetting() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName('Setting');
  
  if (!sheet) {
    throw new Error('Sheet Setting tidak ditemukan');
  }
  
  var data = [
    ['nama_toko', 'Aktif Laundry', 'Nama toko laundry'],
    ['alamat', 'Jl. Merdeka No. 123, Jakarta Pusat', 'Alamat toko'],
    ['telepon', '021-12345678', 'Nomor telepon toko'],
    ['whatsapp', '81234567890', 'Nomor WhatsApp (format: 8xxx tanpa 0)'],
    ['email', 'info@aktiflaundry.com', 'Email toko'],
    ['minimal_berat', '1', 'Minimal berat cucian (Kg)'],
    ['format_id_transaksi', 'TRX', 'Prefix ID transaksi'],
    ['format_id_pelanggan', 'PLG', 'Prefix ID pelanggan'],
    ['format_id_layanan', 'LYN', 'Prefix ID layanan'],
    ['jam_buka', '01:00', 'Jam buka toko'],
    ['jam_tutup', '24:59', 'Jam tutup toko'],
    ['thermal_paper_width', '57', 'Lebar kertas thermal printer (mm)'],
    ['thermal_font_size', '8', 'Ukuran font struk thermal (px)'],
    ['thermal_line_height', '1.1', 'Tinggi baris struk thermal'],
    ['thermal_padding', '1.5', 'Padding kiri-kanan struk (mm)']
  ];
  
  sheet.getRange(2, 1, data.length, data[0].length).setValues(data);
  Logger.log('Dummy data Setting inserted');
}

/**
 * Fungsi untuk menghapus semua data (kecuali header)
 */
function clearAllData() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var ui = SpreadsheetApp.getUi();
  
  var response = ui.alert(
    'Konfirmasi Hapus Data',
    'Apakah Anda yakin ingin menghapus semua data? (Header tetap ada)',
    ui.ButtonSet.YES_NO
  );
  
  if (response == ui.Button.YES) {
    try {
      clearSheetData('Pelanggan');
      clearSheetData('Layanan');
      clearSheetData('Transaksi');
      clearSheetData('Setting');
      
      ui.alert('Berhasil!', 'Semua data berhasil dihapus.', ui.ButtonSet.OK);
      Logger.log('Clear all data selesai');
      
    } catch (error) {
      Logger.log('Error clear data: ' + error);
      ui.alert('Error', 'Terjadi kesalahan: ' + error, ui.ButtonSet.OK);
    }
  }
}

/**
 * Helper function - Clear data dari sheet tertentu
 */
function clearSheetData(sheetName) {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName(sheetName);
  
  if (!sheet) {
    throw new Error('Sheet ' + sheetName + ' tidak ditemukan');
  }
  
  var lastRow = sheet.getLastRow();
  
  if (lastRow > 1) {
    sheet.getRange(2, 1, lastRow - 1, sheet.getLastColumn()).clearContent();
  }
  
  Logger.log('Data sheet ' + sheetName + ' cleared');
}