<?php

namespace App\Filament\Resources\BillingSystemResource\Pages;

use App\Filament\Resources\BillingSystemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBillingSystems extends ListRecords
{
    protected static string $resource = BillingSystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
