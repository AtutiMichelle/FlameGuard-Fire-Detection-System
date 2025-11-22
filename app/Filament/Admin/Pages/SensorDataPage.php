<?php

namespace App\Filament\Admin\Pages;

use App\Models\SensorData;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;


class SensorDataPage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $title = 'Sensor Data';
    protected string $view = 'filament.admin.pages.sensor-data-page';
    // protected static ?int $navigationSort = 30;
    protected static string|UnitEnum|null $navigationGroup = '📊 Data & Reports';

    public function getHeading(): string
    {
        return '';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                TextColumn::make('device_id')
                    ->label('Device ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sensor_type')
                    ->label('Sensor Type')
                    ->searchable(),

                BadgeColumn::make('ml_results.fire_detected')
                    ->label('Fire Detected')
                    ->colors([
                        'success' => fn ($state): bool => $state === false,
                        'danger'  => fn ($state): bool => $state === true,
                    ])
                    ->formatStateUsing(fn ($state): string => $state ? '🔥 Yes' : '✅ No'),

                TextColumn::make('raw_data.temp')
                    ->label('Temperature (°C)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('raw_data.mq2')
                    ->label('Gas Level (MQ2)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('raw_data.humidity')
                    ->label('Humidity (%)')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'normal',
                        'warning' => 'warning',
                        'danger' => 'alert',
                    ]),

                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('Y-m-d H:i:s')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('ml_results.fire_detected')
                    ->label('Fire Detected'),

                SelectFilter::make('device_id')
                    ->label('Device')
                    ->options(fn (): array => SensorData::distinct()->pluck('device_id', 'device_id')->toArray()),

                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('created_until')->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                DeleteAction::make(), // Single record delete button
            ])
            ->bulkActions([
                DeleteBulkAction::make(), // Bulk delete multiple selected rows
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    protected function getQuery(): Builder
    {
        return SensorData::query()->latest();
    }
}
