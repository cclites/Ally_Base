<template>
    <div>
        <b-card header="Emergency Contacts"
            header-text-variant="white"
            header-bg-variant="info"
            class="pb-3"
        >
            <div class="d-flex mb-3">
                <b-btn variant="info" @click="add(true)" class="ml-auto" :disabled="busy || authInactive">Add Emergency Contact</b-btn>
            </div>

            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                    :items="emergencyContacts"
                    :fields="emergencyFields"
                    sortBy="emergency_priority"
                    empty-text="No emergency contacts available."
                    class="table-fit-more"
                >
                    <template slot="relationship" scope="data">
                        {{ formatRelationship(data.item) }}
                    </template>
                    <template slot="address" scope="data">
                        {{ formatAddress(data.item) }}
                    </template>
                    <template slot="actions" scope="data">
                        <b-btn v-if="data.item.emergency_priority > 1"
                            title="Increase Priority"
                            variant="primary" size="sm"
                            @click="raisePriority(data.item)" 
                            :disabled="busy || authInactive"
                        >
                            <i class="fa fa-chevron-up"></i>
                        </b-btn>
                        <b-btn variant="secondary" title="Edit" @click="edit(data.item)" size="sm" :disabled="busy || authInactive">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                        <b-btn variant="danger" title="Remove" @click="destroyEmergency(data.item)" size="sm" :disabled="busy || authInactive">
                            <i class="fa fa-times"></i>
                        </b-btn>
                    </template>
                </b-table>
            </div>
        </b-card>
        <b-card header="Contacts"
            header-text-variant="white"
            header-bg-variant="info"
            class="pb-3"
        >
            <div class="d-flex mb-3">
                <b-btn variant="info" @click="add()" class="ml-auto" :disabled="busy || authInactive">Add Contact</b-btn>
            </div>

            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                    :items="otherContacts"
                    :fields="fields"
                    :sort-by.sync="sortBy"
                    :sort-desc.sync="sortDesc"
                    empty-text="No contacts available."
                >
                    <template slot="relationship" scope="data">
                        {{ formatRelationship(data.item) }}
                    </template>
                    <template slot="address" scope="data">
                        {{ formatAddress(data.item) }}
                    </template>
                    <template slot="actions" scope="data">
                        <b-btn variant="primary" title="Move to emergency contacts" @click="moveToEmergency(data.item)" size="sm" :disabled="busy || authInactive">
                            <i class="fa fa-medkit"></i>
                        </b-btn>
                        <b-btn variant="secondary" title="Edit" @click="edit(data.item)" size="sm" :disabled="busy || authInactive">
                            <i class="fa fa-edit"></i>
                        </b-btn>
                        <b-btn variant="danger" title="Delete" @click="destroy(data.item)" size="sm" :disabled="busy || authInactive">
                            <i class="fa fa-times"></i>
                        </b-btn>
                    </template>
                </b-table>
            </div>

            <client-contacts-modal
                :source="currentContact" 
                :client="client"
                v-model="contactModal"
                @updated="setItems"
                @created="setItems"
            ></client-contacts-modal>
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
                items: [],
                sortBy: 'name',
                sortDesc: false,
                currentContact: {},
                contactModal: false,
                busy: false,
            }
        },
        
        computed: {
            emergencyContacts() {
                return this.items.filter(x => x.is_emergency == 1); 
            },

            otherContacts() {
                return this.items.filter(x => x.is_emergency == 0); 
            },

            isClient() {
                return this.authUser.id === this.client.id;
            },

            fields() {
                let fields = [
                    { key: 'name', sortable: true },
                    { key: 'relationship', sortable: true },
                    { key: 'phone1', label: 'Phone Number 1', sortable: true },
                    { key: 'phone2', label: 'Phone Number 2', sortable: true },
                    { key: 'email', label: 'Email Address', sortable: true },
                    { key: 'address', label: 'Address', sortable: true },
                    { key: 'actions', sortable: false },
                ];

                if (this.isClient) {
                    fields = fields.filter(x => ['name', 'relationship', 'actions'].includes(x.key) );
                }
                return fields;
            },

            emergencyFields() {
                let fields = [
                    { key: 'name', sortable: false },
                    { key: 'relationship', sortable: false },
                    { key: 'phone1', label: 'Phone Number 1', sortable: false },
                    { key: 'phone2', label: 'Phone Number 2', sortable: false },
                    { key: 'email', label: 'Email Address', sortable: false },
                    { key: 'address', label: 'Address', sortable: false },
                    { key: 'actions', sortable: false },
                ];

                if (this.isClient) {
                    fields = fields.filter(x => ['name', 'relationship', 'actions'].includes(x.key) );
                }
                return fields;
            },
        },

        methods: {
            add(emergency = false) {
                this.currentContact = { is_emergency: emergency };
                this.contactModal = true;
            },

            edit(item) {
                this.currentContact = item;
                this.contactModal = true;  
            },

            destroy(item) {
                if (! confirm('Are you sure you wish to remove this contact?  This cannot be undone.')) {
                    return;
                }

                this.busy = true;
                let form = new Form({});
                form.submit('DELETE', this.itemUrl(item))
                    .then( ({ data }) => {
                        this.setItems(data.data);
                    })
                    .catch(e => {})
                    .finally(() => { this.busy = false; });
            },

            setItems(items) {
                this.items = items;
            },

            raisePriority(item) {
                this.busy = true;
                let form = new Form({ priority: item.emergency_priority - 1 });
                form.patch(this.itemUrl(item) + '/priority')
                    .then( ({ data }) => {
                        this.setItems(data.data);
                    })
                    .catch(e => {})
                    .finally(() => { this.busy = false; });
            },

            moveToEmergency(item) {
                if (! confirm('Move this contact to the Emergency Contact list?')) {
                    return;
                }

                this.updateEmergencyValue(item, 1);
            },

            destroyEmergency(item) {
                if (! confirm('Remove this contact from the Emergency Contact list?')) {
                    return;
                }

                this.updateEmergencyValue(item, 0);
            },

            updateEmergencyValue(item, value) {
                this.busy = true;
                let form = new Form(item);
                form.is_emergency = value;
                form.submit('patch', this.itemUrl(item))
                    .then( ({ data }) => {
                        this.setItems(data.data);
                    })
                    .catch(e => {
                    })
                    .finally(() => this.busy = false)
            },

            formatAddress(item) {
                let address = item.address;
                if (item.city) {
                    address += ', ' + item.city;
                }
                if (item.state) {
                    address += ', ' + item.state;
                }
                if (item.zip) {
                    address += ' ' + item.zip;
                }
                return address;
            },

            formatRelationship(item) {
                switch (item.relationship) {
                    case 'family': return 'Family';
                    case 'poa': return 'Power of Attorney';
                    case 'physician': return 'Physician';
                    case 'other': return 'Other';
                    case 'custom':
                        return item.relationship_custom ? 'Custom: ' + item.relationship_custom : '-';
                        break;
                }
                return '-';
            },

            itemUrl(item) {
                let url = `/business/clients/${this.client.id}/contacts/${item.id}`
                if (this.isClient) {
                    url = `/contacts/${item.id}`;
                }
                return url;
            },
        },

        mounted() {
            this.setItems(this.contacts);
        },
    }
</script>
