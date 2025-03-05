<script>
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
</script>

<div class="checks" {...$$restProps}>
	<div class="check">
		<IconCheckCircleFill />
		14-day free trial
	</div>

	<div class="check">
		<IconCheckCircleFill />
		No credit card required
	</div>

	<div class="check">
		<IconCheckCircleFill />
		Cancel anytime
	</div>
</div>

<style>
	.check {
		display: flex;
		align-items: center;
		gap: 6px;
		margin-bottom: 6px;
	}

	.check :global(svg) {
		color: var(--accent);
	}

	@media (max-width: 992px) {
		.checks {
			justify-content: center;
			flex-direction: column;
			align-items: center;
		}
		.check {
			width: 250px;
			max-width: 100%;
		}
	}
</style>
