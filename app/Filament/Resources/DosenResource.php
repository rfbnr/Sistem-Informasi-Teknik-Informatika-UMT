<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DosenResource\Pages;
use App\Filament\Resources\DosenResource\RelationManagers;
use App\Models\Dosen;
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

class DosenResource extends Resource
{
    protected static ?string $model = Dosen::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('jabatan'),
                FileUpload::make('image')
                  ->disk('public')
                    ->directory('dosen')
                ->image() // Menentukan bahwa file yang diunggah harus berupa gambar
                ->required() // Membuat field ini wajib diisi
                ->previewable() // Menampilkan pratinjau gambar setelah diunggah
                ->reorderable() // Mengizinkan pengurutan ulang file (jika multiple)
                ->openable() // Menyediakan opsi untuk membuka gambar yang diunggah
                ->downloadable(), // Menyediakan opsi untuk mengunduh gambar yang diunggah
                TextInput::make('linkedin'),
                TextInput::make('instagram'),
                TextInput::make('email'),
                TextInput::make('youtube'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                ImageColumn::make('image'),
                TextColumn::make('jabatan'),
                TextColumn::make('linkedin'),
                TextColumn::make('instagram'),
                TextColumn::make('email'),
                TextColumn::make('youtube'),
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
            'index' => Pages\ListDosens::route('/'),
            'create' => Pages\CreateDosen::route('/create'),
            'edit' => Pages\EditDosen::route('/{record}/edit'),
        ];
    }
}
