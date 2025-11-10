<script lang="ts">
	import { goto } from '$app/navigation';
	import type { NewsletterList } from '../../types';
	import { setNewsletterStoreByNewsletterList } from '../../lib/stores/newsletterStore';
	import RoleTag from './RoleTag.svelte';
	import { selectingNewsletter } from '../../lib/stores/consoleStore';

	export let newsletterList: NewsletterList;

	function onClick() {
		setNewsletterStoreByNewsletterList(newsletterList);
		goto(`/console/${newsletterList.newsletter.subdomain}`);
		selectingNewsletter.set(false);
	}
</script>

<div
	class="wrap"
	role="button"
	on:click={onClick}
	on:keyup={(e) => e.key === 'Enter' && onClick()}
	tabindex="0"
>
	<div class="name-id">
		<div class="name">{newsletterList.newsletter.name}</div>
		<div class="id">
			<strong>{newsletterList.newsletter.subdomain}</strong>
		</div>
	</div>

	<div class="role">
		<RoleTag role={newsletterList.role} />
	</div>

	<div class="right">&rarr;</div>
</div>

<style lang="scss">
	.wrap {
		padding: 15px 25px;
		background-color: var(--accent-light-mid);
		cursor: pointer;
		border-radius: var(--box-radius);
		display: flex;
		align-items: center;
		position: relative;
		overflow: hidden;
		margin-bottom: 10px;
	}
	.name-id {
		flex: 2;
	}
	.name {
		font-weight: 600;
	}
	.role {
		margin-right: 15px;
	}

	.id {
		font-size: 14px;
		color: var(--text-light);
		font-weight: normal;
	}

	@media (max-width: 768px) {
		.wrap {
			display: grid;
			grid-template-columns: repeat(5, 1fr);
			grid-template-rows: repeat(3, min-content);
			grid-row-gap: 10px;
		}
		.right {
			grid-area: 1 / 5 / 4 / 6;
			text-align: center;
		}
		.name-id {
			grid-area: 1 / 1 / 1 / 5;
		}
		.role {
			grid-area: 3 / 1 / 3 / 2;
		}
	}
</style>
