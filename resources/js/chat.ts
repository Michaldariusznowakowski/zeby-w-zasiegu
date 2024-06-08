import { openModal } from './utils/Modal';
import { Buffer } from 'buffer';
import { OpenSSL } from './utils/OpenSSL';
import { Communication } from './utils/Communication';
import { DataConv } from './utils/DataConv';
import { Store } from './utils/Store';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

interface Window {
    EchoInst: Echo;
    PusherInst: Pusher;
}
declare var window: Window;
let H_CHAT_INPUT_TEXT = document.querySelector('#input-message') as HTMLInputElement;
let H_META_RECIPIENT_PUBLIC_KEY = document.querySelector('meta[name="recipient_public_key"]') as HTMLMetaElement;
let H_META_RECIPIENT_NAME = document.querySelector('meta[name="recipient_name"]') as HTMLMetaElement;
let H_META_RECIPIENT_SURNAME = document.querySelector('meta[name="recipient_surname"]') as HTMLMetaElement;
let H_META_CHATROOM_ID = document.querySelector('meta[name="chatroom_id"]') as HTMLMetaElement;
let H_META_USER_ID = document.querySelector('meta[name="user_id"]') as HTMLMetaElement;
let H_META_RECIPIENT_ID = document.querySelector('meta[name="recipient_id"]') as HTMLMetaElement;
let H_CHAT_SCROLL_MSG = document.querySelector('.chat-messages') as HTMLElement;
let H_CHAT_INPUT_FILE = document.querySelector('#input-attachment') as HTMLInputElement;

let H_META_MESSAGES = document.querySelector('meta[name="messages"]') as HTMLMetaElement;

let H_DECLARED_LIST = [H_CHAT_INPUT_TEXT
    , H_META_RECIPIENT_PUBLIC_KEY, H_META_RECIPIENT_NAME, H_META_RECIPIENT_SURNAME
    , H_META_CHATROOM_ID, H_META_USER_ID, H_META_RECIPIENT_ID, H_CHAT_SCROLL_MSG, H_META_MESSAGES, H_CHAT_INPUT_FILE];
for (const element of H_DECLARED_LIST) {
    if (element === null) {
        console.error('Element not found');
    }
}
const OpenSSLInstance = new OpenSSL();
OpenSSLInstance.startWASM().then((num) => {
    if (num !== 0) {
        console.error('OpenSSL error');
    } else {
        onLoadGetOldMessages();
    }
});
H_CHAT_INPUT_TEXT.addEventListener('keyup', (event) => {
    if (event.key === 'Enter') {
        sendMessage();
    }
});
class Recipient {
    public id: number;
    public name: string;
    public surname: string;
    public publicKey: Uint8Array;
    public chatroomId: number;
    constructor(name: string, publicKey: Uint8Array, id: number
        , surname: string, chatroomId: number) {
        this.name = name;
        this.publicKey = publicKey;
        this.id = id;
        this.surname = surname;
        this.chatroomId = chatroomId;
    }
}
function onLoadGetOldMessages() {
    let privatekey = Store.getPrivateKey();
    if (privatekey === null) {
        throw new Error('Private key not found');
    }
    let messages = JSON.parse(DataConv.base64ToString(H_META_MESSAGES.content));
    if (messages === null || messages.length === 0) {
        removeLoadingIcon();
        return;
    }
    for (const message of messages) {
        let user_recipient = message.sender_id === RECIPIENT.id;
        let recipient_lost_key = message.recipient_lost_key;
        let sender_lost_key = message.sender_lost_key;
        if (user_recipient && recipient_lost_key || !user_recipient && sender_lost_key) {
            let msg = prepareMessage(true, 'Klucz został zmieniony, nie można odszyfrować wiadomości.', message.type, 'Ty', message.sent_at, message.id,
                false, sender_lost_key);
            appendMessage(msg, true);
            continue;
        }
        let other_user_lost_key = user_recipient ? recipient_lost_key : sender_lost_key;
        if (message.type === 0) {
            OpenSSLInstance.rsaDecrypt(message, privatekey).then((decrypted) => {

                removeLoadingIcon();
                let text = Buffer.from(decrypted).toString('utf8');
                let owner = message.sender_id !== RECIPIENT.id;
                let owner_name = owner ? 'Ty' : RECIPIENT.name + ' ' + RECIPIENT.surname;
                let msg = prepareMessage(owner, text, message.type, owner_name, message.sent_at, message.id, other_user_lost_key, false);
                appendMessage(msg, true);
            }).catch((err) => {
                console.error(err);
            });
        } else if (message.type === 2) {
            removeLoadingIcon();
            let owner = message.sender_id !== RECIPIENT.id;
            let owner_name = owner ? 'Ty' : RECIPIENT.name + ' ' + RECIPIENT.surname;
            let msg = prepareMessage(owner, message.message, message.type, owner_name, message.sent_at, message.id, other_user_lost_key, false);
            appendMessage(msg, true);
        }
    }
    seen(messages[messages.length - 1].id, messages[messages.length - 1].chatroom_id);
    return;
}
function seen(messageId: number, chatroomId: number): void {
    let data = {
        message_id: messageId,
        chatroom_id: chatroomId
    };
    Communication.postData(JSON.stringify(data), 'setSeenToAllBefore').then((response) => {
        console.log(response);
    }).catch((err) => {
        console.error(err);
    });
}

