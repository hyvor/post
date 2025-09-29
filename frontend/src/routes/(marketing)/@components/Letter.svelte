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
		background-color: var(--accent);
		padding: 100px 0;
	}

	.wrap {
		position: relative; /* enable positioning context for overlay */
		z-index: 1;
		font-style: normal;
		font-size: 20px;
		line-height: 32px;
		padding: 30px 55px;
		margin: auto;
		max-width: 1000px;
		border-radius: 20px;
		border: 1.5px solid var(--text);
		overflow: hidden; /* keeps overlay inside rounded corners */
	}

	.wrap::before {
		content: '';
		position: absolute;
		inset: 0; /* top:0; right:0; bottom:0; left:0 */
		background-color: var(--accent-lightest); /* your chosen background color */
		opacity: 0.6; /* adjust transparency */
		z-index: -1; /* place it behind the content */
		border-radius: inherit; /* follow the parent’s radius */
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
	}

	p {
		margin-top: 32px;
		margin-bottom: 32px;
		font-size: 20px;
		line-height: 32px;
	}

	.highlight {
		text-decoration: underline;
	}

	@media (max-width: 768px) {
		.letter-section {
			padding: 50px 0;
		}

		.wrap {
			padding: 20px 25px;
			font-size: 18px;
			line-height: 28px;
			margin: 0 15px;
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
</style>
