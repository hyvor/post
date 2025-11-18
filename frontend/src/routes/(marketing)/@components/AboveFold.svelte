<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import TrialChecks from './TrialChecks.svelte';
	import Envelope from './Envelope.svelte';
	import { getMarketingI18n } from '../locale';
	import BrowserWindow from './BrowserWindow.svelte';
	import emailImg from '../img/email.png';
	import consoleImg from '../img/console.png';

	const I18n = getMarketingI18n();
</script>

<div class="hds-container-max above-fold">
	<div class="left">
		<h1 id="waitlist">
			<span class="highlight">Simple, privacy-first</span> <br />
			{I18n.t('homepage.heading')}
		</h1>

		<h2>
			{I18n.t('homepage.subHeading')}
		</h2>
		<div class="buttons">
			<Button as="a" href="/console?signup" size="x-large"
				>{I18n.t('homepage.cta')}
				{#snippet end()}
					&rarr;
				{/snippet}
			</Button>
		</div>

		<div class="trial-checks">
			<TrialChecks />
		</div>
	</div>

	<div class="right">
		<!-- <MainGraphic /> -->
		<div class="main-browser">
			<BrowserWindow image={consoleImg} link="post.hyvor.com/console" />
		</div>
		<div class="another-browser">
			<Envelope emailImage={emailImg} />
		</div>

		<div class="main-graphic">
			<img src="/img/main-graphic.svg" alt="Main Graphic" />
		</div>
	</div>
</div>

<style>
	:global(body) {
		overflow-x: hidden;
	}

	.above-fold {
		position: relative;
		padding-top: 75px;
		display: flex;
		width: var(--width);
		padding-bottom: 75px;
		max-width: var(--max-width);
		margin: 0 auto;
	}

	.left {
		width: 50%;
	}

	.right {
		width: 50%;
		padding-left: 25px;
	}

	.main-browser {
		width: 150%; /* did this to make it extend over the screen edge */
		max-width: none; /* did this to remove max-width thing */
		margin-left: 0;
		width: clamp(100%, 150%, 900px); /* min, ideal, max */
		margin-left: 0;
		animation:
			heroEnter 1s ease-out,
			subtleFloat 8s ease-in-out infinite 2s;
	}

	/* main-browser scaling for medium screens */
	@media (max-width: 1280px) and (min-width: 993px) {
		.main-browser {
			width: 140%; /* slightly smaller to fit better */
			margin-left: -10%; /* shift it left to balance clipping */
		}

		.another-browser {
			top: 220px; /* adjust vertical position */
			right: 50px; /* closer to edge for balance */
		}
	}

	.another-browser {
		position: absolute;
		top: 250px;
		right: 100px;
		width: 30%;
		animation:
			heroEnterDelayed 1.2s ease-out,
			subtleFloat 6s ease-in-out infinite 3s reverse;
	}

	.main-graphic {
		display: none;
	}

	.trial-checks {
		margin-top: 40px;
		margin-left: 0;
		display: flex;
		text-align: left;
	}

	@keyframes heroEnter {
		0% {
			transform: translateX(30px) translateY(0);
			opacity: 0.9;
		}
		100% {
			transform: translateX(0) translateY(0);
			opacity: 1;
			filter: blur(0);
		}
	}

	@keyframes heroEnterDelayed {
		0% {
			transform: translateX(0) translateY(30px);
			opacity: 0.9;
		}
		100% {
			transform: translateX(0) translateY(0);
			opacity: 1;
		}
	}

	@keyframes subtleFloat {
		0%,
		100% {
			transform: translateY(0) rotate(0deg);
		}
		33% {
			transform: translateY(-3px) rotate(0.5deg);
		}
		66% {
			transform: translateY(-1px) rotate(-0.3deg);
		}
	}

	h1 {
		margin: 0;
		font-size: 60px;
		margin-bottom: 10px;
	}

	h1 .highlight {
		font-size: 45px;
	}

	h2 {
		font-weight: normal;
		font-size: 28px;
		color: var(--grey-dark);
		margin: 0;
		padding-top: 30px;
	}

	.buttons {
		display: flex;
		gap: 10px;
		margin-top: 40px;
	}

	@media (max-width: 992px) {
		.above-fold {
			flex-direction: column;
			text-align: center;
		}

		.left {
			width: 100%;
			padding-left: 0;
			display: flex;
			flex-direction: column;
			align-items: center;
		}

		h1 {
			margin-top: 20px;
		}

		h2 {
			padding-top: 10px;
		}

		.right {
			width: 100%;
			margin-top: 70px;
			padding-left: 0;
		}

		.main-browser {
			display: none;
		}

		.buttons {
			justify-content: center;
		}

		.another-browser {
			display: none;
		}

		.main-graphic {
			display: block;
			max-width: 80%;
			margin: auto;
			padding: 0;

			img {
				width: 100%;
				height: 100%;
			}
		}
	}

	@media (max-width: 768px) {
		.above-fold {
			padding-top: 40px;
			padding-bottom: 40px;
			width: 100%;
		}

		h1 {
			font-size: 40px;
			line-height: 1.2;
		}

		h1 .highlight {
			font-size: 28px;
		}

		h2 {
			font-size: 20px;
			padding-top: 15px;
		}

		.right {
			width: 100%;
			margin-top: 70px;
			padding-left: 0;
		}

		.main-browser {
			display: none;
		}

		.another-browser {
			display: none;
		}

		.main-graphic {
			display: block;
			max-width: 80%;
			margin: auto;
			padding: 0;
		}
	}

	@media (max-width: 480px) {
		h1 {
			font-size: 32px;
		}

		h1 .highlight {
			font-size: 22px;
			display: block;
			margin-bottom: 5px;
		}

		h2 {
			font-size: 18px;
		}

		.buttons {
			flex-direction: column;
		}
	}
</style>
