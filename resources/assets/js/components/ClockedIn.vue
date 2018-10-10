<template>
    <div>
        <b-card>
            <b-row v-if="carePlanActivities.length > 0" class="with-padding-bottom-top blue-box">
                <b-col lg="12">
                    <h5>Recommended Care Plan Activities</h5>
                    <div class="form-check">
                        <label class="large-checkbox" v-for="activity in carePlanActivities">
                            <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                        </label>
                    </div>
                </b-col>
            </b-row>
        </b-card>
    </div>
</template>

<script>
    export default {
        props: {
            'activities': Array,
            'carePlanActivityIds': Array
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

<style scoped>

</style>