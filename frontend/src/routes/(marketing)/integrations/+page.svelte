<script lang="ts">
 import Integrations from "../@components/Integration/Integrations.svelte";
import FeatureSectionHead from "../@homepage/FeatureSectionHead.svelte";
</script>

<svelte:head>
	<title>Integrations</title>
	<link rel="canonical" href="https://talk.hyvor.com/customers" />
</svelte:head>

<div class="hds-container">
	<FeatureSectionHead
		title="Integrations"
		subtitle="Connect Hyvor Post with your favorite tools"
		wrapStyle="margin-top:50px"
	/>
</div>

<Integrations />