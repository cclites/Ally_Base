<template>
    <b-card>
        <b-row>
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
                <template slot="verified" scope="data">
                    <span v-if="data.value" style="color: green">
                        <i class="fa fa-check-square-o"></i>
                    </span>
                    <span v-else style="color: darkred">
                        <i class="fa fa-times-rectangle-o"></i>
                    </span>
                </template>
                <template slot="actions" scope="row">
                    <b-btn size="sm" :href="'/business/shifts/' + row.item.id">Edit &amp; Details</b-btn>
                    <b-btn size="sm" @click.stop="details(row.item)">View</b-btn>
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

        <!-- Details modal -->
        <b-modal id="detailsModal" title="Shift Details" v-model="detailsModal">
            <b-container fluid>
                <h4>Shift</h4>
                <b-row>
                    <b-col sm="12">
                        <b-form-group label="Client" label-for="">
                            {{ selectedItem.client_name }}
                        </b-form-group>
                        <b-form-group label="Caregiver" label-for="">
                            {{ selectedItem.caregiver_name }}
                        </b-form-group>
                        <b-form-group label="Clock In Time" label-for="checked_in_time">
                            <b-form-input
                                id="checked_in_time"
                                name="checked_in_time"
                                type="text"
                                v-model="selectedItem.checked_in_time"
                                disabled
                                >
                            </b-form-input>
                            <!--<input-help :form="form" field="checked_in_time" text=""></input-help>-->
                        </b-form-group>
                        <b-form-group label="Clock Out Time" label-for="checked_out_time">
                            <b-form-input
                                    id="checked_out_time"
                                    name="checked_out_time"
                                    type="text"
                                    v-model="selectedItem.checked_out_time"
                                    disabled
                            >
                            </b-form-input>
                            <!--<input-help :form="form" field="checked_out_time" text=""></input-help>-->
                        </b-form-group>
                    </b-col>
               </b-row>
                <h4>Issues on Shift</h4>
                <b-row>
                    <b-col sm="12">
                        <p v-if="!selectedItem.issues || !selectedItem.issues.length">
                            No issues reported
                        </p>
                        <p else v-for="issue in selectedItem.issues">
                            <strong v-if="issue.caregiver_injury">The caregiver reported an injury to themselves.<br /></strong>
                            {{ issue.comments }}
                        </p>
                    </b-col>
                </b-row>
                <h4>Activities Performed</h4>
                <b-row>
                    <b-col sm="12">
                        <p v-if="!selectedItem.activities || !selectedItem.activities.length">
                            No activities recorded
                        </p>
                        <table class="table" v-else>
                            <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="activity in selectedItem.activities">
                                <td>{{ activity.code }}</td>
                                <td>{{ activity.name }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
                <h4>EVV</h4>
                <b-row>
                    <b-col sm="6">
                        <table class="table">
                            <thead>
                            <tr>
                                <th colspan="2">Clock In</th>
                            </tr>
                            </thead>
                            <tbody v-if="selectedItem.checked_in_latitude || selectedItem.checked_in_longitude">
                            <!-- <tr>
                                <th>Geocode</th>
                                <td>{{ selectedItem.checked_in_latitude.slice(0,8) }},<br />{{ selectedItem.checked_in_longitude.slice(0,8) }}</td>
                            </tr> -->
                            <tr>
                                <th>Distance</th>
                                <td>{{ selectedItem.checked_in_distance }}m</td>
                            </tr>
                            </tbody>
                            <tbody v-else-if="selectedItem.checked_in_number">
                            <tr>
                                <th>Phone Number</th>
                                <td>{{ selectedItem.checked_in_number }}</td>
                            </tr>
                            </tbody>
                            <tbody v-else>
                            <tr>
                                <td colspan="2">No EVV data</td>
                            </tr>
                            </tbody>
                        </table>
                    </b-col>
                    <b-col sm="6">
                        <table class="table">
                            <thead>
                            <tr>
                                <th colspan="2">Clock Out</th>
                            </tr>
                            </thead>
                            <tbody v-if="selectedItem.checked_out_latitude || selectedItem.checked_out_longitude">
                            <tr>
                                <th>Geocode</th>
                                <td>{{ selectedItem.checked_out_latitude.slice(0,8) }},<br />{{ selectedItem.checked_out_longitude.slice(0,8) }}</td>
                            </tr>
                            <tr>
                                <th>Distance</th>
                                <td>{{ selectedItem.checked_out_distance }}m</td>
                            </tr>
                            </tbody>
                            <tbody v-else-if="selectedItem.checked_out_number">
                            <tr>
                                <th>Phone Number</th>
                                <td>{{ selectedItem.checked_out_number }}</td>
                            </tr>
                            </tbody>
                            <tbody v-else>
                            <tr>
                                <td colspan="2">No EVV data</td>
                            </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer">
               <b-btn variant="default" @click="detailsModal=false">Close</b-btn>
               <b-btn variant="info" @click="verifySelected()" v-if="!selectedItem.verified">Mark Verified</b-btn>
            </div>
        </b-modal>
    </b-card>
</template>

<script>
    import Form from "../classes/Form";

    export default {
        props: {
            'shifts': {
                default() {
                    return [];
                }
            },
        },

        data() {
            return {
                totalRows: 0,
                perPage: 15,
                currentPage: 1,
                sortBy: null,
                sortDesc: false,
                filter: null,
                detailsModal: false,
                selectedItem: {},
                fields: [
                    {
                        key: 'date',
                        label: 'Date',
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
                        key: 'hours',
                        label: 'Hours',
                        sortable: true,
                    },
                    {
                        key: 'verified',
                        label: 'Verified',
                        sortable: true,
                    },
                    'actions'
                ],
                items: this.shifts.map(function(shift) {
                        let start = moment.utc(shift.checked_in_time);
                        let end = (shift.checked_out_time) ? moment.utc(shift.checked_out_time) : null;
                        let hours = (shift.checked_out_time) ? (parseInt(end.diff(start, 'minutes')) / 60).toFixed(2) : 'CLOCKED IN';
                        return {
                            id: shift.id,
                            date: start.local().format('L LTS'),
                            client_name: shift.client_name,
                            caregiver_name: shift.caregiver_name,
                            hours: hours,
                            verified: shift.verified
                        }
                    }),

            }
        },

        mounted() {
            this.totalRows = this.items.length;
        },

        computed: {

        },

        methods: {
            details(item) {
                var component = this;
                axios.get('/business/shifts/' + item.id)
                    .then(function(response) {
                        let shift = response.data;
                        shift.checked_in_time = moment.utc(shift.checked_in_time).local().format('L LT');
                        shift.checked_out_time = moment.utc(shift.checked_out_time).local().format('L LT');
                        component.selectedItem = shift;
                        component.detailsModal = true;
                        console.log(component.selectedItem);
                    })
                    .catch(function(error) {
                        alert('Error loading shift details');
                    });
            },
            verifySelected() {
                let component = this;
                let form = new Form();
                form.post('/business/shifts/' + component.selectedItem.id + '/verify')
                    .then(function(response) {
                        component.detailsModal = false;
                        component.items.map(function(shift) {
                           if (shift.id == component.selectedItem.id) {
                               shift.verified = 1;
                           }
                           return shift;
                        });
                    });
            },
            onFiltered(filteredItems) {
                // Trigger pagination to update the number of buttons/pages due to filtering
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            }
        }
    }
</script>
