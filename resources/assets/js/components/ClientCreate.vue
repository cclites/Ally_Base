<template>
    <b-card header="New Client"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="saveProfile()" @keydown="form.clearError($event.target.name)">
            <client-create-form v-model="form"></client-create-form>
            <b-row>
                <b-col lg="12">
                    <b-button id="save-profile" variant="success" type="submit">Create &amp; Continue</b-button>
                </b-col>
            </b-row>
        </form>
        <b-modal id="duplicateWarning" title="Potential Duplicate Found" v-model="duplicateModal">
            <b-container fluid>
                <h4>{{ duplicateWarning }}  Do you want to continue anyways?</h4>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="duplicateWarning=null">No, Cancel</b-btn>
                <b-btn variant="success" @click="saveProfileWithOverride()">Yes, Continue</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import ClientCreateForm from "./forms/ClientCreateForm";

    export default {
        components: {ClientCreateForm},
        props: {},

        data() {
            return {
                form: new Form(),
                duplicateWarning: null,
            }
        },

        computed: {
            duplicateModal() { return !!this.duplicateWarning },
        },

        mounted() {
        },

        methods: {

            async saveProfile() {
                try {
                    await this.form.post('/business/clients');
                }
                catch(error) {
                    switch(error.response.status) {
                        case 449:
                            this.duplicateWarning = error.response.data.message;
                            break;
                    }
                }
            },

            saveProfileWithOverride() {
                this.duplicateWarning = null;
                this.form.override = true;
                return this.saveProfile();
            }

        },

    }
</script>
