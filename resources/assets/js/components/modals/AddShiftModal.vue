<template>
    <b-modal title="Create a Manual Shift" v-model="showModal" size="lg" class="modal-fit-more">
        <b-container fluid>
            <business-shift 
                :activities="activities"
                :caregiver="caregiver"
                :client="client"
                ref="businessShift"
            ></business-shift>
        </b-container>
        <div slot="modal-footer">
            <slot name="buttons"></slot>
        </div>
    </b-modal>
</template>

<script>
    export default {
        data() { 
            return {
                activities: [],
            }
        },

        props: {
            value: {},
            caregiver: {},
            client: {},
        },

        computed: {
            showModal: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$refs.businessShift.resetForm();
                    this.$refs.businessShift.form.caregiver_id = this.caregiver;
                    this.$refs.businessShift.form.client_id = this.client;
                    this.$emit('input', value);
                }
            },
        },

        methods: {
            loadData() {
                axios.get('/business/activities')
                    .then(response => {
                        console.log('fetched activities');
                        if (Array.isArray(response.data)) {
                            this.activities = response.data;
                        }
                        else {
                            this.activities = [];
                        }
                    }).catch(e => {
                        console.log('axios error:');
                        console.log(e);
                    });
            },
        },

        mounted() {
            this.loadData();
        },
    }
</script>
