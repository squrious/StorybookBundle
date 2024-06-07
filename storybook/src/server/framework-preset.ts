import { StorybookConfig, SymfonyOptions } from '../types';
import { PreviewCompilerPlugin } from './lib/preview-compiler-plugin';
import { DevPreviewCompilerPlugin } from './lib/dev-preview-compiler-plugin';
import { TwigLoaderPlugin } from './lib/twig-loader-plugin';
import { PresetProperty } from '@storybook/types';
import dedent from 'ts-dedent';
import { SymfonyTwigComponentConfig } from '../symfony-api';
import type { SymfonyApi } from '../symfony-api';
import { pathToFileURL } from 'node:url';

type BuildOptions = {
    twigComponentConfiguration: SymfonyTwigComponentConfig;
    projectDir: string;
    additionalWatchPaths: string[];
    getPreviewHtml: () => Promise<string>;
};

const getBuildOptions = async (symfonyOptions: SymfonyOptions): Promise<BuildOptions> => {
    const apiOptions =
        symfonyOptions.api === undefined
            ? { type: 'console', config: {} }
            : typeof symfonyOptions.api === 'string'
              ? {
                    type: symfonyOptions.api,
                    config: {},
                }
              : {
                    type: symfonyOptions.api.type,
                    config: symfonyOptions.api.config || {},
                };

    const apiModulePath = require.resolve(`../symfony-api/${apiOptions.type}`);

    const api: SymfonyApi = await import(pathToFileURL(apiModulePath).href);
    const apiConfig = api.processConfig(symfonyOptions);

    return {
        twigComponentConfiguration: await api.getTwigComponentConfiguration(apiConfig),
        projectDir: await api.getKernelProjectDir(apiConfig),
        additionalWatchPaths: symfonyOptions.additionalWatchPaths || [],
        getPreviewHtml: api.generatePreview(apiConfig),
    };
};

export const webpack: StorybookConfig['webpack'] = async (config, options) => {
    const framework = await options.presets.apply('framework');

    const frameworkOptions = typeof framework === 'string' ? {} : framework.options;

    // This options resolution should be done right before creating the build configuration (i.e. not in options presets).
    const buildOptions = await getBuildOptions(frameworkOptions.symfony);

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
