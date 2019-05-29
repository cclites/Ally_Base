<template>
    <li class="nav-item dropdown pr-2">
        <a class="nav-link dropdown-toggle text-muted text-muted" id="tasksDropdown" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fa fa-check-square notification-icon"></i>
            <span class="badge badge-warning badge-notifications" v-if="tasks.length">{{ tasks.length }}</span>
            <b-tooltip target="tasksDropdown" placement="left" show title="You have been assigned tasks" v-if="showTooltip"></b-tooltip>
        </a>
        <div class="dropdown-menu dropdown-menu-right mailbox scale-up">
            <ul>
                <li>
                    <div class="drop-title">My Tasks</div>
                </li>
                <li>
                    <div class="message-center">
                        <!-- Message -->
                        <a v-for="item in items" :href="url" :key="item.id">
                            <div class="btn btn-danger btn-circle"><i class="fa fa-check"></i></div>
                            <div class="mail-content">
                                <h5>{{ item.name }}</h5>
                                <span class="mail-desc">{{ item.notes }}</span> 
                                <span v-if="item.due_date" class="time">Due {{ formatDateFromUTC(item.due_date) }}</span>
                            </div>
                        </a>
                        <a href="javascript:void(0);" v-if="! tasks.length">
                            <b>No open tasks</b>
                        </a>
                    </div>
                </li>
                <li>
                    <a class="nav-link text-center" :href="url"> <strong>View all tasks</strong> <i class="fa fa-angle-right"></i> </a>
                </li>
            </ul>
        </div>
    </li>
</template>

<script>
    import { mapGetters } from 'vuex';
    import FormatsDates from "../mixins/FormatsDates";
    export default {
        mixins: [ FormatsDates ],

        props: {
            role: {
                type: String,
                default: 'business',
            },
        },

        data() {
            return {
            };
        },

        computed: {
            ...mapGetters({
                tasks: 'tasks/tasks',
            }),

            count() {
                return this.tasks.length;
            },

            items() {
                let maxTitle = 36;
                let maxDescription = 72;
                return this.tasks.map((task) => {
                    let data = JSON.parse(JSON.stringify(task));
                    if (data.notes && data.notes.length > maxDescription) {
                        data.notes = data.notes.substring(0, maxDescription) + '..';
                    }
                    if (data.name.length > maxTitle) {
                        data.name = data.name.substring(0, maxTitle) + '..';
                    }
                    return data;
                })
            },

            showTooltip() {
                // Suppressed always temporarily
                return false;
            },

            url() {
                return this.role == 'caregiver' ? '/tasks' : '/business/tasks';
            },
        },

        methods: {
        },

        async mounted() {
            await this.$store.dispatch('tasks/start', this.role);
        },
    }
</script>

<style>
    .badge-notifications {
        position: absolute;
        top: 18px;
        left: 34px;
        font-size: 13px;
    }
    .mdi-message {
        font-size: 24px;
        margin-right: -7px;
        padding-left: 7px;
    }
</style>