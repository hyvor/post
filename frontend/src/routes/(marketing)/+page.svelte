<script>
	import AboveFold from './@components/AboveFold.svelte';
	import ArchiveSite from './@components/ArchiveSite.svelte';
	import CustomEmailDesign from './@components/CustomEmailDesign.svelte';
	import FaqHome from './@components/FaqHome.svelte';
	import Letter from './@components/Letter.svelte';
	import SignupForm from './@components/SignupForm.svelte';
	import TrialSignUp from './@components/TrialSignUp.svelte';
	import AllFeatures from './@homepage/AllFeatures.svelte';
</script>

<svelte:head>
	<title>Hyvor Post - Simple, Privacy-First Newsletter Platform</title>
	<meta
		name="description"
		content="Hyvor Post is a simple, privacy-first newsletter platform. No Tracking. Just Ideas, Delivered."
	/>
	<link rel="canonical" href="https://post.hyvor.com" />

	<script type="text/javascript">
		window.$crisp = [];
		window.CRISP_WEBSITE_ID = '1cab78fb-4baf-497e-a10f-00a3b12cfcfe';
		(function () {
			d = document;
			s = d.createElement('script');
			s.src = 'https://client.crisp.chat/l.js';
			s.async = 1;
			d.getElementsByTagName('head')[0].appendChild(s);
		})();
	</script>
</svelte:head>

<AboveFold />
<Letter />
<CustomEmailDesign />
<ArchiveSite />
<SignupForm />
<AllFeatures />
<FaqHome />
<TrialSignUp />
