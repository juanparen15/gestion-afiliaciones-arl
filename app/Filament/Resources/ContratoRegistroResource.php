<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContratoRegistroResource\Pages;
use App\Filament\Resources\ContratoRegistroResource\RelationManagers;
use App\Models\ContratoRegistro;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContratoRegistroResource extends Resource
{
    protected static ?string $model = ContratoRegistro::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Contratación';

    protected static ?string $navigationLabel = 'Contratos y Convenios';

    protected static ?string $modelLabel = 'contrato';

    protected static ?string $pluralModelLabel = 'contratos, convenios y comodatos';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tipo')
                    ->label('Tipo')->options(ContratoRegistro::TIPOS)
                    ->native(false)->required()->default('CONTRATO'),
                Forms\Components\TextInput::make('numero')->label('N° / Ítem')->maxLength(50),
                Forms\Components\DatePicker::make('fecha')->label('Fecha')->native(false),
                Forms\Components\TextInput::make('contratista')->label('Contratista')->maxLength(255)->columnSpanFull(),
                Forms\Components\TextInput::make('proceso_texto')->label('Proceso')->placeholder('Ej: CD-CPS 001 DE 2026')->maxLength(255),
                Forms\Components\TextInput::make('modalidad')->label('Modalidad')->maxLength(100),
                Forms\Components\Select::make('dependencia_id')
                    ->label('Dependencia')->relationship('dependencia', 'nombre')->searchable()->preload(),
                Forms\Components\Select::make('elaborador_id')
                    ->label('Quién elaboró')->relationship('elaborador', 'nombre')->searchable()->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nombre')->required()
                            ->dehydrateStateUsing(fn ($s) => mb_strtoupper(trim((string) $s))),
                    ]),
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
                    ->searchable()->native(false)->live()
                    ->helperText('Seleccione la vigencia y luego el registro del Plan.')
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if ($p = \App\Models\Planadquisicione::find($state)) {
                            $set('consecutivo_paa', $p->vigencia . '-' . $p->id_vigencia);
                        }
                    }),
                Forms\Components\TextInput::make('consecutivo_paa')
                    ->label('Consecutivo PAA (texto)')->placeholder('Ej: 2026-221')
                    ->helperText('Se completa al elegir el registro; editable si el PAA no está en el sistema.'),
                Forms\Components\TextInput::make('valor')->label('Valor')->numeric()->prefix('$'),
                Forms\Components\Textarea::make('observaciones')->label('Observaciones')->rows(2)->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')->label('N°')->badge()->color('primary')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('tipo')->label('Tipo')->badge()
                    ->formatStateUsing(fn ($state) => ContratoRegistro::TIPOS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) {
                        'CONTRATO' => 'success',
                        'CONVENIO' => 'info',
                        'COMODATO' => 'warning',
                        default => 'gray',
                    })->sortable(),
                Tables\Columns\TextColumn::make('fecha')->label('Fecha')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('contratista')->label('Contratista')->limit(35)->tooltip(fn ($record) => $record->contratista)->searchable(),
                Tables\Columns\TextColumn::make('proceso_texto')->label('Proceso')->searchable()->placeholder('-')->toggleable(),
                Tables\Columns\TextColumn::make('dependencia_texto')->label('Dependencia')->badge()->color('gray')->placeholder('-'),
                Tables\Columns\TextColumn::make('consecutivo_paa')->label('PAA')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('valor')->label('Valor')->money('COP', 0)->sortable()->placeholder('-'),
                Tables\Columns\TextColumn::make('elaborador.nombre')->label('Elaboró')->searchable()->placeholder('-')->toggleable(),
            ])
            ->defaultSort('fecha', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')->label('Tipo')->options(ContratoRegistro::TIPOS),
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
            'index' => Pages\ListContratoRegistros::route('/'),
            'create' => Pages\CreateContratoRegistro::route('/create'),
            'edit' => Pages\EditContratoRegistro::route('/{record}/edit'),
        ];
    }
}
