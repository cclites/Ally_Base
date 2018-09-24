<template>
    <b-modal title="Edit Shift" v-model="showModal" size="lg" class="modal-fit-more" hide-footer style="overflow-y: auto;">
        <b-container>
            <business-shift
                v-if="shift"
                :shift="shift"
                :in_distance="shift.checked_in_distance"
                :out_distance="shift.checked_out_distance"
                :activities="activities"
                :admin="1"
                :caregiver="shift.caregiver"
                :client="shift.client"
                @shift-updated="$emit('shift-updated', shift.id)"
                @shift-deleted="$emit('shift-deleted', shift.id)"
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
            }
        },

        props: {
            value: {},
            shift_id: null,
            activities: {
                type: Array,
                default: [],
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
                    await this.loadData();
                    this.isMounted = true;
                }
            }
        }
    }
</script>
