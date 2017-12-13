<template>
    <b-card header-bg-variant="info"
            header-text-variant="white">
        <div slot="header">
            <b-row align-h="between">
                <b-col>Emergency Contacts</b-col>
                <b-col>
                    <b-btn @click="addingNew = true" class="pull-right" :disabled="addingNew">New Contact</b-btn>
                </b-col>
            </b-row>
        </div>
        <b-row v-if="addingNew">
            <b-col>
                <b-form-group label="Name">
                    <b-form-input v-model="form.name"></b-form-input>
                </b-form-group>
                <b-form-group label="Phone Number">
                    <b-form-input v-model="form.phone_number"></b-form-input>
                </b-form-group>
                <b-form-group label="Relationship">
                    <b-form-input v-model="form.relationship"></b-form-input>
                </b-form-group>
                <b-form-group>
                    <b-btn variant="info" @click="save">Save</b-btn>
                    <b-btn @click="cancel">Cancel</b-btn>
                </b-form-group>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    export default {
        props: ['contacts'],
        
        data() {
            return{
                addingNew: false,
                form: new Form()
            }
        },
        
        methods: {
            save() {
                console.log('saving...');
            },

            cancel() {
                this.addingNew = false;
                this.form = new Form();
            }

        }
    }
</script>