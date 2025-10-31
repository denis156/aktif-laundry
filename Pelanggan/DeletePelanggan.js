/**
 * Fungsi untuk menghapus pelanggan berdasarkan ID
 */
function deletePelanggan(id) {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Pelanggan');

    if (!sheet) {
      return { success: false, message: 'Sheet Pelanggan tidak ditemukan' };
    }

    var lastRow = sheet.getLastRow();

    if (lastRow <= 1) {
      return { success: false, message: 'Data tidak ditemukan' };
    }

    // Cari row berdasarkan ID
    for (var i = 2; i <= lastRow; i++) {
      var currentId = sheet.getRange(i, 1).getValue();

      if (currentId == id) {
        sheet.deleteRow(i);
        return { success: true, message: 'Pelanggan berhasil dihapus' };
      }
    }

    return { success: false, message: 'Pelanggan tidak ditemukan' };

  } catch (error) {
    Logger.log('Error deletePelanggan: ' + error);
    return { success: false, message: error.toString() };
  }
}
