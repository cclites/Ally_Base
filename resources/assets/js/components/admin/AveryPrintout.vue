<template>

    <b-card>

        <b-row>

            <b-col lg="12">

                <b-card header="Filters" header-text-variant="white" header-bg-variant="info">

                    <b-form inline @submit.prevent=" loadLabels() ">

                        <b-row class="d-flex w-100">

                            <b-col class="f-1">

                                <b-form-group label="Business Chains" class="w-100">

                                    <b-form-select id="chain_id" name="chain_id" v-model="chain_id" class="w-100">

                                        <option value>All Business Chains</option>
                                        <option v-for="chain in chains" :value="chain.id" :key=" chain.id ">{{ chain.name }}</option>
                                    </b-form-select>
                                </b-form-group>
                            </b-col>
                            <b-col class="f-1">

                                <b-form-group label="Start Date" class="w-100">

                                    <date-picker v-model=" start_date " class="w-100"></date-picker>
                                </b-form-group>
                            </b-col>
                            <b-col class="f-1">

                                <b-form-group label="End Date" class="w-100">

                                    <date-picker v-model=" end_date " class="w-100"></date-picker>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row class="d-flex w-100 mt-3">

                            <b-col class="f-1">

                                <b-form-group label="Status" class="w-100">

                                    <b-form-select name="Status" v-model=" status " class="w-100">

                                        <option value="all">All</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </b-form-select>
                                </b-form-group>
                            </b-col>
                            <b-col class="f-1">

                                <b-form-group label="Clients" class="w-100">

                                    <b-form-select name="client_id" v-model=" client_id " class="w-100">

                                        <option value>All Clients</option>
                                        <option
                                            v-for="row in clients"
                                            :value="row.id"
                                            :key="row.id"
                                            :text="row.name"
                                        >{{ row.nameLastFirst }}</option>
                                    </b-form-select>
                                </b-form-group>
                            </b-col>
                            <b-col class="f-1">

                                <b-form-group label="Caregivers" class="w-100">

                                    <b-form-select name="caregiver_id" v-model=" caregiver_id " class="w-100">

                                        <option value>All Caregivers</option>
                                        <option
                                            v-for="row in caregivers"
                                            :value="row.id"
                                            :key="row.id"
                                            :text="row.name"
                                        >{{ row.nameLastFirst }}</option>
                                    </b-form-select>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row class="w-100 mt-5">

                            <b-col class="d-flex justify-content-end align-items-center">

                                <b-button type="submit" variant="info" :disabled=" loading ">Generate Report</b-button>
                            </b-col>
                        </b-row>
                    </b-form>
                </b-card>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>
    import FormatsDates from "../../mixins/FormatsDates";
    import FormatsNumbers from "../../mixins/FormatsNumbers";

    export default {

        mixins: [FormatsDates, FormatsNumbers],

        props: {

            chains: {

                type: Array
            }
        },
        data() {

            return {

                chain_id     : "",
                start_date   : moment().startOf("month").format("MM/DD/YYYY"),
                end_date     : moment().format("MM/DD/YYYY"),
                loading      : false,
                clients      : "",
                client_id    : "",
                caregivers   : "",
                caregiver_id : "",
                status       : "all"
            };
        },
        methods: {

            async getClients() {

                axios.get( "/business/dropdown/clients-for-chain?chain=" + this.chain_id + "&active=" + this.status )
                    .then(({ data }) => {

                        this.clients = data;
                    })
                    .catch(e => {})
                    .finally(() => {});
            },
            async getCaregivers() {

                axios.get( "/business/dropdown/caregivers-for-chain?chain=" + this.chain_id + "&active=" + this.status )
                    .then(({ data }) => {

                        this.caregivers = data;
                    })
                    .catch(() => {})
                    .finally(() => {});
            },
            resetUsers(){

                this.clients   = [];
                this.client_id = "";

                this.caregivers   = [];
                this.caregiver_id = "";
            },
            loadLabels(){

                console.log( 'testing..' );

                // this.loaded = 0;
                // let url = '/admin/avery-printout/printLabels?json=1&start_date=' + this.start_date + '&end_date=' + this.end_date +
                //     '&chain_id=' + this.chain_id + '&paid=' + this.paid + '&client_id=' + this.client_id;
                // const response = await axios.get(url);
                // this.items = response.data.data.map(item => {
                //     item.chain_name = (item.client && item.client.business && item.client.business.chain) ? item.client.business.chain.name : "";

                //     let flags = [];
                //     if (item.client_on_hold) flags.push("On Hold");
                //     if (!item.payer_payment_type) flags.push("No Payment Method");
                //     if (item.payment_errors) flags.push( item.payment_errors );

                //     item.flags = flags.join(' | ');
                //     return item;
                // });
                // this.loaded = 1;
            }
        },
        watch: {

            async chain_id( newValue, oldValue ){

                this.resetUsers();

                if( newValue !== "" ) {

                    await this.getClients();
                    await this.getCaregivers();
                }
            },
            async status( newValue, oldValue ){

                if( this.chain_id !== "" ) {

                    this.resetUsers();

                    await this.getClients();
                    await this.getCaregivers();
                }
            },
        }
    }
</script>

<style scoped>
</style>