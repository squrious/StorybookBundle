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

export interface SymfonyApi<TConfig = any> {
    /**
     * Process framework options to get the API config.
     */
    processConfig: (options: SymfonyOptions) => TConfig;

    /**
     * Get TwigComponent configuration from Symfony.
     */
    getTwigComponentConfiguration: ApiAction<SymfonyTwigComponentConfig, TConfig>;

    /**
     * Get Kernel project directory from Symfony.
     */
    getKernelProjectDir: ApiAction<string, TConfig>;

    /**
     * Generate the HTML preview template.
     */
    generatePreview: ApiActionFn<string, TConfig>;
}

type ApiActionFn<T, TConfig, TArgs = undefined> = TArgs extends undefined
    ? (config: TConfig) => () => Promise<T>
    : (config: TConfig) => (args: TArgs) => Promise<T>;

type ApiAction<T, TConfig, TArgs = undefined> = TArgs extends undefined
    ? (config: TConfig) => Promise<T>
    : (config: TConfig, args: TArgs) => Promise<T>;

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
