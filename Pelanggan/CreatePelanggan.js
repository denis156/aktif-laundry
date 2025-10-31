/**
 * Fungsi untuk menambah pelanggan baru
 */
function createPelanggan(data) {
  try {
    Logger.log('=== START createPelanggan ===');
    Logger.log('Input data: ' + JSON.stringify(data));

    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Pelanggan');

    if (!sheet) {
      Logger.log('ERROR: Sheet Pelanggan tidak ditemukan');
      return { success: false, message: 'Sheet Pelanggan tidak ditemukan' };
    }

    // Validasi data
    if (!data.nama || data.nama.trim() === '') {
      Logger.log('ERROR: Nama kosong');
      return { success: false, message: 'Nama pelanggan harus diisi' };
    }

    if (!data.noHp || data.noHp.trim() === '') {
      Logger.log('ERROR: NoHP kosong');
      return { success: false, message: 'No. HP harus diisi' };
    }

    // Generate ID otomatis
    var lastRow = sheet.getLastRow();
    var newIdNumber = lastRow > 1 ? lastRow : 1;
    var newId = 'PLG' + String(newIdNumber).padStart(3, '0');

    Logger.log('Generated ID: ' + newId);
    Logger.log('Alamat yang akan disimpan: "' + (data.alamat || '') + '"');

    // Tambah data ke row baru
    var newRow = [
      newId,
      data.nama,
      data.noHp,
      data.alamat || '',
      data.email || '',
      new Date(), // Tanggal Daftar
      0, // Total Transaksi (default 0)
      data.status || 'Aktif'
    ];

    Logger.log('New row data: ' + JSON.stringify(newRow));
    sheet.appendRow(newRow);
    Logger.log('Row appended successfully');

    return {
      success: true,
      message: 'Pelanggan berhasil ditambahkan',
      data: {
        id: newId,
        nama: data.nama,
        noHp: data.noHp,
        alamat: data.alamat || '',
        email: data.email || '',
        status: data.status || 'Aktif'
      }
    };

  } catch (error) {
    Logger.log('Error createPelanggan: ' + error);
    return { success: false, message: error.toString() };
  }
}
