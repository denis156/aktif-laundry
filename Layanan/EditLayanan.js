/**
 * Fungsi untuk update layanan berdasarkan ID
 */
function editLayanan(id, data) {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Layanan');
    
    if (!sheet) {
      return { success: false, message: 'Sheet Layanan tidak ditemukan' };
    }
    
    // Validasi data
    if (!data.nama || data.nama.trim() === '') {
      return { success: false, message: 'Nama layanan harus diisi' };
    }
    
    if (!data.harga || data.harga <= 0) {
      return { success: false, message: 'Harga harus lebih dari 0' };
    }
    
    if (!data.durasi || data.durasi <= 0) {
      return { success: false, message: 'Durasi harus lebih dari 0' };
    }
    
    var lastRow = sheet.getLastRow();
    
    if (lastRow <= 1) {
      return { success: false, message: 'Data tidak ditemukan' };
    }
    
    // Cari row berdasarkan ID
    for (var i = 2; i <= lastRow; i++) {
      var currentId = sheet.getRange(i, 1).getValue();
      
      if (currentId == id) {
        // Update data
        sheet.getRange(i, 2).setValue(data.nama);
        sheet.getRange(i, 3).setValue(Number(data.harga));
        sheet.getRange(i, 4).setValue(Number(data.durasi));
        sheet.getRange(i, 5).setValue(data.deskripsi || '');
        sheet.getRange(i, 6).setValue(data.status || 'Aktif');
        
        return { 
          success: true, 
          message: 'Layanan berhasil diupdate',
          data: {
            id: id,
            nama: data.nama,
            harga: data.harga,
            durasi: data.durasi,
            deskripsi: data.deskripsi || '',
            status: data.status || 'Aktif'
          }
        };
      }
    }
    
    return { success: false, message: 'Layanan tidak ditemukan' };
    
  } catch (error) {
    Logger.log('Error editLayanan: ' + error);
    return { success: false, message: error.toString() };
  }
}

/**
 * Fungsi untuk toggle status layanan (Aktif/Tidak Aktif)
 */
function toggleStatusLayanan(id) {
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
        var currentStatus = sheet.getRange(i, 6).getValue();
        var newStatus = currentStatus === 'Aktif' ? 'Tidak Aktif' : 'Aktif';
        
        sheet.getRange(i, 6).setValue(newStatus);
        
        return { 
          success: true, 
          message: 'Status layanan berhasil diubah menjadi ' + newStatus,
          status: newStatus
        };
      }
    }
    
    return { success: false, message: 'Layanan tidak ditemukan' };
    
  } catch (error) {
    Logger.log('Error toggleStatusLayanan: ' + error);
    return { success: false, message: error.toString() };
  }
}