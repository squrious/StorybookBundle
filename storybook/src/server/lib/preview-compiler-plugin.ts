import { createUnplugin } from 'unplugin';
import HtmlWebpackPlugin from 'html-webpack-plugin';
import { logger } from '@storybook/node-logger';
import dedent from 'ts-dedent';
import { injectPreviewHtml } from './injectPreviewHtml';

const PLUGIN_NAME = 'preview-plugin';

export type Options = {
    getPreviewHtml: () => Promise<string>;
};

/**
 * Compile preview HTML.
 */
export const PreviewCompilerPlugin = createUnplugin<Options>((options) => {
    const { getPreviewHtml } = options;
    return {
        name: PLUGIN_NAME,
        webpack(compiler) {
            compiler.hooks.thisCompilation.tap(PLUGIN_NAME, (compilation) => {
                // Inject previewHead and previewBody in the compiled iframe.html before it is output
                HtmlWebpackPlugin.getHooks(compilation).afterTemplateExecution.tapPromise(
                    PLUGIN_NAME,
                    async (params) => {
                        try {
                            const previewHtml = await getPreviewHtml();
                            params.html = injectPreviewHtml(previewHtml, params.html);

                            return params;
                        } catch (err) {
                            logger.error(dedent`
                            Failed to inject Symfony preview template in main iframe.html.
                            ERR: ${err}
                            `);
                            return params;
                        }
                    }
                );
            });
        },
    };
});
