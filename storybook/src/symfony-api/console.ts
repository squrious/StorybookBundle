import { exec, ExecException } from 'child_process';
import dedent from 'ts-dedent';
import { ApiErrorData, SymfonyApi } from './types';
import { formatStackTrace } from './lib/formatStackTrace';

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
    constructor(error: ExecException, stdout: string, stderr: string) {
        let message = dedent`
                Symfony console failed with exit status ${error.code}.
                CMD: ${error.cmd}
                Output: ${stdout}
                `;

        try {
            const parsedStderr = JSON.parse(stderr) as ApiErrorData;
            // console.log(parsedStderr);
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

    // const result = await execSymfonyCommand(finalCommand);
    return new Promise<T>((resolve, reject) => {
        exec(finalCommand, (error, stdout, stderr) => {
            if (error) {
                reject(new ConsoleError(error, stdout, stderr));
            }

            try {
                resolve(JSON.parse(stdout) as T);
            } catch (err) {
                reject(new Error(dedent`
                Failed to process JSON output for Symfony command.
                CMD: ${finalCommand}
                Output: ${stdout}
                `));
            }
        });
    });
};

export const getKernelProjectDir: SymfonyApi['getKernelProjectDir'] = async () => {
    return  runSymfonyCommand<string>('get-container-parameter', ['kernel.project_dir']);
};

type StorybookBundleConfig = {
    storybook: {
        runtime_dir: string;
    };
};

export const getStorybookBundleConfig: SymfonyApi['getStorybookBundleConfig'] = async () => {
    return (
        await runSymfonyCommand<StorybookBundleConfig>('bundle-config', ['storybook'])
    )['storybook'];
};

type SymfonyTwigComponentConfiguration = {
    twig_component: {
        anonymous_template_directory: string;
        defaults: {
            [p: string]: {
                name_prefix: string;
                template_directory: string;
            };
        };
    };
};

export const getTwigComponentConfiguration: SymfonyApi['getTwigComponentConfiguration'] = async () => {
    return (
        await runSymfonyCommand<SymfonyTwigComponentConfiguration>('bundle-config', ['twig_component'])
    )['twig_component'];
};

export const generatePreview: SymfonyApi['generatePreview'] = async () => {
    return runSymfonyCommand<string>('generate-preview');
}
