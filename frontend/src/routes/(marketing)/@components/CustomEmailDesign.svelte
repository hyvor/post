<script lang="ts">
	import BrowserWindow from './BrowserWindow.svelte';
	import customEmailImg from '../img/custom-email.jpeg';
	import customEmailImg1 from '../img/custom-email1.jpeg';
</script>

<div class="custom-email-design">
	<div class="left">
		<h1>Custom Email Design</h1>

		<h2>Choose from a variety of templates and customize them to match your brand</h2>
	</div>

	<div class="right">
		<!-- You can add images or additional content here if needed -->
		<div class="form-preview">
			<BrowserWindow image={customEmailImg} link="https://post.hyvor.com" />
		</div>

		<div class="another-browser">
			<BrowserWindow image={customEmailImg1} link="https://post.hyvor.com" />
		</div>
	</div>
</div>

<style>
	.custom-email-design {
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
		width: 120%; /* did this to make it extend over the screen edge */
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
		width: 500px;
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

	/* mobilr */
	@media (max-width: 900px) {
		.custom-email-design {
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
			/* display: flex; */
			display: none;
		}
	}

	/* for larger screens */
	@media (min-width: 1200px) {
		.custom-email-design {
			padding: 100px 0;
			margin: auto;
		}
	}
</style>
