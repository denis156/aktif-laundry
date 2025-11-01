/**
 * ============================================================
 * TRANSAKSI BACKEND - CRUD FUNCTIONS
 * ============================================================
 */

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
 * Fungsi untuk menghapus transaksi berdasarkan ID
 */
function deleteTransaksi(id) {
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
        sheet.deleteRow(i);
        return { success: true, message: 'Transaksi berhasil dihapus' };
      }
    }

    return { success: false, message: 'Transaksi tidak ditemukan' };

  } catch (error) {
    Logger.log('Error deleteTransaksi: ' + error);
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

/**
 * ============================================================
 * FRONTEND ORCHESTRATOR - Global Variables & Helper Functions
 * ============================================================
 */

function includeTransaksiScripts() {
  var output = HtmlService.createHtmlOutput();

  // Add global variables and helper functions
  output.append('<script>');
  output.append('window.cachedPelanggan = [];');
  output.append('window.cachedLayanan = [];');
  output.append('window.cachedAllLayanan = [];');
  output.append('window.isDataLoaded = false;');

  output.append('function preloadDropdownData() {');
  output.append('  if (window.isDataLoaded) return;');
  output.append('  google.script.run.withSuccessHandler(function(result) {');
  output.append('    if (result && result.success && result.data) {');
  output.append('      window.cachedPelanggan = result.data;');
  output.append('      checkDataLoaded();');
  output.append('    }');
  output.append('  }).withFailureHandler(function(error) {');
  output.append('    console.error("Error pre-loading pelanggan:", error);');
  output.append('  }).getActivePelanggan();');

  output.append('  google.script.run.withSuccessHandler(function(result) {');
  output.append('    if (result && result.success && result.data) {');
  output.append('      window.cachedLayanan = result.data;');
  output.append('      checkDataLoaded();');
  output.append('    }');
  output.append('  }).withFailureHandler(function(error) {');
  output.append('    console.error("Error pre-loading layanan aktif:", error);');
  output.append('  }).getActiveLayanan();');

  output.append('  google.script.run.withSuccessHandler(function(result) {');
  output.append('    if (result && result.success && result.data) {');
  output.append('      window.cachedAllLayanan = result.data;');
  output.append('      checkDataLoaded();');
  output.append('    }');
  output.append('  }).withFailureHandler(function(error) {');
  output.append('    console.error("Error pre-loading all layanan:", error);');
  output.append('  }).getAllLayanan();');
  output.append('}');

  output.append('function checkDataLoaded() {');
  output.append('  if (window.cachedPelanggan.length > 0 && window.cachedLayanan.length > 0 && window.cachedAllLayanan.length > 0) {');
  output.append('    window.isDataLoaded = true;');
  output.append('  }');
  output.append('}');

  output.append('function populatePelangganDropdown(selectId, selectedId) {');
  output.append('  var select = document.getElementById(selectId);');
  output.append('  if (!select) return;');
  output.append('  select.innerHTML = \'<option value="">Pilih Pelanggan</option>\';');
  output.append('  window.cachedPelanggan.forEach(function(item) {');
  output.append('    var option = document.createElement("option");');
  output.append('    option.value = item.id;');
  output.append('    var noHp = item.noHp ? String(item.noHp) : "";');
  output.append('    option.textContent = item.nama + " - " + noHp;');
  output.append('    if (selectedId && String(item.id) === String(selectedId)) {');
  output.append('      option.selected = true;');
  output.append('    }');
  output.append('    select.appendChild(option);');
  output.append('  });');
  output.append('}');

  output.append('function populateLayananDropdown(selectId, selectedId) {');
  output.append('  populateLayananDropdownFromArray(selectId, window.cachedLayanan, selectedId);');
  output.append('}');

  output.append('function populateLayananDropdownFromArray(selectId, dataArray, selectedId) {');
  output.append('  var select = document.getElementById(selectId);');
  output.append('  if (!select) return;');
  output.append('  select.innerHTML = \'<option value="">Pilih Layanan</option>\';');
  output.append('  dataArray.forEach(function(item) {');
  output.append('    var option = document.createElement("option");');
  output.append('    option.value = item.id;');
  output.append('    var statusLabel = (item.status === "Tidak Aktif") ? " [TIDAK AKTIF]" : "";');
  output.append('    option.textContent = item.nama + " - Rp " + Number(item.harga).toLocaleString("id-ID") + statusLabel;');
  output.append('    if (selectedId && String(item.id) === String(selectedId)) {');
  output.append('      option.selected = true;');
  output.append('    }');
  output.append('    select.appendChild(option);');
  output.append('  });');
  output.append('}');

  output.append('setTimeout(function() { preloadDropdownData(); }, 100);');
  output.append('</script>');

  // Include all sub-components using HtmlService
  output.append(HtmlService.createHtmlOutputFromFile('Transaksi/CreateTransaksi').getContent());
  output.append(HtmlService.createHtmlOutputFromFile('Transaksi/EditTransaksi').getContent());
  output.append(HtmlService.createHtmlOutputFromFile('Transaksi/DeleteTransaksi').getContent());
  output.append(HtmlService.createHtmlOutputFromFile('Transaksi/ListTransaksi').getContent());

  return output.getContent();
}
