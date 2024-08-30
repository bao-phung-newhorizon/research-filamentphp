<?php

namespace App\Filament\Admin\Resources\BlogResource\Pages;

use App\Filament\Admin\Resources\BlogResource;
use App\Models\Blog;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateBlog extends CreateRecord
{
    protected static string $resource = BlogResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255)
                            ->afterStateUpdated(fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        TextInput::make('slug')
                            ->disabled()
                            ->dehydrated() // đây là một trường ảo, không cần lưu vào database
                            ->required()
                            ->maxLength(255)
                            ->unique(Blog::class, 'slug', ignoreRecord: true),

                        MarkdownEditor::make('content')
                            ->required()
                            ->columnSpan('full'),
                        \Filament\Forms\Components\Actions::make([
                            Action::make('Generate Content')
                                ->action(function (Get $get, Set $set) {
                                    $value = $get('content');
                                    $set('content', $value.file_get_contents('https://loripsum.net/api/1/short/plaintext'));
                                }),
                        ])->columnSpan('full'),

                        Select::make('status')
                            ->options([
                                '0' => 'Draft',
                                '1' => 'Published',
                            ])
                            ->required()
                            ->default('0')
                            ->columnSpan('full'),
                    ])
                    ->columns(2),

                Section::make('Image')
                    ->schema([
                        FileUpload::make('image')
                            ->image()
                            ->hiddenLabel(),
                    ])
                    ->collapsible(),

            ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
