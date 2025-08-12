import type {LayoutServerLoad} from './$types';

export const load: LayoutServerLoad = async ({request}) => {
    const host = request.headers.get('host') || '';
    const subdomain = host.split('.')[0];

    return {
        subdomain
    };
};
