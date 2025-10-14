<script lang="ts">
    export let children: string;
    import { Link } from "@hyvor/design/components";
    
</script>

<a>
    <Link href="https://hyvor.com/signup" target="_blank" rel="noreferrer">{children}</Link>
</a>