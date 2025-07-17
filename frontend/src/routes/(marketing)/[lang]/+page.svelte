<script>
	import AboveFold from './@components/AboveFold.svelte';
	import FaqHome from './@components/FaqHome.svelte';
	import FaqComingSoon from './@components/FaqHome.svelte';
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
<Letter />
<!-- <StartStep /> -->
<AllFeatures />
<FaqHome />
<TrialSignUp />
