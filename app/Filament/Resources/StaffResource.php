<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffResource\Pages;
use App\Filament\Resources\StaffResource\RelationManagers;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Staff Management';

    protected static ?string $navigationGroup = 'HR Core';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('employee_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-identification'),
                        Forms\Components\TextInput::make('full_name')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-user'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-envelope'),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20)
                            ->prefixIcon('heroicon-o-phone'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn ($context) => $context === 'create')
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-lock'),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ])
                            ->prefixIcon('heroicon-o-user-circle'),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->prefixIcon('heroicon-o-cake'),
                        Forms\Components\FileUpload::make('profile_photo')
                            ->image()
                            ->directory('staff-photos')
                            ->visibility('public'),
                    ]),

                Forms\Components\Section::make('Employment Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->required()
                            ->options([
                                'super_admin' => 'Super Admin',
                                'hr_manager' => 'HR Manager',
                                'supervisor' => 'Supervisor',
                                'observer' => 'Observer',
                                'staff' => 'Staff',
                            ])
                            ->prefixIcon('heroicon-o-shield-check'),
                        Forms\Components\Select::make('department')
                            ->options([
                                'hr' => 'Human Resources',
                                'finance' => 'Finance',
                                'academic' => 'Academic',
                                'admin' => 'Administration',
                                'it' => 'IT',
                                'operations' => 'Operations',
                            ])
                            ->prefixIcon('heroicon-o-building-office'),
                        Forms\Components\TextInput::make('designation')
                            ->maxLength(100)
                            ->prefixIcon('heroicon-o-briefcase'),
                        Forms\Components\DatePicker::make('hire_date')
                            ->required()
                            ->prefixIcon('heroicon-o-calendar'),
                        Forms\Components\Select::make('employment_type')
                            ->required()
                            ->options([
                                'full_time' => 'Full Time',
                                'part_time' => 'Part Time',
                                'contract' => 'Contract',
                                'probation' => 'Probation',
                            ])
                            ->prefixIcon('heroicon-o-clock'),
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'on_leave' => 'On Leave',
                                'suspended' => 'Suspended',
                                'terminated' => 'Terminated',
                            ])
                            ->default('active')
                            ->prefixIcon('heroicon-o-check-circle'),
                        Forms\Components\Select::make('supervisor_id')
                            ->relationship('supervisor', 'full_name')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-user-group'),
                    ]),

                Forms\Components\Section::make('Personal Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('national_id')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-document-text'),
                        Forms\Components\TextInput::make('nationality')
                            ->maxLength(100)
                            ->prefixIcon('heroicon-o-globe'),
                        Forms\Components\Textarea::make('address')
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\TextInput::make('emergency_contact_name')
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-user-plus'),
                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->tel()
                            ->maxLength(20)
                            ->prefixIcon('heroicon-o-phone'),
                    ]),

                Forms\Components\Section::make('Financial Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('base_salary')
                            ->numeric()
                            ->prefix('AFN')
                            ->prefixIcon('heroicon-o-currency-dollar'),
                        Forms\Components\TextInput::make('bank_account')
                            ->maxLength(50)
                            ->prefixIcon('heroicon-o-credit-card'),
                        Forms\Components\TextInput::make('bank_name')
                            ->maxLength(100)
                            ->prefixIcon('heroicon-o-building-library'),
                    ]),

                Forms\Components\Section::make('Qualifications & Skills')
                    ->schema([
                        Forms\Components\Textarea::make('qualifications')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('skills')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name) . '&background=0d9488&color=fff'),
                Tables\Columns\TextColumn::make('employee_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-bold'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('department')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hr' => 'success',
                        'finance' => 'warning',
                        'academic' => 'info',
                        'admin' => 'gray',
                        'it' => 'danger',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('designation')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'hr_manager' => 'warning',
                        'supervisor' => 'info',
                        'observer' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'gray',
                        'on_leave' => 'warning',
                        'suspended' => 'danger',
                        'terminated' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('hire_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->options([
                        'hr' => 'Human Resources',
                        'finance' => 'Finance',
                        'academic' => 'Academic',
                        'admin' => 'Administration',
                        'it' => 'IT',
                        'operations' => 'Operations',
                    ]),
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'hr_manager' => 'HR Manager',
                        'supervisor' => 'Supervisor',
                        'observer' => 'Observer',
                        'staff' => 'Staff',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'on_leave' => 'On Leave',
                        'suspended' => 'Suspended',
                        'terminated' => 'Terminated',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\ContractsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/create'),
            'edit' => Pages\EditStaff::route('/{record}/edit'),
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
