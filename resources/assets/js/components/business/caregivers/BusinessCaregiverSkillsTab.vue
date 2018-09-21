<template>
    <b-row>
        <b-col>
            <b-card header="Skills"
                    header-bg-variant="info"
                    header-text-variant="white"
                    title="Check any that apply and click Save."
                    id="skills-tab"
            >
                <loading-card v-show="loading"></loading-card>
                <b-row>
                    <b-col lg="4" v-for="activity in activities" :key="activity.id">
                        <b-form-group>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" v-model="skills" :value="activity.id">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">{{ activity.name | capitalize }}</span>
                            </label>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col>
                        <b-btn variant="success" @click="saveSkills()">Save</b-btn>
                    </b-col>
                </b-row>
            </b-card>
        </b-col>
    </b-row>
</template>

<script>
    export default {
        props: ['caregiver'],

        data() {
            return{
                activities: [],
                skills: this.getSkills(),
                loading: false,
            }
        },

        mounted() {
            this.loadActivities();
        },

        methods: {
            async loadActivities() {
                this.loading = true;
                const response = await axios.get('/business/activities?json=1');
                this.activities = response.data;
                this.loading = false;
            },

            getSkills() {
                if (!this.caregiver.skills) return [];
                return this.caregiver.skills.map(skill => skill.id);
            },

            saveSkills() {
                let form = new Form({skills: this.skills});
                form.put('/business/caregivers/' + this.caregiver.id + '/skills');
            }
        }
    }
</script>

<style>

</style>
