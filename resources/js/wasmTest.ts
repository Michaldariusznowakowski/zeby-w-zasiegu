//@ts-ignore 
import { OpenSSLWrapper } from './utils/OpenSSLWrapper';
import { DataConv } from './utils/DataConv';
import { Buffer } from 'buffer/'

class OpenSSLModified {
    private static counter = 0;
    private runtimeInitialized = false;
    private wrapper: any;
    public async startWASM(): Promise<number> {
        if (this.runtimeInitialized) {
            return 0;
        }
        if (OpenSSLModified.counter > 1) {
            console.error('OpenSSL is a singleton, only one instance can be created, Counter: ' + OpenSSLModified.counter);
            return -1;
        }
        let status = 0;
        //@ts-ignore
        let promise = OpenSSLWrapper(this.module);
        promise.then((wrapper: any | null) => {
            this.wrapper = wrapper;
            this.runtimeInitialized = true;
        }
        ).catch((error: any) => {
            console.error('Failed to initialize OpenSSL: ' + error);
            status = -1;
        }
        );
        await promise;
        if (this.runtimeInitialized === false || status != 0) {
            console.error('Failed to initialize OpenSSL, runtimeInitialized: ' + this.runtimeInitialized + ' status: ' + status);
            return -1;
        }
        this.initAES256();
        return 0;
    }

