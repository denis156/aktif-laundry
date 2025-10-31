/**
 * Fungsi untuk menambah transaksi baru
 */
function createTransaksi(data) {
  try {
    Logger.log('=== START createTransaksi ===');
    Logger.log('Input data: ' + JSON.stringify(data));

    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName('Transaksi');

    if (!sheet) {
      Logger.log('ERROR: Sheet Transaksi tidak ditemukan');
      return { success: false, message: 'Sheet Transaksi tidak ditemukan' };
    }

    // Validasi data
    if (!data.idPelanggan || data.idPelanggan.trim() === '') {
      Logger.log('ERROR: idPelanggan kosong');
      return { success: false, message: 'Pelanggan harus dipilih' };
    }

    if (!data.idLayanan || data.idLayanan.trim() === '') {
      Logger.log('ERROR: idLayanan kosong');
      return { success: false, message: 'Layanan harus dipilih' };
    }

    if (!data.berat || data.berat <= 0) {
      Logger.log('ERROR: Berat invalid: ' + data.berat);
      return { success: false, message: 'Berat harus lebih dari 0' };
    }

    // Get data pelanggan dan layanan
    Logger.log('Mencari pelanggan dengan ID: ' + data.idPelanggan);
    var pelanggan = getPelangganById(data.idPelanggan);
    Logger.log('Hasil pelanggan: ' + JSON.stringify(pelanggan));

    Logger.log('Mencari layanan dengan ID: ' + data.idLayanan);
    var layanan = getLayananById(data.idLayanan);
    Logger.log('Hasil layanan: ' + JSON.stringify(layanan));

    if (!pelanggan.success) {
      return { success: false, message: 'Data pelanggan tidak ditemukan' };
    }

    if (!layanan.success) {
      return { success: false, message: 'Data layanan tidak ditemukan' };
    }

    // Generate ID otomatis
    var lastRow = sheet.getLastRow();
    var newIdNumber = lastRow > 1 ? lastRow : 1;
    var newId = 'TRX' + String(newIdNumber).padStart(3, '0');

    // Hitung subtotal dan total
    var hargaPerKg = layanan.data.harga;
    var subtotal = Number(data.berat) * hargaPerKg;
    var diskon = Number(data.diskon) || 0;
    var total = subtotal - diskon;

    // Hitung tanggal selesai berdasarkan durasi layanan
    var tanggalMasuk = new Date(data.tanggalMasuk || new Date());
    var durasi = layanan.data.durasi; // dalam jam
    var tanggalSelesai = new Date(tanggalMasuk);
    tanggalSelesai.setHours(tanggalSelesai.getHours() + Number(durasi));

    // Tambah data ke row baru
    var newRow = [
      newId,
      tanggalMasuk,
      data.idPelanggan,
      pelanggan.data.nama,
      data.idLayanan,
      layanan.data.nama,
      Number(data.berat),
      hargaPerKg,
      subtotal,
      diskon,
      total,
      data.metodePembayaran || 'Tunai',
      tanggalSelesai,
      data.status || 'Menunggu',
      data.catatan || ''
    ];

    sheet.appendRow(newRow);

    Logger.log('Transaksi berhasil ditambahkan dengan ID: ' + newId);
    Logger.log('=== END createTransaksi SUCCESS ===');

    var result = {
      success: true,
      message: 'Transaksi berhasil ditambahkan',
      data: {
        id: newId,
        total: total,
        tanggalSelesai: Utilities.formatDate(tanggalSelesai, Session.getScriptTimeZone(), 'yyyy-MM-dd HH:mm:ss')
      }
    };

    Logger.log('Return result: ' + JSON.stringify(result));
    return result;

  } catch (error) {
    Logger.log('=== ERROR createTransaksi ===');
    Logger.log('Error: ' + error);
    Logger.log('Stack: ' + error.stack);
    return { success: false, message: error.toString() };
  }
}
