import fetch from 'node-fetch';

const API_BASE_URI = '/_storybook/api';

const prepareRequest = (host: string, endpoint: string) => {
    return `${host}${API_BASE_URI}${endpoint}`;
}

type PreviewHtml = {
    content: string
};

export const getPreviewHtml = async () => {
    const url = prepareRequest('http://localhost:8000', '/preview');
    const response = await fetch(url, {
        method: 'GET',
        headers: {
            Accept: 'application/json'
        }
    });

    return await response.json() as PreviewHtml;
}
