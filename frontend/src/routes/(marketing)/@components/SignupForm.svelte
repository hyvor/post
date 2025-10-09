<script lang="ts">
	import BrowserWindow from './BrowserWindow.svelte';
	import signupImg from '../img/signup.png';
	import emailImg from '../img/email.png';
	import Envelope from './Envelope.svelte';
	import IconBullseye from '@hyvor/icons/IconBullseye';
	import IconLink from '@hyvor/icons/IconLink';
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

		<div class="bento-grid">
			<div class="int">
				<IconBullseye class="int-icon" size={30} />
				<div class="seo-feature-name">Easy embedding</div>
			</div>

			<div class="int">
				<IconLink class="int-icon" size={30} />
				<div class="seo-feature-name">Double opt-in</div>
			</div>

			<div class="int">
				<IconLink class="int-icon" size={30} />
				<div class="seo-feature-name">Fully-customizable</div>
			</div>
		</div>
	</div>
	<div class="right">
		<div class="form-preview">
			<BrowserWindow image={signupImg} link="https://post.hyvor.com" />
		</div>

		<!-- <div class="another-browser">
			<Envelope emailImage={emailImg} />
		</div> -->
	</div>
</div>

<style>
	:global(body) {
		overflow-x: hidden;
	}
	.signup-form {
		width: var(--width);
		max-width: var(--max-width);
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
		/* filter: blur(1px); */
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

	.bento-grid {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 20px 40px;
		margin-top: 40px;
	}

	.int {
		display: flex;
		align-items: center;
		gap: 15px;
		/* font-size: 25px; */
		font-size: 20px;
		margin-bottom: 10px;
		color: var(--gray-dark);
	}

	.seo-feature-name {
		color: var(--gray-dark);
	}

	/* h1 {
		margin: 0;
		font-size: 40px;
	} */

	/* h2 {
		font-weight: normal;
		/* font-size: 25px; */
	/* font-size: 20px;
	color: var(--grey-dark);
	margin: 0;
	padding-top: 20px; */
	/* } */
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

	@media (max-width: 992px) {
		.signup-form {
			flex-direction: column;
			align-items: center;
			text-align: center;
			margin: 20px auto;
			gap: 30px;
			padding: 40px 0;
		}

		.left {
			width: 100%;
			padding: 0 15px;
			display: flex;
			flex-direction: column;
			align-items: center;
		}

		.right {
			width: 100%;
			padding-left: 0;
			text-align: center;
		}

		h1 {
			font-size: 42px;
			text-align: center;
			margin-top: 10px;
		}

		h2 {
			font-size: 18px;
			text-align: center;
			padding-top: 10px;
			max-width: 90%;
		}

		.form-preview {
			width: 100%;
			margin-left: 0;
			padding: 40px 20px;
		}

		.bento-grid {
			grid-template-columns: 1fr;
			gap: 20px;
			justify-items: center;
		}

		.int {
			justify-content: center;
		}

		.another-browser {
			display: none;
		}
	}

	@media (max-width: 768px) {
		h1 {
			font-size: 36px;
			line-height: 1.2;
		}

		h2 {
			font-size: 16px;
			max-width: 90%;
		}

		.form-preview {
			padding: 30px 15px;
		}
	}

	@media (max-width: 480px) {
		h1 {
			font-size: 30px;
		}

		h2 {
			font-size: 15px;
			padding-top: 5px;
		}

		.bento-grid {
			margin-top: 25px;
			gap: 15px;
		}

		.int {
			font-size: 16px;
			gap: 10px;
		}
	}
</style>
