<script lang="ts">
	import BrowserWindow from './BrowserWindow.svelte';
	import IconPencilSquare from '@hyvor/icons/IconPencilSquare';
	import IconLink from '@hyvor/icons/IconLink';
</script>

<div class="archive-site">
	<div class="wrap hds-container-max">
		<div class="left">
			<div class="main-browser">
				<BrowserWindow image="/img/home/archive.png" link="weekly-digest.hyvorpost.email" />
			</div>

			<div class="another-browser">
				<BrowserWindow image="/img/home/email.png" link="weekly-digest.hyvorpost.email" />
			</div>
		</div>

		<div class="right">
			<h1>Archive Site</h1>
			<h2>This site is for all the emails sent through Hyvor Post.</h2>

			<div class="bento-grid">
				<div class="item">
					<div class="int">
						<IconPencilSquare class="int-icon" size={28} />
						<div class="seo-feature-name">Customizable</div>
					</div>
					<div class="seo-feature-description">
						Design your archive site to match your brand's look and feel with
						customizable templates.
					</div>
				</div>

				<div class="item">
					<div class="int">
						<IconLink class="int-icon" size={28} />
						<div class="seo-feature-name">Free subdomain</div>
					</div>
					<div class="seo-feature-description">
						Get a free subdomain (e.g., yoursubdomain.hyvorpost.email) to host your
						archive site without needing your own domain.
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	.archive-site {
		padding-top: 80px;
		padding-bottom: 80px;
		background-color: var(--accent-light);
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
		text-align: end;
		color: var(--text);
	}

	h2 {
		font-weight: normal;
		font-size: 20px;
		color: var(--text);
		margin: 0;
		padding-top: 20px;
		text-align: end;
	}

	.left {
		flex: 1;
		padding-right: 25px;
		position: relative;
	}

	.main-browser {
		width: 150%;
		max-width: none;
		margin-left: -50%;

		border-radius: 20px;
		opacity: 0.9;
		animation:
			heroEnter 1s ease-out,
			subtleFloat 8s ease-in-out infinite 2s;
	}

	.another-browser {
		position: absolute;
		width: 350px;
		bottom: 30px;
		left: 330px;
		border-radius: 20px;
		box-shadow: var(--box-shadow);
		animation:
			heroEnterDelayed 1.2s ease-out,
			subtleFloat 6s ease-in-out infinite 3s reverse;
	}

	.right {
		flex: 1;
		margin-left: 35px;
	}

	.bento-grid {
		gap: 25px;
		margin-top: 40px;
		display: grid;
		grid-template-columns: 1fr; /* only one column */
	}

	.item {
		background: #ffffff;
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
		justify-content: flex-end;
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
			transform: translateX(30px);
			opacity: 0.9;
		}
		100% {
			transform: translateX(0);
			opacity: 1;
		}
	}

	@keyframes heroEnterDelayed {
		0% {
			transform: translateY(30px);
			opacity: 0.9;
		}
		100% {
			transform: translateY(0);
			opacity: 1;
		}
	}

	@keyframes subtleFloat {
		0%,
		100% {
			transform: translateY(0);
		}
		50% {
			transform: translateY(-3px);
		}
	}

	@media (max-width: 992px) {
		.wrap {
			flex-direction: column-reverse;
			text-align: center;
			margin: 20px auto;
			gap: 30px;
		}

		.left {
			width: 100%;
			padding-right: 0;
			display: flex;
			flex-direction: column;
			align-items: center;
		}

		.right {
			width: 100%;
			margin-left: 0;
			text-align: center;
		}

		h1 {
			font-size: 42px;
			text-align: center;
			margin-top: 10px;
		}

		h2 {
			font-size: 18px;
			padding-top: 10px;
			text-align: center;
		}

		.main-browser {
			width: 100% !important;
			margin-left: 0 !important;
		}

		.another-browser {
			display: none;
		}

		.bento-grid {
			width: 100%;
			grid-template-columns: 1fr;
			gap: 20px;
			justify-items: center;
		}

		.int {
			width: 100%;
			max-width: 420px;
			/* justify-content: center; */
		}

		.seo-feature-description {
			word-break: break-word;
		}
	}

	@media (max-width: 767px) {
		.archive-site {
			padding: 40px 0;
			width: auto;
		}

		h1 {
			font-size: 40px;
			line-height: 1.2;
		}

		h2 {
			font-size: 20px;
		}

		.main-browser {
			padding: 30px 15px;
		}

		.int {
			justify-content: center;
		}
	}

	@media (max-width: 480px) {
		h1 {
			font-size: 40px;
		}

		h2 {
			font-size: 18px;
			padding-top: 20px;
		}

		.bento-grid {
			width: 100% !important;
			margin-top: 25px;
			gap: 15px;
		}

		.int {
			width: 100% !important;
			font-size: 16px;
			gap: 10px;
		}
	}

	/* for larger screens */

	@media (min-width: 1500px) {
		.wrap {
			padding: 100px 0;
			margin: auto;
		}
	}
</style>
