<script lang="ts">
	import { getMarketingI18n } from '../locale';

	const I18n = getMarketingI18n();
</script>

<div class="hds-container">
	<div class="wrap" id="letter">
		<div class="head">
			<h1 class="title">{I18n.t('homepage.whyHyvorPost')}</h1>
		</div>

		<p>
			{I18n.t('homepage.letter.s1')},
			<strong>{I18n.t('homepage.letter.s1Desc')}</strong>.
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
	.wrap {
		font-style: normal;
		font-size: 20px;
		line-height: 32px;
		padding: 90px 0 30px 0;
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
