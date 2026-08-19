<template>
    <div class="floating-ai-chat" v-if="hasPermission">
        <transition name="panel-fade">
            <div v-if="isOpen" class="ai-chat-panel">
                <div class="panel-header">
                    <div class="header-content">
                        <div class="ai-avatar-small">
                            <i class="fa fa-robot"></i>
                        </div>
                        <div>
                            <div class="header-title">MaterialTrack AI</div>
                            <div class="header-subtitle">Asisten inventory</div>
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="/apps/ai-assistant" class="expand-btn" title="Buka AI Assistant">
                            <i class="fa fa-expand"></i>
                        </a>
                        <button @click="closePanel" class="close-btn" title="Tutup">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="panel-messages" ref="panelMessages">
                    <div v-if="messages.length === 0" class="mini-empty-state">
                        <div class="mini-ai-icon">
                            <i class="fa fa-robot"></i>
                        </div>
                        <p class="mb-2 small">Tanyakan tentang inventory Anda</p>
                        <div class="mini-suggested">
                            <button v-for="prompt in miniPrompts" :key="prompt" @click="sendPrompt(prompt)" class="mini-prompt">
                                {{ prompt }}
                            </button>
                        </div>
                    </div>

                    <div v-for="(msg, idx) in messages" :key="idx" class="mini-message" :class="msg.role">
                        <div class="mini-bubble">
                            <div v-if="msg.role === 'assistant'" class="mini-avatar">
                                <i class="fa fa-robot"></i>
                            </div>
                            <div class="mini-content" v-html="formatMessage(msg.content)"></div>
                        </div>
                    </div>

                    <div v-if="isLoading" class="mini-message assistant">
                        <div class="mini-bubble">
                            <div class="mini-avatar">
                                <i class="fa fa-robot"></i>
                            </div>
                            <div class="mini-typing">
                                <span></span><span></span><span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-input">
                    <button
                        v-if="speechSupported"
                        @click="toggleVoice"
                        class="voice-btn-sm"
                        :class="{ recording: isRecording }"
                        :title="isRecording ? 'Hentikan' : 'Rekam suara'"
                        type="button"
                    >
                        <i class="fa" :class="isRecording ? 'fa-stop' : 'fa-microphone'"></i>
                    </button>
                    <input
                        v-model="userInput"
                        @keydown.enter="sendMessage"
                        :placeholder="isRecording ? 'Mendengarkan...' : 'Tanyakan...'"
                        :disabled="isLoading || !isConfigured"
                        maxlength="500"
                    />
                    <button @click="sendMessage" :disabled="!userInput.trim() || isLoading || !isConfigured" class="mini-send">
                        <i class="fa" :class="isLoading ? 'fa-circle-notch fa-spin' : 'fa-paper-plane'"></i>
                    </button>
                </div>

                <div v-if="!isConfigured" class="panel-warning">
                    <i class="fa fa-exclamation-triangle me-1"></i>
                    AI belum dikonfigurasi
                </div>
            </div>
        </transition>

        <button @click="togglePanel" class="floating-ai-button" :class="{ active: isOpen }">
            <i class="fa" :class="isOpen ? 'fa-times' : 'fa-robot'"></i>
        </button>
    </div>
</template>

<script>
export default {
    data() {
        return {
            isOpen: false,
            messages: [],
            userInput: '',
            isLoading: false,
            isConfigured: true,
            miniPrompts: [
                'Ringkasan inventory',
                'Stok menipis',
                'Material habis',
                'Stock Out hari ini',
            ],
            isRecording: false,
            speechSupported: false,
            recognition: null,
        };
    },
    mounted() {
        this.initSpeech();
    },
    beforeUnmount() {
        if (this.recognition) {
            this.recognition.abort();
        }
    },
    computed: {
        hasPermission() {
            return this.hasAnyPermission(['ai_assistant.use']);
        },
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

        togglePanel() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.checkConfiguration();
            }
        },

        closePanel() {
            this.isOpen = false;
        },

        async checkConfiguration() {
            try {
                const response = await fetch('/apps/ai-assistant');
                this.isConfigured = response.ok;
            } catch (error) {
                this.isConfigured = false;
            }
        },

        async sendMessage() {
            if (!this.userInput.trim() || this.isLoading || !this.isConfigured) return;

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
                        conversation_history: this.messages.slice(-6).map(msg => ({
                            role: msg.role === 'user' ? 'user' : 'assistant',
                            content: msg.content,
                        })),
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    this.messages.push({ role: 'assistant', content: data.message });
                } else {
                    this.messages.push({
                        role: 'assistant',
                        content: data.message || 'Maaf, terjadi kesalahan.'
                    });
                }

            } catch (error) {
                this.messages.push({
                    role: 'assistant',
                    content: 'Koneksi gagal. Coba lagi.'
                });
            } finally {
                this.isLoading = false;
                this.scrollToBottom();
            }
        },

        sendPrompt(prompt) {
            this.userInput = prompt;
            this.sendMessage();
        },

        formatMessage(content) {
            return this.escapeHtml(content || '')
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>');
        },

        escapeHtml(content) {
            return String(content)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.panelMessages;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },
    },
};
</script>

