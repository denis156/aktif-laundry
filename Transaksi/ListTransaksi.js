/**
 * Fungsi untuk mengambil semua data transaksi dari sheet
 */
function getAllTransaksi() {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Transaksi');

    if (!sheet) {
      return { success: false, message: 'Sheet Transaksi tidak ditemukan' };
    }

    var lastRow = sheet.getLastRow();

    // Jika tidak ada data
    if (lastRow <= 1) {
      return { success: true, data: [] };
    }

    // Ambil semua data (skip header)
    var range = sheet.getRange(2, 1, lastRow - 1, 15);
    var values = range.getValues();

    // Convert ke array of objects
    var transaksiList = [];
    for (var i = 0; i < values.length; i++) {
      transaksiList.push({
        id: values[i][0],
        tanggalMasuk: values[i][1],
        idPelanggan: values[i][2],
        namaPelanggan: values[i][3],
        idLayanan: values[i][4],
        namaLayanan: values[i][5],
        berat: values[i][6],
        hargaPerKg: values[i][7],
        subtotal: values[i][8],
        diskon: values[i][9],
        total: values[i][10],
        metodePembayaran: values[i][11],
        tanggalSelesai: values[i][12],
        status: values[i][13],
        catatan: values[i][14]
      });
    }

    // Reverse array agar data terbaru di atas (DESC)
    transaksiList.reverse();

    return { success: true, data: transaksiList };

  } catch (error) {
    Logger.log('Error getAllTransaksi: ' + error);
    return { success: false, message: error.toString() };
  }
}

/**
 * Fungsi untuk mengambil transaksi berdasarkan ID
 */
function getTransaksiById(id) {
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

    var range = sheet.getRange(2, 1, lastRow - 1, 15);
    var values = range.getValues();

    for (var i = 0; i < values.length; i++) {
      if (values[i][0] == id) {
        return {
          success: true,
          data: {
            id: values[i][0],
            tanggalMasuk: values[i][1],
            idPelanggan: values[i][2],
            namaPelanggan: values[i][3],
            idLayanan: values[i][4],
            namaLayanan: values[i][5],
            berat: values[i][6],
            hargaPerKg: values[i][7],
            subtotal: values[i][8],
            diskon: values[i][9],
            total: values[i][10],
            metodePembayaran: values[i][11],
            tanggalSelesai: values[i][12],
            status: values[i][13],
            catatan: values[i][14]
          }
        };
      }
    }

    return { success: false, message: 'Transaksi tidak ditemukan' };

  } catch (error) {
    Logger.log('Error getTransaksiById: ' + error);
    return { success: false, message: error.toString() };
  }
}

/**
 * Fungsi untuk mengambil transaksi berdasarkan status
 */
function getTransaksiByStatus(status) {
  try {
    var result = getAllTransaksi();

    if (!result.success) {
      return result;
    }

    var filteredTransaksi = result.data.filter(function(transaksi) {
      return transaksi.status === status;
    });

    return { success: true, data: filteredTransaksi };

  } catch (error) {
    Logger.log('Error getTransaksiByStatus: ' + error);
    return { success: false, message: error.toString() };
  }
}

/**
 * Fungsi untuk mengambil transaksi berdasarkan pelanggan
 */
function getTransaksiByPelanggan(idPelanggan) {
  try {
    var result = getAllTransaksi();

    if (!result.success) {
      return result;
    }

    var filteredTransaksi = result.data.filter(function(transaksi) {
      return transaksi.idPelanggan === idPelanggan;
    });

    return { success: true, data: filteredTransaksi };

  } catch (error) {
    Logger.log('Error getTransaksiByPelanggan: ' + error);
    return { success: false, message: error.toString() };
  }
}
