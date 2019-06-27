<template>
    <b-card>
        <h2>Skilled Nursing POC</h2>

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
                form: {},
                options: {
                    pets: {
                        cats: 'Cats',
                        dogs: 'Dogs',
                        birds: 'Birds'
                    },
                }
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
        },

        mounted() {
            if (this.client.skilled_nursing_poc) {
                this.fillForm(JSON.parse(JSON.stringify(this.client.skilled_nursing_poc)));
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
                comments: '',
                instructions: '',
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