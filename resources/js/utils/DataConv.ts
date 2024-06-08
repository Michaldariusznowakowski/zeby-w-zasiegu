import { Buffer } from 'buffer';

class DataConv {

    public static safeStringToUint8Array(data: string): Uint8Array {
        return new Uint8Array(Buffer.from(data, 'hex'));
    }
    public static safeUint8ArrayToString(data: Uint8Array): string {
        return Buffer.from(data).toString('hex');
    }
    public static safeInt32ArrayToString(data: Int32Array): string {
        let out = '';
        for (let i = 0; i < data.length; i++) {
            out += data[i].toString();
            if (i < data.length - 1) { out += ' '; }
        }
        return out;
    }
    public static safeStringToInt32Array(data: string): Int32Array {
        let str = data.split(' ');
        let out = new Int32Array(str.length);
        for (let i = 0; i < str.length; i++) {
            out[i] = parseInt(str[i]);
        }
        return out;
    }
    public static base64ToString(data: string): string {
        return Buffer.from(data, 'base64').toString('utf-8');
    }
    public static stringToBase64(data: string): string {
        return Buffer.from(data).toString('base64');
    }
}


// Export the classes
export { DataConv };