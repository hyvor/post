<script lang="ts">
	import { Modal, SplitControl } from '@hyvor/design/components';
	import type { Domain } from '../types';

	export let domain: Domain;
	export let show: boolean;
</script>

<Modal
	bind:show
	title="DNS Records"
	on:cancel={() => show = false}
>
	<div class="modal-content">
		<SplitControl label="DKIM Public Key">
			<pre class="dns-record">{domain.dkim_public_key}</pre>
		</SplitControl>
		<SplitControl label="DKIM TXT Name">
			<pre class="dns-record">{domain.dkim_txt_name}</pre>
		</SplitControl>
		<SplitControl label="DKIM TXT Value">
			<pre class="dns-record">{domain.dkim_txt_value}</pre>
		</SplitControl>
	</div>
</Modal>

<style>
	.modal-content {
		padding: 20px;
		display: flex;
		flex-direction: column;
		gap: 8px;
	}

	.dns-record {
		background-color: var(--hds-color-background);
		padding: 10px;
		border-radius: var(--hds-border-radius);
		white-space: pre-wrap;
		word-break: break-all;
		font-family: monospace;
		font-size: 14px;
		margin: 0;
	}
</style> 