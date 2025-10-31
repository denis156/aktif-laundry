/**
 * Fungsi untuk mengambil semua data layanan dari sheet
 */
function getAllLayanan() {
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
    
    // Convert ke array of objects
    var layananList = [];
    for (var i = 0; i < values.length; i++) {
      layananList.push({
        id: values[i][0],
        nama: values[i][1],
        harga: values[i][2],
        durasi: values[i][3],
        deskripsi: values[i][4],
        status: values[i][5]
      });
    }

    // Reverse array agar data terbaru di atas (DESC)
    layananList.reverse();

    return { success: true, data: layananList };
    
  } catch (error) {
    Logger.log('Error getAllLayanan: ' + error);
    return { success: false, message: error.toString() };
  }
}

/**
 * Fungsi untuk mengambil layanan berdasarkan ID
 */
function getLayananById(id) {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Layanan');
    
    if (!sheet) {
      return { success: false, message: 'Sheet Layanan tidak ditemukan' };
    }
    
    var lastRow = sheet.getLastRow();
    
    if (lastRow <= 1) {
      return { success: false, message: 'Data tidak ditemukan' };
    }
    
    var range = sheet.getRange(2, 1, lastRow - 1, 6);
    var values = range.getValues();
    
    for (var i = 0; i < values.length; i++) {
      if (values[i][0] == id) {
        return {
          success: true,
          data: {
            id: values[i][0],
            nama: values[i][1],
            harga: values[i][2],
            durasi: values[i][3],
            deskripsi: values[i][4],
            status: values[i][5]
          }
        };
      }
    }
    
    return { success: false, message: 'Layanan tidak ditemukan' };
    
  } catch (error) {
    Logger.log('Error getLayananById: ' + error);
    return { success: false, message: error.toString() };
  }
}

/**
 * CATATAN: Fungsi getActiveLayanan() dipindahkan ke Code.js
 * agar self-contained dan tidak bergantung pada getAllLayanan()
 * Lihat Code.js:157 untuk implementasinya
 */

/**
 * Fungsi untuk format currency Rupiah
 */
function formatRupiah(amount) {
  return 'Rp ' + Number(amount).toLocaleString('id-ID');
}