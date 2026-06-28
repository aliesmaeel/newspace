<?php

namespace App\Filament\Resources\EventHistories\Widgets;

use App\Filament\Resources\EventHistories\Pages\ListEventHistories;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventHistoryStats extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListEventHistories::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        $total = (clone $query)->count();
        $confirmed = (clone $query)->where('status', 'confirmed')->count();
        $pending = (clone $query)->where('status', 'pending_payment')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();
        $revenueCents = (int) (clone $query)->where('payment_status', 'paid')->sum('amount_cents');

        return [
            Stat::make('Registrations', number_format($total))
                ->description('Matching current filters'),
            Stat::make('Confirmed', number_format($confirmed))
                ->color('success'),
            Stat::make('Pending payment', number_format($pending))
                ->color('warning'),
            Stat::make('Cancelled', number_format($cancelled))
                ->color('danger'),
            Stat::make('Revenue (paid)', 'GBP '.number_format($revenueCents / 100, 2))
                ->description('Sum of paid registrations'),
        ];
    }
}
