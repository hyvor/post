import {ITEMS_PER_PAGE} from "../generalActions";

export function getSubscriberImports(
    subdomain: string | null = null,
    limit: number = ITEMS_PER_PAGE,
    offset: number = 0
) {
    return [
        {
            id: 1,
            created_at: 1700000000,
            newsletter_subdomain: "Tech News",
            total_rows: 1500,
            source: "CSV Upload"
        },
        {
            id: 2,
            created_at: 1700000500,
            newsletter_subdomain: "Daily Updates",
            total_rows: 800,
            source: "API Import"
        }
    ]
}