<template>
    <b-modal title="Convert Scheduled Shift" v-model="showModal">
        <b-container fluid>
            <b-row>
                Convert this scheduled shift to an actual shift?
                <br />
                Date: {{ formatDate(selectedItem.Day) }}
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
                        this.$emit('convert', this.selectedItem.key);
                        this.showModal = false;
                    });
            }
        },
    }
</script>