    constructor() {
        OpenSSLModified.counter++;
    }
    public async generatePrivateKey(): Promise<Uint8Array> {
        if (!this.runtimeInitialized) {
            throw new Error('OpenSSL not initialized');
        }
        //@ts-ignore Module has VectorUint8
        const vec = new this.wrapper.VectorUint8();
        startTimerSolo();
        let status = await this.wrapper.generateRSAKey(vec);
        outputResult('Key generated');
        if (status !== 0) {
            throw new Error('Failed to generate RSA key');
        }
        outputResult(' Solo key generation done', true);
        let key = new Uint8Array(vec.size());

        for (let i = 0; i < vec.size(); i++) {
            key[i] = vec.get(i);
        }
        outputResult('Key data copied');
        return key;
    }
    public async generatePublicKey(privateKey: Uint8Array): Promise<Uint8Array> {
        if (!this.runtimeInitialized) {
            throw new Error('OpenSSL not initialized');
        }
        //@ts-ignore Module has VectorUint8
        const publicKey = new this.wrapper.VectorUint8();
        //@ts-ignore Module has VectorUint8
        const privateKeyVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < privateKey.length; i++) {
            privateKeyVec.push_back(privateKey[i]);
        }
        outputResult('Private key copied');
        startTimerSolo();
        let status = this.wrapper.generatePublicKey(privateKeyVec, publicKey);
        if (status !== 0) {
            throw new Error('Failed to generate public key');
        }
        outputResult(' Solo public key generation done', true);
        outputResult('Public key generated');
        let keyPublic = new Uint8Array(publicKey.size());
        for (let i = 0; i < publicKey.size(); i++) {
            keyPublic[i] = publicKey.get(i);
        }
        outputResult('Public key copied');
        return keyPublic;
    }
    public async signMessage(privateKey: Uint8Array, message: string): Promise<Uint8Array> {
        if (!this.runtimeInitialized) {
            throw new Error('OpenSSL not initialized');
        }
        //@ts-ignore Module has VectorUint8
        let privateKeyVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < privateKey.length; i++) {
            //@ts-ignore Module has push_back
            privateKeyVec.push_back(privateKey[i]);
        }
        outputResult('Private key copied');
        //@ts-ignore Module has VectorUint8
        const messageVec = new this.wrapper.VectorUint8();
        //@ts-ignore Module has VectorUint8
        const signatureVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < message.length; i++) {
            messageVec.push_back(message.charCodeAt(i));
        }
        outputResult('Message copied');
        //@ts-ignore Module has signMessage
        startTimerSolo();
        let status = await
            this.wrapper.signMessage(privateKeyVec, messageVec, signatureVec);
        if (status !== 0) {
            throw new Error('Failed to sign message');
        }
        outputResult(' Solo signing done', true);
        outputResult('Message signed');
        let sig = new Uint8Array(signatureVec.size());
        for (let i = 0; i < signatureVec.size(); i++) {
            sig[i] = signatureVec.get(i);
        }
        outputResult('Signature copied');
        return sig;
    }
    public async verifySignature(publicKey: Uint8Array, message: string, signature: Uint8Array): Promise<boolean> {
        if (!this.runtimeInitialized) {
            throw new Error('OpenSSL not initialized');
        }
        //@ts-ignore Module has VectorUint8
        const messageVec = new this.wrapper.VectorUint8();
        //@ts-ignore Module has VectorUint8
        const signatureVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < message.length; i++) {
            messageVec.push_back(message.charCodeAt(i));
        }
        outputResult('Message copied');
        for (let i = 0; i < signature.length; i++) {
            signatureVec.push_back(signature[i]);
        }
        outputResult('Signature copied');
        // @ts-ignore Module has VectorUint8
        const publicKeyVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < publicKey.length; i++) {
            publicKeyVec.push_back(publicKey[i]);
        }
        outputResult('Public key copied');
        startTimerSolo();
        //@ts-ignore Module has verifySignature
        let status = await this.wrapper.verifySignature(publicKeyVec, messageVec, signatureVec);
        if (status < 0) {
            throw new Error('Failed to verify signature: ' + status);
        }
        outputResult(' Solo verification done', true);
        outputResult('Signature verified');
        if (status === 0) {
            return true;
        }
        return false;
    }
    private initAES256() {
        if (!this.runtimeInitialized) {
            throw new Error('OpenSSL not initialized');
        }
        //@ts-ignore Module has aes256Init
        let status = this.wrapper.initAES256();
        if (status !== 0) {
            throw new Error('Failed to initialize AES: ' + status);
        }
    }
    private destroyAES256() {
        if (!this.runtimeInitialized) {
            throw new Error('OpenSSL not initialized');
        }
        //@ts-ignore Module has aes256Destroy
        let status = this.wrapper.destroyAES256();
        if (status !== 0) {
            throw new Error('Failed to destroy AES: ' + status);
        }
    }
    public async rsaEncrypt(publicKey: Uint8Array, publicKeySecond: Uint8Array | null, message: Uint8Array, chatRoomId: number, recipient_id: number, type: number): Promise<any> {
        if (!this.runtimeInitialized) {
            throw new Error('OpenSSL not initialized');
        }
        //@ts-ignore Module has VectorUint8
        const publicKeyVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < publicKey.length; i++) {
            publicKeyVec.push_back(publicKey[i]);
        }
        outputResult('Public key copied');

        //@ts-ignore Module has VectorUint8
        const messageVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < message.length; i++) {
            messageVec.push_back(message[i]);
        }
        outputResult('Message copied');
        //@ts-ignore Module has VectorUint8
        let publicKeySecondVec = new this.wrapper.VectorUint8();
        if (publicKeySecond !== null) {
            for (let i = 0; i < publicKeySecond.length; i++) {
                publicKeySecondVec.push_back(publicKeySecond[i]);
            }
        }
        outputResult('Public key second copied');
        //@ts-ignore Module has VectorUint8
        const ivOut = new this.wrapper.VectorUint8();
        //@ts-ignore Module has VectorUint8
        const dataOut = new this.wrapper.VectorUint8();
        //@ts-ignore Module has VectorUint8
        const ekOut = new this.wrapper.VectorUint8();
        //@ts-ignore Module has VectorUint8
        const eklOut = new this.wrapper.VectorInt();
        startTimerSolo();
        //@ts-ignore Module has rsaEncrypt
        let status = await this.wrapper.encryptRSA(publicKeyVec, publicKeySecondVec, messageVec, dataOut, ivOut, ekOut, eklOut);
        if (status !== 0) {
            throw new Error('Failed to encrypt message: ' + status);
        }
        outputResult(' Solo encryption done', true);
        outputResult('Message encrypted');
        let encrypted = new Uint8Array(dataOut.size());
        for (let i = 0; i < dataOut.size(); i++) {
            encrypted[i] = dataOut.get(i);
        }
        outputResult('Encrypted data copied');

        let ek = new Uint8Array(ekOut.size());
        for (let i = 0; i < ekOut.size(); i++) {
            ek[i] = ekOut.get(i);
        }
        outputResult('EK copied');


        let ekl;
        if (eklOut.size() === 0) {
            ekl = null;
        } else {
            ekl = new Int32Array(eklOut.size());
            for (let i = 0; i < eklOut.size(); i++) {
                ekl[i] = eklOut.get(i);
            }
        }
        outputResult('EKL copied');
        let iv;
        if (ivOut.size() === 0) {
            iv = null;
        } else {
            iv = new Uint8Array(ivOut.size());
            for (let i = 0; i < ivOut.size(); i++) {
                iv[i] = ivOut.get(i);
            }
        }
        outputResult('IV copied');
        if (ek === null || encrypted === null || ekl === null || iv === null) {
            throw new Error('Failed to encrypt message'); // @todo MiNo resolve this
        }
        let data = {
            'type': type, // 0 - 'text', 1 - 'photo', 2 - 'file'
            'chatroom_id': chatRoomId,
            'recipient_id': recipient_id,
            'iv': DataConv.safeUint8ArrayToString(iv),
            'ek': DataConv.safeUint8ArrayToString(ek),
            'ekl': DataConv.safeInt32ArrayToString(ekl),
            'message': DataConv.safeUint8ArrayToString(encrypted)
        };
        return data;
    }
    public async rsaDecrypt(data: any, privateKey: Uint8Array): Promise<Uint8Array> {
        if (!this.runtimeInitialized) {
            throw new Error('OpenSSL not initialized');
        }
        //@ts-ignore Module has VectorUint8
        const keyVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < privateKey.length; i++) {
            keyVec.push_back(privateKey[i]);
        }
        outputResult('Private key copied');
        const ek = DataConv.safeStringToUint8Array(data['ek']);
        const ekl = DataConv.safeStringToInt32Array(data['ekl']);
        const iv = DataConv.safeStringToUint8Array(data['iv']);
        const cipher = DataConv.safeStringToUint8Array(data['message']);
        if (ek === null || ek === undefined || cipher === null || cipher === undefined || ekl === undefined || ekl === null) {
            throw new Error('Invalid message');
        }
        //@ts-ignore Module has VectorUint8
        const cipherVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < cipher.length; i++) {
            cipherVec.push_back(cipher[i]);
        }
        outputResult('Cipher copied');


        //@ts-ignore Module has VectorUint8
        const ekVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < ek.length; i++) {
            ekVec.push_back(ek[i]);
        }
        outputResult('EK copied');
        //@ts-ignore Module has VectorInt
        const eklVec = new this.wrapper.VectorInt();
        if (ekl !== null) {
            for (let i = 0; i < ekl.length; i++) {
                eklVec.push_back(ekl[i]);
            }
        }
        outputResult('EKL copied');
        //@ts-ignore Module has VectorUint8
        const ivVec = new this.wrapper.VectorUint8();
        if (iv !== null) {
            for (let i = 0; i < iv.length; i++) {
                ivVec.push_back(iv[i]);
            }
        }
        outputResult('IV copied');

        //@ts-ignore Module has rsaDecrypt
        const dataOut = new this.wrapper.VectorUint8();

        startTimerSolo();
        let status = await this.wrapper.decryptRSA(keyVec, cipherVec, ivVec, ekVec, eklVec, dataOut);
        if (status !== 0) {
            throw new Error('Failed to decrypt message: ' + status);
        }
        outputResult('Decryption done')
        outputResult(' Solo decryption done', true);
        let decrypted = new Uint8Array(dataOut.size());
        for (let i = 0; i < dataOut.size(); i++) {
            decrypted[i] = dataOut.get(i);
        }
        outputResult('Decrypted data copied');
        return decrypted;
    }
}

