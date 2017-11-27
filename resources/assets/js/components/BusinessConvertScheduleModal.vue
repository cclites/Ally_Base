<template>
    <b-modal title="Convert Scheduled Shift" v-model="showModal">
        <b-container fluid>
            <b-row>
                <b-col sm="12">
                    Convert this scheduled shift to an actual shift?

                    <b>Date:</b> {{ formatDate(selectedItem.Day) }}<br />
                    <b>Time:</b> {{ formatTime(selectedItem.Day) }}<br />
                    <b>Hours:</b> {{ selectedItem.Hours }}<br />
                    <b>Client:</b> {{ selectedItem.Client }}<br />
                    <b>Caregiver:</b> {{ selectedItem.Caregiver }}<br />
                </b-col>
            </b-row>
        </b-container>
        <div slot="modal-footer">
            <b-btn variant="default" @click="showModal=false">Close</b-btn>
            <b-btn variant="info" @click="save()">Convert</b-btn>
        </div>
    </b-modal>
</template>

<script>
    import FormatDates from '../mixins/FormatsDates';

    export default {
        mixins: [FormatDates],

        props: {
            value: {},
            selectedItem: {},
        },

        data() {
            return {
                form: new Form({
                    date: null,
                })
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
        },

        methods: {
            save() {
                let method = 'post';
                let url = '/business/shifts/convert/' + this.selectedItem.schedule_id;
                this.form.date = moment(this.selectedItem['Day']).format('YYYY-MM-DD');
                this.form.submit(method, url)
                    .then(response => {
                        this.$emit('convert', this.selectedItem.key, response.data.data.id);
                        this.showModal = false;
                    });
            }
        },
    }
</script>
