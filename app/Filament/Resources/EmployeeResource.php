<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\City;
use App\Models\Country;
use App\Models\Employee;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Select::make('country_id')
                        ->label('Country')
                        ->options(Country::select('id', 'name')->pluck('name', 'id'))
                        ->reactive()
                        ->afterStateUpdated(fn (callable $set) => $set('state_id', null))
                        ->required(),
                    Select::make('state_id')
                        ->label('State')
                        ->options(fn (callable $get) => State::select('id', 'name')->where('country_id', $get('country_id'))->pluck('name', 'id') ?? [])
                        ->reactive()
                        ->afterStateUpdated(fn (callable $set) => $set('city_id', null))
                        ->required(),
                    Select::make('city_id')
                        ->label('City')
                        ->options(fn (callable $get) => City::select('id', 'name')->where('id', $get('state_id'))->pluck('name', 'id') ?? [])
                        ->required(),
                    Select::make('department_id')
                        ->relationship('department', 'name')
                        ->required(),
                    TextInput::make('first_name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('last_name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('address')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('zip_code')
                        ->required()
                        ->maxLength(5),
                    DatePicker::make('birth_date')
                        ->required(),
                    DatePicker::make('date_hired')
                        ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('first_name')->sortable()->searchable(),
                TextColumn::make('last_name')->sortable()->searchable(),
                TextColumn::make('department.name')->sortable(),
                TextColumn::make('date_hired')->date(),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('department')->relationship('department', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
