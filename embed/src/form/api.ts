
export function apiFromInstance(instance: string) {

    return async function <T>(path: string, params: Record<string, any> = {}): Promise<T> {

        path = path.replace(/^\//, "");
        
        const response = await fetch(`${instance}/api/public/form/${path}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(params),
        })

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message);
        }
        const data = await response.json();
        return data;

    }

}