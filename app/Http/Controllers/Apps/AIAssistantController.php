<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Services\AI\AIAssistantService;
use App\Services\AI\GeminiProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AIAssistantController extends Controller
{
    /**
     * Display AI Assistant page.
     */
    public function index()
    {
        return Inertia::render('Apps/AIAssistant/Index', [
            'isConfigured' => !empty(config('ai.gemini.api_key')),
        ]);
    }

    /**
     * Process chat message.
     */
    public function chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:' . config('ai.assistant.max_query_length', 2000),
            'conversation_history' => 'nullable|array|max:' . config('ai.assistant.max_conversation_history', 10),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if AI is configured
        if (empty(config('ai.gemini.api_key'))) {
            return response()->json([
                'success' => false,
                'message' => 'AI Assistant belum dikonfigurasi. Silakan hubungi administrator.',
            ], 503);
        }

        $user = auth()->user();
        $isAdmin = $user->hasRole('admin');
        $departmentId = null;

        // Get department ID for non-admin users
        if (!$isAdmin && $user->hasRole('department_user')) {
            // Get first department from user's categories
            $departmentId = $user->department_id ?? null;
        }

        try {
            $provider = new GeminiProvider();
            $service = new AIAssistantService($provider, $departmentId, $isAdmin);

            $result = $service->processQuery(
                $request->input('message'),
                $request->input('conversation_history', [])
            );

            return response()->json($result);

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
}
