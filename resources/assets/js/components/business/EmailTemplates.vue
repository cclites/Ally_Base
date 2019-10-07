<template>
    <b-card header="Custom Email Templates"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-select v-model="selectedType">
            <option v-for="type in types" :key="type.id">{{ type.name }}</option>
        </b-select>
    </b-card>
</template>

<script>
    export default {
        name: "EmailTemplates",
        props: {
            templates: [],
            types: [],
        },
        data() {
            return {
                selectedType: "",
            };
        },
    }
</script>

<style scoped>

</style>