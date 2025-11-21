<script lang="ts">
	import BrowserWindow from './BrowserWindow.svelte';
	import IconPencilSquare from '@hyvor/icons/IconPencilSquare';
	import IconCheckAll from '@hyvor/icons/IconCheckAll';
	import IconCodeSlash from '@hyvor/icons/IconCodeSlash';
</script>

<div class="signup-form hds-container-max">
	<div class="left">
		<h1>Signup Form</h1>
		<h2>
			Embeddable, customizable newsletter form to collect emails from your users on your
			website
		</h2>
		<!-- <img src={signupSvg} alt="Signup Form Illustration" width="300" /> -->

		<div class="bento-grid">
			<div class="item">
				<div class="int">
					<IconCodeSlash class="int-icon" size={28} />
					<div class="seo-feature-name">Easy embedding</div>
				</div>
				<div class="seo-feature-description">
					Easily embed the signup form on your website with a simple copy-paste of the
					generated code snippet.
				</div>
			</div>

			<div class="item">
				<div class="int">
					<IconCheckAll class="int-icon" size={28} />
					<div class="seo-feature-name">Double opt-in</div>
				</div>
				<div class="seo-feature-description">
					Ensure compliance and improve email deliverability with built-in double opt-in
					functionality.
				</div>
			</div>

			<div class="item">
				<div class="int">
					<IconPencilSquare class="int-icon" size={23} />
					<div class="seo-feature-name">Fully-customizable</div>
				</div>
				<div class="seo-feature-description">
					Customize the appearance and behavior of the signup form to match your website's
					design and branding.
				</div>
			</div>
		</div>
	</div>
	<div class="right">
		<div class="form-preview">
			<BrowserWindow image="/img/signup.png" link="example.com" />
		</div>
	</div>
</div>

<style>
	:global(body) {
		overflow-x: hidden;
	}

	.signup-form {
		display: flex;
		padding-top: 80px;
		padding-bottom: 80px;
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
		padding-left: 70px;
		border-radius: 20px;
		opacity: 0.9;
	}

	.bento-grid {
		display: grid;
		grid-template-columns: 1fr; /* only one column */
		gap: 25px;
		margin-top: 40px;
	}

	.item {
		background: #f1f1f1;
		border-radius: 20px;
		padding: 25px 30px;
		transition:
			transform 0.3s ease,
			box-shadow 0.3s ease;
	}

	.item:hover {
		transform: translateY(-5px);
		box-shadow: 0 6px 16px var(--box-shadow);
	}

	.int {
		display: flex;
		align-items: center;
		gap: 12px;
		margin-bottom: 10px;
		color: var(--text);
	}

	.seo-feature-name {
		font-weight: 600;
		font-size: 20px;
		color: var(--text);
	}

	.seo-feature-description {
		font-size: 18px;
		line-height: 1.6;
		color: var(--gray-dark);
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

	/* .another-browser {
        animation:
            heroEnterDelayed 3s ease-out,
            subtleFloat 6s ease-in-out infinite 3s reverse;
        /* differed the appearance and float animation a bit using different timings
    } */

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

	/* for larger screens */
	@media (min-width: 1500px) {
		.signup-form {
			padding: 100px 0;
			margin: auto;
		}
	}
</style>
