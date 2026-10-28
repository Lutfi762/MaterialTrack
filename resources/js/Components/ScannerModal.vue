<template>
    <div v-if="show" class="scanner-overlay" @click.self="$emit('close')">
        <div class="scanner-modal">
            <div class="scanner-header">
                <h5><i class="fa fa-camera"></i> Scan QR / Barcode</h5>
                <button @click="$emit('close')" class="close-btn"><i class="fa fa-times"></i></button>
            </div>
            <div class="scanner-body">
                <!-- Step 1: Permission / Camera Error -->
                <div v-if="!cameraReady && !manualMode" class="permission-screen">
                    <div v-if="permissionError" class="alert-box error">
                        <i class="fa fa-exclamation-triangle"></i>
                        <div>
                            <strong>Akses Kamera Gagal</strong>
                            <p class="mb-2 mt-1">{{ permissionError }}</p>
                            <button class="mtx-btn mtx-btn-danger mtx-btn-sm" @click="requestCamera">
                                <i class="fa fa-redo"></i> Coba Lagi
                            </button>
                        </div>
                    </div>
                    <div v-else class="requesting-screen">
                        <div class="fa fa-circle-notch fa-spin text-3xl text-indigo-600 mb-3" role="status"></div>
                        <p>Meminta izin kamera...</p>
                        <small class="text-gray-400">Browser akan menampilkan popup izin kamera.<br>Klik <strong>"Allow"</strong> atau <strong>"Izinkan"</strong>.</small>
                    </div>
                </div>

                <!-- Step 2: Camera Picker -->
                <div v-if="cameras.length > 1 && !manualMode" class="camera-picker mb-3">
                    <label class="mtx-label">Pilih Kamera:</label>
                    <select class="mtx-select" v-model="selectedCameraId" @change="switchCamera">
                        <option v-for="cam in cameras" :key="cam.deviceId" :value="cam.deviceId">
                            {{ cam.label || 'Kamera ' + cam.deviceId.substring(0,8) }}
                        </option>
                    </select>
                </div>

                <!-- Step 3: Scanner -->
                <div v-show="cameraReady && !manualMode">
                    <div id="qr-reader" ref="qrReader"></div>
                </div>

                <!-- Step 4: Manual Input -->
                <div v-if="manualMode" class="manual-input">
                    <div class="alert-box info mb-3">
                        <i class="fa fa-keyboard"></i>
                        <span>Mode input manual — ketik kode material secara langsung</span>
                    </div>
                    <div class="input-group">
                        <input
                            type="text"
                            class="mtx-input"
                            v-model="manualCode"
                            placeholder="Contoh: MTR-0001"
                            @keyup.enter="lookupManualCode"
                            ref="manualInput"
                        />
                        <button class="mtx-btn mtx-btn-primary" @click="lookupManualCode" :disabled="!manualCode.trim()">
                            <i class="fa fa-search"></i> Cari
                        </button>
                    </div>
                </div>

                <!-- Scanning hint -->
                <div v-if="cameraReady && !manualMode && !scanResult" class="scanning-hint mt-2">
                    <i class="fa fa-spinner fa-spin"></i> Arahkan kamera ke QR code...
                </div>

                <!-- Scan Result -->
                <div v-if="scanResult" class="scan-result mt-2">
                    <div v-if="scanResult.success" class="result-success">
                        <i class="fa fa-check-circle"></i>
                        <div>
                            <strong>{{ scanResult.item.code }}</strong> — {{ scanResult.item.name }}
                            <div class="result-stock">Stok: {{ scanResult.item.current_stock }} {{ scanResult.item.unit }}</div>
                        </div>
                    </div>
                    <div v-else class="result-error">
                        <i class="fa fa-times-circle"></i>
                        <span>{{ scanResult.message }}</span>
                    </div>
                </div>
            </div>

            <div class="scanner-footer">
                <button v-if="scanResult?.success" @click="selectItem" class="btn-select">
                    <i class="fa fa-check"></i> Pilih Material
                </button>
                <button v-if="scanResult || permissionError" @click="restartScan" class="btn-restart">
                    <i class="fa fa-redo"></i> Coba Lagi
                </button>
                <button @click="toggleManualMode" class="btn-manual" :class="{ active: manualMode }">
                    <i class="fa fa-keyboard"></i> {{ manualMode ? 'Gunakan Kamera' : 'Input Manual' }}
                </button>
                <button @click="$emit('close')" class="btn-cancel">Batal</button>
            </div>
        </div>
    </div>
