<template>

    <div>

        <transition mode="out-in" name="slide-fade">

            <div v-if=" !editing_claim_item " :key=" 'first' ">

                <b-row>

                    <b-col sm="12" class="mb-4"><strong>Claim Information:</strong></b-col>
                    <b-col sm="6">

                        <b-form-group
                            id="client_name"
                            label-cols-sm="2"
                            label-cols-lg="2"
                            description="name of the client being serviced"
                            label="Client:"
                            label-for="client_name"
                        >
                            <div class="d-flex">

                                <b-form-input class="flex-1 mr-2" :disabled=" !editing_claim " size="sm" id="client_name" v-model=" claim_details.client_first_name "></b-form-input>
                                <b-form-input class="flex-1" :disabled=" !editing_claim " size="sm" id="client_name" v-model=" claim_details.client_last_name "></b-form-input>
                            </div>
                        </b-form-group>
                    </b-col>
                    <b-col sm="6">

                        <b-form-group
                            id="payer_code"
                            label-cols-sm="2"
                            label-cols-lg="2"
                            description="corresponding payer code"
                            label="Payer Code:"
                            label-for="payer_code"
                        >
                            <b-form-input :disabled=" !editing_claim " size="sm" id="payer_code" v-model=" claim_details.payer_code "></b-form-input>
                        </b-form-group>
                    </b-col>
                    <b-col sm="6">

                        <b-form-group
                            id="payer_name"
                            label-cols-sm="2"
                            label-cols-lg="2"
                            description="corresponding payer name"
                            label="Payer Name:"
                            label-for="payer_name"
                        >
                            <b-form-input :disabled=" !editing_claim " size="sm" id="payer_name" v-model=" claim_details.payer_name "></b-form-input>
                        </b-form-group>
                    </b-col>
                    <b-col sm="6">

                        <b-form-group
                            id="medicaid_id"
                            label-cols-sm="2"
                            label-cols-lg="2"
                            description="client medicaid id number"
                            label="Medicaid ID:"
                            label-for="medicaid_id"
                        >
                            <b-form-input :disabled=" !editing_claim " size="sm" id="medicaid_id" v-model=" claim_details.client_medicaid_id "></b-form-input>
                        </b-form-group>
                    </b-col>
                    <b-col sm="6">

                        <b-form-group
                            id="medicaid_diagnosis_codes"
                            label-cols-sm="2"
                            label-cols-lg="2"
                            description="relevant codes for medicaid diagnosis"
                            label="Medicaid Diagnosis Codes:"
                            label-for="medicaid_diagnosis_codes"
                        >
                            <b-form-input :disabled=" !editing_claim " size="sm" id="medicaid_diagnosis_codes" v-model=" claim_details.client_medicaid_diagnosis_codes "></b-form-input>
                        </b-form-group>
                    </b-col>
                    <b-col sm="6">

                        <b-form-group
                            id="plan_code"
                            label-cols-sm="2"
                            label-cols-lg="2"
                            description="service plan code"
                            label="Client Plan Code:"
                            label-for="plan_code"
                        >
                            <b-form-input :disabled=" !editing_claim " size="sm" id="payer_code" v-model=" claim_details.plan_code "></b-form-input>
                        </b-form-group>
                    </b-col>
                    <b-col sm="6">

                        <b-form-group
                            id="transmission_method"
                            label-cols-sm="2"
                            label-cols-lg="2"
                            description="claim transmission method"
                            label="Transmission Method:"
                            label-for="transmission_method"
                        >
                            <b-form-input :disabled=" !editing_claim " size="sm" id="transmission_method" v-model=" claim_details.transmission_method "></b-form-input>
                        </b-form-group>
                    </b-col>
                    <b-col sm="6" xs="12" class="mb-2 d-flex align-items-center justify-content-end">

                        <transition name="slide-fade" mode="out-in">

                            <div v-if=" editing_claim ">

                                <b-button variant="outline-success" class="mr-2" @click=" editing_claim = !editing_claim ">Cancel</b-button>
                                <b-button variant="primary" @click.prevent=" updateClaim() " :disabled=" process_loading ">Save</b-button>
                            </div>

                            <b-button variant="primary" @click=" editing_claim = !editing_claim " v-else>Edit Claim</b-button>
                        </transition>
                    </b-col>
                </b-row>

                <hr />

                <b-row>

                    <b-col sm="12">

                        <div class="my-4">

                            <strong>Claim Services:</strong>
                        </div>

                        <div class="table-responsive">

                            <table class="table table-bordered table-fit-more table-striped table-hover mb-0">

                                <thead>

                                    <tr>
                                        <th>Actions</th>
                                        <th>Caregiver First Name</th>
                                        <th>Caregiver Last Name</th>
                                        <th>Caregiver Gender</th>
                                        <th>Caregiver D.O.B</th>
                                        <th>Caregiver SSN</th>
                                        <th>Caregiver Medicaid ID</th>
                                        <th>Caregiver Comments</th>
                                        <th>Service Name</th>
                                        <th>Service Code</th>
                                        <th>Service Charge</th>
                                        <th>Service Duration</th>
                                        <th>Client Rate</th>
                                        <th>Client Address 1</th>
                                        <th>Client Address 2</th>
                                        <th>Client City</th>
                                        <th>Client State</th>
                                        <th>Client Zip</th>
                                        <th>Client Latitude</th>
                                        <th>Client Longitude</th>
                                        <th>Checked In #</th>
                                        <th>Checked Out #</th>
                                        <th>Checked In Latitude</th>
                                        <th>Checked Out Longitude</th>
                                        <th>Scheduled Start</th>
                                        <th>Scheduled End</th>
                                        <th>Visit Start</th>
                                        <th>Visit End</th>
                                        <th>Activities</th>
                                        <th>EVV Start</th>
                                        <th>EVV End</th>
                                        <th>EVV Method In</th>
                                        <th>EVV Method Out</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr v-for=" ( service, i ) in claimableServices " :key=" i ">

                                        <td>

                                            <transition name="slide-fade" mode="out-in">

                                                <div v-if=" !service.removing && !editing_claim_item " class="d-flex flex-column">

                                                    <b-button variant="outline-info" size="sm" class="mb-1" @click=" editItem( true, service ) ">Edit</b-button>
                                                    <b-button variant="outline-danger" size="sm" @click=" service.removing = true ">Remove</b-button>
                                                </div>
                                                <div v-if=" service.removing && !editing_claim_item " class="d-flex flex-column">

                                                    <b-button variant="outline-success" size="sm" class="mb-1" @click=" service.removing = false ">Cancel</b-button>
                                                    <b-button variant="danger" size="sm" @click=" deleteItem( service ) ">Delete</b-button>
                                                </div>
                                                <!-- <div v-if=" !service.removing && service.editing " class="d-flex flex-column">

                                                    <b-button variant="outline-default" size="sm" class="mb-1" @click=" service.editing = false ">Cancel</b-button>
                                                    <b-button variant="info" size="sm" @click=" saveChanges( service ) ">Save</b-button>
                                                </div> -->
                                            </transition>
                                        </td>
                                        <td>{{ service.claimable.caregiver_first_name }}</td>
                                        <td>{{ service.claimable.caregiver_last_name }}</td>
                                        <td>{{ service.claimable.gender }}</td>
                                        <td>{{ service.claimable.caregiver_dob }}</td>
                                        <td>{{ service.claimable.caregiver_ssn }}</td>
                                        <td>{{ service.claimable.caregiver_medicaid_id }}</td>
                                        <td>{{ service.claimable.caregiver_comments }}</td>
                                        <td>{{ service.claimable.service_name }}</td>
                                        <td>{{ service.claimable.service_code }}</td>
                                        <td>{{ service.amount }}</td>
                                        <td>{{ service.units }}</td>
                                        <td>{{ service.rate }}</td>
                                        <td>{{ service.claimable.address1 }}</td>
                                        <td>{{ service.claimable.address2 }}</td>
                                        <td>{{ service.claimable.city }}</td>
                                        <td>{{ service.claimable.state }}</td>
                                        <td>{{ service.claimable.zip }}</td>
                                        <td>{{ service.claimable.latitude }}</td>
                                        <td>{{ service.claimable.longitude }}</td>
                                        <td>{{ service.claimable.checked_in_number }}</td>
                                        <td>{{ service.claimable.checked_out_number }}</td>
                                        <td>{{ service.claimable.checked_in_latitude }}</td>
                                        <td>{{ service.claimable.checked_out_longitude }}</td>
                                        <td>{{ service.claimable.scheduled_start_time }}</td>
                                        <td>{{ service.claimable.scheduled_end_time }}</td>
                                        <td>{{ service.claimable.visit_start_time }}</td>
                                        <td>{{ service.claimable.visit_end_time }}</td>
                                        <td>{{ service.claimable.activities }}</td>
                                        <td>{{ service.claimable.evv_start_time }}</td>
                                        <td>{{ service.claimable.evv_end_time }}</td>
                                        <td>{{ service.claimable.evv_method_in }}</td>
                                        <td>{{ service.claimable.evv_method_out }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </b-col>

                    <b-col sm="12">

                        <div class="my-4">

                            <strong>Claim Expenses:</strong>
                        </div>

                        <div class="table-responsive">

                            <table class="table table-bordered table-fit-more table-striped table-hover mb-0">

                                <thead>

                                    <tr>
                                        <th>Expense Name</th>
                                        <th>Expense Charge</th>
                                        <th>Expense Balance</th>
                                        <th>Expense Units</th>
                                        <th>Expense Rate</th>
                                        <th>Expense Date</th>
                                        <th>Expense Notes</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr v-for=" ( expense, j ) in claimableExpenses " :key=" j ">

                                        <td>{{ expense.claimable.name }}</td>
                                        <td>{{ expense.amount }}</td>
                                        <td>{{ expense.balance }}</td>
                                        <td>{{ expense.units }}</td>
                                        <td>{{ expense.rate }}</td>
                                        <td>{{ expense.claimable.date }}</td>
                                        <td>{{ expense.claimable.notes }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </b-col>
                </b-row>
            </div>
            <div v-if=" editing_claim_item " :key=" 'second' ">

                <b-row>

                    <b-col sm="12">

                        <div class="d-flex align-items-center justify-content-between">

                            <h3>Editing {{ ( editing_item.claimable_type == 'App\\ClaimableService' ? 'Service' : 'Expense' ) }}</h3>

                            <div>

                                <b-button variant="outline-success" @click=" editing_claim_item = false " class="mr-2">Cancel</b-button>
                                <b-button variant="info" @click=" saveEditingItem() " :disabled=" process_loading ">Save</b-button>
                            </div>
                        </div>
                    </b-col>

                    <b-col class="my-1" sm="6" v-for=" ( row, j ) in editable_rows " :key=" j ">

                        <label :for=" row.name ">{{ row.label }}:</label>
                        <b-form-input
                            :id=" row.name "
                            v-model=" row.value "
                            trim
                        ></b-form-input>
                    </b-col>
                </b-row>

                <div class="d-flex align-items-center justify-content-end mt-2">

                    <b-button variant="outline-success" @click=" editing_claim_item = false " class="mr-2">Cancel</b-button>
                    <b-button variant="info" @click=" saveEditingItem() " :disabled=" process_loading ">Save</b-button>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>

    // import authUser from '../../mixins/AuthUser';
    // import ShiftServices from "../../mixins/ShiftServices";
    // import FormatsNumbers from "../../mixins/FormatsNumbers";

    export default {

        // mixins: [ authUser, ShiftServices, FormatsNumbers ],

        props: {

            claim: {

                type    : Object,
                default : () => { return {} },
            },
            transmitUpdate   : Function,
            transmitDelete   : Function,
            transmitEditItem : Function

        },

        data: () => ({

            editing_claim      : false,
            editing_item       : null,
            editing_claim_item : false,
            process_loading    : false,
            claim_details      : {},

            editable_rows : [

                {
                    name      : 'caregiver_first_name',
                    label     : 'First Name',
                    claimable : true
                },
                {
                    name      : 'caregiver_last_name',
                    label     : 'Last Name',
                    claimable : true
                },
                {
                    name      : 'gender',
                    label     : 'Gender',
                    claimable : true
                },
                {
                    name      : 'caregiver_dob',
                    label     : 'Date of Birth',
                    claimable : true
                },
                {
                    name      : 'caregiver_ssn',
                    label     : 'Caregiver Ssn',
                    claimable : true
                },
                {
                    name      : 'caregiver_medicaid_id',
                    label     : 'Caregiver Medicaid ID',
                    claimable : true
                },
                {
                    name      : 'caregiver_comments',
                    label     : 'Caregiver Comments',
                    claimable : true
                },
                {
                    name      : 'service_name',
                    label     : 'Service Name',
                    claimable : true
                },
                {
                    name      : 'service_code',
                    label     : 'Service Code',
                    claimable : true
                },
                {
                    name      : 'amount',
                    label     : 'Amount',
                    claimable : false
                },
                {
                    name      : 'amount_due',
                    label     : 'Balance',
                    claimable : false
                },
                {
                    name      : 'units',
                    label     : 'Units',
                    claimable : false
                },
                {
                    name      : 'rate',
                    label     : 'Rate',
                    claimable : false
                },
                {
                    name      : 'address1',
                    label     : 'Address 1',
                    claimable : true
                },
                {
                    name      : 'address2',
                    label     : 'Address 2',
                    claimable : true
                },
                {
                    name      : 'city',
                    label     : 'City',
                    claimable : true
                },
                {
                    name      : 'state',
                    label     : 'State',
                    claimable : true
                },
                {
                    name      : 'zip',
                    label     : 'Zip',
                    claimable : true
                },
                {
                    name      : 'latitude',
                    label     : 'Latitude',
                    claimable : true
                },
                {
                    name      : 'longitude',
                    label     : 'Longitude',
                    claimable : true
                },
                {
                    name      : 'checked_in_number',
                    label     : 'Checked-in Number',
                    claimable : true
                },
                {
                    name      : 'checked_out_number',
                    label     : 'Checked-out Number',
                    claimable : true
                },
                {
                    name      : 'checked_in_latitude',
                    label     : 'Checked-in Latitude',
                    claimable : true
                },
                {
                    name      : 'checked_out_longitude',
                    label     : 'Checked-out Longitude',
                    claimable : true
                },
                {
                    name      : 'scheduled_start_time',
                    label     : 'Scheduled Start Time',
                    claimable : true
                },
                {
                    name      : 'scheduled_end_time',
                    label     : 'Scheduled End Time',
                    claimable : true
                },
                {
                    name      : 'visit_start_time',
                    label     : 'Visit Start Time',
                    claimable : true
                },
                {
                    name      : 'visit_end_time',
                    label     : 'Visit End Time',
                    claimable : true
                },
                {
                    name      : 'activities',
                    label     : 'Activities',
                    claimable : true
                },
                {
                    name      : 'evv_start_time',
                    label     : 'Evv Start Time',
                    claimable : true
                },
                {
                    name      : 'evv_end_time',
                    label     : 'Evv End Time',
                    claimable : true
                },
                {
                    name      : 'evv_method_in',
                    label     : 'Evv Method In',
                    claimable : true
                },
                {
                    name      : 'evv_method_out',
                    label     : 'Evv Method Out',
                    claimable : true
                },
            ]
        }),

        computed: {

            claimableServices(){

                if( !this.claim.id ) return [];

                return this.claim.items.filter( item => item.claimable_type == 'App\\ClaimableService' );
            },
            claimableExpenses(){

                if( !this.claim.id ) return [];

                return this.claim.items.filter( item => item.claimable_type == 'App\\ClaimableExpense' );
            }
        },

        methods: {

            updateClaim(){

                this.process_loading = true;

                axios.patch( '/business/claims/' + this.claim.id, this.claim_details )
                    .then( res => {

                        console.log( 'response: ', res );
                        this.transmitUpdate( res.data );
                    })
                    .catch( err => {

                        console.error( err );
                        alert( 'Error updating claim' );
                    })
                    .finally( () => {

                        this.process_loading = false;
                        this.editing_claim = false;
                    });
            },
            deleteItem( item ){

                console.log( 'deleting item.. ', item );
                item.processing = true;
                axios.delete( '/business/claims/item/' + item.id )
                    .then( res => {

                        console.log( 'response: ', res );
                        let index;
                        this.claim.items.find( ( claimItem, i ) => {

                            if( claimItem.id == item.id ) index = i;
                        });

                        this.claim.items.splice( index, 1 );
                        this.transmitDelete( item );
                    })
                    .catch( err => {

                        console.error( err );
                        alert( 'Error updating claim' );
                        item.processing = false;
                    });
            },
            saveEditingItem(){

                this.process_loading = true;

                const previous_amount = parseFloat( this.editing_item.amount );

                this.editable_rows.forEach( row => {
                    // update the value of the row in the modal component
                    // having this deep copy in the first place prevents the need to do this after a cancel

                    if( row.claimable ) this.editing_item.claimable[ row.name ] = row.value;
                    else this.editing_item[ row.name ] = row.value;
                });

                // calculate the new amount based on rate and units because amount is not editable
                const new_amount = parseFloat( this.editing_item.rate ) * parseFloat( this.editing_item.units );
                this.editing_item.amount = new_amount;

                const form = new Form( this.editing_item );
                form.patch( '/business/claims/item/' + this.editing_item.id )
                    .then( res => {

                        console.log( 'response: ', res );

                        // send the change in amount to the table-parent-component for update there, since the table doesnt contain the item data
                        const changed_amount = previous_amount - new_amount;
                        const data = {

                            changed_amount,
                            claim_invoice_id : this.editing_item.claim_invoice_id
                        };
                        this.transmitEditItem( data );
                        this.editing_claim_item = false;
                    })
                    .catch( err => {

                        console.error( err );
                    })
                    .finally( () => {

                        this.process_loading = false;
                    });
            },
            editItem( state, item ){

                this.editing_claim_item = state;
                this.editing_item = item;

                this.editable_rows.forEach( row => {
                    // set the value for the editing object

                    if( row.claimable ) row.value = this.editing_item.claimable[ row.name ];
                    else row.value = this.editing_item[ row.name ];
                });
            }
        },

        watch : {

            'claim.id' : function( oldVal, newVal ){
                // everytime another claim is chosen for editing/viewing

                this.claim_details = {

                    client_first_name               : this.claim.client_first_name,
                    client_last_name                : this.claim.client_last_name,
                    payer_code                      : this.claim.payer_code,
                    payer_name                      : this.claim.payer_name,
                    client_medicaid_id              : this.claim.client_medicaid_id,
                    client_medicaid_diagnosis_codes : this.claim.client_medicaid_diagnosis_codes,
                    plan_code                       : this.claim.plan_code,
                    transmission_method             : this.claim.transmission_method,
                }
            }
        },

        created() {

            if( this.claim ){
                // initial load

                this.claim_details = {

                    client_first_name               : this.claim.client_first_name,
                    client_last_name                : this.claim.client_last_name,
                    payer_code                      : this.claim.payer_code,
                    payer_name                      : this.claim.payer_name,
                    client_medicaid_id              : this.claim.client_medicaid_id,
                    client_medicaid_diagnosis_codes : this.claim.client_medicaid_diagnosis_codes,
                    plan_code                       : this.claim.plan_code,
                    transmission_method             : this.claim.transmission_method,
                }
            }
        },
    }
</script>

<style scoped>

    td {

        vertical-align: middle;
    }
    ul {

        padding-inline-start: 1.5rem;
        margin-bottom: 0px;
    }

    /* this can probably be moved to a more global palce for reusability */
    /* Enter and leave animations can use different */
    /* durations and timing functions.              */
    .slide-fade-enter-active {

        transition: all .3s ease;
    }
    .slide-fade-leave-active {

        transition: all .4s cubic-bezier(1.0, 0.5, 0.8, 1.0);
    }
    .slide-fade-enter, .slide-fade-leave-to
    /* .slide-fade-leave-active below version 2.1.8 */ {

        transform: translateX( 10px );
        opacity: 0;
    }
</style>