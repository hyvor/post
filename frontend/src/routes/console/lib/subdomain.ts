

export function isSubdomainValid(subdomain: string): boolean {
    // Regex for a single DNS label
    const regex = /^(?!-)[A-Za-z0-9-]{1,63}(?<!-)$/;
    return regex.test(subdomain);
}