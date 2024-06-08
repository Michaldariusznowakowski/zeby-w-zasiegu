//@ts-ignore :)
import { OpenSSLWrapper } from './OpenSSLWrapper';
import { DataConv } from './DataConv';
import { Buffer } from 'buffer/'

class OpenSSL {
    private static counter = 0;
    private runtimeInitialized = false;
    private wrapper: any;
    public async startWASM(): Promise<number> {
        if (this.runtimeInitialized) {
            return 0;
        }
        if (OpenSSL.counter > 1) {
            console.error('OpenSSL is a singleton, only one instance can be created, Counter: ' + OpenSSL.counter);
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
        OpenSSL.counter++;
    }
    public async generatePrivateKey(): Promise<Uint8Array> {
        if (!this.runtimeInitialized) {
            throw new Error('OpenSSL not initialized');
        }
        //@ts-ignore Module has VectorUint8
        const vec = new this.wrapper.VectorUint8();
        let status = await this.wrapper.generateRSAKey(vec);
        if (status !== 0) {
            throw new Error('Failed to generate RSA key');
        }
        let key = new Uint8Array(vec.size());
        for (let i = 0; i < vec.size(); i++) {
            key[i] = vec.get(i);
        }
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
        let status = this.wrapper.generatePublicKey(privateKeyVec, publicKey);
        if (status !== 0) {
            throw new Error('Failed to generate public key');
        }
        let keyPublic = new Uint8Array(publicKey.size());
        for (let i = 0; i < publicKey.size(); i++) {
            keyPublic[i] = publicKey.get(i);
        }
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
        //@ts-ignore Module has VectorUint8
        const messageVec = new this.wrapper.VectorUint8();
        //@ts-ignore Module has VectorUint8
        const signatureVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < message.length; i++) {
            messageVec.push_back(message.charCodeAt(i));
        }
        //@ts-ignore Module has signMessage
        let status = await
            this.wrapper.signMessage(privateKeyVec, messageVec, signatureVec);
        if (status !== 0) {
            throw new Error('Failed to sign message');
        }
        let sig = new Uint8Array(signatureVec.size());
        for (let i = 0; i < signatureVec.size(); i++) {
            sig[i] = signatureVec.get(i);
        }
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
        for (let i = 0; i < signature.length; i++) {
            signatureVec.push_back(signature[i]);
        }
        // @ts-ignore Module has VectorUint8
        const publicKeyVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < publicKey.length; i++) {
            publicKeyVec.push_back(publicKey[i]);
        }
        //@ts-ignore Module has verifySignature
        let status = await this.wrapper.verifySignature(publicKeyVec, messageVec, signatureVec);
        if (status < 0) {
            throw new Error('Failed to verify signature: ' + status);
        }
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
        //@ts-ignore Module has VectorUint8
        const messageVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < message.length; i++) {
            messageVec.push_back(message[i]);
        }
        //@ts-ignore Module has VectorUint8
        let publicKeySecondVec = new this.wrapper.VectorUint8();
        if (publicKeySecond !== null) {
            for (let i = 0; i < publicKeySecond.length; i++) {
                publicKeySecondVec.push_back(publicKeySecond[i]);
            }
        }
        //@ts-ignore Module has VectorUint8
        const ivOut = new this.wrapper.VectorUint8();
        //@ts-ignore Module has VectorUint8
        const dataOut = new this.wrapper.VectorUint8();
        //@ts-ignore Module has VectorUint8
        const ekOut = new this.wrapper.VectorUint8();
        //@ts-ignore Module has VectorUint8
        const eklOut = new this.wrapper.VectorInt();
        //@ts-ignore Module has rsaEncrypt
        let status = await this.wrapper.encryptRSA(publicKeyVec, publicKeySecondVec, messageVec, dataOut, ivOut, ekOut, eklOut);
        if (status !== 0) {
            throw new Error('Failed to encrypt message: ' + status);
        }
        let encrypted = new Uint8Array(dataOut.size());
        for (let i = 0; i < dataOut.size(); i++) {
            encrypted[i] = dataOut.get(i);
        }

        let ek = new Uint8Array(ekOut.size());
        for (let i = 0; i < ekOut.size(); i++) {
            ek[i] = ekOut.get(i);
        }


        let ekl;
        if (eklOut.size() === 0) {
            ekl = null;
        } else {
            ekl = new Int32Array(eklOut.size());
            for (let i = 0; i < eklOut.size(); i++) {
                ekl[i] = eklOut.get(i);
            }
        }
        let iv;
        if (ivOut.size() === 0) {
            iv = null;
        } else {
            iv = new Uint8Array(ivOut.size());
            for (let i = 0; i < ivOut.size(); i++) {
                iv[i] = ivOut.get(i);
            }
        }
        if (ek === null || encrypted === null || ekl === null || iv === null) {
            throw new Error('Failed to encrypt message');
        }
        let data = {
            'type': type,
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
        //@ts-ignore Module has VectorUint8
        const ekVec = new this.wrapper.VectorUint8();
        for (let i = 0; i < ek.length; i++) {
            ekVec.push_back(ek[i]);
        }
        //@ts-ignore Module has VectorInt
        const eklVec = new this.wrapper.VectorInt();
        if (ekl !== null) {
            for (let i = 0; i < ekl.length; i++) {
                eklVec.push_back(ekl[i]);
            }
        }
        //@ts-ignore Module has VectorUint8
        const ivVec = new this.wrapper.VectorUint8();
        if (iv !== null) {
            for (let i = 0; i < iv.length; i++) {
                ivVec.push_back(iv[i]);
            }
        }

        //@ts-ignore Module has rsaDecrypt
        const dataOut = new this.wrapper.VectorUint8();

        let status = await this.wrapper.decryptRSA(keyVec, cipherVec, ivVec, ekVec, eklVec, dataOut);
        if (status !== 0) {
            throw new Error('Failed to decrypt message: ' + status);
        }
        let decrypted = new Uint8Array(dataOut.size());
        for (let i = 0; i < dataOut.size(); i++) {
            decrypted[i] = dataOut.get(i);
        }
        return decrypted;
    }
}

export { OpenSSL };