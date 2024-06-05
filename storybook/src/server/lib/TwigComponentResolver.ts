import path from 'path';
import dedent from 'ts-dedent';
import { TwigComponentConfiguration } from '../../symfony-api';

export class TwigComponentResolver {
    constructor(private config: TwigComponentConfiguration) {}

    isInAnonymousDirectory(file: string) {
        return 0 === file.indexOf(this.config.anonymousTemplateDirectory);
    }

    resolveNameFromFile(file: string) {
        const stripDirectory = (file: string, dir: string) => {
            return file
                .replace(dir, '')
                .replace(/^\//, '')
                .replace('/', ':')
                .replace('.html.twig', '')
            ;
        };

        for (const namespace in this.config.namespaces) {
            const dir = this.config.namespaces[namespace];
            if (0 === file.indexOf(dir)) {
                const trimmed = stripDirectory(file, dir);
                if ('' !== namespace) {
                    return `${namespace}:${trimmed}`;
                }
                return trimmed;
            }
        }

        if (this.isInAnonymousDirectory(file)) {
            return stripDirectory(file, this.config.anonymousTemplateDirectory);
        }

        throw new Error(dedent`Unable to determine template name for file "${file}":`);
    }

    resolveFileFromName(name: string) {
        const nameParts = name.split(':');
        const dirParts = nameParts.slice(0, -1);
        const filename = `${nameParts.slice(-1)}.html.twig`;

        const lookupPaths: string[] = [];

        for (const namespace in this.config.namespaces) {
            if ('' !== namespace && 0 === name.indexOf(namespace)) {
                lookupPaths.push(path.join(this.config.namespaces[namespace], dirParts.slice(1).join('/')));
                break;
            }
        }

        if (this.config.namespaces[''] !== undefined) {
            lookupPaths.push(path.join(this.config.namespaces[''], dirParts.join('/')));
        }

        lookupPaths.push(path.join(this.config.anonymousTemplateDirectory, dirParts.join('/')));

        try {
            return require.resolve(`./${filename}`, { paths: lookupPaths });
        } catch (err) {
            throw new Error(dedent`Unable to find template file for component "${name}": ${err}`);
        }
    }

    resolvePhpFileFromName(name: string) {

    }
}
