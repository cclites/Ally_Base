<template>
    <div>
        <b-card v-if="!selectedImport.id">
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                         :fields="importFields"
                         :items="imports"
                >
                    <template slot="actions" scope="row">
                        <b-btn variant="info" size="sm" @click="selectedImport = row.item">View Shifts</b-btn>
                    </template>
                </b-table>
            </div>
        </b-card>
        <b-card
                header="Related Shifts"
                header-text-variant="white"
                header-bg-variant="info"
                v-if="selectedImport.id"
        >
            <div class="pull-right">
                <b-btn variant="primary" @click="selectedImport = {}">Return to Import List</b-btn>
            </div>
            <div class="table-responsive">
                <b-table bordered striped hover show-empty
                         :fields="shiftFields"
                         :items="shifts"
                         :sort-by.sync="sortBy"
                         :sort-desc.sync="sortDesc"
                         class="shift-table"
                >
                    <template slot="checked_in_time" scope="data">
                        {{ formatDate(data.value) }} {{ formatTime(data.value) }}
                    </template>
                    <template slot="actions" scope="row">

                    </template>
                </b-table>
            </div>
        </b-card>
    </div>
</template>

<script>
    import FormatsDates from '../../../mixins/FormatsDates'
    import FormatsNumbers from '../../../mixins/FormatsNumbers'

    export default {
        mixins: [
            FormatsDates,
            FormatsNumbers
        ],

        components: {ShiftHistoryTable},

        props: {

        },

        data() {
            return {
                imports: [],
                importFields: [
                        'id',
                        'created_at',
                        'actions'
                ],
                selectedImport: {},
                sortBy: 'checked_in_time',
                sortDesc: false,
                columnsModal: false,
                shifts: [],
                shiftFields: [
                    {
                        key: 'checked_in_time',
                        label: 'Date',
                        sortable: true,
                    },
                    {
                        key: 'hours',
                        sortable: true,
                    },
                    {
                        key: 'client_name',
                        label: 'Client',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_name',
                        label: 'Caregiver',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_rate',
                        label: 'CG Rate',
                        sortable: true,
                    },
                    {
                        key: 'provider_fee',
                        label: 'Reg Rate',
                        sortable: true,
                    },
                    {
                        key: 'ally_fee',
                        label: 'Ally Fee',
                        sortable: true,
                    },
                    {
                        key: 'hourly_total',
                        label: 'Total Hourly',
                        sortable: true,
                    },
                    {
                        key: 'caregiver_total',
                        label: 'CG Total',
                        sortable: true,
                    },
                    {
                        key: 'provider_total',
                        label: 'Reg Total',
                        sortable: true,
                    },
                    {
                        key: 'ally_total',
                        label: 'Ally Total',
                        sortable: true,
                    },
                    {
                        key: 'mileage_costs',
                        label: 'Mileage Costs',
                        sortable: true,
                    },
                    {
                        key: 'other_expenses',
                        label: 'Other',
                        sortable: true,
                    },
                    {
                        key: 'shift_total',
                        label: 'Shift Total',
                        sortable: true,
                    },
                    // {
                    //     key: 'actions',
                    //     class: 'hidden-print'
                    // }
                ],
                loading: false,
            }
        },

        mounted() {
            this.loadImports();
        },

        methods: {
            async loadImports() {
                const response =  await axios.get('/admin/imports?json=1');
                this.imports = response.data;
            },
            async loadShifts() {
                const response = await axios.get('/admin/shifts/data?import_id=' + this.selectedImport.id);
                this.shifts = response.data;
            }
        },

        watch: {
            selectedImport(val) {
                if (val) {
                    this.loadShifts();
                }
            }
        }
    }
</script>
