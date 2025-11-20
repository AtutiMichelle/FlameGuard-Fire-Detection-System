<?php

namespace App\Filament\Admin\Pages;

use App\Models\SensorData;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ActiveAlerts extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = '🚨 Fire & Alerts';
    protected static ?string $title = 'Active Alerts';
    protected static ?string $slug = 'active-alerts';
    // protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected string $view = 'filament.admin.pages.active-alerts';

    public function getHeading(): string
    {
        return '';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('device_id')
                    ->label('Device ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sensor_type')
                    ->label('Sensor Type')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('ml_results.fire_detected')
                    ->label('Fire Detected')
                    ->colors([
                        'success' => fn ($state): bool => $state === false,
                        'danger'  => fn ($state): bool => $state === true,
                    ])
                    ->formatStateUsing(fn ($state): string => $state ? '🔥 Yes' : '✅ No'),
                Tables\Columns\TextColumn::make('raw_data.temp')
                    ->label('Temperature (°C)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('raw_data.mq2')
                    ->label('Gas Level (MQ2)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('raw_data.humidity')
                    ->label('Humidity (%)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'normal',
                        'warning' => 'warning',
                        'danger' => 'alert',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('ml_results.fire_detected')
                    ->label('Fire Detected'),
            ])
            ->emptyStateHeading('No Active Alerts')
            ->emptyStateDescription('All systems normal - no fire detected in the last 24 hours')
            ->emptyStateIcon('heroicon-o-check-badge')
            ->paginated(false)
            ->poll('10s');
    }

    protected function getQuery(): Builder
    {
        return SensorData::query()
            ->whereJsonContains('ml_results->fire_detected', true)
            ->where('created_at', '>=', now()->subHours(24))
            ->latest();
    }
}