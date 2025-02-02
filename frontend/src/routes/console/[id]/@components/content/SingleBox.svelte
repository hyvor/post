<div class="single-box">
	<div class="inner hds-box" {...$$restProps}>
		<slot />
	</div>
</div>

<style lang="scss">
	.single-box {
		height: 100%;
		padding: 15px;
		flex: 1;
		display: flex;
		flex-direction: column;
		--padding-x: 25px;
	}
	.inner {
		height: 100%;
		display: flex;
		flex: 1;
		flex-direction: column;
		position: relative;
	}
</style>
