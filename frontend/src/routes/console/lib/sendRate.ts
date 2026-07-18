/**
 * Estimates how long an issue will take to fully send, given the number of
 * recipients and the newsletter's daily sending rate. Emails are spread evenly
 * (see the backend SendIssueMessageHandler), so the duration is count / rate.
 *
 * Returns the i18n key and count to render, or null when there is nothing to show.
 */
export type SendRateEstimateKey =
	| 'console.issues.draft.sendRateEstimate.withinHour'
	| 'console.issues.draft.sendRateEstimate.hours'
	| 'console.issues.draft.sendRateEstimate.days';

export function getSendDurationEstimate(
	count: number,
	ratePerDay: number
): { key: SendRateEstimateKey; count: number } | null {
	if (count <= 0 || ratePerDay <= 0) {
		return null;
	}

	const days = count / ratePerDay;
	const hoursTotal = days * 24;

	if (hoursTotal < 1) {
		return { key: 'console.issues.draft.sendRateEstimate.withinHour', count: 0 };
	}

	if (days < 1) {
		return { key: 'console.issues.draft.sendRateEstimate.hours', count: Math.ceil(hoursTotal) };
	}

	return { key: 'console.issues.draft.sendRateEstimate.days', count: Math.ceil(days) };
}
