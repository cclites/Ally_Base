<template>
    <div>
        <div v-if="client && hasAdjoiningCaregivers" class="card">
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
            'client': {},
            'shift': {},
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
                return this.before.length > 0 || this.after.length > 0;
            },
        },

        methods: {
            async fetch() {
                this.before = [];
                this.after = [];

                let url = `/caregiver/schedules/${this.client}/adjoining`;
                if (this.shift) {
                    url += `?shift=${this.shift}`;
                }
                axios.get(url)
                    .then( ({ data }) => {
                        this.before = data.before;
                        this.after = data.after;
                    })
                    .catch(() => {});
            },

            formatShiftTime(schedule) {
                let start = moment(schedule.starts_at).format('h:mm');
                let end = moment(schedule.starts_at).add(schedule.duration, 'minutes').format('h:mm');
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
