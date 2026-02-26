<?php

namespace App\Filament\Resources\StaffContractResource\Pages;

use App\Filament\Resources\StaffContractResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffContracts extends ListRecords
{
    protected static string $resource = StaffContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
