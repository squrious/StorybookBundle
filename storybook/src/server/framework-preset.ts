import { StorybookConfig, SymfonyOptions } from '../types';
import { PreviewCompilerPlugin } from './lib/preview-compiler-plugin';
import { DevPreviewCompilerPlugin } from './lib/dev-preview-compiler-plugin';
import { TwigLoaderPlugin } from './lib/twig-loader-plugin';
import { PresetProperty } from '@storybook/types';
import dedent from 'ts-dedent';
import { ApiType, SymfonyTwigComponentConfig } from '../symfony-api';
import type { SymfonyApi } from '../symfony-api';

type BuildOptions = {
    twigComponentConfiguration: SymfonyTwigComponentConfig;
    projectDir: string;
    additionalWatchPaths: string[];
    getPreviewHtml: () => Promise<string>;
};

import { pathToFileURL } from 'node:url';
const getSymfonyApi = async <T extends ApiType>(symfonyOptions: SymfonyOptions): Promise<SymfonyApi<T>> => {
    const api =
        symfonyOptions.api === undefined
            ? { type: 'console', config: {} }
            : typeof symfonyOptions.api === 'string'
              ? {
                    type: symfonyOptions.api,
                    config: {},
                }
              : {
                    type: symfonyOptions.api.type,
                    config: symfonyOptions.api.config,
                };

    const apiModulePath = require.resolve(`../symfony-api/${api.type}`);

    const apiModule = await import(pathToFileURL(apiModulePath).href);

    apiModule.setConfig(api.config, symfonyOptions);

    return apiModule;
};

export const webpack: StorybookConfig['webpack'] = async (config, options) => {
    const framework = await options.presets.apply('framework');

    const frameworkOptions = typeof framework === 'string' ? {} : framework.options;

    const symfonyOptions = frameworkOptions.symfony as SymfonyOptions;

    const api = await getSymfonyApi(symfonyOptions);

    // This options resolution should be done right before creating the build configuration (i.e. not in options presets).
    const buildOptions = {
        twigComponentConfiguration: await api.getTwigComponentConfiguration(),
        projectDir: await api.getKernelProjectDir(),
        additionalWatchPaths: symfonyOptions.additionalWatchPaths || [],
        getPreviewHtml: api.generatePreview,
    } as BuildOptions;

    return {
        ...config,
        plugins: [
            ...(config.plugins || []),
            ...[
                options.configType === 'PRODUCTION'
                    ? PreviewCompilerPlugin.webpack({ getPreviewHtml: buildOptions.getPreviewHtml })
                    : DevPreviewCompilerPlugin.webpack({
                          projectDir: buildOptions.projectDir,
                          additionalWatchPaths: buildOptions.additionalWatchPaths,
                          getPreviewHtml: buildOptions.getPreviewHtml,
                      }),
                TwigLoaderPlugin.webpack({
                    twigComponentConfiguration: buildOptions.twigComponentConfiguration,
                    projectDir: buildOptions.projectDir,
                }),
            ],
        ],
        module: {
            ...config.module,
            rules: [...(config.module?.rules || [])],
        },
    };
};

export const previewHead: PresetProperty<'previewHead'> = async (base: any) => dedent`
    ${base}
    <!--PREVIEW_HEAD_PLACEHOLDER-->
    `;

export const previewBody: PresetProperty<'previewBody'> = async (base: any) => dedent`
    ${base}
    <!--PREVIEW_BODY_PLACEHOLDER-->
    `;
