<script lang="ts">
	import archiveImg from '../img/archive.png';
	import emailImg from '../img/email.png';
	import BrowserWindow from './BrowserWindow.svelte';
	import IconBullseye from '@hyvor/icons/IconBullseye';
	import IconLink from '@hyvor/icons/IconLink';
</script>

<div class="archive-site">
	<div class="wrap">
		<div class="left">
			<div class="main-browser">
				<BrowserWindow image={archiveImg} link="post.hyvor.com/archive" />
			</div>

			<div class="another-browser">
				<BrowserWindow image={emailImg} link="pensee.com/fr" />
			</div>
		</div>

		<div class="right">
			<h1>Archive Site</h1>
			<h2>This site is for all the emails sent through Hyvor Post.</h2>

			<div class="bento-grid">
				<div class="int">
					<IconBullseye class="int-icon" size={30} />
					<div class="seo-feature-name">Customizable</div>
				</div>

				<div class="int">
					<IconLink class="int-icon" size={30} />
					<div class="seo-feature-name">Free subdomain</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	.archive-site {
		padding: 60px 0;
		background-color: var(--accent);
	}

	.wrap {
		display: flex;
		margin: auto 55px;
		text-align: right;
	}

	h1 {
		margin: 0;
		font-size: 60px;
		text-align: end;
		color: var(--text-white);
	}

	h2 {
		font-weight: normal;
		font-size: 20px;
		color: var(--text-white);
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
		max-width: none; /* did this to remove max-width thing */
		margin-left: -50%; /* This makes it extend over the left edge */
		padding-top: 70px;
		padding-bottom: 70px;
		padding-left: 70px;
		border-radius: 20px;
		opacity: 0.9;
	}

	.another-browser {
		position: absolute;
		width: 350px;
		bottom: 30px;
		left: 330px;
		border-radius: 20px;
		box-shadow: var(--box-shadow);
	}

	.right {
		flex: 1;
		margin-left: 35px;
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
		color: var(--gray-light);
		justify-content: flex-end;
	}

	.seo-feature-name {
		color: var(--gray-light);
	}

	@media (max-width: 992px) {
		.wrap {
			flex-direction: column-reverse;
			text-align: center;
			margin: 20px auto;
		}

		.left {
			width: 100%;
			padding-right: 0;
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

		.main-browser {
			width: 100%;
			margin-left: 0;
		}

		.another-browser {
			position: relative;
			bottom: 0;
			left: 0;
			margin-top: 30px;
			box-shadow: var(--box-shadow);
		}
	}
</style>
