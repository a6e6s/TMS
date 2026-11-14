<?php

namespace App\Filament\Resources\Tickets\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament/resources/tickets.comments.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('body')
                    ->label(__('filament/resources/tickets.comments.fields.body'))
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Toggle::make('is_internal')
                    ->label(__('filament/resources/tickets.comments.fields.is_internal'))
                    ->helperText(__('filament/resources/tickets.comments.internal_help'))
                    ->default(false),
                FileUpload::make('attachments')
                    ->label(__('filament/resources/tickets.fields.attachments'))
                    ->multiple()
                    ->maxFiles(5)
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'application/pdf'])
                    ->disk('local')
                    ->directory('tickets/comment-attachments')
                    ->visibility('private')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                IconColumn::make('is_internal')
                    ->label(__('filament/resources/tickets.comments.fields.visibility'))
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedLockClosed)
                    ->falseIcon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                    ->trueColor('warning')
                    ->falseColor('success'),
                TextColumn::make('body')
                    ->label(__('filament/resources/tickets.comments.fields.body'))
                    ->limit(120)
                    ->wrap(),
                TextColumn::make('author.name')
                    ->label(__('filament/resources/tickets.comments.fields.author'))
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label(__('filament/resources/tickets.fields.created_at'))
                    ->dateTime(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['author_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
