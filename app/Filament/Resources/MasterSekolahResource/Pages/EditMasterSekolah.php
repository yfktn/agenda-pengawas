<?php

namespace App\Filament\Resources\MasterSekolahResource\Pages;

use App\Filament\Resources\MasterSekolahResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterSekolah extends EditRecord
{
    protected static string $resource = MasterSekolahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
