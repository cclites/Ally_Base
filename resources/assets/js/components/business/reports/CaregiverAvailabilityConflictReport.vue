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
                                    v-model="filters.businesses"
                                    label="Location"
                                    class="mr-1"
                                    :allow-all="false"
                            />
                        </b-col>
                        <b-col lg="4">
                            <b-form-group label="Caregivers" class="mb-2 mr-2">
                                <b-select v-model="filters.caregiver">
                                    <option value="">Select Caregiver</option>
                                    <option v-for="caregiver in caregivers" :key="caregiver.id" :value="caregiver.id">{{ caregiver.nameLastFirst }}</option>
                                </b-select>
                            </b-form-group>
                        </b-col>
                        <b-col md="2">
                            <b-form-group label="&nbsp;">
                                <b-button-group>
                                    <b-button @click="fetchReport()" variant="info" :disabled="busy"><i class="fa fa-file-pdf-o mr-1"></i>Generate</b-button>
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
                        <!--template slot="schedule_id" scope="row" class="primary">
                            <b-link :href="`/business/schedule/${ row.item.schedule_id }`" target="_blank">{{ row.item.schedule_id }}</b-link>
                        </template-->
                    </b-table>
                </b-card>
            </b-col>
            <b-col lg="12">
                <b-row v-if="this.conflicts.length > 0">
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
                items: this.conflicts,
                fields: {
                    schedule_id: { sortable: true},
                    starts_at: { sortable: true, formatter: x => moment(x).format('M/D/YY hh:mm ') },
                    reason: { sortable: true },
                },
                totalRows: this.conflicts.length,
                perPage: 15,
                currentPage: 1,
                headerText: "Availability Conflict - " + this.caregiver.name,
                filters: {
                    business: '',
                    caregiver: ''
                },
                caregivers: [],
                busy: false,
                emptyText: "There are no recods for this Caregiver."

            }
        },
        methods: {
            fetchCaregivers(){
                axios.get('/business/caregivers/' + this.filters.business)
                    .then( ({ data }) => {
                        this.caregivers = data;
                    })
                    .catch(e => {})
                    .finally(() => {
                    })
            },
            fetchReport(){
                let url= '/business/reports/caregiver-availability-conflict/' + this.filters.caregiver;
                window.location = url;

            }
        },
        mounted() {
            this.fetchCaregivers();
        },
    }
</script>

<style scoped>

</style>