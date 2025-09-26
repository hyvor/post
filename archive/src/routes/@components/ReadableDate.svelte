<script lang="ts">
    import dayjs from "dayjs";

    interface Props {
        time: number;
    }

    let {time}: Props = $props();
    let day: dayjs.Dayjs = $state(dayjs.unix(time));

    $effect(() => {
        day = dayjs.unix(time);
    });
</script>

<time datetime={day.toISOString()}>{day.format('MMM D, YYYY')}</time>
