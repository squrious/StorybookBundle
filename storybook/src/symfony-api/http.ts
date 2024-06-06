import fetch from 'node-fetch';
import type { Response } from 'node-fetch';

import { ApiErrorData, ApiOptions, BundleConfiguration, SymfonyApi as BaseSymfonyApi } from './types';
import dedent from 'ts-dedent';
import { formatStackTrace } from './lib/formatStackTrace';

const API_BASE_URI = '/_storybook/api';

export type HttpApi = 'http';

export type ApiConfig = {
    server: string;
};

export interface SymfonyApi extends BaseSymfonyApi<HttpApi, ApiConfig> {}

class HttpError extends Error {
    constructor(response: Response, text: string) {
        let message = dedent`
            Symfony API call failed with HTTP status ${response.status} (${response.statusText}
            URL: ${response.url}
            Output: ${text}
        `;

        try {
            const parsedError = JSON.parse(text) as ApiErrorData;
            message += dedent`\n
            Error: ${parsedError.error}
            `;

            if (parsedError.trace !== undefined) {
                message += dedent`\n
                Trace: 
                ${formatStackTrace(parsedError.trace)}
                `;
            }
        } catch (err) {
            message += dedent`\n
            (Failed to parse JSON)
            `;
        }
        super(message);
    }
}

const makeRequest = async <T>(endpoint: string, parameters: any = {}) => {
    const host = getConfig().server;
    const url = new URL(`${host}${API_BASE_URI}/${endpoint}`);

    url.search = new URLSearchParams(parameters).toString();

    const response = await fetch(url, {
        method: 'GET',
        headers: {
            Accept: 'application/json',
        },
    });

    const text = await response.text();
    if (!response.ok) {
        throw new HttpError(response, text);
    }

    return JSON.parse(text) as T;
};

let apiConfig: ApiConfig;

export const setConfig: SymfonyApi['setConfig'] = (config: ApiOptions<'http'>, options) => {
    const server = config.server ?? options.server;
    if (server === undefined) {
        throw new Error(dedent`
        Unable to determine the server host to use for API communication.
        `);
    }

    apiConfig = {
        server: server,
    };
};

export const getConfig: SymfonyApi['getConfig'] = () => {
    if (apiConfig === undefined) {
        throw new Error('Config is not initialized');
    }
    return apiConfig;
};

export const generatePreview: SymfonyApi['generatePreview'] = async () => {
    return makeRequest<string>('generate-preview');
};

export const getKernelProjectDir: SymfonyApi['getKernelProjectDir'] = async () => {
    return makeRequest<string>('get-container-parameter', { name: 'kernel.project_dir' });
};

export const getTwigComponentConfiguration: SymfonyApi['getTwigComponentConfiguration'] = async () => {
    return (await makeRequest<BundleConfiguration>('bundle-config', { name: 'twig_component' }))['twig_component'];
};
