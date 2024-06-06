import { SymfonyOptions } from '../types';

export type ApiErrorData = {
    error: string;
    trace?: StackTrace;
};

export type StackTrace = {
    file: string;
    line: number;
    function: string;
    class?: string;
    type?: string;
}[];

export type BundleConfiguration = {
    [p: string]: any;
};

export type SymfonyTwigComponentConfig = {
    anonymous_template_directory: string;
    defaults: {
        [p: string]: {
            name_prefix: string;
            template_directory: string;
        };
    };
};

export interface SymfonyApi<TApi extends ApiType = ApiType, TConfig = unknown> {
    getConfig: () => TConfig;
    setConfig: (config: ApiOptions<TApi>, options: SymfonyOptions) => void;
    getTwigComponentConfiguration: () => Promise<SymfonyTwigComponentConfig>;
    getKernelProjectDir: () => Promise<string>;
    generatePreview: () => Promise<string>;
}

type ApiConfigMap = {
    console: unknown;
    http: {
        /**
         * URL of the API server.
         */
        server?: string;
    };
};

export type ApiType = keyof ApiConfigMap;

export type ApiOptions<T extends ApiType> = ApiConfigMap[T];
