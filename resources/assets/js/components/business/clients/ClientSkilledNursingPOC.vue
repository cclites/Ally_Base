<template>
    <b-card header="Skilled Nursing Plan Of Care"
            header-text-variant="white"
            header-bg-variant="info"
            class="client-poc"
    >

        <b-row>

            <!--b-col lg="4">
                <b-form-group label="Select A Client To Auto-Populate Fields" label-for="provider_number" class="mb-2 mr-2">
                    <b-select v-model="client">
                        <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.nameLastFirst }}</option>
                    </b-select>
                </b-form-group>
            </b-col-->
            <b-col>
                <b-form-group class="float-lg-right action-buttons">
                    <b-btn @click="print()" variant="primary" class="text-right"><i class="fa fa-print"></i> Print</b-btn>
                    <b-btn variant="success" @click.prevent="save()" :disabled="busy" class="text-right mr-2">Save Changes</b-btn>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>

            <b-col lg="2">
                <b-form-group label="Certification Start Date">
                    <date-picker v-model="form.certification_start" name="certification_start" class=""></date-picker>
                </b-form-group>
            </b-col>

            <b-col lg="2">
                <b-form-group label="Certification End Date" class="mb-2 mr-2">
                    <date-picker v-model="form.certification_end" name="certification_end" class=""></date-picker>
                </b-form-group>
            </b-col>

            <b-col lg="2">
                <b-form-group label="Medical Record No."class="mb-2 mr-2">
                    <b-form-input
                            id="medical_record_number"
                            name="medical_record_number"
                            type="text"
                            v-model="form.medical_record_number"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>

            <b-col lg="2">
                <b-form-group label="Provider No." class="mb-2 mr-2">
                    <b-form-input
                            id="provider_number"
                            name="provider_number"
                            type="text"
                            v-model="form.provider_number"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>
            <div class="h5 pl-3 pt-2">Medications</div>
        </b-row>

        <b-row>
            <b-col>
                <b-form-group label="Medications: Dose/Frequency/Route (N)ew (C)hanged" class="mb-2 mr-2">
                    <b-form-textarea v-model="form.medications" rows="4">
                    </b-form-textarea>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>
            <div class="h5 pl-3 pt-2">Principal Diagnosis</div>
        </b-row>

        <b-row>
            <b-col lg="4">
                <b-form-group label="Principal Diagnosis ICD-CM" class="mb-2 mr-2">
                    <b-form-input
                            id="principal_diagnosis_icd_cm"
                            name="principal_diagnosis_icd_cm"
                            type="text"
                            v-model="form.principal_diagnosis_icd_cm"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>

            <b-col lg="4">
                <b-form-group label="Principal Diagnosis" class="mb-2 mr-2">
                    <b-form-input
                            id="principal_diagnosis"
                            name="principal_diagnosis"
                            type="text"
                            v-model="form.principal_diagnosis"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>

            <b-col lg="4">
                <b-form-group label="Date of Principal Diagnosis" class="mb-2 mr-2">
                    <date-picker v-model="form.principal_diagnosis_date" name="principal_diagnosis_date"></date-picker>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>
            <div class="h5 pl-3 pt-2">Surgical Procedure</div>
        </b-row>

        <b-row>
            <b-col lg="4">
                <b-form-group label="Surgical Procedure ICD-CM" class="mb-2 mr-2">
                    <b-form-input
                            id="surgical_diagnosis_icd_cm"
                            name="surgical_diagnosis_icd_cm"
                            type="text"
                            v-model="form.surgical_diagnosis_icd_cm"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>

            <b-col lg="4">
                <b-form-group label="Surgical Procedure" class="mb-2 mr-2">
                    <b-form-input
                            id="surgical_diagnosis"
                            name="surgical_diagnosis"
                            type="text"
                            v-model="form.surgical_diagnosis"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>

            <b-col lg="4">
                <b-form-group label="Date of Surgical Procedure" class="mb-2 mr-2">
                    <date-picker v-model="form.surgical_diagnosis_date" name="surgical_diagnosis_date"></date-picker>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>
            <div class="h5 pl-3 pt-2">Other Diagnoses</div>
        </b-row>

        <b-row>
            <b-col lg="4">
                <b-form-group label="Other Pertinent Diagnoses ICD-CM" class="mb-2 mr-2">
                    <b-form-input
                            id="other_diagnosis_icd_cm"
                            name="other_diagnosis_icd_cm"
                            type="text"
                            v-model="form.other_diagnosis_icd_cm"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>

            <b-col lg="4">
                <b-form-group label="Other Pertinent Diagnoses" class="mb-2 mr-2">
                    <b-form-input
                            id="other_diagnosis"
                            name="other_diagnosis"
                            type="text"
                            v-model="form.other_diagnosis"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>

            <b-col lg="4">
                <b-form-group label="Date of Other Pertinent Diagnoses" class="mb-2 mr-2">
                    <date-picker v-model="form.other_diagnosis_date" name="other_diagnosis_date"></date-picker>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>
            <div class="h5 pl-3 pt-2">Additional</div>
        </b-row>

        <b-row>
            <b-col lg="6">
                <b-form-group label="DME and Supplies" class="mb-2 mr-2">
                    <b-form-input
                            id="dme_and_supplies"
                            name="dme_and_supplies"
                            type="text"
                            v-model="form.dme_and_supplies"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <b-form-group label="Safety Measures" class="mb-2 mr-2">
                    <b-form-input
                            id="safety_measures"
                            name="safety_measures"
                            type="text"
                            v-model="form.safety_measures"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>
            <b-col lg="6">
                <b-form-group label="Nutritional Requirements" class="mb-2 mr-2">
                    <b-form-input
                            id="nutritional_req"
                            name="nutritional_req"
                            type="text"
                            v-model="form.nutritional_req"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <b-form-group label="Allergies" class="mb-2 mr-2">
                    <b-form-input
                            id="allergies"
                            name="allergies"
                            type="text"
                            v-model="form.allergies"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>
            <b-col>
                <b-form-group label="Functional Limitations" class="mb-2 mr-2">

                    <b-form-checkbox-group id="functional" v-model="form.functional">
                        <b-form-checkbox v-for="(label, key) in options.functional" :key="key" :value="key">{{ label }}</b-form-checkbox>
                    </b-form-checkbox-group>

                    <b-form-input
                            type="text"
                            v-model="form.functional_other"
                            placeholder="Other functional limitations"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>
            <b-col>
                <b-form-group label="Activities Permitted" class="mb-2 mr-2">
                    <b-form-checkbox-group  v-model="form.mobility">
                        <b-form-checkbox v-for="(label, key) in options.mobility" :key="key" :value="key">{{ label }}</b-form-checkbox>
                    </b-form-checkbox-group>
                    <b-form-input
                            type="text"
                            v-model="form.mobility_instructions"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>
            <b-col>
                <b-form-group label="Mental Status" class="mb-2 mr-2">
                    <b-form-checkbox-group v-model="form.mental_status">
                        <b-form-checkbox v-for="(label, key) in options.mental_status" :key="key" :value="key">{{ label }}</b-form-checkbox>
                    </b-form-checkbox-group>
                </b-form-group>

            </b-col>
        </b-row>

        <b-row>
            <b-col>
                <b-form-group label="Prognosis" class="mb-2 mr-2">
                    <b-form-radio-group v-model="form.prognosis">
                        <b-form-radio v-for="(label, key) in options.prognosis" :key="key" :value="key">{{ label }}</b-form-radio>
                    </b-form-radio-group>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>
            <b-col>
                <b-form-group label="Orders for Discipline and Treatments (Specify Amount/Frequency/Duration)" class="mb-2 mr-2">
                    <b-form-textarea v-model="form.orders" rows="10">
                    </b-form-textarea>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>
            <b-col>
                <b-form-group label="Goals/Rehabilitation Potential/Discharge Plans" class="mb-2 mr-2">
                    <b-form-textarea v-model="form.goals" rows="4">
                    </b-form-textarea>
                </b-form-group>
            </b-col>
        </b-row>

        <b-row>
            <div class="h5 pl-3 pt-2">Physician's Information</div>
        </b-row>

        <b-row>
            <b-col lg="3">
                <b-form-group label="Physician's Name" class="mb-2 mr-2">
                    <b-form-input
                            id="physician_name"
                            name="physician_name"
                            type="text"
                            v-model="form.physician_name"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <b-form-group label="Physician's Address" class="mb-2 mr-2">
                    <b-form-input
                            id="physician_address"
                            name="physician_address"
                            type="text"
                            v-model="form.physician_address"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>

            <b-col lg="2">
                <b-form-group label="Physician's Phone" class="mb-2 mr-2">
                    <b-form-input
                            class="mt-1"
                            id="physician_phone"
                            name="physician_phone"
                            type="text"
                            v-model="form.physician_phone"
                    >
                    </b-form-input>
                </b-form-group>
            </b-col>
        </b-row>
    </b-card>
