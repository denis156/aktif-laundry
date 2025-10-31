/**
 * Fungsi untuk update pelanggan berdasarkan ID
 */
function editPelanggan(id, data) {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Pelanggan');

    if (!sheet) {
      return { success: false, message: 'Sheet Pelanggan tidak ditemukan' };
    }

    // Validasi data
    if (!data.nama || data.nama.trim() === '') {
      return { success: false, message: 'Nama pelanggan harus diisi' };
    }

    if (!data.noHp || data.noHp.trim() === '') {
      return { success: false, message: 'No. HP harus diisi' };
    }

    var lastRow = sheet.getLastRow();

    if (lastRow <= 1) {
      return { success: false, message: 'Data tidak ditemukan' };
    }

    // Cari row berdasarkan ID
    for (var i = 2; i <= lastRow; i++) {
      var currentId = sheet.getRange(i, 1).getValue();

      if (currentId == id) {
        // Update data (tidak update ID, Tanggal Daftar, dan Total Transaksi)
        sheet.getRange(i, 2).setValue(data.nama);
        sheet.getRange(i, 3).setValue(data.noHp);
        sheet.getRange(i, 4).setValue(data.alamat || '');
        sheet.getRange(i, 5).setValue(data.email || '');
        sheet.getRange(i, 8).setValue(data.status || 'Aktif');

        return {
          success: true,
          message: 'Pelanggan berhasil diupdate',
          data: {
            id: id,
            nama: data.nama,
            noHp: data.noHp,
            alamat: data.alamat || '',
            email: data.email || '',
            status: data.status || 'Aktif'
          }
        };
      }
    }

    return { success: false, message: 'Pelanggan tidak ditemukan' };

  } catch (error) {
    Logger.log('Error editPelanggan: ' + error);
    return { success: false, message: error.toString() };
  }
}

/**
 * Fungsi untuk toggle status pelanggan (Aktif/Tidak Aktif)
 */
function toggleStatusPelanggan(id) {
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
        var currentStatus = sheet.getRange(i, 8).getValue();
        var newStatus = currentStatus === 'Aktif' ? 'Tidak Aktif' : 'Aktif';

        sheet.getRange(i, 8).setValue(newStatus);

        return {
          success: true,
          message: 'Status pelanggan berhasil diubah menjadi ' + newStatus,
          status: newStatus
        };
      }
    }

    return { success: false, message: 'Pelanggan tidak ditemukan' };

  } catch (error) {
    Logger.log('Error toggleStatusPelanggan: ' + error);
    return { success: false, message: error.toString() };
  }
}
