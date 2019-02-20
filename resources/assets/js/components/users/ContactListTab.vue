<template>
    <b-card header-bg-variant="info"
            header-text-variant="white">
        <div slot="header">
            <b-row align-h="between">
                <b-col>Emergency Contacts</b-col>
                <b-col>
                    <b-btn @click="addingNew = true" class="pull-right" :disabled="addingNew || authInactive">New Contact</b-btn>
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
        <div class="table-responsive">
            <b-table :items="contacts"
                     sort-by="priority"
                     :fields="fields">
                <template slot="actions" scope="data">
                    <b-btn v-if="data.item.priority > 1" variant="secondary" @click="raisePriority(data.item)" :disabled="authInactive">
                        <i class="fa fa-chevron-up"></i>
                    </b-btn>
                    <b-btn variant="danger" title="Delete" @click="destroy(data.item.id)" :disabled="authInactive">
                        <i class="fa fa-times"></i>
                    </b-btn>
                </template>
            </b-table>
        </div>
    </b-card>
</template>

<script>
    import AuthUser from '../../mixins/AuthUser';

    export default {
        mixins: [ AuthUser ],

        props: ['emergencyContacts', 'userId'],
        
        data() {
            return{
                addingNew: false,
                form: new Form({
                    name: '',
                    phone_number: '',
                    relationship: ''
                }),
                contacts: this.emergencyContacts,
                fields: [
                    'priority',
                    'name',
                    'phone_number',
                    'relationship',
                    'actions'
                ]
            }
        },
        
        methods: {
            save() {
                this.form.post('/emergency-contacts/' + this.userId)
                    .then(response => {
                        this.contacts.push(response.data);
                        alerts.addMessage('success', 'Emergency Contact Added');
                        this.cancel();
                    });
            },

            cancel() {
                this.addingNew = false;
                this.form = new Form({
                    name: '',
                    phone_number: '',
                    relationship: ''
                });
            },

            destroy(id) {
                axios.delete('/emergency-contacts/'+id)
                    .then(response => {
                        alerts.addMessage('success', 'Emergency Contact Removed');
                        this.contacts = response.data;
                    }).catch(error => {
                        console.error(error.response);
                    });
            },

            raisePriority(contact) {
                let priority = contact.priority - 1;
                axios.patch(`/emergency-contacts/${this.userId}/${contact.id}`, { priority })
                    .then(response => {
                        alerts.addMessage('success', 'Emergency Contact Priority Updated');
                        this.contacts = response.data;
                    }).catch(error => {
                        console.error(error.response);
                    });
            },
        }
    }
</script>
