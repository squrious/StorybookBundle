'use strict';

import { exec, ChildProcess, ExecException } from 'child_process';
import { runSymfonyCommand } from './console';
import { vi } from 'vitest';

vi.mock('child_process');

function mockExec(error: ExecException | null = null, stdout = '""', stderr = '') {
    // Vitest mock types don't support signature overload?
    // @ts-ignore
    vi.mocked(exec).mockImplementation((command: string, callback: (...args) => void) => {
        callback(error, stdout, stderr);
        return new ChildProcess();
    });
}

describe('Symfony console API', () => {
    beforeEach(() => {
        vi.mocked(exec).mockReset();
    });

    it('uses default options', async () => {
        mockExec();

        await runSymfonyCommand('command');

        expect(exec).toHaveBeenCalledWith("'php' 'bin/console' 'storybook:api:command'", expect.any(Function));
    });

    it('with custom options', async () => {
        mockExec();

        const options = {
            php: '/usr/bin/php',
            script: 'custom/bin/console',
        };

        await expect(runSymfonyCommand('command', [], options)).resolves.toBe('');

        expect(exec).toHaveBeenCalledWith(
            "'/usr/bin/php' 'custom/bin/console' 'storybook:api:command'",
            expect.any(Function)
        );
    });

    it('rejects on exec failure', async () => {
        mockExec({ code: 1, cmd: "'php' 'bin/console' 'storybook:api:command'" } as ExecException, '');

        await expect(runSymfonyCommand('command')).rejects.toThrow();

        expect(exec).toHaveBeenCalledWith("'php' 'bin/console' 'storybook:api:command'", expect.any(Function));
    });

    it('accepts input arguments and options', async () => {
        mockExec();

        await runSymfonyCommand('command', ['arg1', '-o', '--option=foo']);

        expect(exec).toHaveBeenCalledWith(
            "'php' 'bin/console' 'storybook:api:command' 'arg1' '-o' '--option=foo'",
            expect.any(Function)
        );
    });

    describe('parse JSON output', () => {
        it('returns a JS object', async () => {
            mockExec(null, '{ "prop": "value" }');

            const expected = {
                prop: 'value',
            };

            await expect(runSymfonyCommand('command')).resolves.toEqual(expected);
        });
    });
});
