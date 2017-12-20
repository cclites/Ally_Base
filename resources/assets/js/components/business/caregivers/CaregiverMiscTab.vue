<template>
    <b-row>
        <b-col>
            <b-card header="Miscellaneous"
                    header-bg-variant="info"
                    header-text-variant="white">
                <b-form-group>
                    <b-form-textarea v-model="form.misc" rows="3"></b-form-textarea>
                </b-form-group>
                <b-form-group>
                    <b-btn @click="updateCaregiver">Save</b-btn>
                </b-form-group>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        props: ['misc', 'caregiver'],

        data() {
            return{
                form: new Form({
                    misc: this.misc
                })
            }
        },

        methods: {
            updateCaregiver() {
                this.form.put('/business/caregivers/' + this.caregiver.id + '/misc');
            }
        }

    }
</script>