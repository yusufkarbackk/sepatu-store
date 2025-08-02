<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTransactionResource\Pages;
use App\Filament\Resources\ProductTransactionResource\RelationManagers;
use App\Models\ProductTransaction;
use App\Models\PromoCode;
use App\Models\Shoe;
use Dom\Text;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductTransactionResource extends Resource
{
    protected static ?string $model = ProductTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('product and price')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Select::make('shoe_id')
                                        ->relationship('shoe', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                            $shoe = Shoe::find($state);
                                            $price = $shoe ? $shoe->price : 0;
                                            $quantity = $get('quantity') ?? 1;
                                            $subTotalAmount = $price * $quantity;

                                            $set('price', $price);
                                            $set('sub_total_amount', $subTotalAmount);

                                            $discount = $get('discount_amount') ?? 0;
                                            $grandTotalAmount = $subTotalAmount - $discount;
                                            $set('grand_total_amount', $grandTotalAmount);

                                            $sizes = $shoe ? $shoe->sizes->pluck('size', 'id')->toArray() : [];
                                            //dd($sizes);
                                            $set('shoe_sizes', $sizes);
                                        })
                                        ->afterStateHydrated(function (callable $get, callable $set, $state) {
                                            $shoeId = $state;
                                            if ($shoeId) {
                                                $shoe = Shoe::find($shoeId);
                                                $sizes = $shoe ? $shoe->sizes->pluck('size', 'id')->toArray() : [];
                                                $set('shoe_sizes', $sizes);
                                            }
                                        }),
                                    Select::make('shoe_size')
                                        ->label('Shoe Size')
                                        ->options(function (callable $get) {
                                            $sizes = $get('shoe_sizes');
                                            //dd($sizes);
                                            return is_array($sizes) ? $sizes : [];
                                        })
                                        ->required()
                                        ->live(),

                                    TextInput::make('quantity')
                                        ->required()
                                        ->numeric()
                                        ->default(1)
                                        ->prefix('Qty')
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                            $price = $get('price') ?? 0;
                                            $quantity = $state;
                                            $subTotalAmount = $price * $quantity;

                                            $set('sub_total_amount', $subTotalAmount);

                                            $discount = $get('discount_amount') ?? 0;
                                            $grandTotalAmount = $subTotalAmount - $discount;
                                            $set('grand_total_amount', $grandTotalAmount);
                                        }),
                                    Select::make('promo_code_id')
                                        ->relationship('promoCode', 'code')
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                            $subTotalAmount = $get('sub_total_amount') ?? 0;
                                            $promoCode = PromoCode::find($state);
                                            $discount = $promoCode ? $promoCode->discount_amount : 0;

                                            $set('discount_amount', $discount);

                                            $grandTotalAmount = $subTotalAmount - $discount;
                                            $set('grand_total_amount', $grandTotalAmount);
                                        }),
                                    TextInput::make('sub_total_amount')
                                        ->required()
                                        ->readOnly()
                                        ->numeric()
                                        ->prefix('Rp'),
                                    TextInput::make('grand_total_amount')
                                        ->required()
                                        ->readOnly()
                                        ->numeric()
                                        ->prefix('Rp'),
                                    TextInput::make('discount_amount')
                                        ->required()
                                        ->numeric()
                                        ->prefix('Rp')
                                ])
                        ]),
                    Wizard\Step::make('customer details')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('name')
                                        ->required()
                                        ->label('Customer Name')
                                        ->maxLength(255),
                                    TextInput::make('email')
                                        ->email()
                                        ->required()
                                        ->label('Customer Email')
                                        ->maxLength(255),
                                    TextInput::make('phone')
                                        ->tel()
                                        ->required()
                                        ->label('Customer Phone')
                                        ->maxLength(20),
                                    TextInput::make('address')
                                        ->required()
                                        ->label('Customer Address')
                                        ->maxLength(500),

                                    TextInput::make('city')
                                        ->required()
                                        ->label('Customer city')
                                        ->maxLength(500),
                                    TextInput::make('post_code')
                                        ->required()
                                        ->label('Customer Postcode')
                                        ->maxLength(500)
                                ])
                        ]),
                    Wizard\Step::make('Payment Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('booking_trx_id')
                                    ->required()
                                    ->label('Booking Transaction ID')
                                    ->maxLength(255),
                                ToggleButtons::make('is_paid')
                                    ->label('Apakah sudah membayar?')
                                    ->boolean()
                                    ->grouped()
                                    ->icons([
                                        true => 'heroicon-o-check-circle',
                                        false => 'heroicon-o-x-circle',
                                    ])
                                    ->required(),
                                FileUpload::make('proof')
                                ->image()
                                ->label('Proof of Payment')
                                ->required()
                            ]),
                    ]),

                ])
                ->columnSpan('full')
                ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('shoe.thumbnail')
                    ->circular()
                    ->size(50)
                    ->label('Shoe Image'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Customer Name'),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->label('Customer Phone'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('Customer Email'),
                Tables\Columns\TextColumn::make('booking_trx_id')
                    ->searchable()
                    ->sortable()
                    ->label('Booking Transaction ID'),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('post_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sub_total_amount')
                    ->money('idr'),
                Tables\Columns\TextColumn::make('grand_total_amount')
                    ->money('idr'),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->money('idr'),
                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
            ])
            ->filters([
                SelectFilter::make('shoe_id')
                    ->relationship('shoe', 'name')
                    ->label('Shoe'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('Approve')
                ->label('Approve')
                ->action(function (ProductTransaction $record) {
                    $record->is_paid = true;
                    $record->save();

                    Notification::make()
                        ->title('Order Approved')
                        ->success()
                        ->body("Transaction {$record->booking_trx_id} has been approved.")
                        ->send();   
                })

                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (ProductTransaction $record) => !$record->is_paid)
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
            'index' => Pages\ListProductTransactions::route('/'),
            'create' => Pages\CreateProductTransaction::route('/create'),
            'edit' => Pages\EditProductTransaction::route('/{record}/edit'),
        ];
    }
}
