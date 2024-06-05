import { StorybookConfig, SymfonyOptions } from '../types';
import { join } from 'path';
import { PreviewCompilerPlugin } from './lib/preview-compiler-plugin';
import { DevPreviewCompilerPlugin } from './lib/dev-preview-compiler-plugin';
import { TwigLoaderPlugin } from './lib/twig-loader-plugin';
import { PresetProperty } from '@storybook/types';
import dedent from 'ts-dedent';
import { getSymfonyApi, TwigComponentConfiguration } from '../symfony-api';
import type { SymfonyApi } from '../symfony-api';
type BuildOptions = {
    twigComponent: TwigComponentConfiguration;
    runtimeDir: string;
    projectDir: string;
    additionalWatchPaths: string[];
    api: SymfonyApi;
};

const getBuildOptions = async (symfonyOptions: SymfonyOptions) => {
    const apiType = symfonyOptions.api || 'console';

    const symfonyApi = await getSymfonyApi(apiType);

    const projectDir = await symfonyApi.getKernelProjectDir();
    const twigComponentsConfig = await symfonyApi.getTwigComponentConfiguration();

    const componentNamespaces: { [p: string]: string } = {};

    for (const { name_prefix: namePrefix, template_directory: templateDirectory } of Object.values(
        twigComponentsConfig.defaults
    )) {
        componentNamespaces[namePrefix] = join(projectDir, 'templates', templateDirectory);
    }

    const anonymousNamespace = join(projectDir, 'templates', twigComponentsConfig['anonymous_template_directory']);

    const runtimeDir = (await symfonyApi.getStorybookBundleConfig()).runtime_dir;

    return {
        twigComponent: {
            anonymousTemplateDirectory: anonymousNamespace,
            namespaces: componentNamespaces,
        },
        runtimeDir,
        projectDir,
        additionalWatchPaths: symfonyOptions.additionalWatchPaths || [],
        api: symfonyApi,
    } as BuildOptions;
};

export const webpack: StorybookConfig['webpack'] = async (config, options) => {
    const framework = await options.presets.apply('framework');

    const frameworkOptions = typeof framework === 'string' ? {} : framework.options;

    // This options resolution should be done right before creating the build configuration (i.e. not in options presets).
    const symfonyOptions = await getBuildOptions(frameworkOptions.symfony);

    return {
        ...config,
        plugins: [
            ...(config.plugins || []),
            ...[
                options.configType === 'PRODUCTION'
                    ? PreviewCompilerPlugin.webpack({api: symfonyOptions.api})
                    : DevPreviewCompilerPlugin.webpack({
                          projectDir: symfonyOptions.projectDir,
                          additionalWatchPaths: symfonyOptions.additionalWatchPaths,
                          api: symfonyOptions.api,
                      }),
                TwigLoaderPlugin.webpack({ twigComponentConfiguration: symfonyOptions.twigComponent }),
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
