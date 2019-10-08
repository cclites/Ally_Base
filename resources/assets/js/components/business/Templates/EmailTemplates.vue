<template>
    <b-card header="Custom Email Templates"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-select v-model="selectedType">
            <option v-for="type in types" :key="type.id">{{ type.name }}</option>
        </b-select>

        <b-row class="pane caregiver_expirations mt-3">
            <b-col>
                <caregiver-expiration-notice :template="caregiver_expiration"></caregiver-expiration-notice>
            </b-col>
        </b-row>

    </b-card>
</template>

<script>

    export default {
        name: "EmailTemplates",
        props: {
            types: '',
            templates: '',
        },
        data() {
            return {
                selectedType: "",
                caregiver_expiration: {},
            };
        },
        mounted(){
            this.caregiver_expiration = this.templates.filter( x => x.type === 'caregiver_expiration');
        },
        watch: {
        }
    }
</script>

<style scoped>
    .pane{
       /*display: none;*/
    }
</style>