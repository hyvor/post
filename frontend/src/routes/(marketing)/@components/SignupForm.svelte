<script lang="ts">
	import BrowserWindow from './BrowserWindow.svelte';
	import signupImg from '../img/signup.png';
	import emailImg from '../img/email.png';
	import Envelope from './Envelope.svelte';
	// import signupSvg from './img/img/api-webhooks.svg';
</script>

<div class="signup-form">
	<div class="left">
		<h1>Signup Form</h1>
		<h2>
			Embeddable, customizable newsletter form to collect emails from your users on your
			website
		</h2>
		<!-- <img src={signupSvg} alt="Signup Form Illustration" width="300" /> -->
	</div>
	<div class="right">
		<div class="form-preview">
			<BrowserWindow image={signupImg} link="https://post.hyvor.com" />
		</div>

		<div class="another-browser">
			<Envelope emailImage={emailImg} />
			<!-- Additional content can go here if needed -->
		</div>
	</div>
</div>

<style>
	:global(body) {
		overflow-x: hidden;
	}
	.signup-form {
		/* width: 95vw; */
		padding: 60px 0;
		margin: auto 55px;
		display: flex;
	}

	h1 {
		margin: 0;
		font-size: 60px;
		text-align: start;
	}

	h2 {
		font-weight: normal;
		font-size: 20px;
		color: var(--grey-dark);
		margin: 0;
		padding-top: 20px;
		text-align: start;
	}

	.left {
		flex: 1;
	}

	.right {
		flex: 1;
		padding-left: 55px;
		position: relative;
	}

	.form-preview {
		width: 110%; /* did this to make it extend over the screen edge */
		max-width: none; /* did this to remove max-width thing */
		margin-left: 0;
		/* background-color: #191e19; */
		padding-top: 70px;
		padding-bottom: 70px;
		padding-left: 70px;
		border-radius: 20px;
		filter: blur(1px);
		opacity: 0.9;
	}

	.another-browser {
		position: absolute;
		width: 400px;
		bottom: 30px;
		right: 0;
		border-radius: 20px;
		box-shadow: var(--box-shadow);
	}

	@keyframes heroEnter {
		0% {
			/* //transform: translateX(30px) translateY(0) translateZ(20px); */
			transform: translateX(300px) translateY(0) translateZ(20px);
		}
		100% {
			/* //transform: translateX(0) translateY(0) translateZ(0); */
			transform: translateX(0) translateY(0) translateZ(0);
			opacity: 1;
			filter: blur(0);
		}
	}

	@keyframes heroEnterDelayed {
		0% {
			transform: translateX(200px) translateY(30px) translateZ(10px);
			/* transform: translateX(0) translateY(30px) translateZ(10px); */
			opacity: 0.1;
		}
		100% {
			/* transform: translateX(0) translateY(0) translateZ(0); */
			opacity: 1;
			transform: translateX(0) translateY(0) translateZ(0);
			/* filter: blur(0); */
		}
	}

	@keyframes subtleFloat {
		0%,
		100% {
			transform: translateY(0);
		}
		50% {
			transform: translateY(-10px);
		}
	}

	.form-preview {
		animation:
			heroEnter 1s ease-out,
			subtleFloat 8s ease-in-out infinite 2s;
	}

	.another-browser {
		animation:
			heroEnterDelayed 3s ease-out,
			subtleFloat 6s ease-in-out infinite 3s reverse;
		/* differed the appearance and float animation a bit using different timings */
	}

	/* mobile */
	@media (max-width: 900px) {
		.signup-form {
			flex-direction: column;
			padding: 20px 0;
			margin: auto 20px;
		}

		.left {
			padding: 0 10px;
			text-align: center;
		}

		h1 {
			font-size: 40px;
		}

		h2 {
			font-size: 18px;
		}

		.right {
			padding: 30px 0 0 0;
		}

		.form-preview {
			display: none;
		}

		.another-browser {
			display: block;
		}
	}
</style>
