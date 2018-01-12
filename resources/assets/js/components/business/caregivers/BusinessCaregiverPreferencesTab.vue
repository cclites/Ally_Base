<template>
    <b-row>
        <b-col>
            <b-card header="Preferences"
                    header-bg-variant="info"
                    header-text-variant="white">
                <b-form-group>
                    <b-form-textarea v-model="form.preferences" rows="3"></b-form-textarea>
                </b-form-group>
                <b-form-group>
                    <b-btn @click="updatePreferences">Save</b-btn>
                </b-form-group>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        props: ['caregiver'],

        data() {
            return{
                form: new Form({
                    preferences: this.caregiver.preferences
                })
            }
        },

        methods: {
            updatePreferences() {
                this.form.put('/business/caregivers/' + this.caregiver.id + '/preferences');
            }
        }
    }
</script>