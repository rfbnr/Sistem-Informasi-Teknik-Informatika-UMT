<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SorotanResource\Pages;
use App\Filament\Resources\SorotanResource\RelationManagers;
use App\Models\Sorotan;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SorotanResource extends Resource
{
    protected static ?string $model = Sorotan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->required(),
                TextInput::make('subtitle'),
                TextInput::make('link')
                    ->label('Link'),
                TextInput::make('description')->required(),
                FileUpload::make('image')
                  ->disk('public')
                    ->directory('sorotan')
                ->image() // Menentukan bahwa file yang diunggah harus berupa gambar
                ->required() // Membuat field ini wajib diisi
                ->previewable() // Menampilkan pratinjau gambar setelah diunggah
                ->reorderable() // Mengizinkan pengurutan ulang file (jika multiple)
                ->openable() // Menyediakan opsi untuk membuka gambar yang diunggah
                ->downloadable(), // Menyediakan opsi untuk mengunduh gambar yang diunggah
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul'),
                TextColumn::make('subtitle'),
                TextColumn::make('description'),
                TextColumn::make('link')
                    ->label('Link')
                    ->url(fn ($record) => $record->link, true),
                ImageColumn::make('image')
                    ->label('Image'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListSorotans::route('/'),
            'create' => Pages\CreateSorotan::route('/create'),
            'edit' => Pages\EditSorotan::route('/{record}/edit'),
        ];
    }
}
