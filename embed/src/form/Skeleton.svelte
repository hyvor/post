<div class="wrap">
    <div class="header"></div>
    <div class="input"></div>
</div>

<style>
    .wrap {
        width: 425px;
        margin: 0 auto;
        max-width: 100%;
        --skeleton-color: #eee;
    }

    .header,
    .input {
        background-color: var(--skeleton-color);
        animation: shimmer 1.5s infinite linear;
        background: linear-gradient(
            to right,
            var(--skeleton-color) 0%,
            #f6f6f6 20%,
            var(--skeleton-color) 40%,
            var(--skeleton-color) 100%
        );
        background-size: 1000px 100%;
    }

    .header {
        width: 100%;
        height: 25px;
        margin-bottom: 10px;
        border-radius: 5px;
    }

    .input {
        width: 100%;
        height: 40px;
        margin-bottom: 10px;
        border-radius: 5px;
    }

    @keyframes shimmer {
        0% {
            background-position: -468px 0;
        }
        100% {
            background-position: 468px 0;
        }
    }
</style>
