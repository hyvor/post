<script lang="ts">
	import { Loader, SplitControl, Table, TableRow, TextInput } from '@hyvor/design/components';
	import type { User } from '../../../types';
	import { getProjectUsers } from '../../../lib/actions/userActions';
	import { onMount } from 'svelte';

    let loading = true;
    let users: User[] = [];

    function load() {
        getProjectUsers()
            .then((data) => {
                users = data;
            })
            .catch((e) => {
                console.error(e);
            })
            .finally(() => {
                loading = false;
            });
	}

	onMount(() => {
		load();
	});
	
</script>

<div class="moderators">
	<SplitControl label="Moderators" column>
		{#if loading}
			<Loader padding={20} />
		{:else}
            
		{/if}
	</SplitControl>

	<SplitControl label="Invites" column>
		
	</SplitControl>

</div>

<style lang="scss">
	.moderators {
		padding: 15px 30px;
	}
</style>
