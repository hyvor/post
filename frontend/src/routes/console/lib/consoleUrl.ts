export function consoleUrl(path: string) {
	path = path.replace(/^\//, '');

	return '/console/' + path;
}
