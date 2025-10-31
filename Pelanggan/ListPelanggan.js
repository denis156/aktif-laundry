/**
 * Fungsi untuk mengambil semua data pelanggan dari sheet
 */
function getAllPelanggan() {
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

    // Convert ke array of objects
    var pelangganList = [];
    for (var i = 0; i < values.length; i++) {
      pelangganList.push({
        id: values[i][0],
        nama: values[i][1],
        noHp: String(values[i][2]),  // Convert to String to prevent serialization issues
        alamat: values[i][3],
        email: values[i][4],
        tanggalDaftar: values[i][5],
        totalTransaksi: values[i][6],
        status: values[i][7]
      });
    }

    // Reverse array agar data terbaru di atas (DESC)
    pelangganList.reverse();

    return { success: true, data: pelangganList };

  } catch (error) {
    Logger.log('Error getAllPelanggan: ' + error);
    return { success: false, message: error.toString() };
  }
}

/**
 * Fungsi untuk mengambil pelanggan berdasarkan ID
 */
function getPelangganById(id) {
  try {
    Logger.log('getPelangganById called with ID: ' + id);
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Pelanggan');

    if (!sheet) {
      Logger.log('ERROR: Sheet Pelanggan tidak ditemukan');
      return { success: false, message: 'Sheet Pelanggan tidak ditemukan' };
    }

    var lastRow = sheet.getLastRow();
    Logger.log('Last row in Pelanggan sheet: ' + lastRow);

    if (lastRow <= 1) {
      Logger.log('ERROR: Tidak ada data di sheet');
      return { success: false, message: 'Data tidak ditemukan' };
    }

    var range = sheet.getRange(2, 1, lastRow - 1, 8);
    var values = range.getValues();

    for (var i = 0; i < values.length; i++) {
      Logger.log('Checking row ' + i + ': ID = "' + values[i][0] + '" vs search ID = "' + id + '"');
      if (values[i][0] == id) {
        Logger.log('FOUND pelanggan at row ' + i);
        return {
          success: true,
          data: {
            id: values[i][0],
            nama: values[i][1],
            noHp: String(values[i][2]),  // Convert to String to prevent serialization issues
            alamat: values[i][3],
            email: values[i][4],
            tanggalDaftar: values[i][5],
            totalTransaksi: values[i][6],
            status: values[i][7]
          }
        };
      }
    }

    Logger.log('ERROR: Pelanggan dengan ID "' + id + '" tidak ditemukan');
    return { success: false, message: 'Pelanggan tidak ditemukan' };

  } catch (error) {
    Logger.log('Error getPelangganById: ' + error);
    return { success: false, message: error.toString() };
  }
}

/**
 * CATATAN: Fungsi getActivePelanggan() dipindahkan ke Code.js
 * agar self-contained dan tidak bergantung pada getAllPelanggan()
 * Lihat Code.js:108 untuk implementasinya
 */
