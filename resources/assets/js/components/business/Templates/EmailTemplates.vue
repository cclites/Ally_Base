<template>
    <b-card header="Custom Email Templates"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row>
            <b-col lg="2">
                <business-location-form-group v-model="business_id"
                                              field="business_id"
                                              help-text="Select the office location for this client" />
            </b-col>
            <b-col lg="4">
                <b-form-group label="Select a template to customize">
                    <b-select v-model="selectedType">
                        <option value="">Select a Template</option>
                        <option v-for="type in types" :key="type.id">{{ type.name }}</option>
                    </b-select>
                </b-form-group>
            </b-col>
        </b-row>

        <!-- Templates -->
        <b-row v-if="selectedType === 'Caregiver Expiration'" class="pane mt-3">
            <b-col>
                <caregiver-expiration-notice :template="caregiver_expiration" :business_id="business_id"></caregiver-expiration-notice>
            </b-col>
        </b-row>

    </b-card>
</template>

<script>

    import BusinessLocationSelect from '../../business/BusinessLocationSelect';
    import BusinessLocationFormGroup from "../../business/BusinessLocationFormGroup";

    export default {
        name: "EmailTemplates",
        components: {
            BusinessLocationFormGroup,
            BusinessLocationSelect,
        },
        props: {
            types: '',
        },
        data() {
            return {
                selectedType: "",
                caregiver_expiration: {},
                business_id: '',
                templates: [],
            };
        },
        mounted(){
            axios.get('/business/communication/templates?json=1&business_id=' + this.business_id).then(response => {
                this.templates = response.data;
                this.caregiver_expiration = this.templates.filter( x => x.type === 'caregiver_expiration');
            }).finally();


        },
        watch: {
        }
    }
</script>

<style scoped>
</style>