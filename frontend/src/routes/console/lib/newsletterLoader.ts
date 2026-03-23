import type {
  List,
  Newsletter,
  NewsletterPermissions,
  NewsletterStats,
  SendingProfile,
  SubscriberMetadataDefinition,
} from "../types";
import consoleApi from "./consoleApi";
import {
  issueStore,
  listStore,
  newsletterLicenseStore,
  newsletterPermissionsStore,
  newsletterStatsStore,
  sendingProfilesStore,
  setNewsletterStore,
  subscriberMetadataDefinitionStore,
} from "./stores/newsletterStore";

interface NewsletterResponse {
  newsletter: Newsletter;
  stats: NewsletterStats;
  lists: List[];
  subscriber_metadata_definitions: SubscriberMetadataDefinition[];
  sending_profiles: SendingProfile[];
  permissions: NewsletterPermissions;
  has_license: boolean;
}

// to prevent multiple requests for the same subdomain
const LOADER_PROMISES: Record<number, Promise<NewsletterResponse>> = {};

export function loadNewsletter(newsletterId: number) {
  if (LOADER_PROMISES[newsletterId] !== undefined) {
    return LOADER_PROMISES[newsletterId];
  }

  const promise = new Promise<NewsletterResponse>((resolve, reject) => {
    consoleApi
      .get<NewsletterResponse>({
        endpoint: "init/newsletter",
        userApi: true,
        newsletterId: newsletterId,
      })
      .then((res) => {
        setNewsletterStore(res.newsletter);
        newsletterStatsStore.set(res.stats);
        newsletterLicenseStore.set(res.has_license);
        listStore.set(res.lists);
        subscriberMetadataDefinitionStore.set(
          res.subscriber_metadata_definitions,
        );
        sendingProfilesStore.set(res.sending_profiles);
        newsletterPermissionsStore.set(res.permissions);

        issueStore.set([]);

        resolve(res);
      })
      .catch((err) => {
        reject(err);
      })
      .finally(() => {
        delete LOADER_PROMISES[newsletterId];
      });
  });

  LOADER_PROMISES[newsletterId] = promise;

  return promise;
}
