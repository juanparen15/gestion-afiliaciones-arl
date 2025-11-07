<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Dependencia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Usuario')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre Completo')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('correo_institucional')
                            ->label('Correo Institucional')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('usuario@entidad.gov.co'),

                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => $state ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->revealable()
                            ->maxLength(255)
                            ->helperText('Dejar en blanco para mantener la contraseña actual'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Información Laboral')
                    ->schema([
                        Forms\Components\TextInput::make('cargo')
                            ->label('Cargo')
                            ->maxLength(255)
                            ->placeholder('Ej: Técnico Administrativo, Profesional Especializado'),

                        Forms\Components\Select::make('dependencia_id')
                            ->label('Dependencia / Secretaría')
                            ->options(Dependencia::all()->pluck('nombre', 'id'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('area_id', null);
                            })
                            ->helperText('Dependencia a la que pertenece el usuario'),

                        Forms\Components\Select::make('area_id')
                            ->label('Área')
                            ->relationship('area', 'nombre', function ($query, Forms\Get $get) {
                                $dependenciaId = $get('dependencia_id');
                                if ($dependenciaId) {
                                    return $query->where('dependencia_id', $dependenciaId)->where('activo', true);
                                }
                                return $query->where('activo', true);
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Seleccione primero una dependencia')
                            ->disabled(fn(Forms\Get $get) => !$get('dependencia_id')),

                        Forms\Components\Select::make('roles')
                            ->label('Rol del Sistema')
                            ->relationship('roles', 'name')
                            ->options(Role::all()->pluck('name', 'name'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Define los permisos y accesos del usuario')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('correo_institucional')
                    ->label('Email Institucional')
                    ->searchable()
                    ->copyable()
                    ->toggleable()
                    ->icon('heroicon-o-building-office-2'),

                Tables\Columns\TextColumn::make('cargo')
                    ->label('Cargo')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dependencia.nombre')
                    ->label('Dependencia')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('area.nombre')
                    ->label('Área')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'SSST' => 'success',
                        'Dependencia' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'super_admin' => 'Super Administrador',
                        'SSST' => 'SSST',
                        'Dependencia' => 'Dependencia',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Rol')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->native(false),

                Tables\Filters\SelectFilter::make('dependencia')
                    ->label('Dependencia')
                    ->relationship('dependencia', 'nombre')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\SelectFilter::make('area_id')
                    ->label('Área')
                    ->relationship('area', 'nombre')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),

                Tables\Actions\EditAction::make()
                    ->label('Editar'),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No hay usuarios registrados')
            ->emptyStateDescription('Cree un nuevo usuario para comenzar.')
            ->emptyStateIcon('heroicon-o-users');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
