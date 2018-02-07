<template>
    <b-card>
        <b-row class="mb-2">
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
                <template slot="created_at" scope="data">
                    {{ data.value | date }}
                </template>
                <template slot="actions" scope="row">
                    <span v-if="row.item.role_type == 'caregiver' || row.item.role_type == 'client'">
                         <b-btn size="sm" @click="addHold(row.item)" variant="danger" v-if="!row.item.payment_hold">Add Hold</b-btn>
                        <b-btn size="sm" @click="removeHold(row.item)" variant="primary" v-else>Remove Hold</b-btn>
                    </span>
                    <b-btn size="sm" :href="'/admin/impersonate/' + row.item.id">Impersonate</b-btn>
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
</template>>

<script>
    export default {
        props: {},

        data() {
            return {
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                detailsModal: false,
                selectedItem: {
                    client: {}
                },
                fields: [
                    {
                        key: 'id',
                        label: 'ID',
                        sortable: true,
                    },
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
                        key: 'username',
                        label: 'Username',
                        sortable: true,
                    },
                    {
                        key: 'email',
                        label: 'Email',
                        sortable: true,
                    },
                    {
                        key: 'registry',
                        label: 'Registry',
                        sortable: true,
                    },
                    {
                        key: 'role_type',
                        label: 'Type',
                        sortable: true,
                    },
                    {
                        key: 'created_at',
                        label: 'Date Created',
                        sortable: true,
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
                items: [],
            }
        },

        mounted() {
            this.totalRows = this.items.length;
            this.loadItems();
        },

        computed: {

        },

        methods: {
            loadItems() {
                axios.get('/admin/users')
                    .then(response => {
                        this.items = response.data;
                    });
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            },
            addHold(user) {
                let form = new Form();
                form.submit('post', '/admin/users/' + user.id + '/hold')
                    .then(response => {
                        user.payment_hold = true;
                    });
            },
            removeHold(user) {
                let form = new Form();
                form.submit('delete', '/admin/users/' + user.id + '/hold')
                    .then(response => {
                        user.payment_hold = false;
                    });
            },
        }
    }
</script>
