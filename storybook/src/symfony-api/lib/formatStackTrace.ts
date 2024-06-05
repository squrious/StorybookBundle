import { StackTrace } from '../types';

export const formatStackTrace = (trace: StackTrace) => {
    return trace
        .map(
            ({
              file,
              line,
              function: func,
              class: cls,
              type,
            }) => `at ${cls || ''}${type || ''}${func}() (${file}:${line})`
        )
        .join('\n');
}

