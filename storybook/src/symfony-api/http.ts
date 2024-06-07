import fetch from 'node-fetch';
import type { Response } from 'node-fetch';

import { BundleConfiguration, SymfonyApi as BaseSymfonyApi } from './types';
import dedent from 'ts-dedent';
import { ApiError } from './lib/ApiError';

const API_BASE_URI = '/_storybook/api';

export type ApiConfig = {
    server: string;
};

export interface SymfonyApi extends BaseSymfonyApi<ApiConfig> {}

class HttpError extends ApiError {
    constructor(response: Response, text: string) {
        const message = dedent`
            Symfony API call failed with HTTP status ${response.status} (${response.statusText})
            URL: ${response.url}
        `;

        super(message, text);
    }
}

const makeRequest = async <T>(config: ApiConfig, endpoint: string, parameters: any = {}) => {
    const host = config.server;

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

export const processConfig: SymfonyApi['processConfig'] = (options) => {
    const server = options.api?.config?.server ?? options.server;

    if (server === undefined) {
        throw new Error('Missing server');
    }

    return {
        server: server,
    };
};

export const generatePreview: SymfonyApi['generatePreview'] = (config) => {
    return async () => makeRequest<string>(config, 'generate-preview');
};

export const getKernelProjectDir: SymfonyApi['getKernelProjectDir'] = async (config) => {
    return makeRequest<string>(config, 'get-container-parameter', { name: 'kernel.project_dir' });
};

export const getTwigComponentConfiguration: SymfonyApi['getTwigComponentConfiguration'] = async (config) => {
    return (await makeRequest<BundleConfiguration>(config, 'bundle-config', { name: 'twig_component' }))[
        'twig_component'
    ];
};
