import { ApiErrorData, StackTrace } from '../types';
import dedent from 'ts-dedent';
const formatStackTrace = (trace: StackTrace) => {
    return trace
        .map(
            ({ file, line, function: func, class: cls, type }) =>
                `at ${cls || ''}${type || ''}${func}() (${file}:${line})`
        )
        .join('\n');
};

const parseErrorText = (errorText: string) => {
    let message = '';
    try {
        const parsedError = JSON.parse(errorText) as ApiErrorData;
        message += dedent`\n
            Error: ${parsedError.error}
            `;

        if (parsedError.trace !== undefined) {
            message += dedent`\n
                Trace: 
                ${formatStackTrace(parsedError.trace)}
                `;
        }
    } catch (err) {
        message += dedent`\n
        Failed to parse JSON. Output text:
        ${errorText}
        `;
    }

    return message;
};
export class ApiError extends Error {
    constructor(message: string, errorText: string) {
        message += dedent`\n
        ${parseErrorText(errorText)}
        `;

        super(message);
    }
}
