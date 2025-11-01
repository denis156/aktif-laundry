/**
 * ============================================================
 * PELANGGAN BACKEND - CRUD FUNCTIONS
 * ============================================================
 */

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

/**
 * Fungsi untuk mengambil pelanggan aktif saja (untuk dropdown)
 * Dipanggil dari: Transaksi/CreateTransaksi.html, Transaksi/EditTransaksi.html, dan Kasir/KasirInit.html
 */
function getActivePelanggan() {
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

    // Filter hanya pelanggan aktif dan convert ke array of objects
    var activePelanggan = [];
    for (var i = 0; i < values.length; i++) {
      if (values[i][7] === 'Aktif') {  // Kolom 8 (index 7) adalah Status
        activePelanggan.push({
          id: values[i][0] ? String(values[i][0]) : '',
          nama: values[i][1] ? String(values[i][1]) : '',
          noHp: values[i][2] ? String(values[i][2]) : '',
          alamat: values[i][3] ? String(values[i][3]) : '',
          email: values[i][4] ? String(values[i][4]) : '',
          tanggalDaftar: values[i][5] ? values[i][5].toString() : '',
          totalTransaksi: values[i][6] ? Number(values[i][6]) : 0,
          status: values[i][7] ? String(values[i][7]) : ''
        });
      }
    }

    return { success: true, data: activePelanggan };

  } catch (error) {
    Logger.log('ERROR in getActivePelanggan: ' + error);
    return { success: false, message: error.toString() };
  }
}
