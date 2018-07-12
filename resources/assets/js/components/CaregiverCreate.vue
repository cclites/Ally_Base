<template>
    <b-card header="New Caregiver"
            header-bg-variant="info"
            header-text-variant="white"
    >
        <form @submit.prevent="saveProfile()" @keydown="form.clearError($event.target.name)">
            <caregiver-create-form v-model="form"></caregiver-create-form>
            <b-row>
                <b-col lg="12">
                    <submit-button variant="success"
                                   type="submit"
                                   :submitting="submitting"
                    >
                        Create &amp; Continue
                    </submit-button>
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
    import CaregiverCreateForm from "./forms/CaregiverCreateForm";

    export default {

        components: {CaregiverCreateForm},

        props: {},

        data() {
            return {
                form: new Form(),
                duplicateWarning: null,
                submitting: false,
            }
        },

        computed: {
            duplicateModal() { return !!this.duplicateWarning },
        },

        methods: {

            async saveProfile() {
                this.submitting = true;
                try {
                    const response = await this.form.post('/business/caregivers');
                    if (response.data.data.url) {
                        window.location.href = response.data.data.url;
                    }
                }
                catch(error) {
                    this.submitting = false;
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

        }
    }
</script>
