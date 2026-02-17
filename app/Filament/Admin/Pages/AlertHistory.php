<?php

namespace App\Filament\Admin\Pages;

use App\Models\SensorData;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AlertHistory extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = '🚨 Fire & Alerts';
    protected static ?string $title = 'Alert History';
    // protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?int $navigationSort = 11;
    protected string $view = 'filament.admin.pages.alert-history';

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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('raw_data.mq2')
                    ->label('Gas Level (MQ2)')
                    ->searchable()
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
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('ml_results.fire_detected')
                    ->label('Fire Detected'),
                Tables\Filters\SelectFilter::make('device_id')
                    ->label('Device')
                    ->options(fn (): array => SensorData::distinct()->pluck('device_id', 'device_id')->toArray()),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            // ->actions([
            //     Tables\Actions\ViewAction::make(),
            // ])
            ->paginated([10, 25, 50, 100]);
    }

    protected function getQuery(): Builder
    {
        return SensorData::query()
            ->whereJsonContains('ml_results->fire_detected', true) // Only show actual fire alerts
            ->latest();
    }
}