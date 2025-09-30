import { getAppConfig } from "./stores/consoleStore";


export function getNewsletterArchiveUrlFromSubdomain(subdomain: string) {
    const url = getArchiveUrlAsUrl();
    return url.protocol + '//' + subdomain + '.' + url.hostname;
}

export function getArchiveUrlAsUrl(): URL {
    const config = getAppConfig();
    return new URL(config.app.archive_url);
}