<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;
use Filament\Infolists\Components;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email'),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->autocomplete('new-password')
                    ->required(),

//                Role
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->required()
                    ->preload()
                    ->searchable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                                ->searchable(isIndividual: true)
                                ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(isIndividual: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->diffForHumans())
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->diffForHumans())
                    ->sortable(),

//                Role
//                Tables\Columns\TextColumn::make('roles')
//                    ->format(fn ($value) => $value->map(fn ($role) => $role->name)->join(', '))
//                    ->searchable(isIndividual: true)
//                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Profile')
                    ->schema([
                        Components\Grid::make()
                            ->schema([
                                Components\Grid::make()
                                    ->schema([
                                        Components\TextEntry::make('name')
                                            ->label('Name'),
                                        Components\TextEntry::make('email')
                                            ->label('Email'),
                                    ]),
                            ]),
                    ]),
            ]);
    }


    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewUser::class,
            Pages\EditUser::class,
            Pages\ManageUserBlogs::class,
        ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'manageBlogs' => Pages\ManageUserBlogs::route('/{record}/blogs'),
        ];
    }
}
