<script lang="ts">
</script>

<div class="form">
    <div class="title">Subscribe for updates</div>

    <div class="input">
        <input type="text" placeholder="Your Email" />
        <button> Subscribe </button>
    </div>
</div>

<style>
    .form {
        width: 425px;
        margin: 0 auto;
        max-width: 100%;
    }
    .form :global(*) {
        box-sizing: border-box;
    }
    .title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    .input {
        position: relative;
    }
    input {
        padding: 10px 25px;
        border: none;
        background-color: #fff;
        border-radius: 20px;
        box-shadow: 0 0 8px #0000001c;
        border-radius: 20px;
        width: 100%;
        font-family: inherit;
    }
    button {
        position: absolute;
        right: 3px;
        top: 50%;
        transform: translateY(-50%);
        padding: 0 25px;
        background-color: #000;
        color: #fff;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        height: calc(100% - 6px);
        font-family: inherit;
        z-index: 1;
    }
</style>
