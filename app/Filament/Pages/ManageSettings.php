<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page
{
    protected string $view = 'filament.pages.manage-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->getSchema('form')->fill([
            'retention_days' => Setting::get('retention.days'),
            'auto_close_days' => Setting::get('ticket.auto_close_days'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament/pages/settings.sections.tickets'))
                    ->schema([
                        TextInput::make('auto_close_days')
                            ->label(__('filament/pages/settings.fields.auto_close_days'))
                            ->helperText(__('filament/pages/settings.help.auto_close_days'))
                            ->integer()
                            ->minValue(1)
                            ->maxValue(365)
                            ->required(),
                        TextInput::make('retention_days')
                            ->label(__('filament/pages/settings.fields.retention_days'))
                            ->helperText(__('filament/pages/settings.help.retention_days'))
                            ->integer()
                            ->minValue(1)
                            ->maxValue(3650)
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->getSchema('form')->getState();

        Setting::put('ticket.auto_close_days', $state['auto_close_days']);
        Setting::put('retention.days', $state['retention_days']);

        Notification::make()
            ->title(__('filament/pages/settings.messages.saved'))
            ->success()
            ->send();
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament/pages/settings.actions.save'))
                ->action(fn () => $this->save()),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/pages/settings.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament/common.navigation_groups.administration');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->role === UserRole::SuperAdmin;
    }
}
