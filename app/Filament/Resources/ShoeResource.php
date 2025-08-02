<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShoeResource\Pages;
use App\Filament\Resources\ShoeResource\RelationManagers;
use App\Models\Shoe;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShoeResource extends Resource
{
    protected static ?string $model = Shoe::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->label('Shoe Name')
                            ->maxLength(255),
                        TextInput::make('price')
                            ->required()
                            ->label('Price')
                            ->numeric()
                            ->minValue(0),
                        FileUpload::make('thumbnail')
                            ->label('Thumbnail')
                            ->image()
                            ->required(),
                        Repeater::make('photos')
                            ->label('Photos')
                            ->relationship('photos')
                            ->schema([
                                FileUpload::make('photo')
                                    ->image()
                                    ->required(),
                            ]),
                        Repeater::make('sizes')
                            ->label('Sizes')
                            ->relationship('sizes')
                            ->schema([
                                TextInput::make('size')
                                    ->required()
                                    ->label('Size'),
                            ]),
                        ]),
                        Fieldset::make('Additional')
                            ->schema([
                                TextInput::make('about')
                                    ->label('About')
                                    ->required()
                                    ->maxLength(500),
                                TextInput::make('stock')
                                    ->label('Stock')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                                Forms\Components\Select::make('is_popular')
                                    ->options([
                                        true => 'Yes',
                                        false => 'No',
                                    ]),
                                Forms\Components\Select::make('brand_id')
                                    ->relationship('brand', 'name')
                                    ->label('Brand')
                                    ->required(),
                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->label('Category')
                                    ->required(),
                            ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand'),
                Tables\Columns\TextColumn::make('about')
                    ->limit(50),
                Tables\Columns\TextColumn::make('price')
                    ->money('idr'),
                Tables\Columns\TextColumn::make('stock'),
                Tables\Columns\IconColumn::make('is_popular')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x')
                    ->label('Popular'),
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->circular()
                    ->size(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
                SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label('Brand'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListShoes::route('/'),
            'create' => Pages\CreateShoe::route('/create'),
            'edit' => Pages\EditShoe::route('/{record}/edit'),
        ];
    }
}
