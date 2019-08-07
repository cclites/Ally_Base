<template>

    <b-row>

        <b-col>

            <b-card header="Client Account Setup Report"
                header-text-variant="white"
                header-bg-variant="info"
            >
                <div class="form-inline mb-3">

                    <business-location-form-group
                        v-model="form.businesses"
                        :label="null"
                        class="mr-2 mt-1"
                        :allow-all="true"
                    />

                    <b-form-group label="Account Status" label-for="status" class="mr-2">

                        <b-select name="status" id="status" v-model="form.status" class="ml-2">

                            <option value="active_no_payment">Active but No Payment</option>
                            <option value="scheduled_no_payment">Scheduled but No Payment</option>
                            <option value="inactive_no_payment">Inactive with No Payment</option>
                        </b-select>
                        <input-help :form="form" field="status" text=""></input-help>
                    </b-form-group>

                    <b-form-group label="Phone" label-for="phone" class="mr-2">

                        <b-select name="phone" id="phone" v-model="form.phone" class="ml-2">

                            <option value="">-- Filter by Number --</option>
                            <option value="has_mobile">Has Mobile</option>
                            <option value="any">Has Any</option>
                            <option value="none">Has None</option>
                        </b-select>
                        <input-help :form="form" field="phone" text=""></input-help>
                    </b-form-group>
                </div>
                <div class="d-flex mt-2 mb-2">

                    <div class="">

                        <b-button @click="fetch()" variant="info" :disabled="busy" class="mr-1 mt-1">

                            <i class="fa fa-circle-o-notch fa-spin mr-1" v-if="busy"></i>
                            Generate Report
                        </b-button>
                        <b-button @click="txtUsers()" variant="primary" :disabled="busy || totalRows == 0" class="mr-1 mt-1">

                            Send Txt To These Users
                        </b-button>
                    </div>
                    <div class="ml-auto">

                        <b-button @click="download()" variant="success" class="mr-1 mt-1">

                            <i class="fa fa-file-excel-o"></i> Export to Excel
                        </b-button>
                    </div>
                </div>

                <b-row>

                    <b-col>

                        <loading-card v-if="busy" />

                        <div v-else class="table-responsive">

                            <b-table bordered striped hover show-empty
                                :busy="busy"
                                :items="items"
                                :fields="fields"
                                :current-page="currentPage"
                                :per-page="perPage"
                                :sort-by.sync="sortBy"
                                :sort-desc.sync="sortDesc"
                                :empty-text="emptyText"
                            >
                                <template slot="name" scope="row">

                                    <a :href="`/business/clients/${row.item.id}`" target="_blank">{{ row.value }}</a>
                                </template>
                            </b-table>
                        </div>
                        <b-row>

                            <b-col lg="6">

                                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage"/>
                            </b-col>
                            <b-col lg="6" class="text-right">

                                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>

    import BusinessLocationFormGroup from '../../../components/business/BusinessLocationFormGroup';
    import FormatsNumbers from '../../../mixins/FormatsNumbers';
    import FormatsDates from '../../../mixins/FormatsDates';

    export default {

        components: { BusinessLocationFormGroup },
        mixins: [ FormatsNumbers, FormatsDates ],

        computed: {

            emptyText() {

                if ( ! this.hasRun ) {

                    return 'Press Generate Report';
                }
                return 'No matching records available.';
            }
        },

        data() {

            return {

                loading: false,
                clients: [],
                payers: [],
                form: new Form({
                    businesses: '',
                    role_type: 'client',
                    status: 'scheduled_no_payment',
                    phone: '',
                    json: 1,
                }),
                busy: false,
                totalRows: 0,
                perPage: 30,
                currentPage: 1,
                sortBy: 'name',
                sortDesc: false,
                fields: {
                    name: { sortable: true, label: "Client" },
                    email: { sortable: true },
                    mobile_phone: { sortable: true },
                    home_phone: { sortable: true },
                    setup_status: { sortable: true },
                },
                items: [],
                hasRun: false,
            }
        },

        methods: {

            fetch() {

                this.busy = true;
                this.form.get( '/business/reports/client-account-setup' )
                    .then( ({ data }) => {

                        this.items = data;
                        this.totalRows = this.items.length;
                    })
                    .catch( e => {} )
                    .finally( () => {

                        this.busy = false;
                        this.hasRun = true;
                    })
            },

            download() {

                window.location = this.form.toQueryString( '/business/reports/client-account-setup?export=1' );
            },

            txtUsers() {

                let ids = _.map( this.items, 'id' );

                let form = new Form( {ids} );
                form.put(`/business/communication/text-clients`) // CHECK
                    .then( response => {

                        console.log( 'response: ', response );
                    })
                    .catch( e => {

                        console.log( 'error: ', e );
                    });
            },
        },
        async mounted() {

            this.loading = true;
            this.loading = false;
        },
    }
</script>
