<template>
    <b-card class="client-care-needs"
            header="Detailed Client Care Needs"
            header-text-variant="white"
            header-bg-variant="info"
    >

        <b-form-group class="pb-2">
            <b-btn @click="print()" variant="primary" class="float-right"><i class="fa fa-print"></i> Print</b-btn>
        </b-form-group>

        <h2>
            General
        </h2>

        <b-row>
            <b-col lg="6">
            <b-form-group label="Height">
                <b-form-input v-model="form.height" placeholder="i.e.: 5 ft 10" />
            </b-form-group>
            </b-col>
            <b-col lg="6">
            <b-form-group label="Weight">
                <b-form-input v-model="form.weight" placeholder="i.e.: 160 lbs" />
            </b-form-group>
            </b-col>
        </b-row>

        <b-form-group label="Level of competency">
            <b-form-radio-group v-model="form.competency_level">
                <b-form-radio v-for="(label, key) in options.competency_level" :key="key" :value="key">{{ label }}</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <b-form-group label="Living Arrangements">
            <b-form-radio-group id="lives_alone" v-model="form.lives_alone">
                <b-form-radio value="1">Lives alone</b-form-radio>
                <b-form-radio value="0">Others living in the same location</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <checkbox-group label="Pets" v-model="form.pets" :items="options.pets" />

        <b-form-group label="Smoker">
            <b-form-radio-group id="smoker" v-model="form.smoker">
                <b-form-radio value="1">Yes</b-form-radio>
                <b-form-radio value="0">No</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <b-form-group label="Alcohol">
            <b-form-radio-group id="alcohol" v-model="form.alcohol">
                <b-form-radio value="1">Yes</b-form-radio>
                <b-form-radio value="0">No</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <b-form-group label="Has consumer ever been deemed incompetent by licensed professional">
            <b-form-radio-group id="incompetent" v-model="form.incompetent">
                <b-form-radio value="1">Yes</b-form-radio>
                <b-form-radio value="0">No</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <hr />

        <h2>Medication: </h2>
        <b-form-group label="Is consumer able to provide direction to the caregiver to assist consumer in taking medication?">
            <b-form-radio-group id="can_provide_direction" v-model="form.can_provide_direction">
                <b-form-radio value="1">Yes</b-form-radio>
                <b-form-radio value="0">No</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <b-form-group label="Self-Administered Medications">
            <b-form-radio-group id="assist_medications" v-model="form.assist_medications">
                <b-form-radio value="0">Remind / Prompt</b-form-radio>
                <b-form-radio value="1">Assist</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <b-form-group label="Who is responsible for overseeing medications and where they are located?">
            <b-form-textarea id="medication_overseer" v-model="form.medication_overseer" :rows="3" />
        </b-form-group>

        <b-form-group label="Allergies?">
            <b-form-textarea id="allergies" v-model="form.allergies" :rows="3" />
        </b-form-group>

        <b-form-group label="Pharmacy name">
            <b-form-input v-model="form.pharmacy_name" />
        </b-form-group>

        <b-form-group label="Pharmacy phone number">
            <mask-input v-model="form.pharmacy_number" name="pharmacy_number" />
        </b-form-group>

        <hr />

        <h2>Care Details: </h2>
        
        <checkbox-group label="Safety Measures" v-model="form.safety_measures" :items="options.safety_measures" />

        <b-form-group label="Special instructions:" class="ml-4">
            <b-form-textarea id="safety_instructions" v-model="form.safety_instructions" :rows="3" />
        </b-form-group>
        
        <checkbox-group label="Toileting" v-model="form.toileting" :items="options.toileting" />

        <b-form-group label="Special instructions:" class="ml-4">
            <b-form-textarea id="toileting_instructions" v-model="form.toileting_instructions" :rows="3" />
        </b-form-group>

        <checkbox-group label="Bathing" v-model="form.bathing" :items="options.bathing" />

        <b-form-group label="Frequency:" class="ml-4">
            <b-form-textarea id="bathing_frequency" v-model="form.bathing_frequency" :rows="3" />
        </b-form-group>

        <b-form-group label="Special instructions:" class="ml-4">
            <b-form-textarea id="bathing_instructions" v-model="form.bathing_instructions" :rows="3" />
        </b-form-group>

        <b-form-group label="Vision">
            <b-form-radio-group id="vision" v-model="form.vision">
                <b-form-radio v-for="(label, key) in options.vision" :key="key" :value="key">{{ label }}</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <b-form-group label="Hearing">
            <b-form-radio-group id="hearing" v-model="form.hearing">
                <b-form-radio v-for="(label, key) in options.hearing" :key="key" :value="key">{{ label }}</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <b-form-group label="Special instructions:" class="ml-4">
            <b-form-textarea id="hearing_instructions" v-model="form.hearing_instructions" :rows="3" />
        </b-form-group>

        <checkbox-group label="Diet" v-model="form.diet" :items="options.diet" />

        <b-form-group label="Special likes and dislikes:" class="ml-4">
            <b-form-textarea id="diet_likes" v-model="form.diet_likes" :rows="3" />
        </b-form-group>

        <b-form-group label="Feeding instructions:" class="ml-4">
            <b-form-textarea id="feeding_instructions" v-model="form.feeding_instructions" :rows="3" />
        </b-form-group>

        <checkbox-group label="Skin Care" v-model="form.skin" :items="options.skin" />

        <b-form-group label="List any skin conditions:" class="ml-4">
            <b-form-textarea id="skin_conditions" v-model="form.skin_conditions" :rows="3" />
        </b-form-group>

        <b-form-group label="Hair Care">
            <b-form-radio-group id="hair" v-model="form.hair">
                <b-form-radio v-for="(label, key) in options.hair" :key="key" :value="key">{{ label }}</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <b-form-group label="Frequency:" class="ml-4">
            <b-form-textarea id="hair_frequency" v-model="form.hair_frequency" :rows="3" />
        </b-form-group>

        <checkbox-group label="Oral Care" v-model="form.oral" :items="options.oral" />

        <b-form-group label="Shaving">
            <b-form-radio-group id="shaving" v-model="form.shaving">
                <b-form-radio v-for="(label, key) in options.shaving" :key="key" :value="key">{{ label }}</b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <b-form-group label="Special Instructions:" class="ml-4">
            <b-form-textarea id="shaving_instructions" v-model="form.shaving_instructions" :rows="3" />
        </b-form-group>

        <checkbox-group label="Nail Care" v-model="form.nails" :items="options.nails" />

        <checkbox-group label="Dressing" v-model="form.dressing" :items="options.dressing" />

        <b-form-group label="Special Instructions:" class="ml-4">
            <b-form-textarea id="dressing_instructions" v-model="form.dressing_instructions" :rows="3" />
        </b-form-group>

        <checkbox-group label="Housekeeping" v-model="form.housekeeping" :items="options.housekeeping" />

        <b-form-group label="Special Instructions:" class="ml-4">
            <b-form-textarea id="housekeeping_instructions" v-model="form.housekeeping_instructions" :rows="3" />
        </b-form-group>

        <checkbox-group label="Shopping / Errands" v-model="form.errands" :items="options.errands" />

        <checkbox-group label="Supplies Available" v-model="form.supplies" :items="options.supplies" />

        <b-form-group label="Special Instructions:" class="ml-4">
            <b-form-textarea id="supplies_instructions" v-model="form.supplies_instructions" :rows="3" />
        </b-form-group>

        <b-form-group label="Comments">
            <b-form-textarea id="comments" v-model="form.comments" :rows="3" />
        </b-form-group>

        <b-form-group label="Special Instructions">
            <b-form-textarea id="instructions" v-model="form.instructions" :rows="3" />
        </b-form-group>

        <b-btn variant="success" @click.prevent="save()" :disabled="busy">Save Changes</b-btn>
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
                form: new Form({}),
                options: {
                    pets: {
                        cats: 'Cats',
                        dogs: 'Dogs',
                        birds: 'Birds'
                    },
                    competency_level: {
                        alert: 'Alert',
                        forgetful: 'Forgetful',
                        confused: 'Confused',
                        other: 'Other',
                    },
                    safety_measures: {
                        can_leave_alone: 'Client may be left alone',
                        contact_guard: 'Contact guard',
                        gait_belt: 'Gait belt',
                        can_use_stairs: 'Client may use stairs',
                        stair_lift: 'Stair life',
                        other: 'Other'
                    },
                    toileting: {
                        continent: 'Continent',
                        catheter: 'Catheter',
                        bedpan: 'Bedpan',
                        incontinent: 'Incontinent',
                        colostomy: 'Colostomy',
                        urinal: 'Urinal',
                        adult_briefs: 'Adult briefs',
                        bathroom: 'Bathroom',
                        bedside_commode: 'Bedside commode',
                    },
                    bathing: {
                        partial: 'Partial',
                        shower: 'Shower',
                        shower_chair: 'Shower chair',
                        complete: 'Complete',
                        sponge_bath: 'Sponge bath',
                        bed_bath: 'Bed bath',
                        tub: 'Tub',
                        sink: 'Sink',
                    },
                    vision: {
                        right: 'R. Eye',
                        left: 'L. Eye',
                        glasses: 'Wears glasses',
                        normal: 'Normal vision',
                        peripheral: 'Peripheral only',
                        no_peripheral: 'No Peripheral Vision',
                        blind: 'Blind'
                    },
                    hearing: {
                        normal: 'Normal hearing',
                        hard: 'Hard of hearing',
                        hearing_aid: 'Wears hearing aid',
                        deaf: 'Deaf',
                    },
                    diet: {
                        normal: 'Normal',
                        liquid: 'Liquid only',
                        encourage_fluids: 'Encourage fluids',
                        lunch: 'Prepare & serve lunch',
                        diabetic: 'Diabetic',
                        assist_meals: 'Assist with meals',
                        limit_fluids: 'Limit fluids',
                        snacks: 'Prepare & serve snacks',
                        low_sodium: 'Low sodium',
                        assist_feeding: 'Feeding assistance',
                        breakfast: 'Prepare & serve breakfast',
                        dinner: 'Prepare & serve dinner',
                    },
                    skin: {
                        moisturizer: 'Moisturizer',
                        intact: 'Skin intact',
                        powder: 'Powder',
                        breakdown: 'Skin breakdown',
                        preventative: 'Preventative',
                    },
                    hair: {
                        dry: 'Wash and dry',
                        set: 'Wash and set',
                        brush: 'Comb and brush only',
                        hair_dresser: 'Hair dresser',
                    },
                    oral: {
                        brush: 'Brush and floss',
                        dentures: 'Denture care',
                    },
                    shaving: {
                        yes: 'Yes',
                        no: 'No',
                        self: 'Self',
                        assisted: 'Caregiver assistance',
                    },
                    nails: {
                        clean: 'Clean',
                        file: 'File and trim',
                        polish: 'Polish',
                    },
                    dressing: {
                        self: 'Self dress',
                        clothes: 'Help select clothes',
                        assist: 'Assist with dressing',
                    },
                    housekeeping: {
                        vacuuming: 'Vacuuming',
                        dusting: 'Dusting',
                        trash: 'Trash removal',
                        make_bed: 'Make bed',
                        bed_linens: 'Change bed linens',
                        laundry: 'Laundry',
                        clean_bathroom: 'Clean up bathroom after use',
                        bathroom_linens: 'Change bathroom linens',
                        dishes: 'Dishes',
                        clean_kitchen: 'Clean up kitchen after use',
                        mop: 'Damp mop floors',
                        other: 'Other',
                    },
                    errands: {
                        drives: 'Drives self',
                        authorized_take_out: 'Caregiver may take out',
                        call_take_out: 'Call before taking out',
                        has_waiver: 'Waiver of liability on file',
                        taxi: 'Accompany on taxi/bus',
                        caregiver_car: 'Run errands in caregiver\'s car',
                        client_car: 'Run errands in client\'s car',
                    },
                    supplies: {
                        gloves: 'Gloves',
                        sanitizer: 'Hand sanitizer',
                        caregiver: 'Caregiver must bring own',
                        other: 'Other',
                    },
                    mental_status: {
                        oriented: "Oriented",
                        comatose: "Comatose",
                        forgetful: "Forgetful",
                        depressed: "Depressed",
                        disoriented: "Disoriented",
                        lethargic: "Lethargic",
                        agitated: "Agitated",
                        other: "Other",
                    }
                }
            }
        },

        computed: {
            url() {
                return `/business/clients/${this.client.id}/care-details`;
            },
        },
        
        methods: {
            save() {
                this.busy = true;
                this.form.post(this.url)
                    .then( ({ data }) => {
                        window.location.reload();
                    })
                    .catch(e => {
                        this.busy = false;
                    })
            },

            fillForm(data) {
                this.form = new Form(data);
            },

            print(){
                window.location = this.url + '/pdf';
            },
        },

        mounted() {
            if (this.client.care_details) {
                this.fillForm(JSON.parse(JSON.stringify(this.client.care_details)));
                return;
            }

            this.fillForm({
                height: '',
                weight: '',
                lives_alone: '',
                pets: [],
                smoker: '',
                alcohol: '',
                incompetent: '',
                competency_level: '',
                can_provide_direction: '',
                assist_medications: '',
                medication_overseer: '',
                allergies: '',
                pharmacy_name: '',
                pharmacy_number: '',
                safety_measures: [],
                safety_instructions: '',
                mobility: [],
                mobility_instructions: '',
                mobility_other: '',
                toileting: [],
                toileting_instructions: '',
                bathing: [],
                bathing_frequency: '',
                bathing_instructions: '',
                vision: '',
                hearing: '',
                hearing_instructions: '',
                diet: [],
                diet_likes: '',
                feeding_instructions: '',
                skin: [],
                skin_conditions: '',
                hair: '',
                hair_frequency: '',
                oral: [],
                shaving: '',
                shaving_instructions: '',
                nails: [],
                dressing: [],
                dressing_instructions: '',
                housekeeping: [],
                housekeeping_instructions: '',
                errands: [],
                supplies: [],
                supplies_instructions: '',

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