<template>
    <b-card header="Custom Email Templates"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-select v-model="selectedType">
            <option v-for="type in types" :key="type.id">{{ type.name }}</option>
        </b-select>

        <b-row class="pane caregiver_expirations">
            <div>
                <b-input v-model="cg_expire.greeting"></b-input>
            </div>
        </b-row>

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
                cg_expire: {
                    greeting: "",
                }
            };
        },
        watch: {
            selectedType(){

            },
        }
    }
</script>

<style scoped>
    .pane{
        display: none;
    }
</style>