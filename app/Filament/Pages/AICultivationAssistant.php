<?php

namespace App\Filament\Pages;

use App\Services\AI\CultivationChatbotService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AICultivationAssistant extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string $view = 'filament.pages.ai-cultivation-assistant';

    protected static ?string $navigationLabel = 'AI Cultivation Assistant';

    protected static ?string $navigationGroup = 'AI Tools';

    protected static ?int $navigationSort = 3;

    public ?string $query = '';
    public ?int $batchId = null;
    public ?string $response = '';
    public array $chatHistory = [];

    public function mount(): void
    {
        $this->form->fill();
        $this->loadChatHistory();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('batchId')
                    ->label('Select Batch (Optional)')
                    ->relationship('batch', 'batch_code')
                    ->searchable()
                    ->preload()
                    ->placeholder('Ask general questions or select a specific batch'),
                Textarea::make('query')
                    ->label('Your Question')
                    ->required()
                    ->rows(3)
                    ->placeholder('Ask me anything about cultivation, batch management, plant health, etc.')
                    ->helperText('The AI will use your cultivation data to provide accurate answers.'),
            ]);
    }

    public function sendQuery(): void
    {
        $this->validate([
            'query' => 'required|string|max:2000',
        ]);

        try {
            $service = app(CultivationChatbotService::class);
            
            $chatLog = $service->chat(
                $this->query,
                auth()->id(),
                $this->batchId,
                auth()->user()->organization_id,
                array_slice($this->chatHistory, -5) // Last 5 messages for context
            );

            // Add to chat history
            $this->chatHistory[] = [
                'query' => $chatLog->query,
                'response' => $chatLog->response,
                'timestamp' => $chatLog->created_at->format('H:i'),
            ];

            $this->response = $chatLog->response;
            $this->query = ''; // Clear input

            Notification::make()
                ->title('Response Generated')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('AI Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearChat(): void
    {
        $this->chatHistory = [];
        $this->response = '';
        $this->query = '';
        
        Notification::make()
            ->title('Chat Cleared')
            ->success()
            ->send();
    }

    protected function loadChatHistory(): void
    {
        $service = app(CultivationChatbotService::class);
        $history = $service->getConversationHistory(auth()->id(), $this->batchId, 10);
        
        $this->chatHistory = array_map(function($item) {
            return [
                'query' => $item['query'],
                'response' => $item['response'],
                'timestamp' => $item['created_at']->format('H:i'),
            ];
        }, array_reverse($history));
    }

    public static function canAccess(): bool
    {
        return auth()->user()->can('ai.use') 
            || auth()->user()->hasRole('Administrator');
    }
}