function onLoadGetRecipient(): Recipient {
    let recipient = new Recipient(
        H_META_RECIPIENT_NAME.content,
        DataConv.safeStringToUint8Array(H_META_RECIPIENT_PUBLIC_KEY.content),
        parseInt(H_META_RECIPIENT_ID.content),
        H_META_RECIPIENT_SURNAME.content,
        parseInt(H_META_CHATROOM_ID.content)
    );

    return recipient;
}

const RECIPIENT = onLoadGetRecipient();
//@ts-ignore
window.EchoInst.private('chatroom.' + RECIPIENT.chatroomId).listen('NewMessage', (e) => {
    eventMsgReceived(e);
});

function eventMsgReceived(e: any) {
    if (e.message === null) {
        return;
    }
    let is_own = e.message.sender_id !== RECIPIENT.id;

    let recipient_lost_key = e.message.recipient_lost_key;
    let sender_lost_key = e.message.sender_lost_key;
    if (recipient_lost_key) {
        let msg = prepareMessage(true, 'Klucz został zmieniony, nie można odszyfrować wiadomości.', e.message.type, 'Ty', e.message.sent_at, e.message.id, false, sender_lost_key);
        appendMessage(msg);
        return;
    }
    let privatekey = Store.getPrivateKey();
    if (privatekey === null) {
        throw new Error('Private key not found');
    }
    if (e.message.type === 0) {
        OpenSSLInstance.rsaDecrypt(e.message, privatekey).then((decrypted) => {
            let text = Buffer.from(decrypted).toString('utf8');
            let msg = null;
            if (is_own) {
                msg = prepareMessage(true, text, e.message.type, 'Ty', e.message.sent_at, e.message.id, false, sender_lost_key);
                appendMessage(msg);
            } else {
                msg = prepareMessage(false, text, e.message.type, RECIPIENT.name + ' ' + RECIPIENT.surname, e.message.sent_at, e.message.id, false, sender_lost_key);
            }
            appendMessage(msg);
        }).catch((err) => {
            console.error(err);
        });
    }
    else if (e.message.type === 2) {
        let msg = null;
        if (is_own) {
            msg = prepareMessage(true, e.message.message, e.message.type, 'Ty', e.message.sent_at, e.message.id, false, sender_lost_key);
        } else {
            msg = prepareMessage(false, e.message.message, e.message.type, RECIPIENT.name + ' ' + RECIPIENT.surname, e.message.sent_at, e.message.id, false, sender_lost_key);
        }
        appendMessage(msg);
    }
    if (e.message.sender_id !== RECIPIENT.id) {
        seen(e.message.id, e.message.chatroom_id);
    }

}
function prepareMessage(isOwn: boolean, message: string,
    type: number, name: string, time: Date, message_id: number, other_user_lost_key: boolean = false, owner_lost_key: boolean = false): HTMLElement {
    let div = document.createElement('div');
    if (isOwn) {
        div.classList.add('message-sent');
    } else {
        div.classList.add('message-received');
    }
    if (other_user_lost_key) {
        div.classList.add('message-other-user-lost-key');
    }
    if (owner_lost_key) {
        div.classList.add('message-owner-lost-key');
    }
    let header = document.createElement('div');
    header.classList.add('message-header');
    let author = document.createElement('span');
    author.classList.add('message-author');
    author.innerText = name;
    let date = document.createElement('span');
    date.classList.add('message-date');
    date.innerText = time.toLocaleString();
    header.appendChild(author);
    header.appendChild(date);
    let content = document.createElement('div');
    content.classList.add('message-content');
    if (type === 0) {
        let paragraph = document.createElement('p');
        paragraph.innerText = message;
        content.appendChild(paragraph);
    } else if (type === 2) {
        let anchor = document.createElement('a');
        anchor.href = '/chat/downloadfile/' + message_id;
        anchor.innerText = 'Pobierz plik';
        anchor.target = '_blank';
        content.appendChild(anchor);
        let paragraph = document.createElement('p');
        paragraph.innerText = 'Załączono plik: ' + message;
        content.appendChild(paragraph);
    }
    let message_container = document.createElement('div');
    message_container.classList.add('message-container');
    message_container.appendChild(header);
    message_container.appendChild(content);
    div.appendChild(message_container);
    return div;
}

