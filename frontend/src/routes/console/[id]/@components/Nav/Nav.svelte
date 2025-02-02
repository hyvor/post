<script>
	import AccountNav from './AccountNav.svelte';
	import ProjectNav from './ProjectNav.svelte';
</script>

<div id="nav">
	<div id="nav-wrap">
		<div class="account-nav">
			<AccountNav />
		</div>
		<div class="nav">
			<ProjectNav />
		</div>
	</div>
</div>

<style lang="scss">
	.account-nav {
		margin-bottom: 15px;
	}

	#nav {
		width: 280px;
		padding: 15px;
		padding-right: 0;
		flex-shrink: 0;
	}

	#nav-wrap {
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.nav {
		flex: 1;
		overflow: auto;
	}

	@media (max-width: 992px) {
		.account-nav {
			margin-bottom: 0;
		}
		#nav-wrap {
			position: fixed;
			bottom: 0;
			left: 0;
			width: 100%;
			z-index: 100;
			height: initial;
			flex: 1;
		}
		#nav {
			height: 0;
			padding: 0;
		}

		.nav {
			border-radius: 0;
			box-shadow: none;
			border-bottom: none;
			width: 100%;
		}
	}
</style>
