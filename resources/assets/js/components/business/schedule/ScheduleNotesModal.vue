<template>
    <b-modal :title="eventTitle" v-model="showModal" size="md">
        <b-container fluid>
            <b-row>
                <b-col>
                    <b-form-radio-group v-model="form.noteType" name="radioSubComponent">
                        <div><b-form-radio value="1">Client Canceled</b-form-radio></div>
                        <div><b-form-radio value="2">Caregiver Canceled</b-form-radio></div>
                        <div><b-form-radio value="3">Open Shift (will remove assigned CG)</b-form-radio></div>
                        <div><b-form-radio value="4">Other</b-form-radio></div>
                        
                        <b-textarea id="notes"
                                    :rows="3"
                                    :disabled="form.noteType != 4"
                                    v-model="form.notes"
                                    placeholder="Other Notes"
                        />
                    </b-form-radio-group>
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <b-btn variant="info" @click="save()" :disabled="busy">
                <i class="fa fa-spinner fa-spin" v-show="busy"></i>
                Save
            </b-btn>
            <b-btn variant="danger" @click="deleteNote()" :disabled="busy">
                <i class="fa fa-spinner fa-spin" v-show="busy"></i>
                Delete Note
            </b-btn>
            <b-btn variant="default" @click="showModal = false">Cancel</b-btn>
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
                    noteType: 1,
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
                return `Note for ${this.event.caregiver} on ${this.startDate}`;
            }
        },

        mounted() {
        },

        methods: {
            save() {
                let url = '/business/schedule/bulk_update';
                this.submitting = true;
                this.form.post(url)
                    .then(response => {
                        this.$emit('refresh-events');
                        this.showModal = false;
                        this.submitting = false;
                    })
                    .catch(error => {
                        this.submitting = false;
                    });
            },

            deleteNote() {

            },
        },

        watch: {
            event() {
                this.form = new Form({
                    noteType: 1,
                    notes: '',
                });
            },
        },
    }
</script>
