import { openModal } from './utils/Modal';
import { OpenSSL } from './utils/OpenSSL';
import { Store } from './utils/Store';
import { Buffer } from 'buffer';
import QrScanner from 'qr-scanner';
import { Communication } from './utils/Communication';
import { DataConv } from './utils/DataConv';
const PRIVATE_KEY_HEADER = '-----BEGIN PRIVATE KEY-----'
const PRIVATE_KEY_FOOTER = '-----END PRIVATE KEY-----'
const ERROR_MSG = 'Bład strony, skontaktuj się z administratorem.';
const H_LOADFILES = document.getElementById('loadFiles') as HTMLButtonElement;
const H_QRCODEFILE_1 = document.getElementById('qrcodeFile1') as HTMLInputElement;
const H_QRCODEFILE_2 = document.getElementById('qrcodeFile2') as HTMLInputElement;
const H_QRCODEIMG_1 = document.getElementById('qrcodeImage1') as HTMLImageElement;
const H_QRCODEIMG_2 = document.getElementById('qrcodeImage2') as HTMLImageElement;
const H_PROGRESS = document.getElementById('progressBar') as HTMLProgressElement;
const H_TXTFILE_3 = document.getElementById('txtFile3') as HTMLInputElement;
const LIST_OF_ELEMENTS = [H_LOADFILES, H_QRCODEFILE_1, H_QRCODEFILE_2, H_TXTFILE_3, H_QRCODEIMG_1, H_QRCODEIMG_2, H_PROGRESS];
for (const element of LIST_OF_ELEMENTS) {
    if (element === null) {
        showErrorDialog('Bład strony, skontaktuj się z administratorem. Element error.');
    }
}
function updateProgress(progress: number) {
    setTimeout(() => {
        H_PROGRESS.value = progress;
    }, 0);
}
const OpenSSLInstance = new OpenSSL();
OpenSSLInstance.startWASM().then((num) => {
    if (num === 0) {
        H_LOADFILES.disabled = false;
        H_LOADFILES.classList.remove('loading');
    } else {
        showErrorDialog(ERROR_MSG + ' OpenSSL error.');
    }
});
if (Store.getPrivateKey() !== null && Store.getPublicKey() !== null) {
    showErrorDialog('Klucz został już załadowany, za chwilę zostaniesz przekierowany na stronę główną.');
    setTimeout(() => {
        window.location.href = '/';
    }, 2000);
}
function showFileInImg(input: HTMLInputElement, img: HTMLImageElement) {
    img.hidden = true;
    img.src = '';
    if (input === null || input.files === null || input.files.length === 0) {
        return;
    }
    const file = input.files[0];
    img.hidden = false;
    const reader = new FileReader();
    reader.onload = function (e) {
        img.src = e.target?.result as string;
    };
    reader.readAsDataURL(file);
}

