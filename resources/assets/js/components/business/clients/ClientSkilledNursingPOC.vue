<template>
    <b-card class="nursing-poc">
        <h2>Skilled Nursing POC</h2>

        <b-btn variant="success" @click.prevent="save()" :disabled="busy">Save Changes</b-btn>
        <b-btn @click="print()" variant="primary" class="float-right"><i class="fa fa-print"></i> Print</b-btn>
    </b-card>
</template>

<script>
    export default {
        props: {
            client: {
                type: Object,
                required: true,
            },
        },

        data() {
            return {
                busy: false,
                form: {},
            }
        },

        computed: {
            url() {
                return `/business/clients/${this.client.id}/skilled-nursing-poc`;
            },
        },

        methods: {
            save() {
                this.busy = true;
                this.form.post(this.url)
                    .then( ({ data }) => {
                        this.fillForm(data.data);
                        this.busy = false;
                    })
                    .catch(e => {
                        this.busy = false;
                    })
            },

            fillForm(data) {
                this.form = new Form(data);
            },

            print(){
                $('.nursing-poc').print();
            },
        },

        mounted() {
            if (this.client.skilled_nursing_poc) {
                //this.fillForm(JSON.parse(JSON.stringify(this.client.skilled_nursing_poc)));
                //return;
            }

            this.fillForm({
                claim_number: '',
                start_of_care: '',
                certification_start: '',
                certification_end: '',
                medical_record_number: '',
                provider_number: '',
                client: '',
                registry: '',
                dob: '',
                sex: '',
                medications: '',
                principal_diagnosis_icd_cm: '',
                principal_diagnosis: '',
                principal_diagnosis_date: '',
                surgical_icd_cm: '',
                surgical: '',
                surgical_date: '',
                other_diagnosis_icd_cm: '',
                other_diagnosis: '',
                other_diagnosis_date: '',
                dme_and_supplies: '',
                safety_measures: '',
                nutritional_req: '',
                allergies: '',
                functional_limitations: '',
                activities_permitted: '',
                mental_status: '',
                prognosis: '',
                orders: '',
                goals: '',

            });
        },
    }
</script>

<style lang="scss">
    .client-care-needs {
        .form-group legend { font-weight: 600; }
        label.custom-radio { align-items: center; }
    }
</style>