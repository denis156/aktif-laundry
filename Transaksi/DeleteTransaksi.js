/**
 * Fungsi untuk menghapus transaksi berdasarkan ID
 */
function deleteTransaksi(id) {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Transaksi');

    if (!sheet) {
      return { success: false, message: 'Sheet Transaksi tidak ditemukan' };
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
        return { success: true, message: 'Transaksi berhasil dihapus' };
      }
    }

    return { success: false, message: 'Transaksi tidak ditemukan' };

  } catch (error) {
    Logger.log('Error deleteTransaksi: ' + error);
    return { success: false, message: error.toString() };
  }
}