<style scoped>
.floating-ai-chat {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1000;
}

.floating-ai-button {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    color: white;
    border: none;
    box-shadow: 0 8px 24px rgba(124,58,237,0.4);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    transition: all 0.3s;
}

.floating-ai-button:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 32px rgba(124,58,237,0.5);
}

.floating-ai-button.active {
    background: #1e293b;
}

.ai-chat-panel {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 380px;
    height: 500px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 12px 48px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.panel-header {
    padding: 16px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ai-avatar-small {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
}

.header-title {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}

.header-subtitle {
    font-size: 11px;
    color: #64748b;
}

.header-actions {
    display: flex;
    gap: 6px;
}

.expand-btn, .close-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    background: white;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all 0.2s;
    text-decoration: none;
}

.expand-btn:hover, .close-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.panel-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.panel-messages::-webkit-scrollbar {
    width: 4px;
}

.panel-messages::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.mini-empty-state {
    text-align: center;
    padding: 20px;
}

.mini-ai-icon {
    width: 50px;
    height: 50px;
    margin: 0 auto 12px;
    background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #7c3aed;
}

.mini-suggested {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.mini-prompt {
    background: white;
    border: 1px solid #e0e7ff;
    color: #7c3aed;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.mini-prompt:hover {
    background: #f5f3ff;
    border-color: #7c3aed;
}

.mini-message {
    display: flex;
}

.mini-message.user {
    justify-content: flex-end;
}

.mini-bubble {
    max-width: 80%;
    display: flex;
    gap: 8px;
    align-items: flex-start;
}

.mini-message.user .mini-bubble {
    flex-direction: row-reverse;
}

.mini-avatar {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    flex-shrink: 0;
}

.mini-content {
    background: #f8fafc;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.5;
    color: #1e293b;
}

.mini-message.user .mini-content {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    color: white;
}

.mini-typing {
    padding: 8px 12px;
    background: #f8fafc;
    border-radius: 10px;
    display: flex;
    gap: 3px;
}

.mini-typing span {
    width: 6px;
    height: 6px;
    background: #94a3b8;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.mini-typing span:nth-child(2) {
    animation-delay: 0.2s;
}

.mini-typing span:nth-child(3) {
    animation-delay: 0.4s;
}

.panel-input {
    display: flex;
    gap: 8px;
    padding: 12px;
    border-top: 1px solid #e5e7eb;
    align-items: center;
}

.panel-input input {
    flex: 1;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
}

.panel-input input:focus {
    outline: none;
    border-color: #7c3aed;
}

.voice-btn-sm {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #f1f5f9;
    color: #64748b;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    transition: all 0.2s;
}

.voice-btn-sm:hover {
    background: #e0e7ff;
    color: #7c3aed;
}

.voice-btn-sm.recording {
    background: #dc2626;
    color: white;
    border-color: #dc2626;
    animation: pulse-rec 1.5s infinite;
}

@keyframes pulse-rec {
    0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.4); }
    50% { box-shadow: 0 0 0 6px rgba(220,38,38,0); }
}

.mini-send {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.mini-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.panel-warning {
    background: #fef3c7;
    color: #92400e;
    font-size: 11px;
    padding: 8px 12px;
    text-align: center;
}

.panel-fade-enter-active, .panel-fade-leave-active {
    transition: all 0.3s;
}

.panel-fade-enter-from, .panel-fade-leave-to {
    opacity: 0;
    transform: translateY(20px) scale(0.9);
}

@media (max-width: 768px) {
    .ai-chat-panel {
        width: calc(100vw - 32px);
        height: 70vh;
        bottom: 70px;
        right: -8px;
    }

    .floating-ai-chat {
        right: 16px;
        bottom: 16px;
    }
}
</style>
