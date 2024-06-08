import { openModal } from './utils/Modal';
import { Store } from './utils/Store';
import { Buffer } from 'buffer/'
import { OpenSSL } from './utils/OpenSSL';
import { Communication } from './utils/Communication';
import { DataConv } from './utils/DataConv';
window.Buffer = Buffer as any
const OpenSSLInstance = new OpenSSL();
OpenSSLInstance.startWASM().then((num) => {
    if (num === 0) {
        H_GENERATENEWKEY.disabled = false;
        H_GENERATENEWKEY.classList.remove('loading');
    } else {
        showDialog('Bład strony, skontaktuj się z administratorem. OpenSSL error.');
    }
});

let KEY: Uint8Array;
let SIGNEDEMAIL: Uint8Array;
let PUBLICKEY: Uint8Array;
const H_QRCODECANVAS1 = document.getElementById('qrcodeCanvas1') as HTMLCanvasElement
const H_QRCODECANVAS2 = document.getElementById('qrcodeCanvas2') as HTMLCanvasElement;
const H_QRCODEDOWNLOAD1 = document.getElementById('qrcodeDownload1') as HTMLAnchorElement;
const H_QRCODEDOWNLOAD2 = document.getElementById('qrcodeDownload2') as HTMLAnchorElement;
const H_QRCODEDOWNLOAD3 = document.getElementById('qrcodeDownload3') as HTMLAnchorElement;
const H_GENERATENEWKEY = document.getElementById('generateNewKey') as HTMLButtonElement;
const H_PROGRESS = document.getElementById('progressBar') as HTMLProgressElement;
const H_NEXTSTEP = document.getElementById('nextStep') as HTMLButtonElement;
const USED_ELEMENTS = [H_QRCODECANVAS1, H_QRCODECANVAS2, H_QRCODEDOWNLOAD1, H_QRCODEDOWNLOAD2, H_QRCODEDOWNLOAD3, H_PROGRESS, H_NEXTSTEP, H_GENERATENEWKEY];
for (const element of USED_ELEMENTS) {
    if (element === null) {
        showDialog('Bład strony, skontaktuj się z administratorem. Element error.');
    }
}
function hideKeyElements() {
    H_QRCODECANVAS1.hidden = true;
    H_QRCODECANVAS2.hidden = true;
    H_QRCODEDOWNLOAD1.hidden = true;
    H_QRCODEDOWNLOAD2.hidden = true;
    H_QRCODEDOWNLOAD3.hidden = true;
    H_NEXTSTEP.hidden = true;
    H_PROGRESS.value = 0;
    H_PROGRESS.hidden = false;
}
function updateProgress(progress: number) {
    setTimeout(() => {
        H_PROGRESS.value = progress;
    }, 0);
}
function GenerateKey(event: Event) {
    if (H_GENERATENEWKEY.disabled === true) { return; }
    event.preventDefault();
    H_GENERATENEWKEY.disabled = true;
    hideKeyElements();
    const button = event.currentTarget as HTMLElement;
    if (button === null) {
        showDialog('Bład strony, skontaktuj się z administratorem. Event error.');
        return;
    }
    setTimeout(() => {
        processData();
    }, 1000);
}
async function processData() {
    const key = OpenSSLInstance.generatePrivateKey();
    key.then((key: Uint8Array | null) => {
        updateProgress(25);

        if (key === null) {
            showDialog('Bład strony, skontaktuj się z administratorem. Key error.');
            return;
        }
        OpenSSLInstance.generatePublicKey(key).then((publicKey: Uint8Array | null) => {
            updateProgress(50);
            if (publicKey === null) {
                showDialog('Bład strony, skontaktuj się z administratorem. Public key error.');
                return;
            }
            PUBLICKEY = publicKey;
            Communication.getData('email', true).then(response => {
                const email = Buffer.from(response).toString('utf-8');
                OpenSSLInstance.signMessage(key, email).then((signedEmail: Uint8Array | null) => {
                    updateProgress(75);
                    if (signedEmail === null) {
                        showDialog('Bład strony, skontaktuj się z administratorem. Signed email error.');
                        return;
                    }
                    KEY = key;
                    SIGNEDEMAIL = signedEmail;
                });
                let halfKey = key.slice(0, key.length / 2);
                let halfKey2 = key.slice(key.length / 2);

                updateProgress(80);
                Store.getQrCode(halfKey).then((data: string) => {
                    makeCanvasQrCode(H_QRCODECANVAS1, data, 'Zęby w Zasięgu, klucz prywatny część 1', "#00ff11").then(data => {
                        if (data === undefined) {
                            showDialog('Bład strony, skontaktuj się z administratorem. Data error.');
                            return;
                        } else {
                            H_QRCODEDOWNLOAD1.href = data;
                            H_QRCODEDOWNLOAD1.hidden = false;
                        }
                    });
                    H_QRCODECANVAS1.hidden = false;
                    H_GENERATENEWKEY.disabled = false;

                });

                Store.getQrCode(halfKey2).then((data: string) => {
                    makeCanvasQrCode(H_QRCODECANVAS2, data, 'Zęby w Zasięgu, klucz prywatny część 2', "#0040ff").then(data => {
                        if (data === undefined) {
                            showDialog('Bład strony, skontaktuj się z administratorem. Data error.');
                            return;
                        } else {
                            H_QRCODEDOWNLOAD2.href = data;
                            H_QRCODEDOWNLOAD2.hidden = false;
                        }
                    });
                    H_QRCODECANVAS2.hidden = false;
                });
                H_QRCODEDOWNLOAD3.href = URL.createObjectURL(new Blob([Buffer.from(key).toString('hex')], { type: 'text/plain' }));
                H_QRCODEDOWNLOAD3.hidden = false;
                updateProgress(100);
                H_PROGRESS.hidden = true;
                H_NEXTSTEP.hidden = false;
            });
        });
    });
}
async function makeCanvasQrCode(canvas: HTMLCanvasElement, qrcodeData64: string, text: string, frameColor: string = '#000000'): Promise<string> {
    return new Promise<string>((resolve, reject) => {
        canvas.hidden = false;
        const img = new Image();
        img.onload = function () {
            canvas.width = img.width + 60;
            canvas.height = img.height + 60;
            const ctx = canvas.getContext('2d');
            if (ctx === null) {
                reject('Error: Unable to get 2D context.');
                return;
            }
            ctx.drawImage(img, 30, 30);
            const frameWidth = 40;
            ctx.lineWidth = frameWidth;
            ctx.strokeStyle = frameColor;
            ctx.strokeRect(frameWidth / 2, frameWidth / 2, canvas.width - frameWidth, canvas.height - frameWidth);
            ctx.font = 'bold 30px Arial';
            ctx.fillStyle = '#000000';
            ctx.textAlign = 'center';
            ctx.fillText(text, canvas.width / 2, 30);
            ctx.font = 'bold 15px Arial';
            ctx.fillStyle = '#000000';
            ctx.textAlign = 'center';
            ctx.fillText('Nigdy nie udostępniaj tego klucza nikomu! Przechowaj ten klucz w celu odszyfrowania swoich wiadomości.', canvas.width / 2, canvas.height - 10);
            const imageData = canvas.toDataURL('image/png');
            resolve(imageData);
        };
        img.onerror = function () {
            reject('Error: Failed to load image.');
        };
        img.src = qrcodeData64;
    });
}

function NextStep(event: Event): void {
    const button = event.currentTarget as HTMLElement;
    if (button === null) {
        showDialog('Bład strony, skontaktuj się z administratorem. Event error.');
        return;
    }
    if (KEY === undefined || SIGNEDEMAIL === undefined || PUBLICKEY === undefined) {
        showDialog('Bład strony, skontaktuj się z administratorem. Next step undefined data error.');
        return;
    }
    Store.savePrivateKey(KEY);
    Store.savePublicKey(PUBLICKEY);
    const data = {
        'signed_email': DataConv.safeUint8ArrayToString(SIGNEDEMAIL),
        'public_key': DataConv.safeUint8ArrayToString(PUBLICKEY),
    };
    Communication.postData(JSON.stringify(data),
        'encrypt/store').then(response => {
            showDialog('Klucz został wygenerowany zostaniesz przekierowany na stronę główną');
            setTimeout(() => {
                window.location.href = '/';
            }, 2000);
        }
        ).catch(error => {
            showDialog('Bład serwera, skontaktuj się z administratorem: ' + error);
        });
}
function showDialog(text: string): boolean {
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
(window as any).GenerateKey = GenerateKey;
(window as any).NextStep = NextStep;