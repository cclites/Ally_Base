<template>
    <b-modal title="Modify Shift" v-model="show" size="lg" class="modal-fit-more" hide-footer style="overflow-y: auto;">
        <b-container>
            <business-shift
                role="client"
                v-if="shift"
                :shift="shift"
                :in_distance="shift.checked_in_distance"
                :out_distance="shift.checked_out_distance"
                :activities="activities"
                :admin="0"
                :caregiver="shift.caregiver"
                :client="shift.client"
            ></business-shift>
        </b-container>
    </b-modal>
</template>

<script>
    export default {
        data() { 
            return {
                shift: {},
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
            show: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },
        },

        methods: {
            async load() {
                await axios.get(`/shifts/${this.shift_id}`)
                    .then( ({ data }) => {
                        this.shift = data;
                    });
            },
        },

        watch: {
            async shift_id(newVal, oldVal) {
                if (newVal && newVal != oldVal) {
                    await this.load();
                }
            }
        }
    }
</script>
