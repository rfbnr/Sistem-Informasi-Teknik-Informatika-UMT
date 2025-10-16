<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Layanan;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\LayananResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LayananResource\RelationManagers;
use App\Filament\Resources\LayananResource\Pages\EditLayanan;
use App\Filament\Resources\LayananResource\Pages\ListLayanans;
use App\Filament\Resources\LayananResource\Pages\CreateLayanan;

class LayananResource extends Resource
{
    protected static ?string $model = Layanan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label('Nama'),

                    FileUpload::make('image')
                    ->image() // Menentukan bahwa file yang diunggah harus berupa gambar
                    ->required() // Membuat field ini wajib diisi
                    ->previewable() // Menampilkan pratinjau gambar setelah diunggah
                    ->reorderable() // Mengizinkan pengurutan ulang file (jika multiple)
                    ->openable() // Menyediakan opsi untuk membuka gambar yang diunggah
                    ->downloadable(), // Menyediakan opsi untuk mengunduh gambar yang diunggah

                    TextInput::make('jabatan')
                    ->required()
                    ->label('Jabatan'),

                Select::make('status')
                    ->options([
                        'Ada' => 'Ada',
                        'Tidak Ada' => 'Tidak Ada',
                    ])
                    ->required()
                    ->label('Status'),

                RichEditor::make('keterangan')
                    ->required()
                    ->label('Keterangan')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'link',
                        'bulletList',
                        'numberList',
                        'quote',
                        'codeBlock',
                        'heading',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('image')
                ->label('Image')
                ->size(50, 50),


                TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),


                TextColumn::make('jabatan')
                    ->label('jabatan')
                    ->size(50, 50),

                TextColumn::make('status')
                    ->label('Status')
                    ->sortable(),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->wrap()
                    ->html(), // Ensure HTML is rendered safely
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define any relationships if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLayanans::route('/'),
            'create' => Pages\CreateLayanan::route('/create'),
            'edit' => Pages\EditLayanan::route('/{record}/edit'),
        ];
    }
}
