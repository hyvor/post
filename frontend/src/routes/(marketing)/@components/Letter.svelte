<script lang="ts">
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();
</script>

<div class="letter-section">
	<div class="wrap" id="letter">
		<div class="overlay"></div>
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
	.overlay {
		position: absolute;
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		z-index: -1;
		opacity: 0.3;
		background-color: rgb(196, 212, 255);
	}
	.letter-section {
		background-color: var(--accent);
		padding: 100px 0;
	}
	.wrap {
		font-style: normal;
		font-size: 20px;
		/* line-height: 32px; */
		line-height: 32px;
		padding: 30px 55px 30px 55px;
		/* margin: 0 55px; */
		/* margin: 0 200px; */
		margin: auto;
		max-width: 1000px;
		border-radius: 20px;
		border: 1px solid var(--text);
		/* background-color: var(--accent-lightest); */

		/* background-image: url("data:image/svg+xml,%3Csvg height='30' width='1000' xmlns='http://www.w3.org/2000/svg'%3E%3Cline x1='0' y1='0' x2='1000' y2='0' style='stroke:%23eee;stroke-width:1.5' /%3E%3C/svg%3E");
		background-repeat: repeat-y;
		background-position-y: top;

		background-color: #fff9ef; */

		/* position: relative; */
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
</style>
