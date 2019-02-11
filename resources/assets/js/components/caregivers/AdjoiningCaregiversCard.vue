<template>
    <div>
        <div v-if="hasAdjoiningCaregivers" class="card">
            <div class="card-body">
                <div v-if="before" class="mb-4">
                    <h4 class="card-title">Previous Shift Caregiver(s):</h4>
                    <div v-for="item in before" :key="item.id" class="mb-1">
                        <strong>{{ item.caregiver.name }}</strong> - {{ formatShiftTime(item) }}
                        <div v-if="item.caregiver.phone_number">Phone: {{ item.caregiver.phone_number.number }}</div>
                    </div>
                </div>
                <div v-if="after">
                    <h4 class="card-title">Next Shift Caregiver(s):</h4>
                    <div v-for="item in after" :key="item.id" class="mb-1">
                        <strong>{{ item.caregiver.name }}</strong> - {{ formatShiftTime(item) }}
                        <div v-if="item.caregiver.phone_number">Phone: {{ item.caregiver.phone_number.number }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import FormatsDates from "../../mixins/FormatsDates";
    import AuthUser from "../../mixins/AuthUser";
    export default {
        mixins: [ FormatsDates, AuthUser ],

        props: {
            client: {
                type: String,
                required: true,
            },
            shift: {
                type: String,
                default: '',
                required: false,
            },
            autoLoad: {
                type: Boolean,
                default: false,
                required: false,
            },
        },

        data() {
            return {
                before: [],
                after: [],
            }
        },

        computed: {
            hasAdjoiningCaregivers() {
                return this.before != [] && this.after != [];
            },
        },

        methods: {
            async fetch() {
                this.before = [];
                this.after = [];

                let response = await axios.get(`/caregiver/schedules/${this.client}/adjoining?shift=${this.shift}`);
                if (response.data.before) {
                    // filter results to not show the current logged in caregiver 
                    // for adjoining schedules.
                    this.before = response.data.before.filter(x => { return x.caregiver_id !== this.authUser.id });
                }
                if (response.data.after) {
                    this.after = response.data.after.filter(x => { return x.caregiver_id !== this.authUser.id });
                }
            },

            formatShiftTime(schedule) {
                let start = moment(schedule.starts_at.date).format('h:mm');
                let end = moment(schedule.starts_at.date).add(schedule.duration, 'minutes').format('h:mm');
                return start + '-' + end;
            },
        },

        mounted() {
            if (this.autoLoad) {
                this.fetch();
            }
        },
    }
</script>
