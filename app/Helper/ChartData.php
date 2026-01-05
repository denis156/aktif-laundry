<?php

declare(strict_types=1);

namespace App\Helper;

use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChartData
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
                'count' => Transaksi::where('status', '!=', 'Batal')->whereDate('tanggal_masuk', $date)->count(),
                'berat' => self::getTotalBeratForDate($date),
                'item' => self::getTotalItemForDate($date),
            ];
        });
    }

    /**
     * Get data transaksi untuk minggu tertentu (7 hari dari tanggal yang dipilih)
     */
    public static function getWeekData(Carbon $startDate): Collection
    {
        $start = $startDate->copy()->startOfWeek();

        return collect()->times(7, function ($i) use ($start) {
            $date = $start->copy()->addDays($i - 1);

            return [
                'label' => $date->locale('id')->isoFormat('ddd, D MMM'),
                'count' => Transaksi::where('status', '!=', 'Batal')->whereDate('tanggal_masuk', $date)->count(),
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
            ->where('transaksi.status', '!=', 'Batal')
            ->whereNull('transaksi.deleted_at')
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
            ->where('transaksi.status', '!=', 'Batal')
            ->whereNull('transaksi.deleted_at')
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
                'count' => Transaksi::where('status', '!=', 'Batal')->whereDate('tanggal_masuk', $date)->count(),
                'berat' => self::getTotalBeratForDate($date),
                'item' => self::getTotalItemForDate($date),
            ];
        });
    }

    /**
     * Get data transaksi untuk bulan tertentu (per hari)
     */
    public static function getMonthData(Carbon $date): Collection
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $daysInMonth = $date->daysInMonth;

        return collect()->times($daysInMonth, function ($i) use ($startOfMonth) {
            $currentDate = $startOfMonth->copy()->addDays($i - 1);

            return [
                'label' => $currentDate->locale('id')->isoFormat('D MMM'),
                'count' => Transaksi::where('status', '!=', 'Batal')->whereDate('tanggal_masuk', $currentDate)->count(),
                'berat' => self::getTotalBeratForDate($currentDate),
                'item' => self::getTotalItemForDate($currentDate),
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
                'count' => Transaksi::where('status', '!=', 'Batal')
                    ->whereMonth('tanggal_masuk', $date->month)
                    ->whereYear('tanggal_masuk', $date->year)
                    ->count(),
                'berat' => self::getTotalBeratForMonth($date),
                'item' => self::getTotalItemForMonth($date),
            ];
        });
    }

    /**
     * Get data transaksi untuk tahun tertentu (12 bulan)
     */
    public static function getYearData(Carbon $date): Collection
    {
        $year = $date->year;

        return collect()->times(12, function ($i) use ($year) {
            $month = $i;
            $date = Carbon::create($year, $month, 1);

            return [
                'label' => $date->locale('id')->isoFormat('MMM'),
                'count' => Transaksi::where('status', '!=', 'Batal')
                    ->whereMonth('tanggal_masuk', $month)
                    ->whereYear('tanggal_masuk', $year)
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
            ->where('transaksi.status', '!=', 'Batal')
            ->whereNull('transaksi.deleted_at')
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
            ->where('transaksi.status', '!=', 'Batal')
            ->whereNull('transaksi.deleted_at')
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

    /**
     * Get data transaksi untuk date range tertentu
     */
    public static function getDateRangeData(Carbon $from, Carbon $to): Collection
    {
        // Copy and normalize to date only (ignore time)
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->startOfDay();

        // Ensure from is before or equal to to
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        // Calculate days (add 1 to include both start and end date)
        $days = (int) $from->diffInDays($to) + 1;

        // Ensure at least 1 day to prevent collect()->times(0) error
        $days = max(1, $days);

        return collect()->times($days, function ($i) use ($from) {
            $date = $from->copy()->addDays($i - 1);

            return [
                'label' => $date->locale('id')->isoFormat('D MMM'),
                'count' => Transaksi::where('status', '!=', 'Batal')->whereDate('tanggal_masuk', $date)->count(),
                'berat' => self::getTotalBeratForDate($date),
                'item' => self::getTotalItemForDate($date),
            ];
        });
    }
}
