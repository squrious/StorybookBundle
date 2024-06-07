import { exec, ExecException } from 'child_process';
import dedent from 'ts-dedent';
import { BundleConfiguration, SymfonyApi as BaseApi } from './types';
import { ApiError } from './lib/ApiError';

interface SymfonyApi extends BaseApi<undefined> {}

type CommandOptions = {
    /**
     * Path to the PHP binary used to execute the command.
     */
    php?: string;

    /**
     * Path to the Symfony Console entrypoint.
     */
    script?: string;
};

const defaultOptions: CommandOptions = {
    php: 'php',
    script: 'bin/console',
};

const STORYBOOK_COMMAND_NAMESPACE = 'storybook:api';

class ConsoleError extends ApiError {
    constructor(error: ExecException, stderr: string) {
        const message = dedent`
                Symfony console failed with exit status ${error.code}.
                CMD: ${error.cmd}
                `;

        super(message, stderr);
    }
}

/**
 * Run a Symfony command with JSON formatted output and get the result as a JS object.
 */
export const runSymfonyCommand = async <T = any>(
    command: string,
    inputs: string[] = [],
    options: CommandOptions = {}
): Promise<T> => {
    const finalOptions = {
        ...defaultOptions,
        ...options,
    };

    const finalCommand = [finalOptions.php, finalOptions.script, `${STORYBOOK_COMMAND_NAMESPACE}:${command}`]
        .concat(...inputs)
        .map((part) => `'${part}'`)
        .join(' ');

    return new Promise<T>((resolve, reject) => {
        exec(finalCommand, (error, stdout, stderr) => {
            if (error) {
                reject(new ConsoleError(error, stderr));
            }

            try {
                resolve(JSON.parse(stdout) as T);
            } catch (err) {
                reject(
                    new Error(dedent`
                    Failed to process JSON output for Symfony command.
                    CMD: ${finalCommand}
                    Output: ${stdout}
                    `)
                );
            }
        });
    });
};

export const processConfig: SymfonyApi['processConfig'] = () => {};

export const getKernelProjectDir: SymfonyApi['getKernelProjectDir'] = async () => {
    return runSymfonyCommand<string>('get-container-parameter', ['kernel.project_dir']);
};

export const getTwigComponentConfiguration: SymfonyApi['getTwigComponentConfiguration'] = async () => {
    return (await runSymfonyCommand<BundleConfiguration>('bundle-config', ['twig_component']))['twig_component'];
};

export const generatePreview: SymfonyApi['generatePreview'] = () => {
    return async () => runSymfonyCommand<string>('generate-preview');
};
