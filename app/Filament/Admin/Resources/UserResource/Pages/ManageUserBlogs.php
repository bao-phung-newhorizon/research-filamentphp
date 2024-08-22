<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Models\Blog;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManageUserBlogs extends ManageRelatedRecords
{
    protected static string $resource = UserResource::class;

    protected static string $relationship = 'blogs';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Blogs';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated() // đây là một trường ảo, không cần lưu vào database
                            ->required()
                            ->maxLength(255)
                            ->unique(Blog::class, 'slug', ignoreRecord: true),

                        Forms\Components\MarkdownEditor::make('content')
                            ->required()
                            ->columnSpan('full'),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('Generate Content')
                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                    $value = $get('content');
                                    $set('content', $value . file_get_contents('https://loripsum.net/api/1/short/plaintext'));
                                })
                        ])->columnSpan('full'),

                        Forms\Components\BelongsToSelect::make('user_id')
                            ->relationship('user', 'name')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                '0' => 'Draft',
                                '1' => 'Published',
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Image')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->hiddenLabel(),
                    ])
                    ->collapsible(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('blogs')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
//                    ->getStateUsing(fn (Blog $record) => $record->status == 1 ? 'published' : 'draft')
                    ->formatStateUsing(fn ($state) => $state == 1 ? 'Published' : 'Draft')
                    ->color(fn (Blog $record) => $record->status == 1 ? 'success' : 'danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->diffForHumans())
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
//                Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
    //                Tables\Actions\DissociateAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DissociateBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

}
