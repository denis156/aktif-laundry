/**
 * Fungsi untuk menghapus layanan berdasarkan ID
 */
function deleteLayanan(id) {
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
    
    // Cari row berdasarkan ID
    for (var i = 2; i <= lastRow; i++) {
      var currentId = sheet.getRange(i, 1).getValue();
      
      if (currentId == id) {
        sheet.deleteRow(i);
        return { success: true, message: 'Layanan berhasil dihapus' };
      }
    }
    
    return { success: false, message: 'Layanan tidak ditemukan' };
    
  } catch (error) {
    Logger.log('Error deleteLayanan: ' + error);
    return { success: false, message: error.toString() };
  }
}