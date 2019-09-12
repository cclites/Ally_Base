<template>
    <div>
        <b-row>
            <b-col lg="6">
                <b-card
                        header="Total Client Verified Statistics"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <div id="verified-client-shift-summary">

                        <h5 class="d-none d-print-block">Verified Client Shift Summary</h5>

                        <div class="hidden-print pull-right">

                            <b-btn type="button" size="sm" @click="printVerifiedClientSummary()" style="margin-top: -105px">Print</b-btn>
                        </div>
                        <div class="table-responsive">

                            <table class="table table-bordered table-hover">

                                <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Shifts</th>
                                    <th>Verified</th>
                                    <th>Unverified</th>
                                    <th>% Verified</th>
                                    <th>Blocked</th>
                                    <th>Outside Range</th>
                                </tr>
                                </thead>
                                <tbody>

                                    <tr v-for=" item in summary.client " :key=" item.id ">

                                        <td><a :href="'/business/clients/' + item.clientId ">{{ item.clientName }}</a></td>
                                        <td>{{ item.totalShifts }}</td>
                                        <td>{{ item.totalVerifiedShifts }}</td>
                                        <td>{{ item.totalUnverifiedShifts }}</td>
                                        <td :class=" percentageClass( item.verifiedPercentage ) ">{{ percentageFormat( item.verifiedPercentage ) }}</td>
                                        <td>{{ item.totalBlocked }}</td>
                                        <td>{{ item.totalOutsideRange }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>

                                    <tr>

                                        <td><strong>Total for Shifts by Client</strong></td>
                                        <td><strong>{{ totalCount( summary.client ) }}</strong></td>
                                        <td><strong>{{ totalVerified( summary.client ) }}</strong></td>
                                        <td><strong>{{ totalUnverified( summary.client ) }}</strong></td>
                                        <td :class=" percentageClass( totalVerifiedPercentage( summary.client ) ) " ><strong>{{ percentageFormat( totalVerifiedPercentage( summary.client ) ) }}</strong></td>
                                        <td><strong>{{ totalBlocked( summary.client ) }}</strong></td>
                                        <td><strong>{{ totalOutsideRange( summary.client ) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </b-card>
            </b-col>
            <b-col lg="6">
                <b-card
                        header="Total Caregiver Verified Statistics"
                        header-text-variant="white"
                        header-bg-variant="info"
                >
                    <div id="verified-caregiver-shift-summary">

                        <h5 class="d-none d-print-block">Verified Caregiver Shift Summary</h5>

                        <div class="hidden-print pull-right">

                            <b-btn type="button" size="sm" @click="printVerifiedCaregiverSummary()" style="margin-top: -105px">Print</b-btn>
                        </div>
                        <div class="table-responsive">

                            <table class="table table-bordered table-hover">

                                <thead>
                                <tr>
                                    <th>Caregiver</th>
                                    <th>Shifts</th>
                                    <th>Verified</th>
                                    <th>Unverified</th>
                                    <th>% Verified</th>
                                    <th>Blocked</th>
                                    <th>Outside Range</th>
                                </tr>
                                </thead>
                                <tbody>

                                    <tr v-for=" item in summary.caregiver " :key=" item.id ">

                                        <td><a :href="'/business/caregivers/' + item.caregiverId ">{{ item.caregiverName }}</a></td>
                                        <td>{{ item.totalShifts }}</td>
                                        <td>{{ item.totalVerifiedShifts }}</td>
                                        <td>{{ item.totalUnverifiedShifts }}</td>
                                        <td :class=" percentageClass( item.verifiedPercentage ) ">{{ percentageFormat( item.verifiedPercentage ) }}</td>
                                        <td>{{ item.totalBlocked }}</td>
                                        <td>{{ item.totalOutsideRange }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>

                                    <tr>

                                        <td><strong>Total for Shifts By Caregiver</strong></td>
                                        <td><strong>{{ totalCount( summary.caregiver ) }}</strong></td>
                                        <td><strong>{{ totalVerified( summary.caregiver ) }}</strong></td>
                                        <td><strong>{{ totalUnverified( summary.caregiver ) }}</strong></td>
                                        <td :class=" percentageClass( totalVerifiedPercentage( summary.caregiver ) ) " ><strong>{{ percentageFormat( totalVerifiedPercentage( summary.caregiver ) ) }}</strong></td>
                                        <td><strong>{{ totalBlocked( summary.caregiver ) }}</strong></td>
                                        <td><strong>{{ totalOutsideRange( summary.caregiver ) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>

    import FormatsNumbers from "../../../mixins/FormatsNumbers";

    export default {

        mixins: [ FormatsNumbers ],

        data : () => ({

            clientCharges : []
        }),

        props: {

            summary : Object
        },

        computed: {

        },

        methods: {

            totalCount( section ){

                return section ? Object.values( section ).reduce( function( total, val ){ return total + val.totalShifts; }, 0 ) : 0;
            },
            totalVerified( section ){

                return section ? Object.values( section ).reduce( function( total, val ){ return total + val.totalVerifiedShifts; }, 0 ) : 0;
            },
            totalUnverified( section ){

                return section ? Object.values( section ).reduce( function( total, val ){ return total + val.totalUnverifiedShifts; }, 0 ) : 0;
            },
            totalVerifiedPercentage( section ){

                return section ? ( this.totalVerified( section ) / this.totalCount( section ) ) : 0;
            },
            totalBlocked( section ){

                return section ? Object.values( section ).reduce( function( total, val ){ return total + val.totalBlocked; }, 0 ) : 0;
            },
            totalOutsideRange( section ){

                return section ? Object.values( section ).reduce( function( total, val ){ return total + val.totalOutsideRange; }, 0 ) : 0;
            },

            percentageClass( percentage ){

                console.log( 'the percent: ', percentage );
                if( percentage >= 0.7 ) return 'text-success';
                else return 'text-danger';
            },

            printVerifiedClientSummary() {

                $( '#verified-client-shift-summary' ).print();
            },
            printVerifiedCaregiverSummary() {

                $( '#verified-caregiver-shift-summary' ).print();
            },
        }
    }
</script>
