<script lang="ts">
    const lists = [
        { id: 1, name: "PHP", description: "Get the latest PHP news" },
        {
            id: 2,
            name: "Typescript",
            description: "Get the latest Typescript news",
        },
    ];
</script>

<div class="form">
    <div class="title">Subscribe for updates</div>

    <div class="lists">
        {#each lists as list}
            <label class="list">
                <div class="name-description">
                    <div class="name">{list.name}</div>
                    <div class="description">{list.description}</div>
                </div>
                <div class="checkbox">
                    <input type="checkbox" />
                </div>
            </label>
        {/each}
    </div>

    <div class="input">
        <input type="text" placeholder="Your Email" class="email-input" />
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
    .email-input {
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

    .lists {
        margin-bottom: 1.2rem;
    }

    .list {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8rem;
        cursor: pointer;
    }

    .name {
        font-size: 16px;
        font-weight: 600;
    }

    .description {
        font-size: 14px;
        color: #666;
    }
</style>
