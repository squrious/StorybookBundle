export * from './types';

import { pathToFileURL } from 'node:url';
import { ApiType, SymfonyApi } from './types';

export async function getSymfonyApi(api: ApiType): Promise<SymfonyApi> {
    const apiModule = require.resolve(`../symfony-api/${api}`);

    return await import(pathToFileURL(apiModule).href);
}
