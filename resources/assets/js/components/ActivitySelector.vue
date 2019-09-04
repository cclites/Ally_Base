<template>
    <div>
        <b-row>
            <b-col cols="12" md="6">
                <div class="form-check">
                    <label class="custom-control custom-checkbox" v-for="activity in leftHalfActivities" :key="activity.id" style="clear: left; float: left;">
                        <input type="checkbox" class="custom-control-input" v-model="selectedActivities" :value="activity.id">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                    </label>
                </div>
            </b-col>
            <b-col cols="12" md="6">
                <div class="form-check">
                    <label class="custom-control custom-checkbox" v-for="activity in rightHalfActivities" :key="activity.id" style="clear: left; float: left;">
                        <input type="checkbox" class="custom-control-input" v-model="selectedActivities" :value="activity.id">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                    </label>
                </div>
            </b-col>
        </b-row>
    </div>
</template>

<script>
    export default {
        props: {
            activities: {
                type: Array,
                default: () => [],
            },
            value: {
                type: Array,
                default: () => [],
            },
        },
        
        data() {
            return {
                activityList: [],
                selectedActivities: [],
            };
        },
        
        computed: {
            leftHalfActivities() {
                return this.getHalfOfActivities(true);
            },
            
            rightHalfActivities() {
                return this.getHalfOfActivities(false);
            },
        },
        
        methods: {
            getHalfOfActivities(leftHalf = true)
            {
                let half_length = Math.ceil(this.activityList.length / 2);
                let clone = this.activityList.slice(0);
                let left = clone.splice(0,half_length);
                return (leftHalf) ? left : clone;
            },
        },

        created() {
            this.selectedActivities = this.value;
            if (this.activities) {
                this.activitiyList = this.activities;
            } else {
                // Fetch the current businesses activity list
                axios.fetch(`/business/activities`)
                    .then( ({ data }) => {
                        this.activitiyList = data;
                    })
                    .catch(() => {
                        this.activityList = [];
                    });
            }
        },
        
        watch: {
            selectedActivities(newValue, oldValue) {
                this.$emit('input', newValue);
            }
        },
    }
</script>

<style>
</style>