<template>
    <b-row>
        <b-col>
            <b-card 
                header="Miscellaneous"
                header-bg-variant="info"
                header-text-variant="white"
            >
                <div v-if="customs.length > 0">
                    <hr />
                        <custom-field-form :form="options" :fields="customs" />
                    <b-form-group>
                        <b-btn @click="update">Save</b-btn>
                    </b-form-group>
                </div>
                <div v-else>
                    <p class="text-center">There are currently no custom fields for clients.</p>
                </div>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        props: {
            client: {
                type: Object,
                required: true,
            }
        },
        
        async mounted() {
            try {
                const {data} = await axios.get('/business/custom-fields?type=client');
                const options = {};

                // Populate custom fields
                data.forEach(({key, default_value}) => {
                    const clientFieldValue = this.client.meta.find(field => key == field.key);
                    options[key] = clientFieldValue ? clientFieldValue.value : default_value;
                });

                this.customs = data;
                this.options = new Form(options);
            }catch(error) {}
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
            update() {
                this.options.post(`/business/custom-fields/client/${this.client.id}`);
            },
        },
    }
</script>