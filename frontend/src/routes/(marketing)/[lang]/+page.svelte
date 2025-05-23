<script>
	import AboveFold from './@components/AboveFold.svelte';
	import Letter from './@components/Letter.svelte';
	import TrialSignUp from './@components/TrialSignUp.svelte';
	import AllFeatures from './@homepage/AllFeatures.svelte';
	import StartStep from './@homepage/startstep/StartStep.svelte';
</script>

<svelte:head>
	<title>Hyvor Post</title>
	<meta name="description" content="Simple Newsletter Platform" />
	<link rel="canonical" href="https://post.hyvor.com" />
</svelte:head>

<AboveFold />
<div class ="letter-wrapper hds-container">
	<Letter />	
</div>
<!-- <StartStep /> -->
<AllFeatures />
<TrialSignUp />
