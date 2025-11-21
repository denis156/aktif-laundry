<?php

namespace App\Observers;

use App\Helper\AddressMetadata;
use App\Models\Kurir;

class KurirObserver
{
    /**
     * Handle the Kurir "saving" event.
     * Auto-sync kolom alamat dari metadata sebelum save
     */
    public function saving(Kurir $kurir): void
    {
        // Auto-sync kolom alamat dari metadata
        $metadata = $kurir->metadata ?? [];

        if (! empty($metadata['detail_alamat']) || ! empty($metadata['kelurahan']) || ! empty($metadata['kecamatan'])) {
            AddressMetadata::syncAlamatColumn($kurir);
        }
    }
}
