<template>
    <b-card>
        <b-row>
            <b-col lg="6">
                <b-btn variant="info" href="/admin/businesses/create">Add a New Provider</b-btn>
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
                <template slot="actions" scope="row">
                    <b-btn size="sm" @click="addHold(row.item)" variant="danger" v-if="!row.item.payment_hold">Add Hold</b-btn>
                    <b-btn size="sm" @click="removeHold(row.item)" variant="primary" v-else>Remove Hold</b-btn>
                    <b-btn size="sm" :href="'/admin/businesses/' + row.item.id">Edit</b-btn>
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
    export default {
        props: {
            'businesses': {},
        },

        data() {
            return {
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                fields: [
                    {
                        key: 'name',
                        label: 'Provider Name',
                        sortable: true,
                    },
                    {
                        key: 'city',
                        label: 'City',
                        sortable: true,
                    },
                    {
                        key: 'state',
                        label: 'State',
                        sortable: true,
                    },
                    {
                        key: 'phone1',
                        label: 'Phone',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
                items: this.businesses,
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
            },
            addHold(business) {
                let form = new Form();
                form.submit('post', '/admin/businesses/' + business.id + '/hold')
                    .then(response => {
                        business.payment_hold = true;
                    });
            },
            removeHold(business) {
                let form = new Form();
                form.submit('delete', '/admin/businesses/' + business.id + '/hold')
                    .then(response => {
                        business.payment_hold = false;
                    });
            },
        }
    }
</script>