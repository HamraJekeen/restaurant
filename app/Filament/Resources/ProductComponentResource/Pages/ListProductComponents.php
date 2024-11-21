<?php

namespace App\Filament\Resources\ProductComponentResource\Pages;

use App\Filament\Resources\ProductComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductComponents extends ListRecords
{
    protected static string $resource = ProductComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
