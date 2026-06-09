<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | UnitEnum | null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->maxLength(255),
                Select::make('role')
                    ->required()
                    ->live()
                    ->options([
                        'Admin' => 'Admin',
                        'Pengawas' => 'Pengawas',
                        'OperatorSekolah' => 'Operator Sekolah',
                    ]),
                Select::make('sekolah_id')
                    ->relationship('sekolah', 'nama_sekolah')
                    ->visible(fn ($get) => $get('role') === 'OperatorSekolah'),
                Select::make('penugasanSekolah')
                    ->relationship('penugasanSekolah', 'nama_sekolah')
                    ->multiple()
                    ->visible(fn ($get) => $get('role') === 'Pengawas'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Admin' => 'danger',
                        'Pengawas' => 'warning',
                        'OperatorSekolah' => 'info',
                    }),
                TextColumn::make('sekolah.nama_sekolah')
                    ->label('Sekolah (Operator)'),
                TextColumn::make('penugasanSekolah.nama_sekolah')
                    ->label('Sekolah Binaan')
                    ->badge(),
                TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'Admin' => 'Admin',
                        'Pengawas' => 'Pengawas',
                        'OperatorSekolah' => 'Operator Sekolah',
                    ]),
                \Filament\Tables\Filters\Filter::make('sekolah')
                    ->form([
                        Select::make('sekolah_id')
                            ->label('Sekolah')
                            ->relationship('sekolah', 'nama_sekolah'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['sekolah_id'], function (Builder $query, $sekolahId) {
                            return $query->where('sekolah_id', $sekolahId)
                                ->orWhereHas('penugasanSekolah', function (Builder $query) use ($sekolahId) {
                                    $query->where('master_sekolah_id', $sekolahId);
                                });
                        });
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
