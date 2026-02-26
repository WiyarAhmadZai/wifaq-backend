<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffContractResource\Pages;
use App\Filament\Resources\StaffContractResource\RelationManagers;
use App\Models\StaffContract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StaffContractResource extends Resource
{
    protected static ?string $model = StaffContract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Contracts';

    protected static ?string $navigationGroup = 'HR Core';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contract Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('staff_id')
                            ->relationship('staff', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon('heroicon-o-user'),
                        Forms\Components\TextInput::make('contract_number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-document'),
                        Forms\Components\Select::make('contract_type')
                            ->required()
                            ->options([
                                'permanent' => 'Permanent',
                                'fixed_term' => 'Fixed Term',
                                'probation' => 'Probation',
                                'consultancy' => 'Consultancy',
                                'internship' => 'Internship',
                            ])
                            ->prefixIcon('heroicon-o-clipboard-document'),
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'terminated' => 'Terminated',
                                'renewed' => 'Renewed',
                            ])
                            ->default('draft')
                            ->prefixIcon('heroicon-o-check-circle'),
                    ]),

                Forms\Components\Section::make('Contract Duration')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->required()
                            ->prefixIcon('heroicon-o-calendar'),
                        Forms\Components\DatePicker::make('end_date')
                            ->prefixIcon('heroicon-o-calendar'),
                        Forms\Components\TextInput::make('probation_period_days')
                            ->numeric()
                            ->default(90)
                            ->suffix('days')
                            ->prefixIcon('heroicon-o-clock')
                            ->visible(fn (Forms\Get $get) => $get('contract_type') === 'probation'),
                        Forms\Components\DatePicker::make('probation_end_date')
                            ->prefixIcon('heroicon-o-calendar')
                            ->visible(fn (Forms\Get $get) => $get('contract_type') === 'probation'),
                        Forms\Components\Select::make('probation_status')
                            ->options([
                                'pending' => 'Pending',
                                'passed' => 'Passed',
                                'failed' => 'Failed',
                                'extended' => 'Extended',
                            ])
                            ->prefixIcon('heroicon-o-check-badge')
                            ->visible(fn (Forms\Get $get) => $get('contract_type') === 'probation'),
                    ]),

                Forms\Components\Section::make('Compensation')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('salary')
                            ->required()
                            ->numeric()
                            ->prefix('AFN')
                            ->prefixIcon('heroicon-o-currency-dollar'),
                        Forms\Components\KeyValue::make('allowances')
                            ->keyLabel('Allowance Type')
                            ->valueLabel('Amount (AFN)'),
                        Forms\Components\KeyValue::make('benefits')
                            ->keyLabel('Benefit')
                            ->valueLabel('Details'),
                    ]),

                Forms\Components\Section::make('Contract Content')
                    ->schema([
                        Forms\Components\Textarea::make('job_description')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('terms_conditions')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('contract_file')
                            ->directory('contracts')
                            ->visibility('private')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Approval')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('approved_by')
                            ->relationship('approver', 'full_name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-user-check'),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->prefixIcon('heroicon-o-clock'),
                    ])
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('staff.full_name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-bold'),
                Tables\Columns\TextColumn::make('contract_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'permanent' => 'success',
                        'fixed_term' => 'info',
                        'probation' => 'warning',
                        'consultancy' => 'gray',
                        'internship' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable()
                    ->placeholder('No end date'),
                Tables\Columns\TextColumn::make('salary')
                    ->money('AFN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        'expired' => 'danger',
                        'terminated' => 'danger',
                        'renewed' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('renewal_alert_sent')
                    ->boolean()
                    ->label('Alert Sent'),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not approved'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('contract_type')
                    ->options([
                        'permanent' => 'Permanent',
                        'fixed_term' => 'Fixed Term',
                        'probation' => 'Probation',
                        'consultancy' => 'Consultancy',
                        'internship' => 'Internship',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'terminated' => 'Terminated',
                        'renewed' => 'Renewed',
                    ]),
                Tables\Filters\Filter::make('expiring_soon')
                    ->query(fn (Builder $query): Builder => $query->expiringSoon(30))
                    ->toggle(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(fn ($record) => $record->update([
                        'status' => 'active',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListStaffContracts::route('/'),
            'create' => Pages\CreateStaffContract::route('/create'),
            'edit' => Pages\EditStaffContract::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
