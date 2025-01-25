<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Pages\ViewTransaction;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TransactionResource\Pages;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?string $navigationGroup = 'Financial Management';
    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Transaction Details')
                    ->description('View the details of this transaction')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('user_id')
                                ->label('User')
                                ->required()
                                ->numeric()
                                ->readOnly(),
                            Forms\Components\TextInput::make('listing_id')
                                ->label('Listing')
                                ->required()
                                ->numeric()
                                ->readOnly(),
                        ]),
                        Grid::make(2)->schema([
                            Forms\Components\DatePicker::make('start_date')
                                ->label('Rental Start')
                                ->required()
                                ->readOnly(),
                            Forms\Components\DatePicker::make('end_date')
                                ->label('Rental End')
                                ->required()
                                ->readOnly(),
                        ]),
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('total_days')
                                ->label('Rental Duration')
                                ->suffix('days')
                                ->readOnly(),
                            Forms\Components\TextInput::make('price_per_day')
                                ->label('Daily Rate')
                                ->prefix('$')
                                ->numeric()
                                ->readOnly(),
                        ]),
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('total_price')
                                ->label('Total Amount')
                                ->prefix('$')
                                ->numeric()
                                ->readOnly(),
                            Forms\Components\TextInput::make('fee')
                                ->label('Service Fee')
                                ->prefix('$')
                                ->numeric()
                                ->readOnly(),
                        ]),
                        Forms\Components\Select::make('status')
                            ->label('Transaction Status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->searchable()
                            ->readOnly(),
                    ])
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Transaction ID')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('listing.title')
                    ->label('Listing')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->label('Rental Period')
                    // ->formatStateUsing(
                    //     fn($state, $record) =>
                    //     $state->format('d M Y') . ' - ' . $record->end_date->format('d M Y')
                    // )
                    ->sortable(),
                TextColumn::make('total_days')
                    ->label('Duration')
                    ->formatStateUsing(fn($state) => $state . ' days')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->color(fn($state) => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray'
                    })
                    ->icon(fn($state) => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'completed' => 'heroicon-o-check-circle',
                        'cancelled' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle'
                    }),
                TextColumn::make('total_price')
                    ->label('Total Amount')
                    ->money('USD')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Transaction Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('From'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date)
                            )
                            ->when(
                                $data['end_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date)
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->size(ActionSize::Small)
                    ->modalWidth(MaxWidth::Large),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            // Add any relation managers if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            // 'view' => ViewTransaction::route('/{record}'),
        ];
    }
}
