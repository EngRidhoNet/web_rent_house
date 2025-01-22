<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Listing;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use App\Filament\Resources\ListingResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'Property Management';
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->description('Enter the main details about the listing')
                    ->icon('heroicon-o-information-circle')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->live(debounce: 250)
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('address')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('Property Specifications')
                    ->description('Specify the property details and capacity')
                    ->icon('heroicon-o-building-office')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('sqft')
                            ->required()
                            ->numeric()
                            ->prefix('sqft')
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('max_person')
                            ->required()
                            ->numeric()
                            ->prefix('👥')
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('wifi_speed')
                            ->required()
                            ->numeric()
                            ->suffix('Mbps')
                            ->prefix('📶')
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('price_per_day')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->default(0),
                    ]),

                Section::make('Amenities')
                    ->description('Select available facilities')
                    ->icon('heroicon-o-sparkles')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Checkbox::make('full_support_available')
                            ->label('24/7 Support')
                            ->helperText('Round-the-clock customer support available')
                            ->inline(),
                        Forms\Components\Checkbox::make('gym_area_available')
                            ->label('Fitness Center')
                            ->helperText('Access to gym facilities')
                            ->inline(),
                        Forms\Components\Checkbox::make('mini_cafe_available')
                            ->label('Café')
                            ->helperText('On-site café services')
                            ->inline(),
                        Forms\Components\Checkbox::make('cinema_available')
                            ->label('Cinema Room')
                            ->helperText('Private cinema facility')
                            ->inline(),
                    ]),

                Section::make('Images')
                    ->description('Upload property images')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->directory('listings')
                            ->image()
                            ->imageEditor()
                            ->openable()
                            ->reorderable()
                            ->appendFiles()
                            ->maxFiles(10)
                            ->columnSpanFull()
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('price_per_day')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_person')
                    ->label('Capacity')
                    ->icon('heroicon-o-users')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListings::route('/'),
            'create' => Pages\CreateListing::route('/create'),
            'edit' => Pages\EditListing::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
