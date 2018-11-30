<template>
    <b-card  header-bg-variant="info"
             header-text-variant="white">
        <div slot="header">
            <i class="fa fa-users mr-2"></i>My Caregivers
        </div>
        <div class="table-responsive">
            <b-table :items="caregivers"
                     :fields="fields"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
            >
                <template slot="actions" scope="row">
                    <b-btn @click="viewCaregiver(row.item)" class="btn btn-secondary"><i class="fa fa-eye mr-2"></i>View</b-btn>
                </template>
            </b-table>
        </div>

        <b-modal v-model="viewCaregiverModal" :title="selectedCaregiver.name" ok-variant="info" ok-only>
            <b-container fluid v-if="selectedCaregiver">
                <client-caregiver-details :caregiver="selectedCaregiver"
                                          :address="selectedCaregiver.address || {}"
                                          :phone="selectedCaregiver.phone_number ? selectedCaregiver.phone_number.number : ''"
                />
            </b-container>
        </b-modal>
    </b-card>
</template>

<script>
    import ClientCaregiverDetails from "./ClientCaregiverDetails";

    export default {
        name: "ClientCaregiverList",

        components: {ClientCaregiverDetails},

        props: {
            caregivers: {
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
                        sortable: true,
                    },
                    {
                        key: 'lastname',
                        sortable: true,
                    },
                    'actions'
                ],
                selectedCaregiver: {},
                viewCaregiverModal: false,
            }
        },

        methods: {
            viewCaregiver(caregiver) {
                this.selectedCaregiver = caregiver;
                this.viewCaregiverModal = true;
            }
        }
    }
</script>
