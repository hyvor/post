<script lang="ts">
	import archiveImg from '../img/archive.png';
	import emailImg from '../img/email.png';
	import BrowserWindow from './BrowserWindow.svelte';
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
		</div>
	</div>
</div>

<style>
	.archive-site {
		/* width: 95vw; */
		padding: 60px 0;
		/* margin: auto 55px; */
		background-color: var(--accent);
	}

	.wrap {
		display: flex;
		/* width: 90%; */
		margin: auto 55px;
		/* max-width: 1200px; */
		text-align: right;
	}

	h1 {
		margin: 0;
		font-size: 60px;
		text-align: end;
	}

	h2 {
		font-weight: normal;
		font-size: 20px;
		color: var(--grey-dark);
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
		width: 150%; /* did this to make it extend over the screen edge */
		max-width: none; /* did this to remove max-width thing */
		margin-left: -50%; /* This makes it extend over the left edge */
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

	@media (max-width: 992px) {
		.wrap {
			margin: auto 20px;
			text-align: center;
		}

		h1 {
			font-size: 40px;
		}

		h2 {
			font-size: 18px;
			padding-top: 10px;
		}

		.left,
		.right {
			float: none;
			width: 100%;
			text-align: center;
		}
	}
</style>
