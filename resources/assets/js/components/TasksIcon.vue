<template>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle text-muted text-muted" id="tasksDropdown" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fa fa-check-square fa-lg"></i>
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
                tasks: [],
            }
        },

        computed: {
            count() {
                return this.tasks.length;
            },

            items() {
                let maxTitle = 36;
                let maxDescription = 72;
                return this.tasks.map(function(task) {
                    if (task.notes.length > maxDescription) {
                        task.notes = task.notes.substring(0, maxDescription) + '..';
                    }
                    if (task.name.length > maxTitle) {
                        task.name = task.name.substring(0, maxTitle) + '..';
                    }
                    return task;
                })
            },

            showTooltip() {
                // Suppressed always temporarily
                return false;
            },

            url() {
                return this.role == 'caregiver' ? '/tasks' : '/business/tasks';
            }
        },

        methods: {
            load() {
                axios.get(`${this.url}?pending=1&assigned=1`)
                    .then( ({ data }) => {
                        this.tasks = data;
                    });
            }
        },

        mounted() {
            this.load();
            setInterval(this.load, 30000);
        },
    }
</script>

<style>
    .badge-notifications {
        position: absolute;
        top: 18px;
        left: 32px;
    }
    .mdi-message {
        font-size: 24px;
        margin-right: -7px;
        padding-left: 7px;
    }
</style>