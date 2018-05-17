<template>
    <b-modal :title="modalTitle" v-model="showModal" size="lg">
        <b-container fluid>
            <b-row>
                <b-col md="12">

                    <!-- start_time -->
                    <b-form-group label="Clocked In" label-for="start_time">
                        <time-picker v-model="form.start_time" placeholder="HH:MM"></time-picker>
                        <input-help :form="form" field="start_time" text=""></input-help>
                    </b-form-group>

                    <!-- end_time -->
                    <b-form-group label="Clocked Out" label-for="end_time">
                        <time-picker v-model="form.end_time" placeholder="HH:MM"></time-picker>
                        <input-help :form="form" field="end_time" text=""></input-help>
                    </b-form-group>

                    <!-- activities -->
                    <b-form-group label="Activities Performed Out" label-for="">
                        <input-help :form="form" field="activities" text=""></input-help>
                        <div class="form-check">
                            <label class="custom-control custom-checkbox" v-for="activity in activities" :key="activity.id" style="clear: left; float: left;">
                                <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                            </label>
                        </div>
                    </b-form-group>

                    <!-- mileage -->
                    <b-form-group label="Mileage" label-for="mileage">
                        <b-form-input
                                id="mileage"
                                name="mileage"
                                type="number"
                                v-model="form.mileage"
                                step="any"
                                min="0"
                                max="1000"
                        />
                        <input-help :form="form" field="mileage" text=""></input-help>
                    </b-form-group>
                    
                    <!-- other_expenses -->
                    <b-form-group label="Other Expenses" label-for="other_expenses">
                        <b-form-input
                                id="other_expenses"
                                name="other_expenses"
                                type="number"
                                v-model="form.other_expenses"
                                step="any"
                                min="0"
                                max="1000"
                        />
                        <input-help :form="form" field="other_expenses" text=""></input-help>
                    </b-form-group>

                    <b-form-group label="Notes" label-for="caregiver_comments">
                        <b-textarea
                                id="caregiver_comments"
                                name="caregiver_comments"
                                :rows="4"
                                v-model="form.caregiver_comments"
                        ></b-textarea>
                        <input-help :form="form" field="caregiver_comments" text=""></input-help>
                    </b-form-group>

                </b-col>
            </b-row>
        </b-container>

        <div slot="modal-footer">
            <b-button variant="success" type="submit" @click="updateShift()">Save</b-button>
            <b-btn variant="default" @click="showModal = false">Close</b-btn>
        </div>
    </b-modal>
</template>

<script>
    export default {
        props: {
            value: {},
            entry: { type: Object, default: {} },
            activities: { type: Array, default: [] },
        },

        data: () => ({
            form: new Form({}),
        }),

        computed: {
            showModal: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value);
                }
            },

            modalTitle() {
                if (! this.form.date) {
                    return '';
                }

                return this.dow(this.form.date, true) + ' ' + moment(this.form.date).format('M/D/YYYY');
            },

            defaultRate() {
                return this.entry.client.caregiver_hourly_rate || 0;
            },

            defaultFee() {
                return this.entry.client.provider_hourly_fee || 0;
            },
        },

        mounted() {
        },

        methods: {
        },

        watch: {
            entry() {
                this.form = new Form(this.entry);
            },
        },

    }
</script>
