<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card
                        header="Select Date Range &amp; Filters"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <form action="/business/reports/print/timesheet-data" method="post" target="_blank">
                        <input type="hidden" name="_token" :value="token">
                        <b-row>
                            <b-col lg="2">
                                <b-form-group label="Start Date">
                                    <date-picker
                                            class="mb-1"
                                            name="start_date"
                                            v-model="form.start_date"
                                            placeholder="Start Date">
                                    </date-picker>
                                </b-form-group>
                            </b-col>
                            <b-col lg="2">
                                <b-form-group label="End Date">
                                    <date-picker
                                            class="mb-1"
                                            v-model="form.end_date"
                                            name="end_date"
                                            placeholder="End Date">
                                    </date-picker>
                                </b-form-group>
                            </b-col>
                            <b-col lg="2">
                                <business-location-form-group v-model="form.business_id"
                                                              :form="form"
                                                              field="business_id"
                                                              name="business_id"
                                                              help-text="">
                                </business-location-form-group>
                            </b-col>
                            <b-col lg="2">
                                <b-form-group label="Caregiver">
                                    <b-form-select v-model="form.caregiver_id" class="mx-1 mb-1" name="caregiver_id">
                                        <option value="">All Caregivers</option>
                                        <option v-for="item in caregiverList" :value="item.id" :key="item.id">{{ item.nameLastFirst }}
                                        </option>
                                    </b-form-select>
                                </b-form-group>
                            </b-col>
                            <b-col lg="2">
                                <b-form-group label="Client">
                                    <b-form-select v-model="form.client_id" class="mr-1 mb-1" name="client_id">
                                        <option value="">All Clients</option>
                                        <option v-for="item in clientList" :value="item.id" :key="item.id">{{ item.nameLastFirst }}
                                        </option>
                                    </b-form-select>
                                </b-form-group>
                            </b-col>
                            <b-col lg="2">
                                <b-form-group label="Client Type">
                                    <b-form-select v-model="form.client_type" class="mb-1" name="client_type">
                                        <option v-for="item in clientTypes" :key="item.value" :value="item.value">{{ item.text }}</option>
                                    </b-form-select>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>

                            <b-col lg="3">
                                <b-form-group label="Export Type">
                                    <b-form-radio-group id="export_type" v-model="form.export_type" name="export_type">
                                        <b-form-radio value="pdf">PDF</b-form-radio>
                                        <b-form-radio value="text">Text</b-form-radio>
                                    </b-form-radio-group>
                                </b-form-group>
                            </b-col>
                            <b-col lg="3">
                                <b-form-group label="&nbsp;">
                                    <!--<b-button type="submit">Preview</b-button>-->
                                    <b-button variant="info" type="submit">Export</b-button>
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </form>
                </b-card>
            </b-col>
        </b-row>

        <!--<b-row>-->
            <!--<b-col lg="12">-->
                <!--<b-card-->
                        <!--header="Preview"-->
                        <!--header-text-variant="white"-->
                        <!--header-bg-variant="info"-->
                <!--&gt;-->
                    <!--<div v-if="preview.length === 0" class="text-center">-->
                        <!--No Results.-->
                    <!--</div>-->
                    <!--<div v-for="shift in preview">-->
                        <!--{{ shift.caregiver.name }}-->
                    <!--</div>-->
                <!--</b-card>-->
            <!--</b-col>-->
        <!--</b-row>-->
    </div>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";
    import Constants from '../../../mixins/Constants';

    export default {
        components: {BusinessLocationFormGroup},

        mixins: [FormatsDates, FormatsNumbers, Constants],

        props: ['clients', 'caregivers', 'token'],

        data() {
            return {
                preview: [],
                form: new Form({
                    start_date: moment().startOf('isoweek').format('MM/DD/YYYY'),
                    end_date: moment().startOf('isoweek').add(6, 'days').format('MM/DD/YYYY'),
                    business_id: '',
                    caregiver_id: '',
                    client_id: '',
                    client_type: '',
                    export_type: 'pdf'
                }),
                selectedItem: {}
            }
        },

        computed: {
            caregiverList() {
                return _.sortBy(this.caregivers, 'nameLastFirst');
            },

            clientList() {
                return _.sortBy(this.clients, 'nameLastFirst');
            }
        },

        methods: {

            fetchPreview() {
                this.form.post('/business/reports/export-timesheets')
                    .then(response => {
                        this.preview = response.data;
                    });
            },

            hoursType(item) {
                switch (item.hours_type) {
                    case 'default':
                        return 'Regular';
                    case 'overtime':
                        return 'OT';
                    case 'holiday':
                        return 'HOL';
                }
            },
        }
    }
</script>

<style>
    table {
        font-size: 14px;
    }

    .table-info,
    .table-info > td,
    .table-info > th {
        font-weight: bold;
        font-size: 13px;
        background-color: #ecf7f9;
    }

    .table-sm td,
    .table-sm th {
        padding: 0.2rem 0;
    }

    .signature > svg {
        margin: -25px 0;
        width: 100%;
        height: auto;
        max-width: 400px;
    }
</style>