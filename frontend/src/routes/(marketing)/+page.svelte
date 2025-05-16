<script>
	import { Button, Dropdown } from "@hyvor/design/components";
	import AboveFold from "./@components/AboveFold.svelte";
	import TrialSignUp from "./@components/TrialSignUp.svelte";
	import AllFeatures from "./@homepage/AllFeatures.svelte";
	import StartStep from "./@homepage/startstep/StartStep.svelte";

	let showDropdown = false;
	
</script>

<svelte:head>
    <title>Hyvor Post</title>
    <meta name="description" content="Add description here">
	<link rel="canonical" href="https://post.hyvor.com">
</svelte:head>

<AboveFold />
<StartStep />
<AllFeatures />
<TrialSignUp />

