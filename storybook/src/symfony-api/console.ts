import { exec, ExecException } from 'child_process';
import dedent from 'ts-dedent';
import { ApiErrorData, BundleConfiguration, SymfonyApi as BaseSymfonyApi } from './types';
import { formatStackTrace } from './lib/formatStackTrace';

type ApiConfig = unknown;
type ApiType = 'console';
export interface SymfonyApi extends BaseSymfonyApi<ApiType, ApiConfig> {}

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

class ConsoleError extends Error {
    constructor(error: ExecException, stderr: string) {
        let message = dedent`
                Symfony console failed with exit status ${error.code}.
                CMD: ${error.cmd}
                `;

        try {
            const parsedStderr = JSON.parse(stderr) as ApiErrorData;

            message += dedent`\n
            Error: ${parsedStderr.error}
            `;
            if (parsedStderr.trace !== undefined) {
                message += dedent`\n
                Trace:
                ${formatStackTrace(parsedStderr.trace)}
                `;
            }
        } catch (err) {
            message += dedent`\n
            Error output: ${stderr}                    
            `;
        }
        super(message);
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

export const setConfig: SymfonyApi['setConfig'] = () => {};
export const getConfig: SymfonyApi['getConfig'] = () => ({});

export const getKernelProjectDir: SymfonyApi['getKernelProjectDir'] = async () => {
    return runSymfonyCommand<string>('get-container-parameter', ['kernel.project_dir']);
};

export const getTwigComponentConfiguration: SymfonyApi['getTwigComponentConfiguration'] = async () => {
    return (await runSymfonyCommand<BundleConfiguration>('bundle-config', ['twig_component']))['twig_component'];
};

export const generatePreview: SymfonyApi['generatePreview'] = async () => {
    return runSymfonyCommand<string>('generate-preview');
};
