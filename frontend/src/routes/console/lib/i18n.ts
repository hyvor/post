import type { InternationalizationService } from '@hyvor/design/components';
import { getContext } from 'svelte';
import type enJson from '../../../../../shared/locale/en-US.json';

type I18nType = InternationalizationService<typeof enJson>;

export function getI18n() {
	return getContext<I18nType>('i18n');
}
