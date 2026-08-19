<template>
    <Head><title>AI Assistant - MaterialTrack</title></Head>
    <div class="ai-assistant-page">

        <div v-if="!isConfigured" class="config-error-card">
            <div class="error-icon">
                <i class="fa fa-exclamation-triangle"></i>
            </div>
            <h4 class="mb-2">{{ $t('ai_assistant.not_configured') }}</h4>
            <p class="text-muted mb-0">{{ $t('ai_assistant.not_configured_desc') }}</p>
        </div>

        <div v-else class="chat-layout">
            <div class="sidebar" :class="{ collapsed: sidebarCollapsed }">
                <div class="sidebar-header">
                    <button @click="newConversation" class="new-chat-btn">
                        <i class="fa fa-plus"></i>
                        <span v-if="!sidebarCollapsed">{{ $t('ai_assistant.new_conversation') }}</span>
                    </button>
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="toggle-sidebar-btn">
                        <i class="fa" :class="sidebarCollapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
                    </button>
                </div>
                <div v-if="!sidebarCollapsed" class="conversation-list">
                    <div v-if="conversations.length === 0" class="no-conversations">
                        <i class="fa fa-comments text-muted"></i>
                        <p class="small text-muted mb-0">{{ $t('ai_assistant.no_conversations') }}</p>
                    </div>
                    <div
                        v-for="conv in conversations"
                        :key="conv.id"
                        class="conversation-item"
                        :class="{ active: activeConversationId === conv.id }"
                        @click="loadConversation(conv.id)"
                    >
                        <div class="conv-title">{{ conv.title || $t('ai_assistant.new_conversation') }}</div>
                        <div class="conv-meta">
                            <span class="conv-date">{{ formatDate(conv.updated_at) }}</span>
                            <button @click.stop="deleteConversation(conv.id)" class="conv-delete" :title="$t('common.delete')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chat-container">
                <div class="chat-header">
                    <div class="header-content">
                        <div class="ai-avatar">
                            <i class="fa fa-robot"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">MaterialTrack AI</h5>
                            <p class="text-muted small mb-0">
                                {{ activeConversationTitle || $t('ai_assistant.subtitle') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="chat-messages" ref="messagesContainer">
                    <div v-if="messages.length === 0" class="empty-state">
                        <div class="ai-logo">
                            <i class="fa fa-robot"></i>
                        </div>
                        <h5 class="mb-2">MaterialTrack AI</h5>
                        <p class="text-muted mb-4">{{ $t('ai_assistant.placeholder') }}</p>

                        <div class="suggested-prompts">
                            <button v-for="prompt in suggestedPrompts" :key="prompt" @click="sendSuggestedPrompt(prompt)" class="prompt-chip">
                                {{ prompt }}
                            </button>
                        </div>
                    </div>

                    <div v-for="(msg, idx) in messages" :key="idx" class="message-wrapper" :class="msg.role">
                        <div class="message-bubble">
                            <div v-if="msg.role === 'assistant'" class="message-avatar">
                                <i class="fa fa-robot"></i>
                            </div>
                            <div class="message-stack">
                                <div v-if="msg.chart_data" class="chart-card">
                                    <div class="chart-card-title">{{ msg.chart_data.title }}</div>
                                    <apexchart
                                        :type="msg.chart_data.type"
                                        height="260"
                                        :options="buildChartOptions(msg.chart_data)"
                                        :series="msg.chart_data.series"
                                    />
                                </div>
                                <div class="message-content" v-html="formatMessage(msg.content)"></div>
                            </div>
                        </div>
                    </div>

                    <div v-if="isLoading" class="message-wrapper assistant">
                        <div class="message-bubble">
                            <div class="message-avatar">
                                <i class="fa fa-robot"></i>
                            </div>
                            <div class="typing-indicator">
                                <span></span><span></span><span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chat-input-area">
                    <button
                        v-if="speechSupported"
                        @click="toggleVoice"
                        class="voice-btn"
                        :class="{ recording: isRecording }"
                        :title="isRecording ? $t('ai_assistant.voice_stop') : $t('ai_assistant.voice_start')"
                        type="button"
                    >
                        <i class="fa" :class="isRecording ? 'fa-stop' : 'fa-microphone'"></i>
                    </button>
                    <textarea
                        v-model="userInput"
                        @keydown.enter.exact.prevent="sendMessage"
                        @keydown.enter.shift.exact="userInput += '\n'"
                        :placeholder="$t('ai_assistant.placeholder')"
                        rows="1"
                        :disabled="isLoading"
                        :maxlength="maxLength"
                    ></textarea>
                    <button @click="sendMessage" :disabled="!userInput.trim() || isLoading" class="send-btn">
                        <i class="fa" :class="isLoading ? 'fa-circle-notch fa-spin' : 'fa-paper-plane'"></i>
                    </button>
                </div>
                <div class="input-hint">
                    <span class="text-muted small">
                        <template v-if="isRecording">
                            <i class="fa fa-circle text-danger me-1" style="animation: blink 1s infinite;"></i> {{ $t('ai_assistant.listening') }}
                        </template>
                        <template v-else>
                            {{ $t('ai_assistant.enter_hint') }}
                            <span v-if="speechSupported"> · {{ $t('ai_assistant.mic_hint') }}</span>
                        </template>
                    </span>
                    <span class="text-muted small">{{ userInput.length }}/{{ maxLength }}</span>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import { Head, router } from '@inertiajs/vue3';
import LayoutApp from '@/Layouts/App.vue';

export default {
    layout: LayoutApp,
    components: { Head },
    props: {
        isConfigured: Boolean,
    },
    data() {
        return {
            messages: [],
            userInput: '',
            isLoading: false,
            maxLength: 2000,
            suggestedPrompts: [
                'Ringkasan inventory hari ini',
                'Tampilkan stok menipis',
                'Material yang habis',
                'Stock Out minggu ini',
                'Top material paling sering keluar',
                'Cari stok Baut M10',
            ],
            isRecording: false,
            speechSupported: false,
            recognition: null,
            conversations: [],
            activeConversationId: null,
            activeConversationTitle: '',
            sidebarCollapsed: false,
        };
    },
    mounted() {
        this.initSpeech();
        this.loadConversations();
    },
    beforeUnmount() {
        if (this.recognition) {
            this.recognition.abort();
        }
    },
    methods: {
        initSpeech() {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) return;
            this.speechSupported = true;
            this.recognition = new SpeechRecognition();
            this.recognition.continuous = false;
            this.recognition.interimResults = true;
            this.recognition.lang = 'id-ID';
            this.recognition.onresult = (event) => {
                let interimTranscript = '';
                let finalTranscript = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    if (event.results[i].isFinal) {
                        finalTranscript += event.results[i][0].transcript;
                    } else {
                        interimTranscript += event.results[i][0].transcript;
                    }
                }
                if (finalTranscript) {
                    this.userInput = (this.userInput ? this.userInput + ' ' : '') + finalTranscript;
                } else if (interimTranscript) {
                    this.userInput = interimTranscript;
                }
            };
            this.recognition.onend = () => {
                this.isRecording = false;
            };
            this.recognition.onerror = (event) => {
                this.isRecording = false;
                if (event.error !== 'no-speech' && event.error !== 'aborted') {
                    console.error('Speech recognition error:', event.error);
                }
            };
        },

        toggleVoice() {
            if (!this.recognition) return;
            if (this.isRecording) {
                this.recognition.stop();
                this.isRecording = false;
            } else {
                this.userInput = '';
                this.recognition.start();
                this.isRecording = true;
            }
        },

        newConversation() {
            this.messages = [];
            this.activeConversationId = null;
            this.activeConversationTitle = '';
        },

        async loadConversations() {
            try {
                const response = await fetch('/apps/ai-assistant/conversations');
                if (response.ok) {
                    this.conversations = await response.json();
                }
            } catch (e) {
                console.error('Failed to load conversations:', e);
            }
        },

        async loadConversation(id) {
            try {
                const response = await fetch(`/apps/ai-assistant/conversations/${id}`);
                if (response.ok) {
                    const conv = await response.json();
                    this.activeConversationId = conv.id;
                    this.activeConversationTitle = conv.title || '';
                    this.messages = conv.messages.map(m => ({
                        role: m.role,
                        content: m.content,
                        chart_data: m.chart_data,
                    }));
                    this.scrollToBottom();
                }
            } catch (e) {
                console.error('Failed to load conversation:', e);
            }
        },

        async deleteConversation(id) {
            try {
                await fetch(`/apps/ai-assistant/conversations/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                if (this.activeConversationId === id) {
                    this.newConversation();
                }
                this.loadConversations();
            } catch (e) {
                console.error('Failed to delete conversation:', e);
            }
        },

        async sendMessage() {
            if (!this.userInput.trim() || this.isLoading) return;

            const userMessage = this.userInput.trim();
            this.messages.push({ role: 'user', content: userMessage });
            this.userInput = '';
            this.isLoading = true;

            this.scrollToBottom();

            try {
                const response = await fetch('/apps/ai-assistant/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        message: userMessage,
                        conversation_history: this.getConversationHistory(),
                        conversation_id: this.activeConversationId,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    this.messages.push({
                        role: 'assistant',
                        content: data.message,
                        chart_data: data.chart_data ?? null,
                    });
                    if (data.conversation_id) {
                        this.activeConversationId = data.conversation_id;
                        if (!this.activeConversationTitle) {
                            this.activeConversationTitle = userMessage.substring(0, 80);
                        }
                        this.loadConversations();
                    }
                } else {
                    this.messages.push({
                        role: 'assistant',
                        content: data.message || $t('ai_assistant.error_generic'),
                        chart_data: null,
                    });
                }

            } catch (error) {
                this.messages.push({
                    role: 'assistant',
                    content: $t('ai_assistant.error_connection'),
                    chart_data: null,
                });
            } finally {
                this.isLoading = false;
                this.scrollToBottom();
            }
        },

        sendSuggestedPrompt(prompt) {
            this.userInput = prompt;
            this.sendMessage();
        },

        getConversationHistory() {
            return this.messages.slice(-10).map(msg => ({
                role: msg.role === 'user' ? 'user' : 'assistant',
                content: msg.content,
            }));
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        },

        formatMessage(content) {
            let html = this.escapeHtml(content || '');
            html = html.replace(/^###\s+(.+)$/gm, '<div class="msg-heading">$1</div>');
            html = html.replace(/^##\s+(.+)$/gm, '<div class="msg-heading">$1</div>');
            html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/^[*-]\s+(.+)$/gm, '<li>$1</li>');
            html = html.replace(/(<li>.*<\/li>)/gs, '<ul class="msg-list">$1</ul>');
            html = html.replace(/^---$/gm, '<hr class="msg-divider">');
            html = html.replace(/\n(?!<)/g, '<br>');
            return html;
        },

        escapeHtml(content) {
            return String(content)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        buildChartOptions(chartData) {
            const baseOptions = {
                chart: { toolbar: { show: false }, background: 'transparent' },
                labels: chartData.labels ?? [],
                xaxis: { categories: chartData.labels ?? [] },
                dataLabels: { enabled: chartData.type !== 'bar' },
                tooltip: { theme: 'light' },
            };
            if (chartData.type === 'donut') {
                return {
                    ...baseOptions,
                    colors: ['#10b981', '#f59e0b', '#ef4444'],
                    legend: { position: 'bottom', fontSize: '12px' },
                    plotOptions: { pie: { donut: { size: '65%' } } },
                };
            }
            if (chartData.type === 'bar') {
                return {
                    ...baseOptions,
                    colors: ['#7c3aed'],
                    plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '60%' } },
                    dataLabels: { enabled: true, style: { fontSize: '11px' } },
                };
            }
            return {
                ...baseOptions,
                colors: ['#7c3aed'],
                stroke: { curve: 'smooth', width: 3 },
            };
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },
    },
};
</script>

<style scoped>
.ai-assistant-page {
    max-width: 1400px;
    margin: 0 auto;
}

.message-content :deep(.msg-heading) {
    font-weight: 700;
    font-size: 14px;
    color: #1e293b;
    margin: 10px 0 6px;
}
.message-content :deep(.msg-heading:first-child) {
    margin-top: 0;
}
.message-content :deep(.msg-list) {
    margin: 6px 0;
    padding-left: 18px;
}
.message-content :deep(.msg-list li) {
    margin-bottom: 4px;
}
.message-content :deep(.msg-divider) {
    border: none;
    border-top: 1px solid #e5e7eb;
    margin: 12px 0;
}

.config-error-card {
    background: white;
    border-radius: 16px;
    padding: 60px 40px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.error-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #dc2626;
}

.chat-layout {
    display: flex;
    gap: 0;
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    height: calc(100vh - 140px);
    min-height: 600px;
    overflow: hidden;
}

.sidebar {
    width: 280px;
    border-right: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    transition: width 0.2s ease;
}

.sidebar.collapsed {
    width: 56px;
}

.sidebar-header {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    gap: 8px;
    align-items: center;
}

.new-chat-btn {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    color: #374151;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.new-chat-btn:hover {
    background: #f8fafc;
    border-color: #c7d2fe;
    color: #7c3aed;
}

.toggle-sidebar-btn {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.toggle-sidebar-btn:hover {
    background: #f1f5f9;
    color: #374151;
}

.conversation-list {
    flex: 1;
    overflow-y: auto;
    padding: 8px;
}

.conversation-list::-webkit-scrollbar {
    width: 4px;
}

.conversation-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.no-conversations {
    padding: 24px;
    text-align: center;
}

.conversation-item {
    padding: 10px 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.15s;
    margin-bottom: 2px;
}

.conversation-item:hover {
    background: #f1f5f9;
}

.conversation-item.active {
    background: #ede9fe;
}

.conv-title {
    font-size: 13px;
    font-weight: 500;
    color: #1e293b;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.conv-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 4px;
}

.conv-date {
    font-size: 11px;
    color: #94a3b8;
}

.conv-delete {
    opacity: 0;
    border: none;
    background: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 4px;
    font-size: 11px;
    transition: all 0.15s;
}

.conversation-item:hover .conv-delete {
    opacity: 1;
}

.conv-delete:hover {
    color: #dc2626;
    background: #fee2e2;
}

.chat-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.chat-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.header-content {
    display: flex;
    align-items: center;
    gap: 14px;
}

.ai-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 22px;
    flex-shrink: 0;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.ai-logo {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: #7c3aed;
}

.suggested-prompts {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
}

.prompt-chip {
    background: white;
    border: 1.5px solid #e0e7ff;
    color: #7c3aed;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.prompt-chip:hover {
    background: #f5f3ff;
    border-color: #7c3aed;
    transform: translateY(-1px);
}

.message-wrapper {
    display: flex;
}

.message-wrapper.user {
    justify-content: flex-end;
}

.message-wrapper.assistant {
    justify-content: flex-start;
}

.message-bubble {
    max-width: 75%;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.message-wrapper.user .message-bubble {
    flex-direction: row-reverse;
}

.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    flex-shrink: 0;
}

.message-stack {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 0;
}

.chart-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    min-width: 320px;
}

.chart-card-title {
    font-size: 13px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 8px;
}

.message-content {
    background: #f8fafc;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.6;
    color: #1e293b;
}

.message-wrapper.user .message-content {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    color: white;
}

.typing-indicator {
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 12px;
    display: flex;
    gap: 4px;
    align-items: center;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: #94a3b8;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
    30% { transform: translateY(-8px); opacity: 1; }
}

.chat-input-area {
    display: flex;
    gap: 12px;
    padding: 16px 24px 8px;
    border-top: 1px solid #e5e7eb;
    align-items: flex-end;
}

.voice-btn {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #f1f5f9;
    color: #64748b;
    border: 1.5px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}

.voice-btn:hover {
    background: #e0e7ff;
    color: #7c3aed;
    border-color: #c7d2fe;
}

.voice-btn.recording {
    background: linear-gradient(135deg, #dc2626, #ef4444);
    color: white;
    border-color: #dc2626;
    animation: pulse-record 1.5s infinite;
}

@keyframes pulse-record {
    0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

.chat-input-area textarea {
    flex: 1;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 14px;
    resize: none;
    max-height: 120px;
    font-family: inherit;
}

.chat-input-area textarea:focus {
    outline: none;
    border-color: #7c3aed;
}

.send-btn {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    transition: transform 0.2s;
    flex-shrink: 0;
}

.send-btn:hover:not(:disabled) {
    transform: scale(1.05);
}

.send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.input-hint {
    padding: 0 24px 16px;
    display: flex;
    justify-content: space-between;
    font-size: 11px;
}

@media (max-width: 768px) {
    .sidebar {
        display: none;
    }

    .chat-container {
        height: calc(100vh - 100px);
    }

    .message-bubble {
        max-width: 85%;
    }
}
</style>
