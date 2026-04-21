<script lang="ts">
	import type { List } from '$lib/types';
	import Switch from './Switch.svelte';
	import Button from '../@components/Button.svelte';
	import { changePreferences } from '$lib/actions/subscriptionActions';
	import { fade } from 'svelte/transition';

	interface Props {
		lists: List[];
		token: string | undefined;
		error: string | undefined;
	}

	let { lists, token, error = $bindable() }: Props = $props();

	let selectedListsIds: number[] = $state([]);
	let saving = $state(false);
	let saved = $state(false);

	function handleListSwitch(listId: number) {
		return (event: Event) => {
			const checkbox = event.target as HTMLInputElement;
			if (checkbox.checked) {
				selectedListsIds.push(listId);
			} else {
				selectedListsIds = selectedListsIds.filter((id) => id !== listId);
			}
		};
	}

	function handleSelectAll() {
		selectedListsIds = lists.map((list) => list.id);
	}

	function handleDeselectAll() {
		selectedListsIds = [];
	}

	function handleSave() {
		if (!token) {
			error = 'Invalid resubscription link';
			return;
		}

		saving = true;

		changePreferences(token, selectedListsIds)
			.then(() => {
				saved = true;
				setTimeout(() => {
					saved = false;
				}, 2000);
			})
			.catch((e) => {
				error = e.message || 'An unexpected error occurred';
			})
			.finally(() => {
				saving = false;
			});
	}
</script>

<div class="resubscribe">
	<div class="lists" class:hidden={lists.length === 0}>
		{#each lists as list (list.id)}
			<label class="list">
				<div class="list-name-description">
					<div class="list-name">{list.name}</div>
					<div class="list-description">{list.description}</div>
				</div>
				<Switch
					checked={selectedListsIds.includes(list.id)}
					onchange={handleListSwitch(list.id)}
				/>
			</label>
		{/each}
	</div>

	<div class="select">
		<button onclick={handleDeselectAll}>Deselect</button>
		<button onclick={handleSelectAll}>Select all</button>
	</div>

	<div>
		<Button onclick={handleSave} disabled={saving}>Save preferences</Button>
	</div>

	{#if saved}
		<div class="saved" transition:fade>Preferences saved</div>
	{/if}
</div>

<style>
	.lists {
		margin: auto;
		margin-top: 30px;
	}

	.list {
		display: flex;
		justify-content: space-between;
		margin-bottom: 0.8em;
		cursor: pointer;
	}

	.list-name-description {
		text-align: left;
	}

	.list-name {
		font-size: 16px;
		font-weight: 600;
	}

	.list-description {
		font-size: 14px;
		color: var(--hp-text-light);
	}

	.select {
		margin-top: 10px;
		margin-bottom: 30px;
		display: flex;
		gap: 6px;
		justify-content: flex-end;
	}

	.select button {
		font-size: 12px;
	}

	.select button:hover {
		text-decoration: underline;
	}

	.saved {
		margin-top: 10px;
		font-size: 14px;
		color: var(--hp-text-light);
	}
</style>
