<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\AI\AIAssistantService;
use App\Services\AI\GeminiProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AIAssistantController extends Controller
{
    public function index()
    {
        return Inertia::render('Apps/AIAssistant/Index', [
            'isConfigured' => !empty(config('ai.gemini.api_key')),
        ]);
    }

    public function chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:' . config('ai.assistant.max_query_length', 2000),
            'conversation_history' => 'nullable|array|max:' . config('ai.assistant.max_conversation_history', 10),
            'conversation_id' => 'nullable|integer|exists:chat_conversations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (empty(config('ai.gemini.api_key'))) {
            return response()->json([
                'success' => false,
                'message' => 'AI Assistant belum dikonfigurasi. Silakan hubungi administrator.',
            ], 503);
        }

        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        $departmentId = null;

        if (!$isAdmin && $user->hasRole('department_user')) {
            $departmentId = $user->department_id ?? null;
        }

        // Load or create conversation
        $conversationId = $request->input('conversation_id');
        if ($conversationId) {
            // IDOR guard: conversation milik user lain tidak boleh diakses
            $conversation = ChatConversation::where('id', $conversationId)
                ->where('user_id', $user->id)
                ->first();

            if (! $conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Percakapan tidak ditemukan.',
                ], 404);
            }
        } else {
            $conversation = ChatConversation::create([
                'user_id' => $user->id,
                'title' => mb_substr($request->input('message'), 0, 80),
            ]);
            $conversationId = $conversation->id;
        }

        // Save user message
        ChatMessage::create([
            'conversation_id' => $conversationId,
            'role' => 'user',
            'content' => $request->input('message'),
        ]);

        try {
            $provider = new GeminiProvider();
            $service = new AIAssistantService($provider, $departmentId, $isAdmin);

            $result = $service->processQuery(
                $request->input('message'),
                $request->input('conversation_history', [])
            );

            // Save assistant message
            if ($result['success']) {
                ChatMessage::create([
                    'conversation_id' => $conversationId,
                    'role' => 'assistant',
                    'content' => $result['message'],
                    'chart_data' => $result['chart_data'] ?? null,
                ]);
            }

            $result['conversation_id'] = $conversationId;

            return response()->json($result, $result['success'] ? 200 : 503);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Assistant chat error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada AI Assistant. Silakan coba kembali.',
            ], 500);
        }
    }

    public function conversations(Request $request)
    {
        $conversations = ChatConversation::where('user_id', $request->user()->id)
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        return response()->json($conversations);
    }

    public function loadConversation(Request $request, int $id)
    {
        $conversation = ChatConversation::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('messages')
            ->firstOrFail();

        return response()->json($conversation);
    }

    public function deleteConversation(Request $request, int $id)
    {
        ChatConversation::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['success' => true]);
    }
}
