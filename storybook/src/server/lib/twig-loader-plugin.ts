import { createUnplugin } from 'unplugin';
import dedent from 'ts-dedent';
import { logger } from '@storybook/node-logger';
import { extractComponentsFromTemplate } from './extractComponentsFromTemplate';
import { TwigComponentResolver } from './TwigComponentResolver';
import crypto from 'crypto';
import { SymfonyTwigComponentConfig } from '../../symfony-api';
import { join } from 'path';

const PLUGIN_NAME = 'twig-loader';

export type Options = {
    twigComponentConfiguration: SymfonyTwigComponentConfig;
    projectDir: string;
};

/**
 * Twig template source loader.
 *
 * Generates JS modules to export raw template source and imports required components.
 */
export const TwigLoaderPlugin = createUnplugin<Options>((options) => {
    const { twigComponentConfiguration, projectDir } = options;
    const componentNamespaces: { [p: string]: string } = {};

    for (const { name_prefix: namePrefix, template_directory: templateDirectory } of Object.values(
        twigComponentConfiguration.defaults
    )) {
        componentNamespaces[namePrefix] = join(projectDir, 'templates', templateDirectory);
    }

    const anonymousNamespace = join(
        projectDir,
        'templates',
        twigComponentConfiguration['anonymous_template_directory']
    );

    const resolver = new TwigComponentResolver({
        anonymousTemplateDirectory: anonymousNamespace,
        namespaces: componentNamespaces,
    });
    return {
        name: PLUGIN_NAME,
        enforce: 'pre',
        transformInclude: (id) => {
            return /\.html\.twig$/.test(id);
        },
        transform: async (code, id) => {
            const imports: string[] = [];

            try {
                const components = new Set<string>(extractComponentsFromTemplate(code));

                components.forEach((name) => {
                    imports.push(resolver.resolveFileFromName(name));
                });
            } catch (err) {
                logger.warn(dedent`
                Failed to parse template in '${id}': ${err}
                `);
            }

            const name = resolver.resolveNameFromFile(id);

            return dedent`
            ${imports.map((file) => `import '${file}';`).join('\n')}            
            export default { 
                name: \'${name}\',
                hash: \`${crypto.createHash('sha1').update(code).digest('hex')}\`,
            }; 
           `;
        },
    };
});
