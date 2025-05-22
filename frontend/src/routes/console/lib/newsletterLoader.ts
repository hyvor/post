import type { List, Newsletter, NewsletterStats, SubscriberMetadataDefinition } from '../types';
import consoleApi from './consoleApi';
import {
	issueStore,
	listStore,
	projectStatsStore,
	setProjectStore,
	subscriberMetadataDefinitionStore
} from './stores/newsletterStore';

interface ProjectResponse {
	project: Newsletter;
	stats: NewsletterStats;
	lists: List[];
	subscriber_metadata_definitions: SubscriberMetadataDefinition[];
}

// to prevent multiple requests for the same subdomain
const LOADER_PROMISES: Record<string, Promise<ProjectResponse>> = {};

export function loadProject(projectId: string) {
	if (LOADER_PROMISES[projectId] !== undefined) {
		return LOADER_PROMISES[projectId];
	}

	const promise = new Promise<ProjectResponse>((resolve, reject) => {
		consoleApi
			.get<ProjectResponse>({
				endpoint: 'init/project',
				userApi: true,
				projectId: projectId
			})
			.then((res) => {
				setProjectStore(res.project);
				projectStatsStore.set(res.stats);
				listStore.set(res.lists);
				subscriberMetadataDefinitionStore.set(res.subscriber_metadata_definitions);

				issueStore.set([]);

				resolve(res);
			})
			.catch((err) => {
				reject(err);
			})
			.finally(() => {
				delete LOADER_PROMISES[projectId];
			});
	});

	LOADER_PROMISES[projectId] = promise;

	return promise;
}
