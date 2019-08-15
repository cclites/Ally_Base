<template>
    <b-card>
        <div class="table-responsive">
            <b-table :items="clients"
                     :fields="fields"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
            >
                <template slot="actions" scope="row">
                    <b-btn @click="viewClient(row.item)" class="btn btn-secondary">View Details</b-btn>
                    <b-btn :href="`/caregiver/clients/${row.item.id}/narrative`" class="btn btn-secondary">View/Edit Narrative</b-btn>
                </template>
            </b-table>
        </div>

        <b-modal v-model="viewClientModal" :title="selectedClient.name" size="lg">
            <b-container fluid v-if="selectedClient">
                <caregiver-client-details :client="selectedClient"
                                          :address="selectedClient.evv_address || {}"
                                          :phone="selectedClient.evv_phone ? selectedClient.evv_phone.number : ''"
                                          :care-details="selectedClient.care_details || {}"
                />
            </b-container>
        </b-modal>
    </b-card>
</template>

<script>
    import CaregiverClientDetails from "./CaregiverClientDetails";

    export default {
        name: "CaregiverClientList",

        components: {CaregiverClientDetails},

        props: {
            clients: {
                required: true,
                type: Array,
            },
        },

        data() {
            return {
                sortBy: 'lastname',
                sortDesc: false,
                fields: [
                    {
                        key: 'firstname',
                        label: 'First Name',
                        sortable: true,
                    },
                    {
                        key: 'lastname',
                        label: 'Last Name',
                        sortable: true,
                    },
                    'actions'
                ],
                selectedClient: {},
                viewClientModal: false,
            }
        },

        methods: {
            viewClient(client) {
                this.selectedClient = client;
                this.viewClientModal = true;
            }
        }
    }
</script>

<style scoped>

</style>