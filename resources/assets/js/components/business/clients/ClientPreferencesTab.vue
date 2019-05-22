<template>
    <b-card
        header="Client CareMatch Preferences"
        header-text-variant="white"
        header-bg-variant="info"
        class="pb-3"
    >
        <b-row>
            <b-col lg="6">
                <b-form-group label="Caregiver Gender" label-for="gender">
                    <b-form-select v-model="form.gender">
                        <option :value="null">No Preference</option>
                        <option value="F">Female</option>
                        <option value="M">Male</option>
                    </b-form-select>
                    <input-help :form="form" field="gender" />
                </b-form-group>
                <b-form-group label="Caregiver License/Certification" label-for="license">
                    <b-form-select id="license" v-model="form.license">
                        <option :value="null">No Preference</option>
                        <option value="CNA">CNA</option>
                        <option value="HHA">HHA</option>
                        <option value="RN">RN</option>
                        <option value="LPN">LPN</option>
                    </b-form-select>
                    <input-help :form="form" field="license" />
                </b-form-group>
                <b-form-group label="Caregiver's Spoken Language" label-for="language">
                    <b-form-select id="language"
                                   v-model="form.language"
                    >
                        <option :value="null">No Preference</option>
                        <option value="en">English</option>
                        <option value="es">Spanish</option>
                        <option value="fr">French</option>
                        <option value="de">German</option>
                    </b-form-select>
                    <input-help :form="form" field="language" />
                </b-form-group>
                <b-form-group label="Caregiver Ethnicity">
                    <b-form-checkbox v-for="item in ethnicityOptions"
                        :key="item.value"
                        v-model="form.ethnicities"
                        :value="item.value"
                        unchecked-value="null"
                    >
                        {{ item.text }}
                    </b-form-checkbox>
                    <input-help :form="form" field="ethnicities" />
                </b-form-group>
            </b-col>
            <b-col lg="6">
                <b-form-group label="Preferred Hospital">
                    <b-form-input id="hospital_name"
                                  v-model="form.hospital_name"></b-form-input>
                </b-form-group>
                <b-form-group label="Hospital Phone Number">
                    <b-form-input id="hospital_number"
                                  v-model="form.hospital_number"></b-form-input>
                </b-form-group>
                <b-form-group label="Does the client smoke?" label-for="smokes">
                    <b-form-select id="smokes"
                                   v-model="form.smokes"
                    >
                        <option :value="1">Yes</option>
                        <option :value="0">No</option>
                    </b-form-select>
                    <input-help :form="form" field="smokes" />
                </b-form-group>
                <b-form-group label="Does this client have pets?">
                    <b-form-checkbox v-model="form.pets_dogs" value="1" unchecked-value="0">Dogs</b-form-checkbox>
                    <b-form-checkbox v-model="form.pets_cats" value="1" unchecked-value="0">Cats</b-form-checkbox>
                    <b-form-checkbox v-model="form.pets_birds" value="1" unchecked-value="0">Birds</b-form-checkbox>
                </b-form-group>
            </b-col>
        </b-row>

        <b-btn @click="save()" variant="success">Save Client Preferences</b-btn>
    </b-card>
</template>

<script>
    import Constants from "../../../mixins/Constants";

    export default {
        props: {
            'client': {},
        },

        mixins: [ Constants ],

        data() {
            return {
                loading: false,
                busy: false,
                form: new Form({
                    gender: this.client.preferences ? this.client.preferences.gender : null,
                    license: this.client.preferences ? this.client.preferences.license : null,
                    language: this.client.preferences ? this.client.preferences.language : null,
                    smokes: this.client.preferences ? this.client.preferences.smokes : 0,
                    pets_dogs: this.client.preferences ? this.client.preferences.pets_dogs : 0,
                    pets_cats: this.client.preferences ? this.client.preferences.pets_cats : 0,
                    pets_birds: this.client.preferences ? this.client.preferences.pets_birds : 0,
                    ethnicities: this.client.preferences ? this.client.preferences.ethnicities.map(x => x.ethnicity) : [],
                }),
            }
        },

        computed: {
        },

        methods: {
            save() {
                this.form.patch(`/business/clients/${this.client.id}/preferences`)
                    .then( ({ data }) => {
                        data.data.ethnicities = data.data.ethnicities.map(x => x.ethnicity);
                        this.form.fill(data.data);
                    })
                    .catch(e => {});
            },
        },

        async mounted() {
        },
    }
</script>

<style>

</style>
