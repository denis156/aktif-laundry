<?php

namespace App\Observers;

use App\Helper\AddressMetadata;
use App\Models\Pelanggan;

class PelangganObserver
{
    /**
     * Handle the Pelanggan "saving" event.
     * Auto-sync kolom alamat dari metadata sebelum save
     */
    public function saving(Pelanggan $pelanggan): void
    {
        // Auto-sync kolom alamat dari metadata
        $metadata = $pelanggan->metadata ?? [];

        if (! empty($metadata['detail_alamat']) || ! empty($metadata['kelurahan']) || ! empty($metadata['kecamatan'])) {
            AddressMetadata::syncAlamatColumn($pelanggan);
        }
    }
}
