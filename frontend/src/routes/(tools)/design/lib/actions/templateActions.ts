import type { TemplateResponse } from "../../types";

export function getDefaultTemplate() : Promise<TemplateResponse> {
    return fetch('api/public/template/default')
        .then((response) => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Failed to fetch default template');
        }
    );
}

export function previewTemplateFromVariable (template: string, variables: string) {
    return fetch('api/public/template/with', 
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                template,
                variables
            })
        }
    ).then((response) => {
        if (response.ok) {
            return response.json();
        }
        throw new Error('Failed to fetch template preview');
    }

    )
}

export function retrieveContentHtml(content: string) {
    return fetch('api/public/template/content', 
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                content
            })
        }
    ).then((response) => {
        if (response.ok) {
            return response.json();
        }
        throw new Error('Failed to fetch content html');
    }
    )
}