//  END OF OPENSSL MODIFIED CLASS

let OPENSSL_WRAPPER = new OpenSSLModified();
OPENSSL_WRAPPER.startWASM().then((status: number) => {
    if (status === 0) {
        console.log('OpenSSL initialized');
    } else {
        console.error('Failed to initialize OpenSSL');
    }
});

let H_WASM_FILE1MB = document.getElementById('file1mb');
let H_WASM_FILE5MB = document.getElementById('file5mb');
let H_WASM_FILE10MB = document.getElementById('file10mb');
let H_WASM_FILE50MB = document.getElementById('file50mb');
let H_WASM_FILE100MB = document.getElementById('file100mb');
let H_WASM_FILE500MB = document.getElementById('file500mb');
let H_WASM_FILE1GB = document.getElementById('file1gb');
let ENCRYPTED_FILES_DATA = [];
let DECRYPTED_FILES_DATA = [];
let H_FILES = [H_WASM_FILE1MB, H_WASM_FILE5MB, H_WASM_FILE10MB, H_WASM_FILE50MB, H_WASM_FILE100MB, H_WASM_FILE500MB, H_WASM_FILE1GB];
let H_CONSOLE = document.getElementsByClassName('console')[0];

for (let i = 0; i < H_FILES.length; i++) {
    if (H_FILES[i] == null) {
        console.error('File input not found' + i);
    }
}

