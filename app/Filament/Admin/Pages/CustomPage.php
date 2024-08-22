<?php

namespace App\Filament\Admin\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Nette\Utils\Html;

class CustomPage extends Page implements HasForms
{
    use InteractsWithForms;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.custom-page';

    public ?array $data = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
           TextInput::make('notify')->label('Notify'),
        ])->statePath('data');
    }

    public function getFormActions(): array
    {
        return [
            Action::make('Notify')->submit('save')
        ] ?? [];
    }


    public function htmlRender(): string
    {
        return "
            <div id='html-render' class='p-4 bg-white shadow-md rounded-md'>
                <p>This is a HTML Render</p>
                <p>Here you can add your custom HTML</p>
            </div>
        ";
    }

    public function notify(): void
    {
        try {
           $data = $this->form->getState('data');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return;
        }

        Notification::make('Notification')
            ->title('Notification')
            ->body($data['notify'])
            ->send();
    }
}
