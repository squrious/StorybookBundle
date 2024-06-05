export type TwigComponentConfiguration = {
    anonymousTemplateDirectory: string;
    namespaces: {
        [p: string]: string;
    };
};

export type ApiErrorData = {
    error: string,
    trace?: StackTrace;
};

export type StackTrace = {
    file: string;
    line: number;
    function: string;
    class?: string;
    type?: string;
}[];

type StorybookBundleConfig = {
    runtime_dir: string;
};

type SymfonyTwigComponentConfiguration = {
    anonymous_template_directory: string;
    defaults: {
        [p: string]: {
            name_prefix: string;
            template_directory: string;
        };
    };
};

export interface SymfonyApi {
    getStorybookBundleConfig: () => Promise<StorybookBundleConfig>,
    getTwigComponentConfiguration: () => Promise<SymfonyTwigComponentConfiguration>,
    getKernelProjectDir: () => Promise<string>,
    generatePreview: () => Promise<string>
}

export type ApiType = 'console' | 'http';
