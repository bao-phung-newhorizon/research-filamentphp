<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BlogResource\Pages;
use App\Filament\Admin\Resources\BlogResource\RelationManagers;
use App\Models\Blog;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Filament\Infolists\Components;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
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
                Tables\Columns\TextColumn::make('updated_at')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->diffForHumans())
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Action::make('Status')
                        ->label(fn (Blog $record) => $record->status == 1 ? 'Draft' : 'Publish')
                        ->icon(fn (Blog $record) => $record->status == 1 ? 'heroicon-o-x-mark' : 'heroicon-o-check')
                        ->requiresConfirmation()
                        ->modalHeading(fn (Blog $record) => $record->status == 1 ? 'Change to Draft' : 'Change to Publish')
                        ->modalDescription(fn (Blog $record) => $record->status == 1 ? 'Are you sure you want to change this blog to draft?' : 'Are you sure you want to publish this blog?')
                        ->modalSubmitActionLabel(fn (Blog $record) => $record->status == 1 ? 'Change to Draft' : 'Publish')
                        ->action(function (Blog $record) {
                            $record->update([
                                'status' => $record->status == 1 ? 0 : 1,
                            ]);
                        }),
                    Action::make('EditUserModel')
                        ->label('Edit User To Model')
                        ->form([
                            Select::make('user_id')
                                ->label('User')
                                ->options(
                                    function (Blog $record) {
                                        return DB::table('users')->pluck('name', 'id');
                                    }
                                )
                                ->required()
                                ->default(fn (Blog $record) => $record->user_id)
                        ])->action(function (Blog $record, $data) {
                            $record->update([
                                'user_id' => $data['user_id'],
                            ]);
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('General')
                    ->schema([
                        Components\Split::make([
                            Components\Grid::make(2)
                                ->schema([
                                    Components\Group::make([
                                        Components\TextEntry::make('title'),
                                        Components\TextEntry::make('slug'),
                                    ]),
                                    Components\Group::make([
                                        Components\TextEntry::make('user.name')->label('Author'),
                                        Components\TextEntry::make('status')
                                            ->label('Status')
                                            ->formatStateUsing(fn ($state) => $state == 1 ? 'Published' : 'Draft')
                                            ->badge()
                                            ->color(fn ($state) => $state == 1 ? 'success' : 'danger'),
                                    ]),
                                ]),
                            //                                check image not isset then 'https://via.placeholder.com/150' else $record->image
                            Components\ImageEntry::make('image')
                                ->hiddenLabel()
                                ->grow(false),
                        ])->from('lg'),
                    ])
                    ->collapsible(),
                Components\Section::make('Content')
                    ->schema([
                        Components\TextEntry::make('content')
                            ->prose()
                            ->markdown()
                            ->hiddenLabel(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'view' => Pages\ViewBlog::route('/{record}'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }

//    public static function getGloballySearchableAttributes(): array
//    {
//        return ['title', 'slug'];
//    }
//getGloballySearchableAttributes là một phương thức tĩnh trả về mảng chứa các trường mà bạn muốn tìm kiếm toàn cục trên tất cả các trang của tài nguyên. Mặc định, Filament sẽ tìm kiếm trên tất cả các trường của tài nguyên, nhưng bạn có thể chỉ định các trường cụ thể mà bạn muốn tìm kiếm bằng cách sử dụng phương thức này.
}
