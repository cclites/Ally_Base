<template>
    <b-card
        header="System Notifications"
        header-text-variant="white"
        header-bg-variant="info"
    >
        <div class="d-flex mb-2">
            <div class="f-1">
                <b-form-checkbox v-model="show_acknowledged" unchecked-value="0" value="1">
                    Show Acknowledged Notifications
                </b-form-checkbox>
            </div>
            <div class="ml-auto">
                <b-button variant="primary" @click="acknowledgeAll()">Acknowledge All My Notifications</b-button>
            </div>
        </div>
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
                <template slot="actions" scope="row">
                    <b-btn size="sm" :href="'/business/notifications/' + row.item.id">Details</b-btn>
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
    import FormatsDates from "../mixins/FormatsDates";

    export default {
        mixins: [FormatsDates],

        data() {
            return {
                loading: false,
                show_acknowledged: 0,
                totalRows: 0,
                perPage: 25,
                currentPage: 1,
                sortBy: 'created_at',
                sortDesc: true,
                selectedItem: {},
                fields: [
                    {
                        key: 'created_at',
                        label: 'Date',
                        sortable: true,
                        formatter: (val) => this.formatDateTimeFromUTC(val),
                    },
                    {
                        key: 'title',
                        label: "Type",
                        sortable: true,
                    },
                    {
                        key: 'message',
                        label: 'Description',
                        sortable: true,
                    },
                    {
                        key: 'acknowledged_at',
                        label: 'Acknowledged',
                        sortable: false,
                        formatter: (val) => val ? this.formatDateTimeFromUTC(val) : '-',
                    },
                    {
                        key: 'actions',
                        class: 'hidden-print'
                    }
                ],
            }
        },

        mounted() {
            this.loadTable();
        },

        render() {
            console.log('render');
        },

        computed: {
            listUrl() {
                return `/business/notifications?json=1&acknowledged=${this.show_acknowledged}`;
            },
        },

        methods: {
            loadTable() {
                this.$refs.table.refresh();
            },

            itemProvider(ctx) {
                this.loading = true;

                let sort = ctx.sortBy == null ? 'created_at' : ctx.sortBy;
                let desc = ctx.sortDesc == true ? '1' : '0';
                return axios.get(this.listUrl + `&page=${ctx.currentPage}&per_page=${ctx.perPage}&sort=${sort}&desc=${desc}`)
                    .then( ({ data }) => {
                        this.totalRows = data.total;
                        return data.results || [];
                    })
                    .catch(() => {
                        return [];
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            acknowledgeAll() {
                let form = new Form({});
                form.post(`notifications/acknowledge-all`)
                    .then(() => {
                        this.loadTable();
                    })
                    .catch(() => {});
            },
        },

        watch: {
            show_acknowledged(newValue, oldValue) {
                this.loadTable();
            }
        },
    }
</script>