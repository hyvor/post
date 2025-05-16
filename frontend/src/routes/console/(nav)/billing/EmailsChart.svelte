<script lang="ts">
	import Chart from 'chart.js/auto';
	import dayjs from 'dayjs';
	import { onMount } from 'svelte';
	import annotationPlugin from 'chartjs-plugin-annotation';

	let canvas: HTMLCanvasElement;
	let chart: Chart;

	export let data: Record<string, number> = {};
	export let max: number;

	function renderChart() {
		Chart.register(annotationPlugin);

		chart = new Chart(canvas, {
			type: 'line',
			options: {
				interaction: {
					intersect: false,
					mode: 'index'
				},
				plugins: {
					legend: {
						display: false,
						labels: {
							font: {
								family: "'Readex Pro', sans-serif",
							}
						}
					},
					tooltip: {
						displayColors: false
					},
					annotation: {
						annotations: {
							max: {
								type: 'line',
								yMin: max,
								yMax: max,
								borderColor: '#ab2525',
								borderWidth: 2
							}
						}
					}
				},
				scales: {
					x: {
						ticks: {
							autoSkip: true,
							maxTicksLimit: 15
						},
						grid: {
							display: false
						}
					},
					y: {
						grid: {
							display: false
						},
						min: 0,
						ticks: {
							precision: 0,
							maxTicksLimit: 6
						}
					}
				}
			},
			data: {
				labels: Object.keys(data).map((d) => dayjs(d).format('MMM')),
				datasets: [
					{
						label: 'Emails',
						data: Object.values(data),
						backgroundColor: '#e5f1f2',
						borderColor: '#5A8387',
						borderWidth: 3,
						borderJoinStyle: 'round',
						tension: 0.15,
						pointRadius: 0,
						pointHoverRadius: 5
					}
				]
			}
		});
	}

	onMount(renderChart);
</script>

<div>
    <canvas bind:this={canvas}></canvas>
</div>

<style>
    div {
        padding: 20px 15px;
    }
</style>