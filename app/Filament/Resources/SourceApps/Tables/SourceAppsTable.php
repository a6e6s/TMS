<?php

namespace App\Filament\Resources\SourceApps\Tables;

use App\Models\SourceApp;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SourceAppsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament/resources/source-apps.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('filament/resources/source-apps.fields.slug'))
                    ->searchable(),
                TextColumn::make('pms_project_id')
                    ->label(__('filament/resources/source-apps.fields.pms_project_id'))
                    ->placeholder('—'),
                TextColumn::make('tickets_count')
                    ->label(__('filament/resources/source-apps.fields.tickets'))
                    ->counts('tickets'),
                IconColumn::make('is_active')
                    ->label(__('filament/resources/source-apps.fields.is_active'))
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('issueToken')
                    ->label(__('filament/resources/source-apps.actions.issue_token'))
                    ->icon(Heroicon::OutlinedKey)
                    ->requiresConfirmation()
                    ->modalDescription(__('filament/resources/source-apps.messages.token_once'))
                    ->action(function (SourceApp $record): void {
                        $user = $record->serviceUser;

                        if (! $user instanceof User) {
                            return;
                        }

                        $token = $user->createToken($record->slug, ['tickets:create'])->plainTextToken;

                        Notification::make()
                            ->title(__('filament/resources/source-apps.messages.token_created'))
                            ->body($token)
                            ->success()
                            ->persistent()
                            ->send();
                    }),
                Action::make('revokeTokens')
                    ->label(__('filament/resources/source-apps.actions.revoke_tokens'))
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (SourceApp $record): void {
                        $record->serviceUser?->tokens()->delete();

                        Notification::make()
                            ->title(__('filament/resources/source-apps.messages.tokens_revoked'))
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ]);
    }
}
