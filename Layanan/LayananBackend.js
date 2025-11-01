/**
 * ============================================================
 * LAYANAN BACKEND - CRUD FUNCTIONS
 * ============================================================
 */

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
    var newIdNumber = lastRow > 1 ? lastRow : 1;
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

/**
 * Fungsi untuk mengambil layanan aktif saja (untuk dropdown)
 * Dipanggil dari: Transaksi/CreateTransaksi.html dan Kasir/KasirInit.html
 */
function getActiveLayanan() {
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

    // Filter hanya layanan aktif dan convert ke array of objects
    var activeLayanan = [];
    for (var i = 0; i < values.length; i++) {
      if (values[i][5] === 'Aktif') {  // Kolom 6 (index 5) adalah Status
        activeLayanan.push({
          id: values[i][0],
          nama: values[i][1],
          harga: values[i][2],
          durasi: values[i][3],
          deskripsi: values[i][4],
          status: values[i][5]
        });
      }
    }

    return { success: true, data: activeLayanan };

  } catch (error) {
    Logger.log('Error getActiveLayanan: ' + error);
    return { success: false, message: error.toString() };
  }
}
