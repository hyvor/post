import publicApi from "$lib/publicApi";

export function confirm(token: string) {
    return publicApi.get({
        endpoint: 'confirm',
        data: {token},
    });
}
