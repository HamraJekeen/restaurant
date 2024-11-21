<?php

namespace App\Filament\Resources\ProductComponentResource\Pages;

use App\Filament\Resources\ProductComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductComponent extends EditRecord
{
    protected static string $resource = ProductComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
