
import * as Qrcode from 'qrcode';
import { Buffer } from 'buffer';
class Store {
    public static async getQrCode(key: Uint8Array): Promise<string> {
        return Qrcode.toDataURL(Buffer.from(key).toString('hex')
            , { errorCorrectionLevel: 'L', version: 40 });
    }
    public static promptDownloadQrCode(data: any): void {
        const downloadLink = document.createElement('a');
        downloadLink.href = data;
        downloadLink.download = 'key.png';
        downloadLink.click();
    }
    public static savePrivateKey(privateKey: Uint8Array) {
        window.localStorage.setItem('privateKey', Buffer.from(privateKey).toString('hex'));
    }
    public static getPrivateKey(): Uint8Array | null {
        const privateKey = window.localStorage.getItem('privateKey');
        if (privateKey === null) {
            return null;
        }
        return new Uint8Array(Buffer.from(privateKey, 'hex'));
    }
    public static savePublicKey(publicKey: Uint8Array) {
        window.localStorage.setItem('publicKey', Buffer.from(publicKey).toString('hex'));
    }
    public static getPublicKey(): Uint8Array | null {
        let name = "publicKey=";
        let pubkey = window.localStorage.getItem('publicKey');
        if (pubkey === null) {
            return null;
        }
        return new Uint8Array(Buffer.from(pubkey, 'hex'));
    }
}
export { Store };