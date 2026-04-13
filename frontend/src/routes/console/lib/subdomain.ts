// export function isSubdomainValid(subdomain: string): boolean {
//     // Regex for a single DNS label
//     const regex = /^(?!-)[A-Za-z0-9-]{1,63}(?<!-)$/;
//     return regex.test(subdomain);
// }

export function validateSubdomain(subdomain: string): string | null {
	if (!subdomain) {
		return 'Subdomain cannot be empty.';
	} else if (!/^[a-z0-9-]*$/.test(subdomain)) {
		return 'Only a-z, 0-9, and hyphens (-) are allowed';
	} else if (/--/.test(subdomain)) {
		return 'Consecutive hyphens are not allowed';
	} else if (/^-|-$/.test(subdomain)) {
		return 'Subdomain cannot start or end with a hyphen';
	} else if (subdomain.length > 63) {
		return 'Subdomain cannot exceed 63 characters';
	}

	return null;
}
