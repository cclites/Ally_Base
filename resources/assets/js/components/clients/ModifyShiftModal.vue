<template>
    <b-modal title="Modify Shift" v-model="show" size="lg" class="modal-fit-more" hide-footer style="overflow-y: auto;">
        <b-container>
            <business-shift
                role="client"
                v-if="shift"
                :shift="shift"
                :activities="activities"
                :admin="0"
                :caregiver="shift.caregiver"
                :client="shift.client"
                :payment_type="payment_type"
                @shift-updated="$emit('shift-updated', shift.id)"
            ></business-shift>
        </b-container>
    </b-modal>
</template>

<script>
    export default {
        data() { 
            return {
                shift: {caregiver: {}},
                payment_type: {},
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
                await axios.get(`/unconfirmed-shifts/${this.shift_id}`)
                    .then( ({ data }) => {
                        this.shift = data;
                    });
            },

            loadClientPaymentType() {
                axios.get('/payment-type').then(response => {
                    this.payment_type = response.data;
                });
            },
        },

        watch: {
            async shift_id(newVal, oldVal) {
                if (newVal && newVal != oldVal) {
                    await this.load();
                }
            }
        },

        mounted() {
            this.loadClientPaymentType();
        }
    }
</script>
