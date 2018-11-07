<template>
    <div>
        <div v-if="activities.length > 0">
            <h5>Service Needs / ADLs for this visit</h5>
            <ul>
                <li v-for="activity in activities.sort((a,b) => a.code - b.code)" :key="activity.id">
                    {{ activity.code }} - {{ activity.name }}
                </li>
            </ul>
        </div>
        <div v-if="carePlan.notes">
            <h5>Service Needs Notes</h5>
            <p class="notes">{{ carePlan.notes }}</p>
        </div>
    </div>
</template>

<script>
    export default {
        name: "CaregiverCarePlan",
        props: {
            carePlan: {
                type: Object,
                required: true,
            },
            activities: {
                type: Array,
                default: [],
            },
        }
    }
</script>

<style scoped>
    h5 {
        text-decoration: underline;
    }
    p.notes {
        white-space: pre-line;
    }
</style>