/**
 * Fungsi untuk update transaksi berdasarkan ID
 */
function editTransaksi(id, data) {
  try {
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Transaksi');

    if (!sheet) {
      return { success: false, message: 'Sheet Transaksi tidak ditemukan' };
    }

    // Validasi data
    if (!data.idPelanggan || data.idPelanggan.trim() === '') {
      return { success: false, message: 'Pelanggan harus dipilih' };
    }

    if (!data.idLayanan || data.idLayanan.trim() === '') {
      return { success: false, message: 'Layanan harus dipilih' };
    }

    if (!data.berat || data.berat <= 0) {
      return { success: false, message: 'Berat harus lebih dari 0' };
    }

    // Get data pelanggan dan layanan
    var pelanggan = getPelangganById(data.idPelanggan);
    var layanan = getLayananById(data.idLayanan);

    if (!pelanggan.success) {
      return { success: false, message: 'Data pelanggan tidak ditemukan' };
    }

    if (!layanan.success) {
      return { success: false, message: 'Data layanan tidak ditemukan' };
    }

    var lastRow = sheet.getLastRow();

    if (lastRow <= 1) {
      return { success: false, message: 'Data tidak ditemukan' };
    }

    // Hitung subtotal dan total
    var hargaPerKg = layanan.data.harga;
    var subtotal = Number(data.berat) * hargaPerKg;
    var diskon = Number(data.diskon) || 0;
    var total = subtotal - diskon;

    // Parse tanggal selesai
    var tanggalSelesai = new Date(data.tanggalSelesai);

    // Cari row berdasarkan ID
    for (var i = 2; i <= lastRow; i++) {
      var currentId = sheet.getRange(i, 1).getValue();

      if (currentId == id) {
        // Update data (tidak update ID dan Tanggal Masuk)
        sheet.getRange(i, 3).setValue(data.idPelanggan);
        sheet.getRange(i, 4).setValue(pelanggan.data.nama);
        sheet.getRange(i, 5).setValue(data.idLayanan);
        sheet.getRange(i, 6).setValue(layanan.data.nama);
        sheet.getRange(i, 7).setValue(Number(data.berat));
        sheet.getRange(i, 8).setValue(hargaPerKg);
        sheet.getRange(i, 9).setValue(subtotal);
        sheet.getRange(i, 10).setValue(diskon);
        sheet.getRange(i, 11).setValue(total);
        sheet.getRange(i, 12).setValue(data.metodePembayaran || 'Tunai');
        sheet.getRange(i, 13).setValue(tanggalSelesai);
        sheet.getRange(i, 14).setValue(data.status || 'Menunggu');
        sheet.getRange(i, 15).setValue(data.catatan || '');

        return {
          success: true,
          message: 'Transaksi berhasil diupdate',
          data: {
            id: id,
            total: total
          }
        };
      }
    }

    return { success: false, message: 'Transaksi tidak ditemukan' };

  } catch (error) {
    Logger.log('Error editTransaksi: ' + error);
    return { success: false, message: error.toString() };
  }
}

/**
 * Fungsi untuk update status transaksi
 */
function updateStatusTransaksi(id, status) {
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
        sheet.getRange(i, 14).setValue(status);

        return {
          success: true,
          message: 'Status transaksi berhasil diubah menjadi ' + status,
          status: status
        };
      }
    }

    return { success: false, message: 'Transaksi tidak ditemukan' };

  } catch (error) {
    Logger.log('Error updateStatusTransaksi: ' + error);
    return { success: false, message: error.toString() };
  }
}
