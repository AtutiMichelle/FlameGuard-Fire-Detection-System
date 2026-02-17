<?php

namespace App\Filament\User\Widgets;

use App\Models\SensorData;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ActiveFireAlertWidget extends TableWidget
{
    protected static ?string $heading = 'Active Fire Alerts';
    protected int|string|array $columnSpan = 'full'; // full width

    protected static ?int $sort = 2;

    public function getTableQuery(): Builder
    {
        return SensorData::query()
            ->whereJsonContains('ml_results->fire_detected', true)
            ->where('created_at', '>=', now()->subHours(24))  // only last 24h
            ->latest();
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('ml_results.fire_detected')
                    ->label('Fire Detected')
                    ->colors([
                        'danger' => fn ($state): bool => $state === true,
                    ])
                    ->formatStateUsing(fn ($state): string => $state ? '🔥 Yes' : '—'),

                Tables\Columns\TextColumn::make('raw_data.temp')
                    ->label('Temp (°C)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('raw_data.mq2')
                    ->label('Gas (MQ2)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('raw_data.humidity')
                    ->label('Humidity (%)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('5s') // auto-refresh every 8 seconds
            ->emptyStateHeading('No Active Alerts')
            ->emptyStateDescription('No fire detected in the last 24 hours.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
