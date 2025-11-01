/**
 * ============================================================
 * KASIR BACKEND - TRANSACTION PROCESSING
 * ============================================================
 */

/**
 * Fungsi utama untuk memproses transaksi dari Kasir
 * Menangani create pelanggan baru (jika perlu) dan create transaksi sekaligus
 *
 * @param {Object} data - Data dari form kasir
 * @param {Object} data.pelanggan - Data pelanggan
 * @param {string} data.pelanggan.id - ID pelanggan (jika existing)
 * @param {string} data.pelanggan.nama - Nama pelanggan
 * @param {string} data.pelanggan.noHp - No HP pelanggan
 * @param {string} data.pelanggan.email - Email pelanggan
 * @param {string} data.pelanggan.alamat - Alamat pelanggan
 * @param {boolean} data.pelanggan.isNew - Flag pelanggan baru atau tidak
 * @param {Object} data.transaksi - Data transaksi
 * @param {string} data.transaksi.tanggalMasuk - Tanggal masuk
 * @param {string} data.transaksi.idLayanan - ID layanan
 * @param {number} data.transaksi.berat - Berat cucian (kg)
 * @param {number} data.transaksi.diskon - Diskon (Rp)
 * @param {string} data.transaksi.metodePembayaran - Metode pembayaran
 * @param {string} data.transaksi.catatan - Catatan tambahan
 *
 * @returns {Object} Result object dengan success flag dan data
 */
function processKasirTransaksi(data) {
  try {
    Logger.log('=== START processKasirTransaksi ===');
    Logger.log('Input data: ' + JSON.stringify(data));

    var pelangganId = data.pelanggan.id;

    // Jika pelanggan baru, create pelanggan terlebih dahulu
    if (data.pelanggan.isNew) {
      Logger.log('Creating new pelanggan...');

      var pelangganData = {
        nama: data.pelanggan.nama,
        noHp: data.pelanggan.noHp,
        email: data.pelanggan.email || '',
        alamat: data.pelanggan.alamat || '',
        status: 'Aktif'
      };

      var pelangganResult = createPelanggan(pelangganData);

      if (!pelangganResult || !pelangganResult.success) {
        Logger.log('ERROR: Failed to create pelanggan');
        return {
          success: false,
          message: 'Gagal membuat data pelanggan: ' + (pelangganResult ? pelangganResult.message : 'Unknown error')
        };
      }

      pelangganId = pelangganResult.data.id;
      Logger.log('New pelanggan created with ID: ' + pelangganId);
    }

    // Create transaksi
    Logger.log('Creating transaksi with pelanggan ID: ' + pelangganId);

    var transaksiData = {
      tanggalMasuk: data.transaksi.tanggalMasuk,
      idPelanggan: pelangganId,
      idLayanan: data.transaksi.idLayanan,
      berat: Number(data.transaksi.berat),
      diskon: Number(data.transaksi.diskon) || 0,
      metodePembayaran: data.transaksi.metodePembayaran,
      catatan: data.transaksi.catatan || '',
      status: 'Menunggu'
    };

    var transaksiResult = createTransaksi(transaksiData);

    if (!transaksiResult || !transaksiResult.success) {
      Logger.log('ERROR: Failed to create transaksi');
      return {
        success: false,
        message: 'Gagal membuat transaksi: ' + (transaksiResult ? transaksiResult.message : 'Unknown error')
      };
    }

    Logger.log('=== SUCCESS processKasirTransaksi ===');

    return {
      success: true,
      message: data.pelanggan.isNew
        ? 'Pelanggan baru dan transaksi berhasil dibuat!'
        : 'Transaksi berhasil dibuat!',
      data: {
        pelangganId: pelangganId,
        transaksiId: transaksiResult.data.id,
        isNewPelanggan: data.pelanggan.isNew
      }
    };

  } catch (error) {
    Logger.log('ERROR in processKasirTransaksi: ' + error);
    return {
      success: false,
      message: 'Terjadi kesalahan: ' + error.toString()
    };
  }
}