</template>

<script>
import { Html5Qrcode } from 'html5-qrcode';

export default {
    props: { show: Boolean },
    emits: ['close', 'item-selected'],
    data() {
        return {
            html5Qrcode: null,
            cameraReady: false,
            isScanning: false,
            scanResult: null,
            cameras: [],
            selectedCameraId: null,
            permissionError: null,
            manualMode: false,
            manualCode: '',
        };
    },
    watch: {
        show(val) {
            if (val) {
                this.reset();
                this.$nextTick(() => this.requestCamera());
            } else {
                this.stopScan();
            }
        },
    },
    beforeUnmount() {
        this.stopScan();
    },
    methods: {
        reset() {
            this.cameraReady = false;
            this.isScanning = false;
            this.scanResult = null;
            this.cameras = [];
            this.selectedCameraId = null;
            this.permissionError = null;
            this.manualMode = false;
            this.manualCode = '';
        },

        async requestCamera() {
            this.permissionError = null;
            this.cameraReady = false;

            try {
                // Step 1: Request permission explicitly
                const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
                // Stop the temp stream immediately — we just needed permission
                tempStream.getTracks().forEach(t => t.stop());

                // Step 2: Enumerate devices
                const allDevices = await navigator.mediaDevices.enumerateDevices();
                this.cameras = allDevices.filter(d => d.kind === 'videoinput');

                if (this.cameras.length === 0) {
                    this.permissionError = 'Tidak ada kamera terdeteksi. Pastikan webcam terhubung.';
                    return;
                }

                // Prefer Logitech or non-firtual cameras
                const preferred = this.cameras.find(c =>
                    (c.label || '').toLowerCase().includes('logitech') ||
                    (c.label || '').toLowerCase().includes('hd pro') ||
                    (c.label || '').toLowerCase().includes('c920') ||
                    (c.label || '').toLowerCase().includes('c922')
                );
                this.selectedCameraId = preferred ? preferred.deviceId : this.cameras[0].deviceId;

                this.cameraReady = true;
                this.$nextTick(() => this.startScan());
            } catch (err) {
                console.error('Camera permission error:', err);
                this.permissionError = this.getUserFriendlyError(err);
            }
        },

        getUserFriendlyError(err) {
            const name = err?.name || '';
            if (name === 'NotAllowedError') {
                return 'Izin kamera ditolak. Klik ikon kamera di address bar browser, lalu pilih "Allow". Atau setelan: Settings → Privacy → Camera → izinkan situs ini.';
            }
            if (name === 'NotFoundError') {
                return 'Webcam tidak ditemukan. Pastikan Logitech terhubung via USB dan driver terinstall.';
            }
            if (name === 'NotReadableError') {
                return 'Kamera tidak bisa diakses. Coba: (1) Tutup Zoom/Teams/Skype, (2) Restart browser, (3) Cabut & tancap ulang USB webcam.';
            }
            if (name === 'OverconstrainedError') {
                return 'Kamera tidak mendukung pengaturan yang diminta.';
            }
            return 'Error: ' + (err?.message || String(err));
        },

        async startScan() {
            if (!this.cameraReady || this.manualMode) return;

            this.scanResult = null;
            this.isScanning = true;

            try {
                this.html5Qrcode = new Html5Qrcode('qr-reader');
                await this.html5Qrcode.start(
                    { deviceId: { exact: this.selectedCameraId } },
                    { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
                    (decodedText) => this.onScanSuccess(decodedText),
                    () => {},
                );
            } catch (err) {
                console.error('Scanner start error:', err);
                this.isScanning = false;
                this.permissionError = 'Gagal memulai scanner: ' + (err?.message || String(err));
            }
        },

        async switchCamera() {
            await this.stopScan();
            this.scanResult = null;
            this.$nextTick(() => this.startScan());
        },

        async stopScan() {
            if (this.html5Qrcode) {
                try { await this.html5Qrcode.stop(); } catch (e) {}
                try { this.html5Qrcode.clear(); } catch (e) {}
                this.html5Qrcode = null;
            }
            this.isScanning = false;
        },

        async restartScan() {
            await this.stopScan();
            this.scanResult = null;
            this.permissionError = null;

            if (this.manualMode) {
                this.$nextTick(() => {
                    if (this.$refs.manualInput) this.$refs.manualInput.focus();
                });
                return;
            }

            this.$nextTick(() => this.startScan());
        },

        async onScanSuccess(decodedText) {
            await this.stopScan();
            await this.lookupCode(decodedText);
        },

        async lookupManualCode() {
            const code = this.manualCode.trim();
            if (!code) return;
            await this.lookupCode(code);
        },

        async lookupCode(code) {
            try {
                const response = await fetch(`/apps/items/lookup?code=${encodeURIComponent(code)}`);
                const data = await response.json();
                this.scanResult = data;
            } catch (e) {
                this.scanResult = { success: false, message: 'Gagal memproses kode: ' + code };
            }
        },

        toggleManualMode() {
            if (this.manualMode) {
                // Switch back to camera
                this.manualMode = false;
                this.scanResult = null;
                this.manualCode = '';
                this.$nextTick(() => {
                    if (this.cameraReady) {
                        this.startScan();
                    } else {
                        this.requestCamera();
                    }
                });
            } else {
                // Switch to manual
                this.stopScan();
                this.manualMode = true;
                this.scanResult = null;
                this.$nextTick(() => {
                    if (this.$refs.manualInput) this.$refs.manualInput.focus();
                });
            }
        },

        selectItem() {
            if (this.scanResult?.success) {
                this.$emit('item-selected', this.scanResult.item);
                this.$emit('close');
            }
        },
    },
};
</script>

<style scoped>
.scanner-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.scanner-modal {
    background: white;
    border-radius: 16px;
    width: 100%;
    max-width: 440px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.scanner-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.scanner-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
}
.close-btn {
    width: 32px; height: 32px;
    border-radius: 8px; border: none;
    background: transparent; color: #64748b;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}
.close-btn:hover { background: #f1f5f9; }
.scanner-body { padding: 16px; min-height: 200px; }

.permission-screen { text-align: center; padding: 20px 0; }

.requesting-screen p { font-size: 14px; color: #374151; margin-bottom: 4px; }

.alert-box {
    display: flex; gap: 12px; padding: 14px;
    border-radius: 10px; font-size: 13px; text-align: left;
}
.alert-box.error {
    background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
}
.alert-box.info {
    background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;
}
.alert-box i { font-size: 18px; flex-shrink: 0; margin-top: 2px; }

#qr-reader { border-radius: 8px; overflow: hidden; }
.scanning-hint { text-align: center; padding: 10px; color: #64748b; font-size: 13px; }

.scan-result { padding: 12px; border-radius: 8px; }
.result-success {
    display: flex; align-items: center; gap: 10px;
    color: #16a34a; font-size: 14px;
}
.result-success i { font-size: 20px; }
.result-stock { font-size: 12px; color: #64748b; margin-top: 2px; }
.result-error {
    display: flex; align-items: center; gap: 8px;
    color: #dc2626; font-size: 14px;
}

.manual-input { padding: 10px 0; }

.scanner-footer {
    padding: 12px 16px;
    border-top: 1px solid #e5e7eb;
    display: flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap;
}
.btn-select {
    background: #7c3aed; color: white; border: none;
    padding: 8px 16px; border-radius: 8px; font-size: 13px;
    font-weight: 500; cursor: pointer;
    display: flex; align-items: center; gap: 6px;
}
.btn-select:hover { background: #6d28d9; }
.btn-restart, .btn-cancel {
    background: white; border: 1.5px solid #e5e7eb; color: #374151;
    padding: 8px 16px; border-radius: 8px; font-size: 13px;
    cursor: pointer; display: flex; align-items: center; gap: 6px;
}
.btn-restart:hover, .btn-cancel:hover { background: #f8fafc; }
.btn-manual {
    background: white; border: 1.5px solid #e5e7eb; color: #374151;
    padding: 8px 16px; border-radius: 8px; font-size: 13px;
    cursor: pointer; display: flex; align-items: center; gap: 6px;
}
.btn-manual:hover { background: #f8fafc; }
.btn-manual.active { background: #eff6ff; border-color: #3b82f6; color: #1d4ed8; }
</style>