function removeLoadingIcon(): void {
    if (H_CHAT_SCROLL_MSG.hasAttribute('aria-busy')) {
        H_CHAT_SCROLL_MSG.removeAttribute('aria-busy');
        H_CHAT_SCROLL_MSG.innerHTML = '';
    }
}
function clearMessages(): void {
    H_CHAT_SCROLL_MSG.innerHTML = '';
}
function appendMessage(html: HTMLElement, prepend: boolean = false): void {
    if (prepend) {
        H_CHAT_SCROLL_MSG.prepend(html);
    } else {
        H_CHAT_SCROLL_MSG.append(html);
    }
    H_CHAT_SCROLL_MSG.scrollTop = H_CHAT_SCROLL_MSG.scrollHeight;
}
clearMessages();
function sendMessage() {
    if (H_CHAT_INPUT_TEXT.value === '') {
        return;
    }
    let message = H_CHAT_INPUT_TEXT.value;
    let messageBuffer = Buffer.from(message);
    OpenSSLInstance.rsaEncrypt(RECIPIENT.publicKey, Store.getPublicKey(), messageBuffer, RECIPIENT.chatroomId, RECIPIENT.id, 0).then((encrypted) => {
        let jsontosend = JSON.stringify(encrypted);
        Communication.postData(jsontosend, 'sendMessage').then((response) => {
            console.log(response);
        }
        ).catch((err) => {
            console.error(err);
        });
    }).catch((err) => {
        console.error(err);
    });

    H_CHAT_INPUT_TEXT.value = '';

}

function sendAttachment() {
    if (H_CHAT_INPUT_FILE.files === null) {
        return;
    }
    let file = H_CHAT_INPUT_FILE.files[0];
    let file_name = file.name;
    let filereader = new FileReader();
    filereader.onload = (e) => {
        // @ts-ignore
        let messageBuffer = Buffer.from(e.target.result as ArrayBuffer);
        OpenSSLInstance.rsaEncrypt(RECIPIENT.publicKey, Store.getPublicKey(), messageBuffer, RECIPIENT.chatroomId, RECIPIENT.id, 2).then((encrypted) => {
            let fileData = encrypted.message;
            encrypted.message = file_name;
            Communication.postFileMessage(fileData, JSON.stringify(encrypted)).then((response) => {
                console.log(response);
            }
            ).catch((err) => {
                console.error(err);
            });
        }).catch((err) => {
            console.error(err);
        });

    };
    filereader.readAsArrayBuffer(file);
    H_CHAT_INPUT_FILE.value = '';

}
// global function
(window as any).sendMessage = sendMessage;
(window as any).sendAttachment = sendAttachment;
