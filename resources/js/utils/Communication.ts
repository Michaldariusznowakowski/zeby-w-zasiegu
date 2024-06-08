import { Buffer } from 'buffer';

const GET_ENDPOINT = '/api/get/';
const PUT_ENDPOINT = '/api/put/';
const POST_ENDPOINT = '/api/post/';
class Communication {
    private static getAuthToken(): string {
        const token = document.querySelector('meta[name="api-token"]');
        if (token === null) {
            return '';
        }
        return token.getAttribute('content') || '';
    }
    public static async postFileMessage(file: File, message: string,): Promise<any> {
        const token = Communication.getAuthToken();
        if (token === '') {
            return Promise.reject('Token not found');
        }
        const formData = new FormData();
        formData.append('file', file);
        formData.append('message', message);
        return new Promise((resolve, reject) => {
            fetch(POST_ENDPOINT + 'sendFile', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                },
                body: formData,
            }).then(response => {
                if (response.ok) {
                    resolve(response.json());
                } else {
                    reject(response);
                }
            }
            ).catch(error => {
                reject(error);
            });
        }
        );
    }
    public static async postData(data: any, endpoint: string): Promise<any> {
        const token = Communication.getAuthToken();
        if (token === '') {
            return Promise.reject('Token not found');
        }
        return new Promise((resolve, reject) => {
            fetch(POST_ENDPOINT + endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-protobuf',
                    'X-CSRF-TOKEN': token,
                },
                body: data,
            }).then(response => {
                if (response.ok) {
                    resolve(response.arrayBuffer());
                } else {
                    reject(response);
                }
            }
            ).catch(error => {
                reject(error);
            });
        }
        );
    }

    public static async getData(endpoint: string, cache: boolean = false): Promise<any> {
        const token = Communication.getAuthToken();
        if (token === '') {
            return Promise.reject('Token not found');
        }
        return new Promise((resolve, reject) => {
            fetch(GET_ENDPOINT + endpoint, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/x-protobuf',
                    'X-CSRF-TOKEN': token,
                    'Cache-Control': cache ? 'max-age=3600' : 'no-cache',
                },
            }).then(response => {
                if (response.ok) {
                    resolve(response.arrayBuffer());
                } else {
                    reject(response);
                }
            }).catch(error => {
                reject(error);
            });
        });
    }
    public static async putData(data: any, endpoint: string): Promise<any> {
        const token = Communication.getAuthToken();
        if (token === '') {
            return Promise.reject('Token not found');
        }
        return new Promise((resolve, reject) => {
            fetch(PUT_ENDPOINT + endpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/x-protobuf',
                    'X-CSRF-TOKEN': token,
                },
                body: data,
            }).then(response => {
                if (response.ok) {
                    resolve(response.arrayBuffer());
                } else {
                    reject(response);
                }
            }
            ).catch(error => {
                reject(error);
            });
        }
        );
    }
}
export { Communication };