<script lang="ts">
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();
</script>

<div class="letter-section">
	<div class="wrap" id="letter">
		<div class="head">
			<h1 class="title">{I18n.t('homepage.whyHyvorPost')}</h1>
		</div>

		<p>
			<I18n.T key="homepage.letter.s1" params={{ strong: { element: 'strong' } }} />
		</p>

		<p>
			{I18n.t('homepage.letter.p1')}
		</p>

		<p>
			{I18n.t('homepage.letter.p2')}
		</p>

		<p>
			{I18n.t('homepage.letter.s2')},
			<strong>{I18n.t('homepage.letter.s2Desc')}</strong>.
		</p>

		<p>
			{I18n.t('homepage.letter.p3')}
		</p>
	</div>
</div>

<style>
	.letter-section {
		background-color: var(--accent-light);
		padding: 100px 0;
	}

	/* larger screens */
	/* @media (min-width: 1200px) {
		.letter-section {
			padding: 150px 0;
		}
	} */

	.wrap {
		z-index: 1;
		font-style: normal;
		font-size: 20px;
		line-height: 32px;
		padding: 30px 55px;
		margin: auto;
		max-width: 100%;
		width: 1000px;
		border-radius: 20px;
	}

	.head {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	.title {
		font-size: 35px;
		font-weight: 600;
		line-height: 60px;
		margin-top: 0;
		margin-bottom: 0;
		/* color: #b7f9ff; */
	}

	p {
		margin-top: 32px;
		margin-bottom: 32px;
		font-size: 20px;
		line-height: 32px;
	}

	/* p :global(strong) {
		color: #b7f9ff;
	} */

	@media (max-width: 768px) {
		.letter-section {
			padding: 50px 0;
		}

		.wrap {
			padding: 20px 25px;
			font-size: 18px;
			line-height: 28px;
			margin: 0 15px;
			width: auto;
		}

		.title {
			font-size: 28px;
			line-height: 40px;
		}

		p {
			margin-top: 24px;
			margin-bottom: 24px;
			font-size: 18px;
			line-height: 28px;
		}
	}

	/* for larger screens */
	@media (min-width: 1200px) {
		.letter-section {
			padding: 150px 0;
			margin: auto;
		}
	}
</style>
