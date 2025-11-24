<?php

declare(strict_types=1);

namespace App\Helper\Dashboard;

use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChartDataHelper
{
    /**
     * Get data transaksi untuk 7 hari terakhir
     */
    public static function getLast7DaysData(): Collection
    {
        return collect()->times(7, function ($i) {
            $date = now()->subDays(7 - $i);

            return [
                'label' => $date->locale('id')->isoFormat('ddd, D MMM'),
                'count' => Transaksi::whereDate('tanggal_masuk', $date)->count(),
                'berat' => self::getTotalBeratForDate($date),
                'item' => self::getTotalItemForDate($date),
            ];
        });
    }

    /**
     * Get total berat untuk tanggal tertentu
     */
    private static function getTotalBeratForDate(Carbon $date): float
    {
        return (float) DB::table('transaksi_layanan')
            ->join('transaksi', 'transaksi_layanan.transaksi_id', '=', 'transaksi.id')
            ->whereDate('transaksi.tanggal_masuk', $date)
            ->whereNull('transaksi_layanan.deleted_at')
            ->sum('transaksi_layanan.berat_kg');
    }

    /**
     * Get total item untuk tanggal tertentu
     */
    private static function getTotalItemForDate(Carbon $date): int
    {
        return (int) DB::table('transaksi_layanan')
            ->join('transaksi', 'transaksi_layanan.transaksi_id', '=', 'transaksi.id')
            ->whereDate('transaksi.tanggal_masuk', $date)
            ->whereNull('transaksi_layanan.deleted_at')
            ->sum('transaksi_layanan.jumlah_satuan');
    }

    /**
     * Get data transaksi untuk bulan ini (per hari)
     */
    public static function getCurrentMonthData(): Collection
    {
        $startOfMonth = now()->startOfMonth();
        $daysInMonth = now()->daysInMonth;

        return collect()->times($daysInMonth, function ($i) use ($startOfMonth) {
            $date = $startOfMonth->copy()->addDays($i - 1);

            return [
                'label' => $date->locale('id')->isoFormat('D MMM'),
                'count' => Transaksi::whereDate('tanggal_masuk', $date)->count(),
                'berat' => self::getTotalBeratForDate($date),
                'item' => self::getTotalItemForDate($date),
            ];
        });
    }

    /**
     * Get data transaksi untuk 12 bulan terakhir
     */
    public static function getLast12MonthsData(): Collection
    {
        return collect()->times(12, function ($i) {
            $date = now()->subMonths(12 - $i);

            return [
                'label' => $date->locale('id')->isoFormat('MMM YYYY'),
                'count' => Transaksi::whereMonth('tanggal_masuk', $date->month)
                    ->whereYear('tanggal_masuk', $date->year)
                    ->count(),
                'berat' => self::getTotalBeratForMonth($date),
                'item' => self::getTotalItemForMonth($date),
            ];
        });
    }

    /**
     * Get total berat untuk bulan tertentu
     */
    private static function getTotalBeratForMonth(Carbon $date): float
    {
        return (float) DB::table('transaksi_layanan')
            ->join('transaksi', 'transaksi_layanan.transaksi_id', '=', 'transaksi.id')
            ->whereMonth('transaksi.tanggal_masuk', $date->month)
            ->whereYear('transaksi.tanggal_masuk', $date->year)
            ->whereNull('transaksi_layanan.deleted_at')
            ->sum('transaksi_layanan.berat_kg');
    }

    /**
     * Get total item untuk bulan tertentu
     */
    private static function getTotalItemForMonth(Carbon $date): int
    {
        return (int) DB::table('transaksi_layanan')
            ->join('transaksi', 'transaksi_layanan.transaksi_id', '=', 'transaksi.id')
            ->whereMonth('transaksi.tanggal_masuk', $date->month)
            ->whereYear('transaksi.tanggal_masuk', $date->year)
            ->whereNull('transaksi_layanan.deleted_at')
            ->sum('transaksi_layanan.jumlah_satuan');
    }

    /**
     * Get data distribusi status transaksi
     */
    public static function getStatusDistribution(): Collection
    {
        return Transaksi::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->status,
                    'count' => $item->total,
                ];
            });
    }
}
