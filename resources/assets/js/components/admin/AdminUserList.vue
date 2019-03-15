<template>
    <b-card header="User List"
        header-bg-variant="info"
        header-text-variant="white"
    >
        <b-row class="mb-2">
            <b-col lg="6">
            </b-col>
            <b-col lg="6" class="text-right">
                <b-form-input v-model="filter" placeholder="Type to Search" />
            </b-col>
        </b-row>

<!--        <loading-card v-if="loading" text="Loading users..."></loading-card>-->
        <div>
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                    :items="items"
                    :fields="fields"
                    :current-page="currentPage"
                    :per-page="perPage"
                    :filter="filter"
                    sort-by="firstname"
                    :sort-desc="false"
                    @filtered="onFiltered"
                    :busy="loading"
                >
                    <template slot="chain_name" scope="row">
                        <span v-if="! row.item.chain_id">-</span>
                        <span v-else>
                            {{ row.item.chain_name }}
                            <b-btn variant="secondary" size="sm" class="ml-1" :href="`/admin/chains/${row.item.chain_id}`"><i class="fa fa-arrow-right"></i></b-btn>
                        </span>
                    </template>
                    <template slot="actions" scope="row">
                        <span v-if="['caregiver', 'client'].includes(row.item.role_type)">
                            <b-btn size="sm" @click="addHold(row.item)" variant="danger" v-if="!row.item.payment_hold">Add Hold</b-btn>
                            <b-btn size="sm" @click="removeHold(row.item)" variant="primary" v-else>Remove Hold</b-btn>
                            <b-btn size="sm" :href="`/business/${row.item.role_type}s/${row.item.id}`">Edit</b-btn>
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
        </div>
    </b-card>
</template>>

<script>
    import FormatsDates from '../../mixins/FormatsDates';
    export default {
        mixins: [ FormatsDates ],

        props: {},

        data() {
            return {
                loading: false,
                items: [],
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                filter: null,
                detailsModal: false,
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
                        key: 'chain_name',
                        label: 'Registry',
                        sortable: true,
                    },
                    {
                        key: 'role_type',
                        label: 'Type',
                        sortable: true,
                        formatter: x => _.startCase(x),
                    },
                    {
                        key: 'created_at',
                        label: 'Date Created',
                        sortable: true,
                        formatter: x => this.formatDateFromUTC(x),
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
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
                this.loading = true;
                axios.get('/admin/users?json=1')
                    .then(response => {
                        this.items = response.data;
                    })
                    .catch(e => {})
                    .finally(() => {
                        this.loading = false;
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