function onLoadShowQR1(event: Event) {
    showFileInImg(H_QRCODEFILE_1, H_QRCODEIMG_1);
}
function onLoadShowQR2(event: Event) {
    showFileInImg(H_QRCODEFILE_2, H_QRCODEIMG_2);
}
function readFile(event: Event): void {
    if (H_TXTFILE_3.files !== null && H_TXTFILE_3.files.length > 0) {
        const file = H_TXTFILE_3.files[0];
        if (file === null) {
            showErrorDialog(ERROR_MSG + 'File error.');
            return;
        }
        const reader = new FileReader();
        reader.readAsText(file);
        reader.onload = function (e) {
            const contents = e.target?.result;
            if (contents === null) {
                showErrorDialog(ERROR_MSG + 'Contents error.');
                return;
            }
            try {
                const key = new Uint8Array(Buffer.from(contents as string, 'hex'));
                const keyString = Buffer.from(key).toString('utf-8');
                if (keyString.includes(PRIVATE_KEY_HEADER) === false && keyString.includes(PRIVATE_KEY_FOOTER) === false) {
                    throw new Error('Key error.');
                }
                processKey(key);
            } catch (error) {
                showErrorDialog(ERROR_MSG + 'Key error.');
            }
        }
        return;
    }
    if (H_QRCODEFILE_1.files !== null && H_QRCODEFILE_1.files.length > 0 && H_QRCODEFILE_1.files[0] !== null && H_QRCODEFILE_2.files !== null && H_QRCODEFILE_2.files.length > 0 && H_QRCODEFILE_2.files[0] !== null) {
        const file1 = H_QRCODEFILE_1.files[0];
        const file2 = H_QRCODEFILE_2.files[0];
        QrScanner.scanImage(file1, {
            returnDetailedScanResult: true,
            alsoTryWithoutScanRegion: true
        }).then(result1 => {
            QrScanner.scanImage(file2, {
                returnDetailedScanResult: true,
                alsoTryWithoutScanRegion: true
            }).then(result2 => {
                const key1 = new Uint8Array(Buffer.from(result1.data, 'hex'));
                const key2 = new Uint8Array(Buffer.from(result2.data, 'hex'));
                const key1String = Buffer.from(key1).toString('utf-8');
                const key2String = Buffer.from(key2).toString('utf-8');
                if (key1String.includes(PRIVATE_KEY_HEADER) === false && key1String.includes(PRIVATE_KEY_FOOTER) === false) {
                    showErrorDialog('Część 1 klucza prywatnego jest niepoprawna.');
                    return;
                }
                if (key2String.includes(PRIVATE_KEY_HEADER) === false && key2String.includes(PRIVATE_KEY_FOOTER) === false) {
                    showErrorDialog('Część 2 klucza prywatnego jest niepoprawna.');
                    return;
                }
                let firtsHalfKey;
                let secondHalfKey;
                if (key1String.includes(PRIVATE_KEY_HEADER) && key2String.includes(PRIVATE_KEY_FOOTER)) {
                    firtsHalfKey = key1;
                    secondHalfKey = key2;
                } else if (key2String.includes(PRIVATE_KEY_HEADER) && key1String.includes(PRIVATE_KEY_FOOTER)) {
                    firtsHalfKey = key2;
                    secondHalfKey = key1;
                } else {
                    showErrorDialog('Sprawdź czy wczytane pliki są poprawne. Znaleziono dwie te same części klucza prywatnego.');
                    return;
                }
                const key = new Uint8Array(firtsHalfKey.length + secondHalfKey.length);
                key.set(firtsHalfKey, 0);
                key.set(secondHalfKey, firtsHalfKey.length);
                processKey(key);
                return
            }).catch(error => showErrorDialog('Nie znaleziono qr code w pliku 2.'));
        }).catch(error => showErrorDialog('Nie znaleziono qr code w pliku 1.'));
        return;
    }
    showErrorDialog('Proszę wczytać dwie części klucza prywatnego, lub plik tekstowy z kluczem.');
    return;
}
function processKey(key: Uint8Array) {
    if (key.length === 0) {
        showErrorDialog(ERROR_MSG + 'Key error.');
        return;
    }
    updateProgress(10);
    OpenSSLInstance.generatePublicKey(key).then((publicKey) => {
        if (publicKey === null) {
            showErrorDialog(ERROR_MSG + 'Public key error.');
            return;
        }
        updateProgress(25);
        const signedEmailPromise = Communication.getData('signedEmail', true);
        const emailPromise = Communication.getData('email', true);
        signedEmailPromise.then((resultSigned) => {
            emailPromise.then(
                (resultEmail) => {
                    const unpackedEmail = Buffer.from(resultEmail, 'base64').toString('utf-8');
                    updateProgress(50);
                    const signedEmailArray = DataConv.safeStringToUint8Array(DataConv.base64ToString(resultSigned));

                    OpenSSLInstance.verifySignature(publicKey, unpackedEmail, signedEmailArray).then((result) => {
                        if (result === false) {
                            showErrorDialog('Klucz jest niepoprawny, upewnij się, że skanujesz poprawny klucz.');
                            return;
                        } else {
                            Store.savePrivateKey(key);
                            Store.savePublicKey(publicKey);
                            showErrorDialog('Klucz został poprawnie załadowny, za chwilę zostaniesz przekierowany na stronę główną.');
                            setTimeout(() => {
                                window.location.href = '/';
                            }, 2000
                            );
                            updateProgress(100);
                        }
                    });
                });
        });
    }
    );
}
function showErrorDialog(text: string): boolean {
    const dialog = document.getElementById('dialogError');
    if (dialog === null) {
        console.error('Dialog not found');
        return false;
    }
    const dialogText = dialog.querySelector('#dialogText');
    if (dialogText === null) {
        console.error('Dialog text not found');
        return false;
    }
    dialogText.textContent = text;
    openModal(dialog);
    return true;
}

// Global variables
(window as any).readFile = readFile;
(window as any).onLoadShowQR1 = onLoadShowQR1;
(window as any).onLoadShowQR2 = onLoadShowQR2;