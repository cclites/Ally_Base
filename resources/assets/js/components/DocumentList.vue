<template>
    <table class="table">
        <thead><tr><th>File</th></tr></thead>
        <tbody>
            <tr v-for="document in documents">
                <td>{{ document.original_filename }}</td>
            </tr>
        </tbody>
    </table>
</template>
<script>
    export default {
        props: ['initialDocuments'],
        data() {
            return {
                documents: this.initialDocuments,
            };
        },
    }
</script>
