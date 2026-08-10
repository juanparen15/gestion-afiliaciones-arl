<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcesoSeleccionResource\Pages;
use App\Filament\Resources\ProcesoSeleccionResource\RelationManagers;
use App\Models\ProcesoSeleccion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProcesoSeleccionResource extends Resource
{
    protected static ?string $model = ProcesoSeleccion::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Contratación';

    protected static ?string $navigationLabel = 'Procesos de Selección';

    protected static ?string $modelLabel = 'proceso de selección';

    protected static ?string $pluralModelLabel = 'procesos de selección';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Datos del proceso')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema([
                            Forms\Components\Select::make('modalidad')
                                ->label('Modalidad')
                                ->options(ProcesoSeleccion::MODALIDADES)
                                ->native(false)->required()->live()
                                ->helperText(fn (Forms\Get $get) => filled($get('modalidad'))
                                    ? 'Código: ' . (ProcesoSeleccion::PREFIJOS[$get('modalidad')] ?? '') . ' ### DE ' . ($get('vigencia') ?: date('Y'))
                                    : null),
                            Forms\Components\TextInput::make('vigencia')->label('Vigencia (Año)')
                                ->numeric()->minValue(2020)->maxValue(2100)->default((int) date('Y'))->required()->live(),
                            Forms\Components\Placeholder::make('codigo_view')
                                ->label('Consecutivo / Código')
                                ->content(fn (?ProcesoSeleccion $record) => $record?->codigo
                                    ?? 'Se asignará automáticamente al guardar (según modalidad y vigencia).'),
                            Forms\Components\DatePicker::make('fecha')->label('Fecha')->native(false)->default(now()),
                            Forms\Components\Select::make('estado')->label('Estado')
                                ->options([
                                    'EN PROCESO' => 'En proceso',
                                    'ADJUDICADO' => 'Adjudicado',
                                    'DESIERTO'   => 'Desierto',
                                    'SUSPENDIDO' => 'Suspendido',
                                    'CANCELADO'  => 'Cancelado',
                                    'TERMINADO'  => 'Terminado',
                                ])->native(false)->placeholder('Seleccione un estado'),
                            Forms\Components\Textarea::make('objeto')
                                ->label('Objeto (abreviado)')->rows(2)->columnSpanFull(),
                        ])->columns(2),

                    Forms\Components\Wizard\Step::make('Responsable y PAA')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Forms\Components\Select::make('dependencia_id')
                                ->label('Dependencia')
                                ->relationship('dependencia', 'nombre')
                                ->searchable()->preload(),
                            Forms\Components\Select::make('elaborador_id')
                                ->label('Quién elaboró')
                                ->relationship('elaborador', 'nombre')
                                ->searchable()->preload()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('nombre')->required()
                                        ->dehydrateStateUsing(fn ($s) => mb_strtoupper(trim((string) $s))),
                                ]),
                            // PAA: primero la vigencia, luego el registro del Plan (busca por N° Reg).
                            Forms\Components\Select::make('paa_vigencia')
                                ->label('Vigencia del PAA')
                                ->options(fn () => \App\Models\Planadquisicione::whereNotNull('vigencia')
                                    ->distinct()->orderBy('vigencia', 'desc')->pluck('vigencia', 'vigencia')->toArray())
                                ->native(false)->live()->dehydrated(false)
                                ->afterStateUpdated(fn (Forms\Set $set) => $set('planadquisicione_id', null))
                                ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get) {
                                    if ($pid = $get('planadquisicione_id')) {
                                        $set('paa_vigencia', optional(\App\Models\Planadquisicione::find($pid))->vigencia);
                                    }
                                }),
                            Forms\Components\Select::make('planadquisicione_id')
                                ->label('Registro del PAA (N° Reg)')
                                ->options(fn (Forms\Get $get) => filled($get('paa_vigencia'))
                                    ? \App\Models\Planadquisicione::where('vigencia', $get('paa_vigencia'))
                                        ->whereNotNull('id_vigencia')->orderBy('id_vigencia')->get()
                                        ->mapWithKeys(fn ($p) => [$p->id => $p->id_vigencia . ' - ' . $p->descripcioncont])
                                    : [])
                                ->getOptionLabelUsing(fn ($value) => ($p = \App\Models\Planadquisicione::find($value))
                                    ? $p->id_vigencia . ' - ' . $p->descripcioncont : $value)
                                ->searchable()->native(false)
                                ->helperText('Seleccione la vigencia y luego el registro del Plan.')
                                ->live()
                                ->afterStateUpdated(function (Forms\Set $set, $state) {
                                    if ($p = \App\Models\Planadquisicione::find($state)) {
                                        $set('consecutivo_paa', $p->vigencia . '-' . $p->id_vigencia);
                                    }
                                }),
                            Forms\Components\Hidden::make('consecutivo_paa'),
                            Forms\Components\Textarea::make('observaciones')
                                ->label('Observaciones')->rows(2)->columnSpanFull(),
                        ])->columns(2),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')->label('Código')->badge()->color('primary')
                    ->sortable(['consecutivo']),
                Tables\Columns\TextColumn::make('modalidad')->label('Modalidad')->badge()
                    ->formatStateUsing(fn ($state) => ProcesoSeleccion::MODALIDADES[$state] ?? $state)
                    ->color('info')->sortable(),
                Tables\Columns\TextColumn::make('vigencia')->label('Vig.')->badge()->color('gray')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('fecha')->label('Fecha')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('objeto')->label('Objeto')->limit(45)->tooltip(fn ($record) => $record->objeto)->searchable(),
                Tables\Columns\TextColumn::make('dependencia_texto')->label('Dependencia')->badge()->color('gray')->placeholder('-'),
                Tables\Columns\TextColumn::make('consecutivo_paa')->label('PAA')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('elaborador.nombre')->label('Elaboró')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('estado')->label('Estado')->badge()->placeholder('-')->toggleable(),
            ])
            ->defaultSort('fecha', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('modalidad')->label('Modalidad')->options(ProcesoSeleccion::MODALIDADES),
                Tables\Filters\SelectFilter::make('dependencia_id')->label('Dependencia')->relationship('dependencia', 'nombre')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('elaborador_id')->label('Elaboró')->relationship('elaborador', 'nombre')->searchable()->preload(),
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
            'index' => Pages\ListProcesoSeleccions::route('/'),
            'create' => Pages\CreateProcesoSeleccion::route('/create'),
            'edit' => Pages\EditProcesoSeleccion::route('/{record}/edit'),
        ];
    }
}
