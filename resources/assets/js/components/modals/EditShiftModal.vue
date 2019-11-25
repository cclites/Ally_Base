<template>
    <b-modal title="Edit Shift" v-model="showModal" size="xl" class="modal-fit-more" hide-footer style="overflow-y: auto;" :no-close-on-backdrop="true" @hidden="$emit('closed')">
        <b-container>
            <loading-card text="Loading Data" v-if="loading"></loading-card>
            <business-shift
                v-if="shift && !loading"
                :shift="shift"
                :activities="activities"
                :admin="1"
                :caregiver="shift.caregiver"
                :client="shift.client"
                @shift-updated="$emit('shift-updated', shift.id)"
                @shift-deleted="$emit('shift-deleted', shift.id)"
                :show-inactive-clients="showInactiveClients"
                :show-inactive-caregivers="showInactiveCaregivers"
            ></business-shift>
        </b-container>
    </b-modal>
</template>

<script>
    export default {
        data() { 
            return {
                shift: {},
                isMounted: false,
                loading: false,
            }
        },

        props: {
            value: {},
            shift_id: null,
            activities: {
                type: Array,
                default: [],
            },
            showInactiveClients: {
                type: Boolean,
                default: false,
            },
            showInactiveCaregivers: {
                type: Boolean,
                default: false,
            },
        },

        computed: {
            showModal: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
            submitting() {
                // return this.isMounted ? this.$refs.businessShift.submitting : false;
                return false;
            }
        },

        methods: {
            async loadData() {
                await axios.get(`/business/shifts/${this.shift_id}`)
                    .then( ({ data }) => {
                        this.shift = data;
                    });
            },
        },

        async mounted() {
        },

        watch: {
            async shift_id(newVal, oldVal) {
                if (newVal && newVal != oldVal) {
                    this.loading = true;
                    try {
                        await this.loadData();
                        this.isMounted = true;
                        this.loading = false;
                    }
                    catch (e) {
                        alert('Error loading shift details.');
                        console.log(e);
                        this.showModal = false;
                    }
                }
            }
        }
    }
</script>
