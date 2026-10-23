export class ApiError extends Error {
	status: number;
	retryAfter: number | null; // seconds, from the Retry-After header

	constructor(message: string, status: number, retryAfter: number | null) {
		super(message);
		this.status = status;
		this.retryAfter = retryAfter;
	}
}

export function apiFromInstance(instance: string) {
	return async function <T>(path: string, params: Record<string, any> = {}): Promise<T> {
		path = path.replace(/^\//, '');

		const response = await fetch(`${instance}/api/public/form/${path}`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(params)
		});

		if (!response.ok) {
			const error = await response.json();
			const retryAfter = parseInt(response.headers.get('Retry-After') ?? '', 10);
			throw new ApiError(error.message, response.status, isNaN(retryAfter) ? null : retryAfter);
		}
		const data = await response.json();
		return data;
	};
}
