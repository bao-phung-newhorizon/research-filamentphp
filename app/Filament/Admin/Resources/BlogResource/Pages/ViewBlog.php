<?php

namespace App\Filament\Admin\Resources\BlogResource\Pages;

use App\Filament\Admin\Resources\BlogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
class ViewBlog extends ViewRecord
{
    protected static string $resource = BlogResource::class;

    public function getTitle(): string | Htmlable
    {
        $record = $this->getRecord();

        return $record->title;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
