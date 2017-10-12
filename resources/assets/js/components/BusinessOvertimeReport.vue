<template>
    <b-card>
        <b-row>
            <b-col lg="6">
            </b-col>
            <b-col lg="6" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <div class="table-responsive">
            <b-table bordered striped hover show-empty
                     :items="items"
                     :fields="fields"
                     :current-page="currentPage"
                     :per-page="perPage"
                     :filter="filter"
                     :sort-by.sync="sortBy"
                     :sort-desc.sync="sortDesc"
                     @filtered="onFiltered"
            >
                <template scope="total">

                </template>
            </b-table>
        </div>

        <b-row>
            <b-col lg="6" >
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    import Form from "../classes/Form";

    export default {
        props: {
            'caregivers': {
                default() {
                    return [];
                }
            },
        },

        data() {
            return {
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                selectedItem: {},
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
                    {
                        key: 'worked',
                        label: 'Worked Hours',
                        sortable: true,
                    },
                    {
                        key: 'scheduled',
                        label: 'Scheduled Hours',
                        sortable: true,
                    },
                    {
                        key: 'total',
                        label: 'Expected Total Hours',
                        sortable: true,
                    },
                ],
                items: this.caregivers.map(function(caregiver) {
                        return {
                            _rowVariant: (caregiver.total > 38) ? (caregiver.total > 40 ? 'danger' : 'warning') : '',
                            id: caregiver.user.id,
                            firstname: caregiver.user.firstname,
                            lastname: caregiver.user.lastname,
                            worked: caregiver.worked,
                            scheduled: caregiver.scheduled,
                            total: caregiver.total,
                        }
                    }),

            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {

        },

        methods: {
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            }
        }
    }
</script>
