<template>
    <div>
        <b-row>
            <b-col lg="12">
                <b-card :header="headerText"
                        header-bg-variant="info"
                        header-text-variant="white"
                >
                    <b-row>
                        <b-col lg="4">
                            <business-location-form-group
                                    v-model="filters.business"
                                    label="Location"
                                    class="mr-1"
                                    :allow-all="false"
                            />
                        </b-col>
                        <b-col lg="4">
                            <b-form-group label="Caregivers" class="mb-2">
                                <b-select v-model="filters.caregiver">
                                    <option value="">Select Caregiver</option>
                                    <option v-for="caregiver in caregivers" :key="caregiver.id" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                                </b-select>
                            </b-form-group>
                        </b-col>
                        <b-col md="2">
                            <b-form-group label="&nbsp;">
                                <b-button-group>
                                    <b-button @click="fetchReport()" variant="info" :disabled="busy"><i class="fa fa-file-pdf-o mr-2"></i>Generate</b-button>
                                </b-button-group>
                            </b-form-group>
                        </b-col>
                        <b-col md="4" v-if="this.items.length > 0 && this.caregiver">
                            <b-form-group label="&nbsp;">
                                <b-button-group>
                                    <b-btn @click="reopenShifts()">
                                        Remove Caregiver from these visits and mark them as open?
                                    </b-btn>
                                </b-button-group>
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-table bordered striped hover show-empty
                             :items="items"
                             :fields="fields"
                             :current-page="currentPage"
                             :per-page="perPage"
                             :empty-text="emptyText"
                    >
                        <template slot="caregiver_name" scope="row" class="primary">
                            <b-link :href="`/business/caregivers/${ row.item.caregiver_id }#availability`" target="_blank">{{ row.item.caregiver_name }}</b-link>
                        </template>
                        <template slot="action" scope="row" class="primary">
                            <b-btn class="btn-sm" @click="reopenSingleShift(row.item.schedule_id)">Reopen</b-btn>
                        </template>
                    </b-table>
                </b-card>
            </b-col>
            <b-col lg="12">
                <b-row v-if="this.items.length > 0">
                    <b-col lg="6" >
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" />
                    </b-col>
                    <b-col lg="6" class="text-right">
                        Showing {{ perPage < totalRows ? perPage : totalRows }} of {{ totalRows }} results
                    </b-col>
                </b-row>
            </b-col>
        </b-row>
    </div>
    
</template>

<script>

    import BusinessLocationSelect from "../../business/BusinessLocationSelect";
    import BusinessLocationFormGroup from "../../business/BusinessLocationFormGroup";
    import FormatsDates from "../../../mixins/FormatsDates";

    export default {
        name: "CaregiverAvailabilityConflictReport",
        mixins: [FormatsDates],
        components: {BusinessLocationFormGroup, BusinessLocationSelect},
        props: ['caregiver', 'conflicts'],
        data() {
            return{
                items: this.conflicts ? this.conflicts : [],
                fields: {
                    //schedule_id: { sortable: true},
                    starts_at: { sortable: true, formatter: x => moment(x).format('M/D/YY hh:mm ') },
                    reason: { sortable: true },
                    caregiver_name: { sortable: true },
                    action: {}
                },
                totalRows: this.conflicts ? this.conflicts.length : 0,
                perPage: 15,
                currentPage: 1,
                headerText: "Availability Conflict - " + (this.caregiver ? this.caregiver.name : ''),
                filters: {
                    business: '',
                    caregiver: this.caregiver ? this.caregiver.id : '',
                },
                caregivers: [],
                busy: false,
                emptyText: "There are no records to display."

            }
        },
        methods: {
            fetchCaregivers(){
                axios.get('/business/caregivers')
                    .then( ({ data }) => {
                        this.caregivers = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                    })
            },
            fetchReport(){

                let form = new Form(this.filters);
                let url= '/business/reports/caregiver-availability-conflict?json=1';

                form.get(url)
                    .then( ({ data }) => {
                        history.replaceState(null, '','/business/reports/caregiver-availability-conflict' )
                        this.items = data;
                    })
                    .catch(e => {
                    });
            },
            reopenShifts(){
                let url = '/business/schedule/reopen/' + this.caregiver.id;
                let form = new Form();
                form.post(url)
                    .then( ({ data }) => {
                        window.location.reload();
                    })
                    .catch(e => {
                    });
            },
            reopenSingleShift(scheduleId){
                let url = '/business/schedule/reopen/single/' + scheduleId;
                let form = new Form();
                form.post(url)
                    .then( ({ data }) => {
                        window.location.reload();
                    })
                    .catch(e => {
                    });
            },
        },
        computed: {
        },
        mounted() {
            this.fetchCaregivers();
        },
    }
</script>

<style scoped>

</style>