<template>
    <b-card :header="title"
            header-bg-variant="info"
            header-text-variant="white"
    >
        <form @submit.prevent="submitForm()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Care Plan Name" label-for="name">
                        <b-form-input
                            id="name"
                            name="name"
                            type="text"
                            v-model="form.name"
                            >
                        </b-form-input>
                        <input-help :form="form" field="name" text="Enter the name of the care plan."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <h5>Activities of Daily Living</h5>
                    <input-help :form="form" field="activities" text="Check off the activities of daily living that are associated with this care plan."></input-help>
                    <b-row>
                        <b-col cols="12" md="6">
                            <div class="form-check">
                                <label class="custom-control custom-checkbox" v-for="activity in leftHalf" style="clear: left; float: left;">
                                    <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                                </label>
                            </div>
                        </b-col>
                        <b-col cols="12" md="6">
                            <div class="form-check">
                                <label class="custom-control custom-checkbox" v-for="activity in rightHalf" style="clear: left; float: left;">
                                    <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                                </label>
                            </div>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button id="complete-clock-out" variant="success" type="submit">{{ buttonTitle }}</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {
            'plan': {},
            'activities': {},
        },

        data() {
            return {
                form: new Form({
                    name: (this.plan) ? this.plan.name : '',
                    activities: [],
                }),
            }
        },

        mounted() {
            this.form.activities = this.getPlanActivityList();
        },

        computed: {
            leftHalf() {
                return this.getHalfOfActivities(true);
            },
            rightHalf() {
                return this.getHalfOfActivities(false);
            },
            buttonTitle() {
                return (this.plan) ? 'Save Plan' : 'Create Plan';
            },
            title() {
                return (this.plan) ? 'Edit Care Plan' : 'Add a New Care Plan';
            }
        },

        methods: {
            getHalfOfActivities(leftHalf = true)
            {
                let half_length = Math.ceil(this.activities.length / 2);
                let clone = this.activities.slice(0);
                let left = clone.splice(0,half_length);
                return (leftHalf) ? left : clone;
            },
            getPlanActivityList() {
                let list = [];
                for (let activity of this.plan.activities) {
                    list.push(activity.id);
                }
                return list;
            },
            submitForm() {
                let component = this;
                let url = '/business/care_plans';
                let method = 'post';
                if (component.plan) {
                    url = url + '/' + component.plan.id;
                    method = 'patch';
                }
                component.form.submit(method, url);
            },
        }

    }
</script>
