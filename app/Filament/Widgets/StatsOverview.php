<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';

    protected int | string | array $columnSpan = 'full';

    private function getPercentage($prev, $current)
    {
        if ($prev == 0) {
            return 0;
        }
        return round((($current - $prev) / $prev) * 100, 1);
    }

    protected function getStats(): array
    {
        $newListing = Listing::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $prevListing = Listing::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $listingPercentage = $this->getPercentage($prevListing, $newListing);

        $currentTransactions = Transaction::whereStatus('approved')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->get();

        $prevTransactions = Transaction::whereStatus('approved')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->get();

        $currentRevenue = $currentTransactions->sum('total_price');
        $prevRevenue = $prevTransactions->sum('total_price');

        $revenuePercentage = $this->getPercentage($prevRevenue, $currentRevenue);
        $transactionPercentage = $this->getPercentage($prevTransactions->count(), $currentTransactions->count());

        return [
            Stat::make('New Listings This Month', $newListing)
                ->description($listingPercentage > 0 ? 'Up by ' . $listingPercentage . '%' : 'Down by ' . abs($listingPercentage) . '%')
                ->descriptionIcon($listingPercentage > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($listingPercentage > 0 ? 'success' : 'danger')
                ->icon('heroicon-o-home')
                ->color('primary'),

            Stat::make('Transactions This Month', $currentTransactions->count())
                ->description($transactionPercentage > 0 ? 'Up by ' . $transactionPercentage . '%' : 'Down by ' . abs($transactionPercentage) . '%')
                ->descriptionIcon($transactionPercentage > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($transactionPercentage > 0 ? 'success' : 'danger')
                ->icon('heroicon-o-shopping-cart')
                ->color('warning'),

            Stat::make('Revenue This Month', 'Rp ' . number_format($currentRevenue, 0, ',', '.'))
                ->description($revenuePercentage > 0 ? 'Up by ' . $revenuePercentage . '%' : 'Down by ' . abs($revenuePercentage) . '%')
                ->descriptionIcon($revenuePercentage > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($revenuePercentage > 0 ? 'success' : 'danger')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
