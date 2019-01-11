<template>
    <b-row>
        <b-col>
            <b-card header="Miscellaneous"
                    header-bg-variant="info"
                    header-text-variant="white">
                <b-form-group>
                    <b-form-textarea v-model="form.misc" rows="3"></b-form-textarea>
                </b-form-group>
                <div v-if="customs.length > 0">
                    <hr />
                    <custom-field-form :form="options" :fields="customs" />
                </div>
                <b-form-group>
                    <b-btn @click="updateCaregiver">Save</b-btn>
                </b-form-group>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        props: ['misc', 'caregiver'],
        
        async mounted() {
            try {
                const {data} = await axios.get('/business/custom-fields?type=caregiver');
                const options = {};

                // Populate custom fields
                data.forEach(({key, default_value}) => {
                    const caregiverFieldValue = this.caregiver.meta.find(field => key == field.key);
                    const defaultVal = default_value || '';
                    options[key] = caregiverFieldValue ? caregiverFieldValue.value : defaultVal;
                });

                this.customs = data;
                this.options = new Form(options);
            }catch(error) {
                console.error(error)
            }
        },

        data() {
            return{
                form: new Form({
                    misc: this.misc,
                }),
                options: new Form({}),
                customs: [],
            };
        },

        methods: {
            updateCaregiver() {
                const {id} = this.caregiver;

                this.form.put(`/business/caregivers/${id}/misc`);
                this.options.post(`/business/custom-fields/caregiver/${id}`);
            }
        }

    }
</script>