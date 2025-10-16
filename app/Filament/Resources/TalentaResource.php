<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TalentaResource\Pages;
use App\Filament\Resources\TalentaResource\RelationManagers;
use App\Models\Talenta;
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

class TalentaResource extends Resource
{
    protected static ?string $model = Talenta::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title'),
                TextInput::make('description'),
                FileUpload::make('image')
                  ->disk('public')
                    ->directory('talenta')
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
                TextColumn::make('title'),
                TextColumn::make('description'),
                ImageColumn::make('image'),
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
            'index' => Pages\ListTalentas::route('/'),
            'create' => Pages\CreateTalenta::route('/create'),
            'edit' => Pages\EditTalenta::route('/{record}/edit'),
        ];
    }
}
