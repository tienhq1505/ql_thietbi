import { Html5Qrcode } from 'html5-qrcode';

window.Html5Qrcode = Html5Qrcode;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('qrScanner', () => ({
        scanner: null,
        isScanning: false,
        lastCode: '',
        lastScanTime: 0,
        errorMessage: '',

        async toggleCamera() {
            if (this.isScanning) {
                await this.stopCamera();
            } else {
                await this.startCamera();
            }
        },

        async startCamera() {
            this.errorMessage = '';
            try {
                if (!this.scanner) {
                    this.scanner = new Html5Qrcode('qr-reader');
                }
                const config = { fps: 10, qrbox: { width: 250, height: 250 } };
                await this.scanner.start(
                    { facingMode: 'environment' },
                    config,
                    (decodedText) => {
                        const now = Date.now();
                        // Chống quét lặp cùng mã trong 3 giây
                        if (decodedText === this.lastCode && (now - this.lastScanTime) < 3000) {
                            return;
                        }
                        this.lastCode = decodedText;
                        this.lastScanTime = now;

                        // Xử lý cả dạng đường dẫn .../q/{code} và mã thô
                        let code = decodedText.trim();
                        const match = code.match(/\/q\/([^/?#]+)/i);
                        if (match && match[1]) {
                            try {
                                code = decodeURIComponent(match[1]);
                            } catch (e) {
                                code = match[1];
                            }
                        }

                        // Gọi phương thức Livewire để xử lý mã
                        this.$wire.handleScannedCode(code);
                    },
                    () => {
                        // Frame scan miss, ignored
                    }
                );
                this.isScanning = true;
            } catch (err) {
                this.errorMessage = 'Không thể mở camera. Vui lòng kiểm tra quyền truy cập camera và đảm bảo chạy trên localhost hoặc HTTPS.';
                this.isScanning = false;
            }
        },

        async stopCamera() {
            if (this.scanner && this.isScanning) {
                try {
                    await this.scanner.stop();
                } catch (e) {
                    console.error('Lỗi khi dừng camera:', e);
                }
                this.isScanning = false;
            }
        },

        init() {
            window.addEventListener('beforeunload', () => {
                this.stopCamera();
            });
        },

        destroy() {
            this.stopCamera();
        }
    }));
});
