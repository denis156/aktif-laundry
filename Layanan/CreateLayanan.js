/**
 * Fungsi untuk menambah layanan baru
 */
function createLayanan(data) {
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
    
    // Generate ID otomatis
    var lastRow = sheet.getLastRow();
    var newIdNumber = lastRow > 1 ? lastRow : 1; // Jika hanya header (row 1), mulai dari 1
    var newId = 'LYN' + String(newIdNumber).padStart(3, '0');
    
    // Tambah data ke row baru
    var newRow = [
      newId,
      data.nama,
      Number(data.harga),
      Number(data.durasi),
      data.deskripsi || '',
      data.status || 'Aktif'
    ];
    
    sheet.appendRow(newRow);
    
    return { 
      success: true, 
      message: 'Layanan berhasil ditambahkan', 
      data: {
        id: newId,
        nama: data.nama,
        harga: data.harga,
        durasi: data.durasi,
        deskripsi: data.deskripsi || '',
        status: data.status || 'Aktif'
      }
    };
    
  } catch (error) {
    Logger.log('Error createLayanan: ' + error);
    return { success: false, message: error.toString() };
  }
}