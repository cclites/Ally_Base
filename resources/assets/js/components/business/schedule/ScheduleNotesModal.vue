<template>
    <b-modal :title="eventTitle" v-model="showModal" size="md">
        <div class="p-3">
            <h5>Status:</h5>
            <b-form-radio-group v-model="form.status" name="status">
                <div><b-form-radio value="OK">OK</b-form-radio></div>
                <div><b-form-radio value="CLIENT_CANCELED">Client Canceled</b-form-radio></div>
                <div><b-form-radio value="CAREGIVER_CANCELED">Caregiver Canceled</b-form-radio></div>
            </b-form-radio-group>

            <h5 class="mt-3">Notes:</h5>
            <b-textarea id="notes"
                        :rows="3"
                        v-model="form.notes"
            />
        </div>
        
        <div slot="modal-footer">
            <b-btn variant="info" @click="save()" :disabled="busy">
                <i class="fa fa-spinner fa-spin" v-show="busy"></i>
                Save
            </b-btn>
            <b-btn variant="default" @click="showModal = false">Close</b-btn>
        </div>
    </b-modal>
</template>

<script>
    import FormatsDates from "../../../mixins/FormatsDates";
    export default {
        mixins: [FormatsDates],

        props: {
            value: {},
            'event': {
                type: Object,
                default: {},
            },
        },

        data() {
            return {
                busy: false,
                form: new Form({
                    id: '',
                    status: 'OK',
                    notes: '',
                }),
            }
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

            startDate() {
                return this.formatDateFromUTC(this.event.start);
            },

            eventTitle() {
                return `Note for ${this.event.client} on ${this.startDate}`;
            }
        },

        methods: {
            save() {
                let url = `/business/schedule/${this.form.id}/status`;
                this.busy = true;
                this.form.patch(url)
                    .then(response => {
                        this.$emit('refresh');
                        this.showModal = false;
                        this.busy = false;
                    })
                    .catch(e => {
                        this.busy = false;
                    });
            },
        },

        watch: {
            showModal() {
                this.form = new Form({
                    id: this.event.id,
                    status: this.event.status || 'OK',
                    notes: this.event.note || '',
                });
            },
        },
    }
</script>
