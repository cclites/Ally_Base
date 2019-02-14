<template>
    <div>
        <b-card title="Emergency Contacts"
            header-bg-variant="info"
            header-text-variant="white"
        >
        </b-card>
        <b-card title="Contacts"
            header-bg-variant="info"
            header-text-variant="white"
        >
            <div class="table-responsive">
                <b-table :items="contacts"
                    sort-by="priority"
                    :fields="fields"
                >
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
    </div>
</template>

<script>
    import AuthUser from '../mixins/AuthUser';

    export default {
        mixins: [ AuthUser ],

        props: ['contacts', 'client'],
        
        data() {
            return {
                items: this.contacts,
                fields: [
                    'name',
                    'email',
                    'actions'
                ],
            }
        },
        
        methods: {
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
