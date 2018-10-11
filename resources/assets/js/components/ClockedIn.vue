<template>
    <div>
        <b-row v-if="schedule.notes" class="with-padding-top">
            <b-col lg="12">
                <b-card title="Schedule Notes">
                    <p class="notes">{{ schedule.notes }}</p>
                </b-card>
            </b-col>
        </b-row>
        <b-row v-if="carePlan" class="with-padding-top">
            <b-col lg="12">
                <b-card title="Care Plan">
                    <div v-if="carePlanActivities.length > 0">
                        <h5>Recommended Activities</h5>
                        <ul>
                            <li v-for="activity in carePlanActivities">
                                {{ activity.code }} - {{ activity.name }}
                            </li>
                        </ul>
                    </div>
                    <div v-if="carePlan.notes">
                        <h5>Notes</h5>
                        <p class="notes">{{ carePlan.notes }}</p>
                    </div>
                </b-card>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    export default {
        props: {
            'activities': Array,
            'carePlanActivityIds': Array,
            'carePlan': Object,
            'schedule': Object,
        },

        data() {
            return {
                carePlanActivities: []
            }
        },

        mounted() {
            let component = this;
            let activities = this.activities.slice(0);

            this.carePlanActivities = activities.filter(function(activity) {
                return (component.carePlanActivityIds.findIndex(item => item === activity.id) !== -1);
            });

        },

        method: {

        },

        carePlanNotes() {

        }

    }
</script>

<style>
    h5 {
        text-decoration: underline;
    }
    p.notes {
        white-space: pre-line;
    }
</style>
