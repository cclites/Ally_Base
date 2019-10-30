<template>

    <b-card
        header="Adjustments Import"
        header-text-variant="white"
        header-bg-variant="info"
    >

        <transition mode="out-in" name="slide-fade">

            <b-row v-if=" results.length < 1 " key="hey">

                <b-col lg="6">

                    <form @submit.prevent=" submit() " enctype="multipart/form-data" @keydown=" form.clearError( $event.target.name ) ">

                        <b-form-group label="Business" label-for="business_id">

                            <b-form-select id="business_id" v-model=" form.business_id ">

                                <option value="">--Select Business--</option>
                                <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                            </b-form-select>
                            <input-help :form="form" field="business_id" text="Select a business" />
                        </b-form-group>

                        <b-form-group label="Transaction Type" label-for="type">

                            <b-form-select id="type" v-model=" form.type ">

                                <option value="caregiver">Caregiver Manual Deposit Adjustment</option>
                            </b-form-select>
                            <input-help :form=" form " field="type" text="Marks that this will be creating a Deposit Invoice, not a Client Invoice" />
                        </b-form-group>

                        <b-form-group label="Notes" label-for="notes">

                            <b-textarea id="notes" :rows="3" v-model=" form.notes " />
                            <input-help :form="form" field="notes" text="Enter a note for these adjustments" />
                        </b-form-group>

                        <div class="form-group">

                            <label for="file">Import File: </label>
                            <input type="file" id="file" @change=" form.file = $event.target.files[ 0 ] " ref="fileInput">
                        </div>

                        <b-btn type="submit" :disabled=" submitting ">Submit</b-btn>
                    </form>
                </b-col>
            </b-row>
            <b-row v-else key="yoo">

                <b-col class="d-flex justify-content-end mb-3 flex-wrap">

                    <b-button variant="default" class="mr-2" @click=" resetForm() ">Reset Form</b-button>
                    <b-button variant="info" @click=" createDeposits() ">Create Deposit Adjustments</b-button>
                </b-col>

                <b-col lg="12" v-for="( res, i ) in results" :key=" i ">

                    <hr />
                    <b-row class="align-items-center">

                        <b-col sm="12" md="3">

                            Found in Rows: {{ res.rows }}
                        </b-col>
                        <b-col sm="12" md="3">

                            <b-form-group label="Caregiver" label-for="caregiver_id">

                                <b-form-select id="caregiver_id" v-model=" results[ i ].caregiver_id ">

                                    <option value="">--Unmatched Caregiver--</option>
                                    <option v-for=" caregiver in caregivers" :value=" caregiver.id " :key=" caregiver.id ">{{ caregiver.nameLastFirst }}</option>
                                </b-form-select>
                                <input-help :form="form" field="caregiver_id" text="Match record to a Caregiver" />
                            </b-form-group>
                        </b-col>
                        <b-col sm="12" md="3">

                            {{ res.name }}
                        </b-col>
                        <b-col sm="12" md="3">

                            <b-form-group label="Amount" label-for="amount">

                                <b-form-input type="number"
                                    id="amount"
                                    v-model=" res.amount "
                                    step="any"
                                />
                                <input-help :form="form" field="amount" text="Enter the transaction amount" />
                            </b-form-group>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
        </transition>
    </b-card>
</template>

<script>

    import FormDataForm from '../../classes/FormDataForm';

    export default {

        props: {},

        data() {

            return {

                businesses : [],
                caregivers : [],
                form       : new Form(),
                submitting : false,
                results    : []
            }
        },

        computed: {

        },

        mounted() {

            this.makeForm();
            this.loadBusinesses();
        },

        methods: {

            async createDeposits(){

                if( this.results.some( value => !value.caregiver_id ) ) alert( 'all values must be mapped to a caregiver' );
                else {

                    this.submitting = true;
                    const form = new Form({

                        invoices : this.results
                    });

                    try {

                        const response = await form.post( '/admin/deposits/finalize-import' );
                        this.resetForm();
                        this.submitting = false;
                    }
                    catch ( err ) {

                        console.error( err );
                        this.submitting = false;
                    }
                }
            },
            resetForm(){

                this.results = [];
                this.makeForm();
            },
            makeForm() {

                this.form = new Form({

                    business_id  : "",
                    type         : "caregiver",
                    notes        : ""
                });

                const input = this.$refs.fileInput;
                if( input ){

                    input.type = 'text';
                    input.type = 'file';
                }
            },

            loadBusinesses() {

                axios.get( '/admin/businesses?json=1' ).then( response => this.businesses = response.data );
            },
            submit() {

                if ( this.submitting ) return; // debounce
                this.submitting = true;

                const notes = _.cloneDeep( this.form.notes ); // save for later

                let formData = new FormData();
                formData.append( 'file', this.form.file );
                formData.append( 'type', this.form.type );
                formData.append( 'notes', this.form.notes );
                formData.append( 'business_id', this.form.business_id );
                this.form = new FormDataForm( formData );

                this.form.setOptions({

                    headers: {

                        'Content-Type': 'multipart/form-data'
                    }
                });

                this.form.post( '/admin/deposits/import' )
                    .then( response => {


                        this.results = Object.values( response.data ).filter( res => res.name != null ).map( res => {

                            let caregiver_id = '';

                            for( let i = 0; i < this.caregivers.length; i++ ){

                                if( ( this.caregivers[ i ].lastname + ', ' + this.caregivers[ i ].firstname ) == res.name ){

                                    caregiver_id = this.caregivers[ i ].id;
                                    break;
                                }
                            }

                            return {

                                caregiver_id : caregiver_id,
                                name         : res.name,
                                amount       : res.amount,
                                rows         : res.rows,
                                notes
                            }
                        });
                    })
                    .catch( error => {

                    })
                    .finally( () => {

                        this.makeForm();
                        this.submitting = false;
                    });
            },


        },

        watch: {

            "form.business_id" () {

                if( this.form.business_id ){

                    axios.get( '/admin/caregivers?json=1&id=' + this.form.business_id ).then( response => this.caregivers = response.data );
                }
            },
        }
    }
</script>

<style>

</style>