<template>
    <b-modal title="Create a Manual Shift" v-model="showModal" size="xl" class="modal-fit-more" :no-close-on-backdrop="true">
        <b-container fluid>
            <business-shift 
                :activities="activities"
                :caregiver="caregiver"
                :client="client"
                :shift="{}"
                ref="businessShift"
                @shift-created="$emit('shift-created')"
                is_modal="1"
            ></business-shift>
        </b-container>
        <div slot="modal-footer">
            <submit-button variant="success"
                           type="button"
                           @click.native="saveShift()"
                           :submitting="submitting"
                           icon="fa fa-save"
            >
                Save Shift
            </submit-button>
            <b-btn variant="default" @click="showModal = false">Close</b-btn>
        </div>
    </b-modal>
</template>

<script>
    export default {
        data() { 
            return {
                activities: [],
                isMounted: false,
            }
        },

        props: {
            value: {},
            caregiver: {
                type: Object,
                default() {
                    return {};
                }
            },
            client: {
                type: Object,
                default() {
                    return {};
                }
            },
        },

        computed: {
            showModal: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$refs.businessShift.resetForm();
                    if (this.caregiver) this.$refs.businessShift.form.caregiver_id = this.caregiver.id;
                    if (this.client) this.$refs.businessShift.form.client_id = this.client.id;
                    this.$emit('input', value);
                }
            },
            submitting() {
                return this.isMounted ? this.$refs.businessShift.submitting : false;
            }
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

            saveShift() {
                this.$refs.businessShift.saveShift();
            },

        },

        mounted() {
            this.isMounted = true;
            this.loadData();
        },
    }
</script>