let TEST_MSG = "Hello World! This is a test message. This message will be signed and then verified. If the signature is correct, then the message is authentic.";


let TIME_START = 0;
let TIME_START_SOLO = 0;

function startTimer() {
    TIME_START = performance.now();
    outputResult('Timer started');
}
function startTimerSolo() {
    TIME_START_SOLO = performance.now();
    outputResult('Solo timer started', true);
}

function getDuration(solo: boolean = false) {
    if (solo) {
        return performance.now() - TIME_START_SOLO;
    } else {
        return performance.now() - TIME_START;
    }
}

function outputResult(data: string, solo: boolean = false) {
    let time = 0;
    if (solo) {
        time = getDuration(true);
    } else {
        time = getDuration();
    }
    let p = document.createElement('p');
    p.innerText = '[' + time + 'ms] ' + data;
    // @ts-ignore Yeah we know it's there :) 
    H_CONSOLE.appendChild(p);
}

function getFileToArrayBuffer(HTMLInputElement: any): Promise<ArrayBuffer> {
    return new Promise((resolve, reject) => {
        let file = HTMLInputElement.files[0];
        let reader = new FileReader();
        reader.onload = function (event: any) {
            resolve(event.target.result);
        }
        reader.readAsArrayBuffer(file);
    });
}

function start() {
    startTimer();
    outputResult('Starting WASM');
    outputResult('Generating RSA key pair');
    outputResult('Generating private key');
    startTimer();
    OPENSSL_WRAPPER.generatePrivateKey().then((key: Uint8Array) => {
        outputResult('Private key generated');
        startTimer();
        outputResult('Generating public key');
        OPENSSL_WRAPPER.generatePublicKey(key).then((publicKey: Uint8Array) => {
            outputResult('Public key generated');
            startTimer();
            outputResult('Signing message');
            OPENSSL_WRAPPER.signMessage(key, TEST_MSG).then((signature: Uint8Array) => {
                outputResult('Message signed');
                startTimer();
                outputResult('Verifying signature');
                OPENSSL_WRAPPER.verifySignature(publicKey, TEST_MSG, signature).then((status: boolean) => {
                    if (status) {
                        outputResult('Signature verified');
                    } else {
                        outputResult('Failed to verify signature');
                    }

                    for (let i = 0; i < H_FILES.length; i++) {
                        // @ts-ignore
                        if (H_FILES[i].files.length === 0) {
                            continue;
                        }
                        getFileToArrayBuffer(H_FILES[i]).then((buffer: ArrayBuffer) => {
                            startTimer();
                            // @ts-ignore
                            outputResult('File ' + H_FILES[i].files[0].name + ' Start encrypting');
                            OPENSSL_WRAPPER.rsaEncrypt(publicKey, null, new Uint8Array(buffer), 1, 2, 0).then((data: any) => {
                                ENCRYPTED_FILES_DATA.push(data);
                                // @ts-ignore
                                outputResult('File ' + H_FILES[i].files[0].name + ' Encrypted');
                                startTimer();
                                // @ts-ignore
                                outputResult('File ' + H_FILES[i].files[0].name + ' Start decrypting');
                                OPENSSL_WRAPPER.rsaDecrypt(data, key).then((decrypted: Uint8Array) => {
                                    DECRYPTED_FILES_DATA.push(decrypted);
                                    // @ts-ignore
                                    outputResult('File ' + H_FILES[i].files[0].name + ' Decrypted');
                                });
                            }
                            );
                        }

                        );
                    }


                });
            });
        }
        );


    }
    );
}

(window as any).start = start;