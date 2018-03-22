<template>
    <b-card header="Main Point of Contact"
            header-text-variant="white"
            header-bg-variant="info">
        <b-form-group label="Name">
            <b-form-input v-model="form.contact_name"></b-form-input>
        </b-form-group>
        <b-form-group label="Email">
            <b-form-input v-model="form.contact_email"></b-form-input>
        </b-form-group>
        <b-form-group label="Phone">
            <b-form-input v-model="form.contact_phone"></b-form-input>
        </b-form-group>
        <b-form-group>
            <b-btn @click="save" variant="info">Save</b-btn>
        </b-form-group>
    </b-card>
</template>

<style lang="scss">
</style>

<script>
    export default {
        props: ['business'],

        data() {
            return{
                form: new Form({
                    contact_name: this.business.contact_name,
                    contact_email: this.business.contact_email,
                    contact_phone: this.business.contact_phone
                })
            }
        },

        methods: {
            save() {
                this.form.put('/admin/businesses/'+this.business.id+'/contact-info');
            }
        }
    }
</script>