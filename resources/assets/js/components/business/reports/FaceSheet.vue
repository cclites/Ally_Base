<template>
    <b-card header="Face Sheet Report"
            header-text-variant="white"
            header-bg-variant="info"
    >

        <b-row>
            <business-location-form-group
                    v-model="businesses"
                    label="Location"
                    class="mr-2 mt-1"
                    :allow-all="false"
            />
            <b-form-group v-if="role === 'client'" label="Clients" class="mr-2 mt-1">
                <b-form-select v-model="client_id" :disabled="loadingClients">
                    <option v-if="loadingClients" selected value="">Loading Clients...</option>
                    <option v-else value="">-- Select Client --</option>
                    <option v-for="item in clients" :key="item.id" :value="item.id">{{ item.nameLastFirst }}
                    </option>
                </b-form-select>
            </b-form-group>

            <b-form-group v-if="role === 'caregiver'" label="Caregivers" class="mr-2 mt-1">
                <b-form-select v-model="caregiver_id" :disabled="loadingCaregivers">
                    <option v-if="loadingCaregivers" selected value="">Loading Caregivers...</option>
                    <option v-else value="">-- Select Caregiver --</option>
                    <option v-for="item in caregivers" :key="item.id" :value="item.id">{{ item.nameLastFirst }}
                    </option>
                </b-form-select>
            </b-form-group>

            <b-form-group label="&nbsp;" class="mr-2 mt-1">
                <b-button-group>
                    <b-btn variant="info" class="btn" :disabled="busy" @click.prevent="generateFaceSheet()">Generate</b-btn>
                </b-button-group>
            </b-form-group>
        </b-row>

    </b-card>
</template>

<script>
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";

    export default {
        name: "FaceSheet",
        components: {BusinessLocationFormGroup},
        props: {
            role: {type: String, default: ''},
        },
        data() {
            return {
                businesses: '',
                client_id: '',
                caregiver_id: '',
                caregivers: [],
                clients: [],
                loadingClients: false,
                loadingCaregivers: false,
                busy: false
            };
        },
        mounted() {

            if(this.role == 'client'){
                this.fetchClients();
            }

            if(this.role == 'caregiver'){
                this.fetchCaregivers();
            }
        },
        methods: {
            generateFaceSheet(){

                let url = `/business/reports/face-sheet/${this.role}s/print/`;

                if(this.role == 'client'){
                    url += this.client_id;
                }
                else{
                    url += this.caregiver_id;
                }

                window.location = url;
            },

            async fetchClients(){

                this.loadingClients = true;
                this.clients = [];

                await axios.get(this.url)
                    .then( ({ data }) => {
                    this.clients = data;
                })
                    .catch(() => {
                        this.clients = [];
                    })
                    .finally(() => {
                        this.loadingClients = false;
                    });
            },

            async fetchCaregivers(){

                this.loadingCaregivers = true;
                this.caregivers = [];

                await axios.get(this.url)
                    .then( ({ data }) => {
                        this.caregivers = data;
                    })
                    .catch(() => {
                        this.caregivers = [];
                    })
                    .finally(() => {
                        this.loadingCaregivers = false;
                    });
            },
        },
        computed: {
            roleType() {
               return _.startCase(this.role) + 's';
            },

            url(){
                return `/business/dropdown/${this.role}s?businesses=${this.businesses}`
            }
        }

    }
</script>

<style scoped>

</style>