<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PolizaResource\Pages;
use App\Filament\Resources\PolizaResource\RelationManagers;
use App\Models\Poliza;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PolizaResource extends Resource
{
    protected static ?string $model = Poliza::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Contratación';

    protected static ?string $navigationLabel = 'Aprobación de Pólizas';

    protected static ?string $modelLabel = 'póliza';

    protected static ?string $pluralModelLabel = 'pólizas';

    protected static ?int $navigationSort = 25;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Datos de la póliza')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Forms\Components\TextInput::make('consecutivo')->label('Consecutivo')->maxLength(50),
                            Forms\Components\DatePicker::make('fecha')->label('Fecha')->native(false)->default(now()),
                            Forms\Components\TextInput::make('contrato_texto')->label('Contrato')
                                ->placeholder('Ej: 468 de 2025')->maxLength(100),
                            Forms\Components\TextInput::make('estado')->label('Estado')
                                ->maxLength(100)
                                ->placeholder('Ej: INICIO, ADICIÓN, PRÓRROGA, FINAL...')
                                ->dehydrateStateUsing(fn ($s) => mb_strtoupper(trim((string) $s)) ?: null),
                        ])->columns(2),

                    Forms\Components\Wizard\Step::make('Responsable')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Forms\Components\Select::make('dependencia_id')->label('Dependencia')
                                ->relationship('dependencia', 'nombre')->searchable()->preload(),
                            Forms\Components\Select::make('aprobador_id')->label('Quién proyecta / aprueba')
                                ->relationship('aprobador', 'nombre')->searchable()->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('nombre')->required()
                                        ->dehydrateStateUsing(fn ($s) => mb_strtoupper(trim((string) $s))),
                                ]),
                            Forms\Components\Textarea::make('observaciones')->label('Observación')->rows(2)->columnSpanFull(),
                        ])->columns(2),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('consecutivo')->label('N°')->badge()->color('primary')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('fecha')->label('Fecha')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('contrato_texto')->label('Contrato')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('estado')->label('Estado')->badge()
                    ->color(fn ($state) => match (mb_strtoupper((string) $state)) {
                        'APROBACION' => 'success',
                        'ADICION', 'PRORROGA' => 'info',
                        'CESION', 'OTROSI' => 'warning',
                        'SUSPENSION', 'TERMINACION' => 'danger',
                        default => 'gray',
                    })->sortable()->placeholder('-'),
                Tables\Columns\TextColumn::make('dependencia_texto')->label('Dependencia')->badge()->color('gray')->placeholder('-'),
                Tables\Columns\TextColumn::make('aprobador.nombre')->label('Aprobó')->searchable()->placeholder('-'),
                Tables\Columns\IconColumn::make('contrato_registro_id')->label('Vinc.')
                    ->boolean()->trueIcon('heroicon-o-link')->falseIcon('heroicon-o-minus')
                    ->tooltip('Vinculado al contrato en el sistema')->toggleable(),
            ])
            ->defaultSort('fecha', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('estado')->label('Estado')
                    ->options(fn () => Poliza::whereNotNull('estado')->distinct()->orderBy('estado')->pluck('estado', 'estado')->toArray()),
                Tables\Filters\SelectFilter::make('dependencia_id')->label('Dependencia')->relationship('dependencia', 'nombre')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('aprobador_id')->label('Aprobó')->relationship('aprobador', 'nombre')->searchable()->preload(),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPolizas::route('/'),
            'create' => Pages\CreatePoliza::route('/create'),
            'edit' => Pages\EditPoliza::route('/{record}/edit'),
        ];
    }
}
