<template>
    <b-card header="User List"
        header-bg-variant="info"
        header-text-variant="white"
    >
        <b-row class="mb-2">
            <b-col lg="6">
                <b-select v-model="chainFilter" :disabled="loading">
                    <option value="">-- All Business Chains --</option>
                    <option v-for="chain in chains" :key="chain.id" :value="chain.id">{{ chain.name }}</option>
                </b-select>
            </b-col>
            <b-col lg="6" class="text-right">
                <b-form-input v-model="search" placeholder="Type to Search" />
            </b-col>
        </b-row>

        <div>
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                    :items="itemProvider"
                    :fields="fields"
                    :current-page="currentPage"
                    :per-page="perPage"
                    :sort-by.sync="sortBy"
                    :sort-desc.sync="sortDesc"
                    :busy="loading"
                    ref="table"
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
                chains: [],
                chainFilter: '',
                sortBy: 'lastname',
                sortDesc: false,
                loading: false,
                totalRows: 0,
                perPage: 20,
                currentPage: 1,
                search: '',
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
                        sortable: false,
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
                        class: 'hidden-print',
                        sortable: false,
                    }
                ],
            }
        },

        async mounted() {
            await this.fetchChains();
            this.loadTable();
        },

        computed: {
        },

        methods: {
            loadTable() {
                this.$refs.table.refresh()
            },

            async fetchChains() {
                await axios.get(`/admin/chains`)
                    .then( ({ data }) => {
                        this.chains = data;
                    })
                    .catch(e => {});
            },

            itemProvider(ctx) {

                //This will be triggered if the Actions header is clicked.
                if( ctx.sortBy === null && typeof ctx.sortBy === "object"){
                    return 0;
                }

                this.loading = true;
                return axios.get(`/admin/users?json=1&page=${ctx.currentPage}&perpage=${ctx.perPage}&sort=${ctx.sortBy}&desc=${ctx.sortDesc}&chain=${this.chainFilter}&search=${this.search}`)
                    .then( ({ data }) => {
                        this.totalRows = data.total;
                        return data.results || [];
                    })
                    .catch(e => {
                        return [];
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            addHold(user) {
                let form = new Form();
                form.submit('post', '/admin/users/' + user.id + '/hold')
                    .then(response => {
                        user.payment_hold = true;
                    })
                    .catch(e => {});
            },

            removeHold(user) {
                let form = new Form();
                form.submit('delete', '/admin/users/' + user.id + '/hold')
                    .then(response => {
                        user.payment_hold = false;
                    })
                    .catch(e => {});
            },
        },

        watch: {
            chainFilter(newValue, oldValue) {
                if (newValue != oldValue) {
                    this.$refs.table.refresh();
                }
            },

            search(newValue, oldValue) {
                // debounce the reloading of the table to prevent
                // unnecessary calls.
                _.debounce(() => {
                    this.loadTable();
                }, 350)();
            },
        },
    }
</script>
