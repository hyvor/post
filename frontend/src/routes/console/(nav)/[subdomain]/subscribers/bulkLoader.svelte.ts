export class SimpleLoadingProgress {
	private loading = $state(false as false | string);
	private current = 1;
	private total = 0;

	constructor() {}

	start(total: number) {
		this.total = total;
		this.writeLoading();
	}

	next() {
		this.current = Math.min(this.total, this.current + 1);
		this.writeLoading();
	}

	done() {
		this.loading = false;
	}

	private writeLoading() {
		this.loading = this.current + '/' + this.total;
	}
	getLoading() {
		return this.loading;
	}
}
