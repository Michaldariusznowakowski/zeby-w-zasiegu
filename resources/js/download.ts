import { openModal } from './utils/Modal';
import { Buffer } from 'buffer';
import { OpenSSL } from './utils/OpenSSL';
import { DataConv } from './utils/DataConv';
import { Store } from './utils/Store';

let FILE_DECRYPTED = false;
let H_NEW_A = null as HTMLAnchorElement | null;
const H_PROGRESS_BAR = document.getElementById('progress-bar') as HTMLProgressElement;
const H_META_FILE_PATH = document.querySelector('meta[name="file_path"]');
const H_META_FILE_NAME = document.querySelector('meta[name="file_name"]');
const H_META_FILE_SIZE = document.querySelector('meta[name="file_size"]');
const H_META_FILE_CONTENT = document.querySelector('meta[name="file_content"]');
const H_META_MESSAGE = document.querySelector('meta[name="message"]');
const H_LIST = [H_PROGRESS_BAR, H_META_FILE_PATH, H_META_FILE_NAME, H_META_FILE_SIZE, H_META_FILE_CONTENT, H_META_MESSAGE];
for (const h of H_LIST) {
    if (h === null) {
        throw new Error('Element not found');
    }
}
const OpenSSLInstance = new OpenSSL();
OpenSSLInstance.startWASM().then((num) => {
});
function updateProgress(progress: number) {
    setTimeout(() => {
        H_PROGRESS_BAR.value = progress;
    }, 0);
}
function downloadFile() {
    if (FILE_DECRYPTED) {
        // @ts-ignore
        if (H_NEW_A !== null) {
            // @ts-ignore
            H_NEW_A.click();
        }
    }
    updateProgress(0);
    //@ts-ignore
    const file_path = H_META_FILE_PATH.getAttribute('content');
    //@ts-ignore
    const file_name = H_META_FILE_NAME.getAttribute('content');
    //@ts-ignore
    const file_size = parseInt(H_META_FILE_SIZE.getAttribute('content'));
    //@ts-ignore
    const file_content = H_META_FILE_CONTENT.getAttribute('content');
    const dataConv = new DataConv();
    if (file_content === null) {
        return;
    }
    const privateKey = Store.getPrivateKey();
    if (privateKey === null) {
        return;
    }
    updateProgress(25);
    //@ts-ignore
    let message = JSON.parse(H_META_MESSAGE.getAttribute('content'));
    if (message === null) {
        return;
    }
    // @ts-ignore
    let filename = message["message"];
    // @ts-ignore
    message["message"] = file_content;
    OpenSSLInstance.rsaDecrypt(message, privateKey).then((decrypted) => {
        console.log(decrypted);
        updateProgress(50);
        FILE_DECRYPTED = true;
        console.log(filename);
        addAnchor(filename, decrypted);
    }).catch((error) => {
    }
    );

    return;
}

function addAnchor(fileName: string, fileContent: any) {
    updateProgress(100);
    let blob = new Blob([fileContent], { type: 'application/octet-stream' });
    H_NEW_A = document.createElement('a');
    H_NEW_A.href = window.URL.createObjectURL(blob);
    H_NEW_A.download = fileName;
    H_NEW_A.click();

}


// Export the function to the window object
(window as any).downloadFile = downloadFile;
