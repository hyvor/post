<script lang="ts">
	import BrowserWindow from './BrowserWindow.svelte';
	import IconPencilSquare from '@hyvor/icons/IconPencilSquare';
	import IconLink from '@hyvor/icons/IconLink';
</script>

<div class="custom-email-design">
	<div class="wrap hds-container-max">
		<div class="left">
			<h1>Custom Email Design</h1>

			<h2>Choose from a variety of templates and customize them to match your brand</h2>

			<div class="bento-grid">
				<div class="item">
					<div class="int">
						<IconPencilSquare class="int-icon" size={23} />
						<div class="seo-feature-name">Customizable Email Templates</div>
					</div>
					<div class="seo-feature-description">
						Choose from a variety of professionally designed email templates that can be
						easily customized to match your brand's look and feel.
					</div>
				</div>

				<div class="item">
					<div class="int">
						<IconLink class="int-icon" size={28} />
						<div class="seo-feature-name">Free subdomain</div>
					</div>
					<div class="seo-feature-description">
						Get a free subdomain (e.g., yourname.hyvorpost.io) to send emails without
						needing your own domain.
					</div>
				</div>
			</div>
		</div>

		<div class="right">
			<!-- You can add images or additional content here if needed -->
			<div class="form-preview">
				<BrowserWindow image="/img/home/custom-email.png" link="/settings/design" />
			</div>

			<div class="another-browser">
				<BrowserWindow image="/img/home/custom-email1.png" link="/settings/design" />
			</div>
		</div>
	</div>
</div>

<style>
	.custom-email-design {
		/* width: var(--width);
		max-width: var(--max-width);
		padding: 60px 0;
		margin: auto 55px; */
		display: flex;
		padding-top: 80px;
		padding-bottom: 80px;
	}

	.wrap {
		display: flex;
		text-align: right;
		justify-content: center;
		gap: 40px;
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
		/* padding: 60px 0; */
	}

	.right {
		flex: 1;
		padding-left: 55px;
		position: relative;
		/* padding: 60px 0; */
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

	.form-preview {
		width: 120%; /* did this to make it extend over the screen edge */
		max-width: none; /* did this to remove max-width thing */
		margin-left: 0;
		padding-left: 70px;
		border-radius: 20px;
		opacity: 0.9;
	}

	.another-browser {
		position: absolute;
		width: 600px;
		bottom: 60px;
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
	@media (max-width: 992px) {
		.custom-email-design {
			flex-direction: column;
			padding: 20px 0;
			margin: auto;
		}
		.wrap {
			flex-direction: column;
			text-align: center;
			margin: 20px auto;
			gap: 30px;
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

		h1,
		h2 {
			text-align: center !important;
		}

		.right {
			padding: 30px 0 0 0;
		}

		.form-preview {
			width: 100%;
			margin-left: 0;
			padding: 0;
		}

		.bento-grid {
			grid-template-columns: 1fr;
			gap: 20px;
			justify-items: center;
			/* justify-items: stretch;  */
			width: 100%;
		}

		.int {
			justify-content: center;
		}

		.another-browser {
			/* display: flex; */
			display: none;
		}
	}

	/* for larger screens */
	@media (min-width: 1500px) {
		.custom-email-design {
			padding: 100px 0;
			margin: auto !important;
		}
	}
</style>
