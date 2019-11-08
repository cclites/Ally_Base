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

            <b-form-group label="Business" label-for="business_id" class="mr-2">
                <b-form-select id="business_id"
                               v-model="form.business_id"
                >
                    <option value="">All Businesses</option>
                    <option v-for="business in businesses" :value="business.id" :key="business.id">{{ business.name }}</option>
                </b-form-select>
            </b-form-group>

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

            <b-form-group label="Clients" label-for="client_id" class="mr-2">
                <b-form-select
                        id="client_id"
                        name="client_id"
                        v-model="form.client_id"
                >
                    <option value="">All Clients</option>
                    <option v-for="client in clients" :value="client.id">{{ client.nameLastFirst }}</option>
                </b-form-select>
            </b-form-group>

            <b-form-group label="&nbsp;" class="mr-2 mt-1">
                <b-btn variant="info" @click="generate()" :disabled="disableGenerate">Generate Preview</b-btn>
            </b-form-group>

        </b-row>

        <div class="d-flex justify-content-center" v-if="busy">
            <div class="my-5">
                <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
            </div>
        </div>
    </b-card>
</template>

<script>
    export default {
        name: "Admin1099PreviewReport",
        props: {},
        data() {
            return {
                form: new Form({
                        business_id: '',
                        client_id: '',
                        caregiver_id: '',
                        year: '2017',
                        json: 1,
                }),
                businesses: [],
                caregivers: [],
                clients: [],
                end: moment().year(),
                busy: false
            }
        },
        methods: {
            async loadFilters() {
                axios.get('/admin/businesses').then(response => this.businesses = response.data);
                axios.get('/admin/caregivers').then(response => this.caregivers = response.data);
                axios.get('/admin/clients').then(response => this.clients = response.data);
            },

            generate(){

            },
        },
        watch: {
            'form.businessId'(newVal, oldVal){

                /*
                if(newVal !== oldval){
                    axios.get('/admin/clients?json=1&id=' + this.form.business_id).then(response => this.clients = response.data);
                    axios.get('/admin/caregivers?json=1&id=' + this.form.business_id).then(response => this.clients = response.data);
                }*/

            },

            'form.caregiverId'(newVal, oldVal){},

            'form.clientId'(newVal, oldVal){},
        },
        computed: {
            years(){
                let x = [];
                var i = this.form.year; while( i <= this.end){
                    x.push(i++);
                };
                return x;
            },

            disableGenerate(){
                if(this.businesses.length && this.clients.length && this.caregivers.length){
                    return false;
                }
                return true;
            }
        },
        async mounted(){
            this.loadFilters();
        }
    }
</script>

<style scoped>

</style>