</template>

<script>

    import States from "../../../classes/States";
    import FormatsListData from "../../../mixins/FormatsListData";

    export default {

        mixins: [FormatsListData],

        props: {
            client: {
                type: Object,
                required: true,
            },
        },

        data() {
            return {
                busy: false,
                form: new Form({
                    certification_start: '',
                    certification_end: '',
                    medical_record_number: '',
                    provider_number: '',
                    medications: '',
                    principal_diagnosis_icd_cd: '',
                    principal_diagnosis: '',
                    principal_diagnosis_date: '',
                    surgical_procedure_icd_cd: '',
                    surgical_procedure: '',
                    surgical_procedure_date: '',
                    other_diagnosis_icd_cd: '',
                    other_diagnosis: '',
                    other_diagnosis_date: '',
                    dme_and_supplies: '',
                    safety_measures: '',
                    nutritional_req: '',
                    allergies: '',
                    functional: [],
                    functional_other: '',
                    mobility: [],
                    competency_level: [],
                    prognosis: '',
                    orders: '',
                    goals: '',
                    physician_name: '',
                    physician_address: '',
                    physician_phone: '',
                    mental_status: [],
                }),
                options: {
                    functional: {
                        amputation: 'Amputation',
                        contracture: "Contracture",
                        paralysis: "Paralysis",
                        endurance: "Endurance",
                        ambulation: "Ambulation",
                        speech: "Speech",
                        dyspnea: 'Dyspnea with Minimal Exertion'
                    },
                    mobility: {
                        complete_bedrest: "Complete Bedrest",
                        bedrest_brp: "Bedrest BRP",
                        up_as_tolerated: "Up As Tolerated",
                        assist_transfers: "Transfer Bed/Chair",
                        exercises_prescribed: "Exercises Prescribed",
                        partial_weight_bearing: "Partial Weight Bearing",
                        independent_at_home: "Independent At Home",
                        crutches: "Crutches",
                        cane: "Cane",
                        wheelchair: "Wheelchair",
                        walker: "Walker",
                    },

                    prognosis: {
                        poor: "Poor",
                        guarded: "Guarded",
                        fair: "Fair",
                        good: "Good",
                        excellent: "Excellent",
                    },
                    mental_status: {
                        oriented: "Oriented",
                        comatose: "Comatose",
                        forgetful: "Forgetful",
                        depressed: "Depressed",
                        lethargic: "Lethargic",
                        agitated: "Agitated",
                        other: "Other",
                    }
                },
                states: new States(),
                clients: [],
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
                this.fillForm(JSON.parse(JSON.stringify(this.client.skilled_nursing_poc)));
                return;
            }

        },
    }
</script>

<style lang="scss">
    .client-poc {
        .form-group legend { font-weight: 600; }
        label.custom-radio { align-items: center; }
    }

</style>