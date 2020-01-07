<template>
    <b-card
            header="1099 Preview Report"
            header-text-variant="white"
            header-bg-variant="info"
    >
        <b-row>
            <b-form-group label="Year" label-for="year" class="mr-2">
                <b-form-select id="year"
                               v-model="form.year"
                >
                    <option v-for="year in years" :value="year">{{ year }}</option>
                </b-form-select>
            </b-form-group>

            <business-location-form-group
                    v-model="form.business_id"
                    :allow-all="true"
                    class="mr-2 business_id"
                    label="Location"
            />

            <b-form-group label="Caregivers" label-for="caregiver_id" class="mr-2">
                <b-form-select
                        id="caregiver_id"
                        name="caregiver_id"
                        v-model="form.caregiver_id"
                >
                    <option value="">All Caregivers</option>
                    <option v-for="caregiver in caregivers" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                </b-form-select>
            </b-form-group>

            <b-form-group label="&nbsp;" class="mr-2 mt-1">
                <b-btn variant="info" @click="generate()" :disabled="disableGenerate">Generate</b-btn>
            </b-form-group>
        </b-row>

        <div class="d-flex justify-content-center" v-if="busy">
            <div class="my-5">
                <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
            </div>
        </div>
        <div v-else>
            <b-row>
                <b-col>
                    <b-table
                            class="admin-1099-report"
                            :items="items"
                            :fields="fields"
                            :sort-by="sortBy"
                            :empty-text="emptyText"
                            :busy="busy"
                            :current-page="currentPage"
                            :per-page="perPage"
                            ref="table"
                    >
                    </b-table>
                </b-col>
            </b-row>
        </div>

        <b-row v-if="this.items.length > 0">
            <b-col lg="6" >
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
            </b-col>
            <b-col lg="6" class="text-right">
                Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
            </b-col>
        </b-row>
        <b-row v-else>
            <b-col class="text-center">{{ emptyText }}</b-col>
        </b-row>

    </b-card>
</template>

<script>
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsDates from "../../../mixins/FormatsDates";
    import BusinessLocationSelect from "../../business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../../business/BusinessLocationFormGroup";

    export default {
        name: "Ally1099PayersPreviewReport",
        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        mixins: [FormatsNumbers, FormatsDates],
        props: {},
        data() {
            return {
                start_date: 2019, //arbitrary start year
                end_date: moment().year(),
                form: new Form({
                        business_id: '',
                        caregiver_id: '',
                        year: '',
                        json: 1,
                }),
                caregivers: [],
                items: [],
                busy: false,
                totalRows: 0,
                perPage: 100,
                currentPage: 1,
                sortBy: 'caregiver_last_name',
                sortDesc: false,
                emptyText: "No records to display",
                selected: '',
                fields: [
                    {key: 'caregiver_first_name', label: 'Caregiver First Name', sortable: true,},
                    {key: 'caregiver_last_name', label: 'Caregiver Last Name', sortable: true,},
                    {key: 'business_name', label: 'Location', sortable: true,},
                    {key: 'payment_total', label: 'Total Year Amount', sortable: true, formatter: x => { return this.moneyFormat(x) }},
                ],

            }
        },
        methods: {
            generate(){
                this.totalRows = 0;
                this.form.get('/admin/ally-preview-1099-report')
                    .then( ({ data }) => {
                        this.items = data;
                        this.totalRows = this.items.length;
                    })
                    .catch(e => {})
                    .finally(() => {
                    })
            },

            create(record){
                let index = record.index;

                let data = new Form({
                    'year': this.form.year,
                    'business_id': this.form.business_id,
                    'caregiver_id' : record.item.caregiver_id,
                });

                data.post('/admin/business-1099/create')
                    .then(response => {
                        this.generate();
                    })
                    .catch( e => {
                    })
                    .finally(() => {
                    });
            },


            downloadPdf(id){
                window.location = '/admin/business-1099/download/' + id;
            },

        },
        watch: {
            'form.business_id'(newVal, oldVal){
                if(newVal !== oldVal){
                    axios.get('/admin/caregivers?json=1&id=' + this.form.business_id + '&active=1').then(response => this.caregivers = response.data);
                    this.caregiver_id='';
                }
            },
        },
        computed: {
            years(){
                let x = [];
                let i = this.start_date;
                while( i <= this.end_date){
                    x.push(i++);
                };

                this.form.year = this.start_date;
                return x;
            },

            disableGenerate(){
                if(this.caregivers.length){
                    return false;
                }
                return true;
            }
        },
    }
</script>

<style scoped>

    .business_id{
        margin-top: 6px;
    }

    button.btn.btn-secondary{
        padding: 2px;
    }
</style>