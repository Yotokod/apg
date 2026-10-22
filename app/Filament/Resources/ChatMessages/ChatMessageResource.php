<?php

namespace App\Filament\Resources\ChatMessages;

use App\Filament\Resources\ChatMessages\Pages\ManageChatMessages;
use App\Models\ChatMessage;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ChatMessageResource extends Resource
{
    protected static ?string $model = ChatMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Chat support';
    protected static ?string $modelLabel = 'Chat support';
    protected static ?string $pluralModelLabel = 'Chat support';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
    TextInput::make('session_id')->required(), Select::make('role')->options(['user' => 'Client', 'bot' => 'Bot', 'agent' => 'Agent'])->default('agent'),
    Textarea::make('content')->required()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([TextColumn::make('session_id')->limit(8), TextColumn::make('role')->badge(), TextColumn::make('content')->limit(80), TextColumn::make('created_at')->since()])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageChatMessages::route('/')];
    }
}
